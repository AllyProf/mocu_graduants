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

	function renderPagination(root, page, totalPages) {
		var nav = root.querySelector("[data-pagination]");
		if (!nav) return;

		if (totalPages <= 1) {
			nav.innerHTML = "";
			nav.style.display = "none";
			return;
		}

		nav.style.display = "";
		var html = "";
		html += '<button type="button" class="sg-page-btn" data-page-action="prev"' + (page <= 1 ? " disabled" : "") + ">Prev</button>";

		for (var i = 1; i <= totalPages; i++) {
			html +=
				'<button type="button" class="sg-page-btn' +
				(i === page ? " is-active" : "") +
				'" data-page="' +
				i +
				'">' +
				i +
				"</button>";
		}

		html += '<button type="button" class="sg-page-btn" data-page-action="next"' + (page >= totalPages ? " disabled" : "") + ">Next</button>";
		nav.innerHTML = html;
	}

	function applyListFilter(root, pageOverride) {
		var matched = getMatchedRows(root);
		var perPage = parseInt(root.getAttribute("data-per-page") || "0", 10);
		var usePaging = perPage > 0 && !!root.querySelector("[data-pagination]");
		var page = parseInt(root.getAttribute("data-current-page") || "1", 10);

		if (typeof pageOverride === "number") {
			page = pageOverride;
		}

		var totalPages = usePaging ? Math.max(1, Math.ceil(matched.length / perPage)) : 1;
		if (page > totalPages) page = totalPages;
		if (page < 1) page = 1;
		root.setAttribute("data-current-page", String(page));

		var start = usePaging ? (page - 1) * perPage : 0;
		var end = usePaging ? start + perPage : matched.length;

		root.querySelectorAll("[data-filter-item]").forEach(function (row) {
			row.style.display = "none";
		});

		matched.forEach(function (row, index) {
			if (index >= start && index < end) {
				row.style.display = "";
				var numCell = row.querySelector("[data-row-num]");
				if (numCell) {
					numCell.textContent = String(index + 1);
				}
			}
		});

		var count = root.querySelector("[data-filter-count]");
		if (count) {
			count.textContent = String(matched.length);
		}

		var empty = root.querySelector("[data-filter-empty]");
		if (empty) {
			empty.style.display = matched.length ? "none" : "";
		}

		renderPagination(root, page, totalPages);
	}

	function initSelect2() {
		if (!window.jQuery || !jQuery.fn || !jQuery.fn.select2) return;

		jQuery(".sg-select2").each(function () {
			var $el = jQuery(this);
			if ($el.hasClass("select2-hidden-accessible")) return;

			$el.select2({
				width: "100%",
				placeholder: $el.data("placeholder") || "Select an option",
				allowClear: true,
				minimumResultsForSearch: 0,
			});
		});
	}

	function bindFilters() {
		document.querySelectorAll("[data-filter-root]").forEach(function (root) {
			root.setAttribute("data-current-page", "1");

			root.querySelectorAll("[data-filter-q]").forEach(function (el) {
				el.addEventListener("input", function () {
					applyListFilter(root, 1);
				});
			});

			root.querySelectorAll("[data-filter-level], [data-filter-programme], [data-filter-type]").forEach(function (el) {
				el.addEventListener("change", function () {
					applyListFilter(root, 1);
				});

				if (window.jQuery) {
					jQuery(el).on("select2:select select2:clear select2:unselect change", function () {
						applyListFilter(root, 1);
					});
				}
			});

			var pager = root.querySelector("[data-pagination]");
			if (pager) {
				pager.addEventListener("click", function (event) {
					var btn = event.target.closest(".sg-page-btn");
					if (!btn || btn.disabled) return;

					var current = parseInt(root.getAttribute("data-current-page") || "1", 10);
					var action = btn.getAttribute("data-page-action");
					var nextPage = current;

					if (action === "prev") nextPage = current - 1;
					else if (action === "next") nextPage = current + 1;
					else if (btn.getAttribute("data-page")) nextPage = parseInt(btn.getAttribute("data-page"), 10);

					applyListFilter(root, nextPage);
				});
			}

			applyListFilter(root, 1);
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
		initSelect2();
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
