/**
 * Kiswahili name playback — Girl=Zuri, Boy=Daudi.
 * WebView-safe: direct TTS URLs, playbackRate=1, retries so names are not skipped.
 * Warms the next name into server/HTTP cache while the current one plays.
 */
(function () {
	"use strict";

	var ua = navigator.userAgent || "";
	var isWebView =
		/\bwv\b/i.test(ua) ||
		/Flutter|; wv\)|WebView/i.test(ua) ||
		typeof window.flutter_inappwebview !== "undefined" ||
		typeof window.FlutterWebView !== "undefined";

	function pack(key, fallback) {
		var p = window.sgGetLangPack ? window.sgGetLangPack() : null;
		return (p && p[key]) || fallback;
	}

	function itemName(item) {
		var name = item.getAttribute("data-name") || "";
		var nameEl = item.querySelector("[data-voice-name]");
		if (nameEl) name = nameEl.textContent || name;
		return String(name).trim();
	}

	function speedKey(speed) {
		if (speed < 0.92) return "slow";
		if (speed > 1.08) return "fast";
		return "normal";
	}

	function bindRoot(root) {
		var fab = root.querySelector("[data-voice-fab]");
		var sheet = root.querySelector("[data-voice-sheet]");
		var playBtn = sheet && sheet.querySelector("[data-voice-play]");
		var stopBtn = sheet && sheet.querySelector("[data-voice-stop]");
		if (!fab || !sheet || !playBtn) return;

		if (sheet.parentElement !== document.body) document.body.appendChild(sheet);
		if (fab.parentElement !== document.body) document.body.appendChild(fab);

		var gender = "female";
		var speed = 1;
		var runGender = "female";
		var runSpeed = 1;
		var queue = [];
		var index = 0;
		var audio = null;
		var runId = 0;
		var playing = false;
		var warmBusy = false;
		var primeToken = 0;
		var ready = Object.create(null); // key -> true when server/HTTP cache warmed

		function cacheKey(g, name, s) {
			return g + "|" + speedKey(s) + "|" + name;
		}

		function ttsUrl(g, text, s, bust) {
			// Relative to the current page so subdirectory installs and LAN IPs work
			// (origin + "/voice/tts" breaks under /Smart_GraduApp/public/…).
			var qs =
				"q=" +
				encodeURIComponent(text) +
				"&g=" +
				encodeURIComponent(g) +
				"&voice=" +
				encodeURIComponent(g === "male" ? "daudi" : "zuri") +
				"&s=" +
				encodeURIComponent(speedKey(s));
			if (bust) qs += "&r=" + encodeURIComponent(String(bust));
			try {
				return new URL("voice/tts?" + qs, window.location.href).href;
			} catch (e) {
				return "voice/tts?" + qs;
			}
		}

		function syncGenderUi() {
			sheet.querySelectorAll("[data-voice-gender]").forEach(function (el) {
				var g = el.getAttribute("data-voice-gender") || "female";
				el.classList.toggle("is-active", g === gender);
			});
		}

		function readGenderFromUi() {
			var active = sheet.querySelector("[data-voice-gender].is-active");
			if (active) {
				return active.getAttribute("data-voice-gender") === "male" ? "male" : "female";
			}
			return gender === "male" ? "male" : "female";
		}

		function visibleItems() {
			return Array.prototype.slice.call(root.querySelectorAll("[data-voice-item]")).filter(function (el) {
				return el.style.display !== "none";
			});
		}

		function allMatchedItems() {
			if (typeof window.sgRevealAllMatched === "function") {
				window.sgRevealAllMatched(root);
			}
			return visibleItems();
		}

		function openSheet() {
			syncGenderUi();
			sheet.hidden = false;
			sheet.setAttribute("aria-hidden", "false");
			document.body.classList.add("sg-voice-sheet-open");
			primeStart(3);
		}

		function closeSheet() {
			sheet.hidden = true;
			sheet.setAttribute("aria-hidden", "true");
			document.body.classList.remove("sg-voice-sheet-open");
		}

		sheet.querySelectorAll("[data-voice-gender]").forEach(function (btn) {
			btn.addEventListener("click", function () {
				gender = btn.getAttribute("data-voice-gender") === "male" ? "male" : "female";
				syncGenderUi();
				primeToken += 1;
				primeStart(3);
			});
		});

		sheet.querySelectorAll("[data-voice-speed]").forEach(function (btn) {
			btn.addEventListener("click", function () {
				speed = parseFloat(btn.getAttribute("data-voice-speed") || "1") || 1;
				sheet.querySelectorAll("[data-voice-speed]").forEach(function (el) {
					el.classList.toggle("is-active", el === btn);
				});
				primeToken += 1;
				primeStart(3);
			});
		});

		function clearHighlight() {
			root.querySelectorAll("[data-voice-item].is-speaking").forEach(function (el) {
				el.classList.remove("is-speaking");
			});
		}

		function setSpeakingUi(active) {
			playing = !!active;
			fab.classList.toggle("is-playing", playing);
			playBtn.hidden = playing;
			if (stopBtn) stopBtn.hidden = !playing;
			var fabIcon = fab.querySelector(".fa");
			if (fabIcon) fabIcon.className = playing ? "fa fa-stop" : "fa fa-volume-up";
			fab.setAttribute("aria-label", playing ? pack("stop", "Stop") : pack("playNames", "Play names"));
			fab.setAttribute("title", playing ? pack("stop", "Stop") : pack("playNames", "Play names"));
		}

		function stopAudio() {
			if (audio) {
				try {
					audio.onended = null;
					audio.onerror = null;
					audio.oncanplay = null;
					audio.pause();
					audio.removeAttribute("src");
					audio.load();
				} catch (e) {}
				audio = null;
			}
		}

		function stopPlayback() {
			runId += 1;
			warmBusy = false;
			primeToken += 1;
			stopAudio();
			clearHighlight();
			setSpeakingUi(false);
			queue = [];
			index = 0;
		}

		/** Pull MP3 into server + browser cache (does not need to play). */
		function warmUrl(url, key, done) {
			fetch(url, { credentials: "same-origin", cache: "force-cache" })
				.then(function (res) {
					if (!res.ok) throw new Error("tts " + res.status);
					return res.arrayBuffer();
				})
				.then(function () {
					ready[key] = true;
				})
				.catch(function () {})
				.then(function () {
					if (done) done();
				});
		}

		function primeStart(count) {
			var g = readGenderFromUi();
			gender = g;
			var s = speed;
			var names = visibleItems()
				.slice(0, count || 3)
				.map(itemName)
				.filter(Boolean);
			if (!names.length) return;

			var token = ++primeToken;
			var i = 0;

			function next() {
				if (token !== primeToken || playing) return;
				if (i >= names.length) return;
				var name = names[i++];
				var key = cacheKey(g, name, s);
				if (ready[key]) {
					next();
					return;
				}
				warmUrl(ttsUrl(g, name, s), key, function () {
					if (token === primeToken) next();
				});
			}

			next();
		}

		function warmNext(fromIndex, myRun) {
			if (warmBusy || myRun !== runId) return;
			if (fromIndex >= queue.length) return;
			var name = itemName(queue[fromIndex]);
			if (!name) {
				warmNext(fromIndex + 1, myRun);
				return;
			}
			var key = cacheKey(runGender, name, runSpeed);
			if (ready[key]) {
				warmNext(fromIndex + 1, myRun);
				return;
			}
			warmBusy = true;
			warmUrl(ttsUrl(runGender, name, runSpeed), key, function () {
				warmBusy = false;
				if (myRun === runId) warmNext(fromIndex + 1, myRun);
			});
		}

		/**
		 * Play one URL. Retries on error so a busy TTS does not skip the name.
		 * onDone(ok) — ok=false only after all retries fail.
		 */
		function playUrl(url, myRun, onDone, attempt) {
			attempt = attempt || 1;
			stopAudio();

			audio = new Audio();
			audio.preload = "auto";
			audio.volume = 1;
			audio.defaultPlaybackRate = 1;
			audio.playbackRate = 1;
			if ("preservesPitch" in audio) audio.preservesPitch = true;

			var finished = false;
			var playUrlFinal = attempt > 1 ? url + (url.indexOf("?") >= 0 ? "&" : "?") + "_try=" + attempt : url;

			function done(ok) {
				if (finished || myRun !== runId) return;
				finished = true;
				audio = null;
				window.setTimeout(function () {
					if (myRun === runId && onDone) onDone(ok);
				}, 60);
			}

			function retryOrFail() {
				if (finished || myRun !== runId) return;
				if (attempt < 3) {
					finished = true;
					audio = null;
					window.setTimeout(function () {
						if (myRun !== runId) return;
						playUrl(url, myRun, onDone, attempt + 1);
					}, 350 * attempt);
					return;
				}
				done(false);
			}

			audio.onended = function () {
				done(true);
			};
			audio.onerror = retryOrFail;

			audio.src = playUrlFinal;

			function tryPlay() {
				if (finished || myRun !== runId || !audio) return;
				var p = audio.play();
				if (p && p.catch) {
					p.catch(function () {
						window.setTimeout(function () {
							if (finished || myRun !== runId || !audio) return;
							audio.play().catch(retryOrFail);
						}, 80);
					});
				}
			}

			// Start ASAP (WebView often never fires canplay reliably).
			audio.oncanplay = tryPlay;
			tryPlay();
			window.setTimeout(tryPlay, 200);
		}

		function speakOne(item, queuePos, myRun, onDone) {
			if (!item || myRun !== runId) {
				if (onDone) onDone();
				return;
			}

			clearHighlight();
			item.classList.add("is-speaking");
			try {
				item.scrollIntoView({ behavior: "smooth", block: "center" });
			} catch (e) {}

			var name = itemName(item);
			if (!name) {
				item.classList.remove("is-speaking");
				if (onDone) onDone();
				return;
			}

			warmNext(queuePos + 1, myRun);

			var url = ttsUrl(runGender, name, runSpeed);
			playUrl(url, myRun, function () {
				item.classList.remove("is-speaking");
				ready[cacheKey(runGender, name, runSpeed)] = true;
				if (onDone) onDone();
			});
		}

		function speakNext(myRun) {
			if (myRun !== runId) return;
			if (index >= queue.length) {
				setSpeakingUi(false);
				return;
			}
			var pos = index;
			var item = queue[index++];
			speakOne(item, pos, myRun, function () {
				speakNext(myRun);
			});
		}

		function startFrom(items, startAt) {
			stopPlayback();
			gender = readGenderFromUi();
			runGender = gender === "male" ? "male" : "female";
			runSpeed = speed;
			var myRun = runId;
			queue = items;
			index = Math.max(0, startAt || 0);
			if (!queue.length || index >= queue.length) {
				closeSheet();
				window.alert(pack("noNamesToRead", "No names to read. Clear filters and try again."));
				return;
			}
			setSpeakingUi(true);
			closeSheet();
			speakNext(myRun);
		}

		function playAll() {
			startFrom(allMatchedItems(), 0);
		}

		fab.addEventListener("click", function () {
			if (playing) {
				stopPlayback();
				return;
			}
			openSheet();
		});

		sheet.querySelectorAll("[data-voice-sheet-close]").forEach(function (el) {
			el.addEventListener("click", closeSheet);
		});

		playBtn.addEventListener("click", playAll);
		if (stopBtn) stopBtn.addEventListener("click", stopPlayback);

		root.addEventListener("click", function (event) {
			var item = event.target.closest("[data-voice-item]");
			if (!item || !root.contains(item)) return;
			if (event.target.closest("a, button, input, select, textarea")) return;
			event.preventDefault();

			var items = allMatchedItems();
			var startAt = items.indexOf(item);
			if (startAt < 0) return;
			startFrom(items, startAt);
		});
	}

	function boot() {
		document.querySelectorAll("[data-voice-root]").forEach(bindRoot);
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", boot);
	} else {
		boot();
	}
})();
