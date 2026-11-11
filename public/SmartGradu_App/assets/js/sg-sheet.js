(function () {
	var MOBILE_MAX = 991;

	function isMobile() {
		return window.matchMedia("(max-width: " + MOBILE_MAX + "px)").matches;
	}

	function boot() {
		var sheet = document.getElementById("sg-sheet");
		if (!sheet) return;

		var titleEl = document.getElementById("sg-sheet-title");
		var searchEl = document.getElementById("sg-sheet-search");
		var listEl = document.getElementById("sg-sheet-list");
		var activeSelect = null;

		function closeSheet() {
			sheet.hidden = true;
			sheet.setAttribute("aria-hidden", "true");
			document.body.classList.remove("sg-sheet-open");
			activeSelect = null;
			if (searchEl) searchEl.value = "";
		}

		function openSheet(select) {
			activeSelect = select;
			var label = select.getAttribute("data-sheet-title");
			if (!label) {
				var lab = document.querySelector('label[for="' + select.id + '"]');
				label = lab ? lab.textContent.trim() : select.getAttribute("data-placeholder") || "Select";
			}
			if (titleEl) titleEl.textContent = label;
			if (searchEl) searchEl.value = "";
			renderOptions("");
			sheet.hidden = false;
			sheet.setAttribute("aria-hidden", "false");
			document.body.classList.add("sg-sheet-open");
			window.setTimeout(function () {
				if (searchEl) searchEl.focus();
			}, 220);
		}

		function renderOptions(query) {
			if (!activeSelect || !listEl) return;
			var q = String(query || "").toLowerCase().trim();
			var html = "";
			Array.prototype.forEach.call(activeSelect.options, function (opt, index) {
				var text = opt.textContent.trim();
				if (q && text.toLowerCase().indexOf(q) === -1) return;
				var selected = opt.selected || (!activeSelect.value && opt.value === "");
				html +=
					'<button type="button" class="sg-sheet-option' +
					(selected ? " is-selected" : "") +
					'" role="option" data-index="' +
					index +
					'" aria-selected="' +
					(selected ? "true" : "false") +
					'">' +
					'<span>' +
					text +
					"</span>" +
					(selected ? '<i class="fa fa-check" aria-hidden="true"></i>' : "") +
					"</button>";
			});
			listEl.innerHTML = html || '<div class="sg-sheet-empty">No matches</div>';
		}

		function syncTrigger(select) {
			var trigger = select.closest(".sg-sheet-field");
			if (!trigger) return;
			var valueEl = trigger.querySelector(".sg-sheet-trigger-value");
			if (!valueEl) return;
			var opt = select.options[select.selectedIndex];
			var placeholder = select.getAttribute("data-placeholder") || "Select";
			var text = opt && opt.value !== "" ? opt.textContent.trim() : placeholder;
			valueEl.textContent = text;
			trigger.classList.toggle("has-value", !!(opt && opt.value !== ""));
		}

		function enhanceSelect(select) {
			if (select.dataset.sheetReady === "1") {
				syncTrigger(select);
				return;
			}

			var wrap = document.createElement("div");
			wrap.className = "sg-sheet-field";
			select.parentNode.insertBefore(wrap, select);
			wrap.appendChild(select);

			var trigger = document.createElement("button");
			trigger.type = "button";
			trigger.className = "sg-sheet-trigger";
			trigger.innerHTML =
				'<span class="sg-sheet-trigger-value"></span><i class="fa fa-chevron-down" aria-hidden="true"></i>';
			wrap.appendChild(trigger);

			trigger.addEventListener("click", function () {
				if (!isMobile()) return;
				openSheet(select);
			});

			select.addEventListener("change", function () {
				syncTrigger(select);
			});

			select.dataset.sheetReady = "1";
			syncTrigger(select);
		}

		function applyMode() {
			var selects = document.querySelectorAll("select.sg-select2");
			if (isMobile()) {
				document.body.classList.add("sg-mobile-app");
				document.body.classList.remove("sg-desktop-app");
				if (window.jQuery && jQuery.fn.select2) {
					jQuery(selects).each(function () {
						if (jQuery(this).hasClass("select2-hidden-accessible")) {
							jQuery(this).select2("destroy");
						}
					});
				}
				selects.forEach(enhanceSelect);
			} else {
				document.body.classList.add("sg-desktop-app");
				document.body.classList.remove("sg-mobile-app");
				closeSheet();
				if (window.jQuery && jQuery.fn.select2) {
					jQuery(selects).each(function () {
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
			}
		}

		sheet.querySelectorAll("[data-sheet-close]").forEach(function (el) {
			el.addEventListener("click", closeSheet);
		});

		if (searchEl) {
			searchEl.addEventListener("input", function () {
				renderOptions(searchEl.value);
			});
		}

		listEl.addEventListener("click", function (event) {
			var btn = event.target.closest(".sg-sheet-option");
			if (!btn || !activeSelect) return;
			var index = parseInt(btn.getAttribute("data-index"), 10);
			if (isNaN(index)) return;
			activeSelect.selectedIndex = index;
			activeSelect.dispatchEvent(new Event("change", { bubbles: true }));
			if (window.jQuery) {
				jQuery(activeSelect).trigger("change");
			}
			syncTrigger(activeSelect);
			closeSheet();
		});

		document.addEventListener("keydown", function (event) {
			if (event.key === "Escape" && !sheet.hidden) closeSheet();
		});

		applyMode();
		window.addEventListener("resize", function () {
			window.clearTimeout(window.__sgSheetResize);
			window.__sgSheetResize = window.setTimeout(applyMode, 150);
		});
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", boot);
	} else {
		boot();
	}
})();
