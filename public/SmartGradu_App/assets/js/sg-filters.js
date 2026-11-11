(function () {
	function normalize(value) {
		return String(value || "")
			.toLowerCase()
			.trim();
	}

	function getMatchedRows(root) {
		var q = normalize(root.querySelector("[data-filter-q]") && root.querySelector("[data-filter-q]").value);
		var level = normalize(root.querySelector("[data-filter-level]") && root.querySelector("[data-filter-level]").value);
		var programme = normalize(root.querySelector("[data-filter-programme]") && root.querySelector("[data-filter-programme]").value);
		var type = normalize(root.querySelector("[data-filter-type]") && root.querySelector("[data-filter-type]").value);
		var matched = [];

		root.querySelectorAll("[data-filter-item]").forEach(function (row) {
			var hay = normalize(row.getAttribute("data-search"));
			var rowLevel = normalize(row.getAttribute("data-level"));
			var rowProgramme = normalize(row.getAttribute("data-programme"));
			var rowType = normalize(row.getAttribute("data-type"));

			var matchQ = !q || hay.indexOf(q) !== -1;
			var matchLevel = !level || rowLevel === level;
			var matchProgramme = !programme || rowProgramme === programme;
			var matchType = !type || rowType === type;

			if (matchQ && matchLevel && matchProgramme && matchType) {
				matched.push(row);
			}
		});

		return matched;
	}

	function renderLoadMore(root, visible, total) {
		var wrap = root.querySelector("[data-load-more-wrap]");
		var btn = root.querySelector("[data-load-more]");
		if (!wrap || !btn) return;

		if (visible >= total) {
			wrap.style.display = "none";
			return;
		}

		wrap.style.display = "";
		var remaining = total - visible;
		btn.textContent = "Load more (" + remaining + ")";
	}

	function applyListFilter(root, resetVisible) {
		var matched = getMatchedRows(root);
		var perPage = parseInt(root.getAttribute("data-per-page") || "10", 10);
		if (perPage < 1) perPage = 10;

		var visibleLimit = parseInt(root.getAttribute("data-visible-count") || String(perPage), 10);
		if (resetVisible) {
			visibleLimit = perPage;
		}
		if (visibleLimit < perPage) visibleLimit = perPage;
		if (visibleLimit > matched.length) visibleLimit = matched.length;

		root.setAttribute("data-visible-count", String(visibleLimit));

		var visibleCount = 0;
		var gpaSum = 0;
		var gpaCount = 0;

		root.querySelectorAll("[data-filter-item]").forEach(function (row) {
			row.style.display = "none";
		});

		matched.forEach(function (row, index) {
			var gpa = parseFloat(row.getAttribute("data-gpa"));
			if (!isNaN(gpa)) {
				gpaSum += gpa;
				gpaCount += 1;
			}

			if (index < visibleLimit) {
				row.style.display = "";
				visibleCount += 1;
				var numCell = row.querySelector("[data-row-num]");
				if (numCell) {
					numCell.textContent = String(index + 1);
				}
			}
		});

		root.querySelectorAll("[data-filter-count]").forEach(function (el) {
			el.textContent = String(matched.length);
		});

		var avgEl = root.querySelector("[data-filter-avg]");
		if (avgEl) {
			avgEl.textContent = gpaCount ? (gpaSum / gpaCount).toFixed(2) : "0.00";
		}

		var empty = root.querySelector("[data-filter-empty]");
		if (empty) {
			empty.style.display = matched.length ? "none" : "";
		}

		renderLoadMore(root, visibleCount, matched.length);
	}

	function loadMore(root) {
		var perPage = parseInt(root.getAttribute("data-per-page") || "10", 10);
		var visibleLimit = parseInt(root.getAttribute("data-visible-count") || String(perPage), 10);
		root.setAttribute("data-visible-count", String(visibleLimit + perPage));
		applyListFilter(root, false);
	}

	function clearFilters(root) {
		root.querySelectorAll("[data-filter-q]").forEach(function (el) {
			el.value = "";
		});

		root.querySelectorAll("[data-filter-level], [data-filter-programme], [data-filter-type]").forEach(function (el) {
			el.value = "";
			if (window.jQuery && jQuery(el).hasClass("select2-hidden-accessible")) {
				jQuery(el).val(null).trigger("change");
			} else {
				el.dispatchEvent(new Event("change", { bubbles: true }));
			}

			var trigger = el.closest(".sg-sheet-field");
			if (trigger) {
				var valueEl = trigger.querySelector(".sg-sheet-trigger-value");
				var placeholder = el.getAttribute("data-placeholder") || "Select";
				if (valueEl) valueEl.textContent = placeholder;
				trigger.classList.remove("has-value");
			}
		});

		applyListFilter(root, true);
	}

	function bindFilters() {
		document.querySelectorAll("[data-filter-root]").forEach(function (root) {
			root.setAttribute("data-visible-count", root.getAttribute("data-per-page") || "10");

			root.querySelectorAll("[data-filter-q]").forEach(function (el) {
				el.addEventListener("input", function () {
					applyListFilter(root, true);
				});
			});

			root.querySelectorAll("[data-filter-level], [data-filter-programme], [data-filter-type]").forEach(function (el) {
				el.addEventListener("change", function () {
					applyListFilter(root, true);
				});

				if (window.jQuery) {
					jQuery(el).on("select2:select select2:clear select2:unselect change", function () {
						applyListFilter(root, true);
					});
				}
			});

			root.querySelectorAll("[data-filter-clear]").forEach(function (btn) {
				btn.addEventListener("click", function () {
					clearFilters(root);
				});
			});

			var loadMoreBtn = root.querySelector("[data-load-more]");
			if (loadMoreBtn) {
				loadMoreBtn.addEventListener("click", function () {
					loadMore(root);
				});
			}

			applyListFilter(root, true);
		});
	}

	function haversineMeters(lat1, lng1, lat2, lng2) {
		var toRad = Math.PI / 180;
		var dLat = (lat2 - lat1) * toRad;
		var dLng = (lng2 - lng1) * toRad;
		var a =
			Math.sin(dLat / 2) * Math.sin(dLat / 2) +
			Math.cos(lat1 * toRad) * Math.cos(lat2 * toRad) * Math.sin(dLng / 2) * Math.sin(dLng / 2);
		return 2 * 6371000 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
	}

	function formatDistance(meters) {
		if (meters < 1000) return Math.round(meters) + " m";
		return (meters / 1000).toFixed(1) + " km";
	}

	function formatDriveTime(meters) {
		var minutes = Math.max(1, Math.round((meters / 1000 / 25) * 60));
		if (minutes < 60) return minutes + " min";
		var h = Math.floor(minutes / 60);
		var m = minutes % 60;
		return h + " h " + m + " min";
	}

	function bindVenueRouter() {
		var from = document.getElementById("route-from");
		var to = document.getElementById("route-to");
		var distanceEl = document.getElementById("route-distance");
		var timeEl = document.getElementById("route-time");
		var summaryEl = document.getElementById("route-summary");
		var resultsEl = document.getElementById("route-results");
		var mapBtn = document.getElementById("route-open-map");
		if (!from || !to || !distanceEl || !timeEl || !mapBtn) return;

		function selectedOption(select) {
			return select.options[select.selectedIndex];
		}

		function updateRoute() {
			var a = selectedOption(from);
			var b = selectedOption(to);
			if (!a || !b || !a.value || !b.value || a.value === b.value) {
				distanceEl.textContent = "—";
				timeEl.textContent = "—";
				if (summaryEl) summaryEl.textContent = "Select From and To to view the route.";
				if (resultsEl) resultsEl.style.display = "none";
				mapBtn.href = "#";
				mapBtn.classList.add("disabled");
				return;
			}

			var meters = haversineMeters(
				parseFloat(a.dataset.lat),
				parseFloat(a.dataset.lng),
				parseFloat(b.dataset.lat),
				parseFloat(b.dataset.lng)
			);

			distanceEl.textContent = formatDistance(meters);
			timeEl.textContent = formatDriveTime(meters);
			if (summaryEl) {
				summaryEl.textContent = "From " + a.text + " to " + b.text;
			}
			if (resultsEl) resultsEl.style.display = "";
			mapBtn.href =
				"https://www.google.com/maps/dir/?api=1&origin=" +
				encodeURIComponent(a.dataset.lat + "," + a.dataset.lng) +
				"&destination=" +
				encodeURIComponent(b.dataset.lat + "," + b.dataset.lng) +
				"&travelmode=driving";
			mapBtn.classList.remove("disabled");
		}

		from.addEventListener("change", updateRoute);
		to.addEventListener("change", updateRoute);

		if (window.jQuery) {
			jQuery(from).on("select2:select select2:clear select2:unselect change", updateRoute);
			jQuery(to).on("select2:select select2:clear select2:unselect change", updateRoute);
		}

		updateRoute();
	}

	function bindLiveStreams() {
		var select = document.getElementById("stream-filter");
		var frame = document.getElementById("stream-frame");
		var status = document.getElementById("stream-status");
		var openBtn = document.getElementById("stream-open-youtube");
		var copyBtn = document.getElementById("stream-copy-url");
		var urlInput = document.getElementById("stream-url");
		if (!select || !frame || !openBtn || !copyBtn || !urlInput) return;

		function applyStream() {
			var option = select.options[select.selectedIndex];
			if (!option) return;
			var embed = option.dataset.embed || "";
			var youtube = option.dataset.youtube || "";
			var live = option.dataset.live === "1";

			frame.src = embed;
			urlInput.value = youtube;
			openBtn.href = youtube || "#";
			if (status) {
				status.textContent = live ? "Live now" : option.dataset.status || "Scheduled";
				status.classList.toggle("is-live", live);
			}
		}

		select.addEventListener("change", applyStream);
		if (window.jQuery) {
			jQuery(select).on("select2:select select2:clear select2:unselect change", applyStream);
		}

		copyBtn.addEventListener("click", function () {
			urlInput.select();
			urlInput.setSelectionRange(0, 99999);
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(urlInput.value);
			} else {
				document.execCommand("copy");
			}
			copyBtn.textContent = "Copied";
			setTimeout(function () {
				copyBtn.textContent = "Copy URL";
			}, 1400);
		});

		applyStream();
	}

	function bindAccordions() {
		document.querySelectorAll(".sg-accordion").forEach(function (accordion) {
			accordion.querySelectorAll(".sg-acc-trigger").forEach(function (btn) {
				btn.addEventListener("click", function () {
					var item = btn.closest(".sg-acc-item");
					var panel = item.querySelector(".sg-acc-panel");
					var wasOpen = item.classList.contains("is-open");

					accordion.querySelectorAll(".sg-acc-item").forEach(function (other) {
						other.classList.remove("is-open");
						var otherBtn = other.querySelector(".sg-acc-trigger");
						var otherPanel = other.querySelector(".sg-acc-panel");
						if (otherBtn) otherBtn.setAttribute("aria-expanded", "false");
						if (otherPanel) otherPanel.hidden = true;
					});

					if (!wasOpen) {
						item.classList.add("is-open");
						btn.setAttribute("aria-expanded", "true");
						if (panel) panel.hidden = false;
					}
				});
			});
		});
	}

	function boot() {
		bindFilters();
		bindVenueRouter();
		bindLiveStreams();
		bindAccordions();
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", boot);
	} else {
		boot();
	}
})();
