/**
 * Kiswahili name playback — Girl=Zuri, Boy=Daudi.
 * Primes the first names when the sheet opens so Play starts with little delay.
 */
(function () {
	"use strict";

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
		var prefetch = Object.create(null); // key -> { url, gender }

		function cacheKey(g, name) {
			return g + "|" + name;
		}

		function ttsUrl(g, text) {
			return (
				"/voice/tts?q=" +
				encodeURIComponent(text) +
				"&g=" +
				encodeURIComponent(g) +
				"&voice=" +
				encodeURIComponent(g === "male" ? "daudi" : "zuri")
			);
		}

		function revokePrefetch() {
			Object.keys(prefetch).forEach(function (k) {
				try {
					URL.revokeObjectURL(prefetch[k].url);
				} catch (e) {}
			});
			prefetch = Object.create(null);
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

		function assertVoiceHeaders(res, g) {
			var gotGender = (res.headers.get("X-SG-Gender") || "").toLowerCase();
			var gotVoice = (res.headers.get("X-SG-Voice") || "").toLowerCase();
			if (g === "female") {
				if (gotGender === "male" || /daudi|rafiki/.test(gotVoice) || !/zuri|rehema/.test(gotVoice)) {
					throw new Error("not girl voice");
				}
			} else if (gotGender === "female" || /zuri|rehema/.test(gotVoice) || !/daudi|rafiki/.test(gotVoice)) {
				throw new Error("not boy voice");
			}
		}

		/** Warm first names while user picks settings (before Play). */
		function primeStart(count) {
			var g = readGenderFromUi();
			gender = g;
			var names = visibleItems()
				.slice(0, count || 2)
				.map(itemName)
				.filter(Boolean);
			if (!names.length) return;

			var token = ++primeToken;
			var i = 0;

			function next() {
				if (token !== primeToken || playing) return;
				if (i >= names.length) return;
				var name = names[i++];
				var key = cacheKey(g, name);
				if (prefetch[key] && prefetch[key].gender === g) {
					next();
					return;
				}
				fetch(ttsUrl(g, name))
					.then(function (res) {
						if (!res.ok) throw new Error("tts");
						assertVoiceHeaders(res, g);
						return res.blob();
					})
					.then(function (blob) {
						if (token !== primeToken) return;
						if (prefetch[key] && prefetch[key].url) {
							try {
								URL.revokeObjectURL(prefetch[key].url);
							} catch (e) {}
						}
						prefetch[key] = { url: URL.createObjectURL(blob), gender: g };
					})
					.catch(function () {})
					.then(function () {
						if (token === primeToken) next();
					});
			}

			next();
		}

		function openSheet() {
			syncGenderUi();
			sheet.hidden = false;
			sheet.setAttribute("aria-hidden", "false");
			document.body.classList.add("sg-voice-sheet-open");
			primeStart(2);
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
				primeToken += 1; // cancel in-flight prime for other gender
				primeStart(2);
			});
		});

		sheet.querySelectorAll("[data-voice-speed]").forEach(function (btn) {
			btn.addEventListener("click", function () {
				speed = parseFloat(btn.getAttribute("data-voice-speed") || "1") || 1;
				sheet.querySelectorAll("[data-voice-speed]").forEach(function (el) {
					el.classList.toggle("is-active", el === btn);
				});
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
					audio.pause();
					audio.removeAttribute("src");
					audio.load();
				} catch (e) {}
				audio = null;
			}
		}

		/** Stop speaking but keep primed audio for instant restart. */
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

		function stop() {
			stopPlayback();
			revokePrefetch();
		}

		function warmNext(fromIndex, myRun) {
			if (warmBusy || myRun !== runId) return;
			if (fromIndex >= queue.length) return;
			var name = itemName(queue[fromIndex]);
			if (!name) {
				warmNext(fromIndex + 1, myRun);
				return;
			}
			var key = cacheKey(runGender, name);
			if (prefetch[key] && prefetch[key].gender === runGender) {
				warmNext(fromIndex + 1, myRun);
				return;
			}
			var expectedGender = runGender;
			warmBusy = true;
			fetch(ttsUrl(runGender, name))
				.then(function (res) {
					if (!res.ok) throw new Error("tts");
					assertVoiceHeaders(res, expectedGender);
					return res.blob();
				})
				.then(function (blob) {
					if (myRun !== runId || expectedGender !== runGender) return;
					if (prefetch[key] && prefetch[key].url) {
						try {
							URL.revokeObjectURL(prefetch[key].url);
						} catch (e) {}
					}
					prefetch[key] = { url: URL.createObjectURL(blob), gender: expectedGender };
				})
				.catch(function () {})
				.then(function () {
					warmBusy = false;
					if (myRun === runId) warmNext(fromIndex + 1, myRun);
				});
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

			stopAudio();
			var rate = Math.max(0.75, Math.min(1.35, runSpeed));
			var key = cacheKey(runGender, name);
			var warmed = prefetch[key];
			var url =
				warmed && warmed.gender === runGender && warmed.url
					? warmed.url
					: ttsUrl(runGender, name);

			warmNext(queuePos + 1, myRun);

			audio = new Audio();
			audio.preload = "auto";
			audio.volume = 1;
			audio.playbackRate = rate;
			audio.src = url;

			var finished = false;
			function finish() {
				if (finished || myRun !== runId) return;
				finished = true;
				item.classList.remove("is-speaking");
				audio = null;
				if (onDone) onDone();
			}

			audio.onended = finish;
			audio.onerror = finish;
			var playPromise = audio.play();
			if (playPromise && playPromise.catch) playPromise.catch(finish);
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
			// Keep primed blobs — do not revokePrefetch (that caused start delay).
			stopPlayback();
			gender = readGenderFromUi();
			runGender = gender === "male" ? "male" : "female";
			runSpeed = speed;
			var myRun = runId;
			queue = items;
			index = Math.max(0, startAt || 0);
			if (!queue.length || index >= queue.length) return;
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
