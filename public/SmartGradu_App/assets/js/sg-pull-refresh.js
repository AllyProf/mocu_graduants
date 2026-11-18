/**
 * MoCU Graduation Companion — mobile pull-to-refresh indicator.
 * Reloads the current page when the user pulls down past the threshold.
 */
(function () {
	"use strict";

	var THRESHOLD = 72;
	var MAX_PULL = 120;
	var startY = 0;
	var pulling = false;
	var armed = false;
	var refreshing = false;
	var indicator = null;
	var labelEl = null;
	var iconEl = null;

	function isMobile() {
		return window.matchMedia("(max-width: 991px)").matches;
	}

	function sheetOpen() {
		var sheets = document.querySelectorAll(".sg-sheet");
		for (var i = 0; i < sheets.length; i++) {
			if (!sheets[i].hasAttribute("hidden")) return true;
		}
		return false;
	}

	function scrollTop() {
		var body = document.scrollingElement || document.documentElement;
		return window.pageYOffset || body.scrollTop || document.body.scrollTop || 0;
	}

	function isAtTop() {
		if (document.body.classList.contains("sg-home-lock")) return true;
		return scrollTop() <= 1;
	}

	function t(key, fallback) {
		var pack = window.sgGetLangPack ? window.sgGetLangPack() : null;
		return (pack && pack[key]) || fallback;
	}

	function ensureIndicator() {
		if (indicator) return;
		indicator = document.createElement("div");
		indicator.className = "sg-pull-refresh";
		indicator.setAttribute("aria-hidden", "true");
		indicator.innerHTML =
			'<span class="sg-pull-refresh-inner">' +
			'<i class="fa fa-refresh" aria-hidden="true"></i>' +
			"<span class=\"sg-pull-refresh-label\"></span>" +
			"</span>";
		document.body.appendChild(indicator);
		iconEl = indicator.querySelector("i");
		labelEl = indicator.querySelector(".sg-pull-refresh-label");
	}

	function setPull(distance) {
		ensureIndicator();
		var progress = Math.min(distance / THRESHOLD, 1);
		indicator.classList.add("is-visible");
		indicator.style.setProperty("--sg-pull", String(progress));
		indicator.style.transform = "translate(-50%, " + Math.min(distance, MAX_PULL) + "px)";

		if (distance >= THRESHOLD) {
			indicator.classList.add("is-ready");
			labelEl.textContent = t("releaseRefresh", "Release to refresh");
		} else {
			indicator.classList.remove("is-ready");
			labelEl.textContent = t("pullRefresh", "Pull to refresh");
		}

		if (iconEl) {
			iconEl.style.transform = "rotate(" + Math.round(progress * 180) + "deg)";
		}
	}

	function resetPull() {
		if (!indicator) return;
		indicator.classList.remove("is-visible", "is-ready", "is-refreshing");
		indicator.style.transform = "";
		indicator.style.removeProperty("--sg-pull");
		if (iconEl) iconEl.style.transform = "";
	}

	function doRefresh() {
		refreshing = true;
		ensureIndicator();
		indicator.classList.add("is-visible", "is-refreshing");
		indicator.style.transform = "translate(-50%, 64px)";
		labelEl.textContent = t("refreshing", "Refreshing...");
		if (iconEl) iconEl.style.transform = "";
		window.setTimeout(function () {
			window.location.reload();
		}, 280);
	}

	function onStart(e) {
		if (!isMobile() || refreshing || sheetOpen()) return;
		if (!isAtTop()) return;
		if (!e.touches || e.touches.length !== 1) return;
		startY = e.touches[0].clientY;
		armed = true;
		pulling = false;
	}

	function onMove(e) {
		if (!armed || refreshing || sheetOpen()) return;
		var y = e.touches[0].clientY;
		var delta = y - startY;
		if (delta < 8 || !isAtTop()) {
			if (pulling) resetPull();
			pulling = false;
			return;
		}
		pulling = true;
		if (e.cancelable) e.preventDefault();
		setPull(delta * 0.55);
	}

	function onEnd() {
		if (!armed) return;
		armed = false;
		if (!pulling || refreshing) {
			pulling = false;
			resetPull();
			return;
		}
		var ready = indicator && indicator.classList.contains("is-ready");
		pulling = false;
		if (ready) {
			doRefresh();
		} else {
			resetPull();
		}
	}

	document.addEventListener("touchstart", onStart, { passive: true });
	document.addEventListener("touchmove", onMove, { passive: false });
	document.addEventListener("touchend", onEnd, { passive: true });
	document.addEventListener("touchcancel", onEnd, { passive: true });
})();
