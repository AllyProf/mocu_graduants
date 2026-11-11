(function () {
	function boot() {
		var root = document.getElementById("sg-hero-slider");
		if (!root) return;

		var slides = Array.prototype.slice.call(root.querySelectorAll(".sg-hero-slide"));
		var dots = Array.prototype.slice.call(root.querySelectorAll(".sg-hero-dots button"));
		if (!slides.length) return;

		var index = 0;
		var timer = null;

		function goTo(next) {
			next = (next + slides.length) % slides.length;
			if (next === index) return;

			slides[index].classList.remove("is-active");
			slides[next].classList.add("is-active");

			dots.forEach(function (dot, i) {
				dot.classList.toggle("is-active", i === next);
			});

			index = next;
		}

		function start() {
			stop();
			timer = window.setInterval(function () {
				goTo(index + 1);
			}, 5500);
		}

		function stop() {
			if (timer) {
				window.clearInterval(timer);
				timer = null;
			}
		}

		dots.forEach(function (dot) {
			dot.addEventListener("click", function () {
				goTo(parseInt(dot.getAttribute("data-slide"), 10) || 0);
				start();
			});
		});

		root.addEventListener("mouseenter", stop);
		root.addEventListener("mouseleave", start);
		start();
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", boot);
	} else {
		boot();
	}
})();
