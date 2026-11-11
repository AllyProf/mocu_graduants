(function () {
	function pad(n) {
		return String(n).padStart(2, "0");
	}

	function tick(root) {
		var target = new Date(root.getAttribute("data-graduation")).getTime();
		var now = Date.now();
		var diff = Math.max(0, target - now);

		var days = Math.floor(diff / 86400000);
		var hours = Math.floor((diff % 86400000) / 3600000);
		var minutes = Math.floor((diff % 3600000) / 60000);
		var seconds = Math.floor((diff % 60000) / 1000);

		var daysEl = root.querySelector('[data-unit="days"]');
		var hoursEl = root.querySelector('[data-unit="hours"]');
		var minutesEl = root.querySelector('[data-unit="minutes"]');
		var secondsEl = root.querySelector('[data-unit="seconds"]');

		if (daysEl) daysEl.textContent = String(days);
		if (hoursEl) hoursEl.textContent = pad(hours);
		if (minutesEl) minutesEl.textContent = pad(minutes);
		if (secondsEl) secondsEl.textContent = pad(seconds);
	}

	function boot() {
		var root = document.getElementById("sg-countdown");
		if (!root) return;
		tick(root);
		setInterval(function () {
			tick(root);
		}, 1000);
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", boot);
	} else {
		boot();
	}
})();
