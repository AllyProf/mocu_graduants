/**
 * Gallery: programme filter sheet, add-memory upload, lightbox.
 */
(function () {
	"use strict";

	var MAX_PHOTOS = 3;
	var activeFilter = "";

	function mountToBody(el) {
		if (el && el.parentNode !== document.body) {
			document.body.appendChild(el);
		}
	}

	/* Keep overlays above page/tabbar stacking contexts */
	mountToBody(document.getElementById("sg-gallery-lightbox"));
	mountToBody(document.getElementById("sg-gallery-filter-sheet"));
	mountToBody(document.getElementById("sg-gallery-sheet"));

	/* ---------- Load more (4 at a time) ---------- */
	(function bindLoadMore() {
		document.querySelectorAll("[data-gallery-load-more]").forEach(function (btn) {
			btn.addEventListener("click", function () {
				var group = btn.closest("[data-gallery-group]");
				if (!group) return;
				var step = parseInt(btn.getAttribute("data-step") || "4", 10);
				var hidden = Array.prototype.slice.call(group.querySelectorAll("[data-gallery-photo][hidden]"));
				hidden.slice(0, step).forEach(function (el) {
					el.hidden = false;
				});
				if (group.querySelectorAll("[data-gallery-photo][hidden]").length === 0) {
					btn.hidden = true;
				}
			});
		});
	})();

	/* ---------- FAB: label by default, icon-only while scrolling ---------- */
	(function bindFabScroll() {
		var fab = document.querySelector("[data-gallery-open]");
		if (!fab) return;
		var timer = null;
		var scrollRoot = document.scrollingElement || document.documentElement;

		function collapse() {
			fab.classList.add("is-compact");
		}

		function expand() {
			fab.classList.remove("is-compact");
		}

		expand();

		function onScroll() {
			collapse();
			if (timer) window.clearTimeout(timer);
			timer = window.setTimeout(expand, 700);
		}

		window.addEventListener("scroll", onScroll, { passive: true });
		if (scrollRoot && scrollRoot !== document.body) {
			scrollRoot.addEventListener("scroll", onScroll, { passive: true });
		}
		document.addEventListener("scroll", onScroll, { passive: true, capture: true });
	})();

	/* ---------- Fullscreen photo viewer ---------- */
	(function bindLightbox() {
		var lightbox = document.getElementById("sg-gallery-lightbox");
		if (!lightbox) return;

		var img = lightbox.querySelector("[data-gallery-lightbox-img]");
		var items = [];
		var index = 0;

		function collectVisible() {
			return Array.prototype.slice.call(document.querySelectorAll("[data-gallery-view]")).filter(function (el) {
				var group = el.closest("[data-gallery-group]");
				var photo = el.closest("[data-gallery-photo]");
				if (group && group.hidden) return false;
				if (photo && photo.hidden) return false;
				return true;
			});
		}

		function show(i) {
			items = collectVisible();
			if (!items.length) return;
			index = (i + items.length) % items.length;
			var el = items[index];
			var src = el.getAttribute("data-gallery-src") || "";
			if (img) {
				img.src = src;
				img.alt = el.querySelector("img") ? el.querySelector("img").alt || "" : "";
			}
			lightbox.hidden = false;
			lightbox.setAttribute("aria-hidden", "false");
			document.body.classList.add("sg-gallery-lightbox-open");
		}

		function close() {
			lightbox.hidden = true;
			lightbox.setAttribute("aria-hidden", "true");
			document.body.classList.remove("sg-gallery-lightbox-open");
			if (img) img.removeAttribute("src");
		}

		document.addEventListener("click", function (event) {
			var openBtn = event.target.closest("[data-gallery-view]");
			if (!openBtn) return;
			event.preventDefault();
			items = collectVisible();
			var i = items.indexOf(openBtn);
			show(i < 0 ? 0 : i);
		});

		lightbox.querySelectorAll("[data-gallery-lightbox-close]").forEach(function (el) {
			el.addEventListener("click", close);
		});

		lightbox.addEventListener("click", function (event) {
			if (event.target === lightbox) close();
		});

		var prev = lightbox.querySelector("[data-gallery-lightbox-prev]");
		var next = lightbox.querySelector("[data-gallery-lightbox-next]");
		if (prev) prev.addEventListener("click", function () { show(index - 1); });
		if (next) next.addEventListener("click", function () { show(index + 1); });

		document.addEventListener("keydown", function (event) {
			if (lightbox.hidden) return;
			if (event.key === "Escape") close();
			if (event.key === "ArrowLeft") show(index - 1);
			if (event.key === "ArrowRight") show(index + 1);
		});

		var touchX = null;
		lightbox.addEventListener("touchstart", function (event) {
			touchX = event.changedTouches[0] ? event.changedTouches[0].clientX : null;
		}, { passive: true });
		lightbox.addEventListener("touchend", function (event) {
			if (touchX === null) return;
			var endX = event.changedTouches[0] ? event.changedTouches[0].clientX : touchX;
			var delta = endX - touchX;
			touchX = null;
			if (Math.abs(delta) < 40) return;
			if (delta > 0) show(index - 1);
			else show(index + 1);
		}, { passive: true });
	})();

	/* ---------- Programme filter ---------- */
	(function bindFilter() {
		var filterSheet = document.getElementById("sg-gallery-filter-sheet");
		var openFilter = document.querySelector("[data-gallery-filter-open]");
		var chip = document.querySelector("[data-gallery-filter-chip]");
		var chipLabel = document.querySelector("[data-gallery-filter-label]");
		var clearBtn = document.querySelector("[data-gallery-filter-clear]");
		var empty = document.querySelector("[data-gallery-filter-empty]");
		var groups = document.querySelectorAll("[data-gallery-group]");
		if (!filterSheet || !openFilter) return;

		function openFilterSheet() {
			filterSheet.hidden = false;
			filterSheet.setAttribute("aria-hidden", "false");
			document.body.classList.add("sg-gallery-sheet-open");
		}

		function closeFilterSheet() {
			filterSheet.hidden = true;
			filterSheet.setAttribute("aria-hidden", "true");
			document.body.classList.remove("sg-gallery-sheet-open");
		}

		function applyFilter(value) {
			activeFilter = String(value || "");
			var visible = 0;

			groups.forEach(function (group) {
				var programme = String(group.getAttribute("data-programme") || "");
				var show = !activeFilter || programme.toLowerCase() === activeFilter.toLowerCase();
				group.hidden = !show;
				if (show) visible += 1;
			});

			document.querySelectorAll("[data-gallery-filter-value]").forEach(function (btn) {
				var val = String(btn.getAttribute("data-gallery-filter-value") || "");
				btn.classList.toggle("is-active", val.toLowerCase() === activeFilter.toLowerCase());
			});

			if (chip && chipLabel) {
				if (activeFilter) {
					chip.hidden = false;
					chipLabel.textContent = activeFilter;
				} else {
					chip.hidden = true;
					chipLabel.textContent = "";
				}
			}

			if (empty) empty.hidden = visible > 0 || groups.length === 0;
		}

		openFilter.addEventListener("click", openFilterSheet);
		filterSheet.querySelectorAll("[data-gallery-filter-close]").forEach(function (el) {
			el.addEventListener("click", closeFilterSheet);
		});

		filterSheet.querySelectorAll("[data-gallery-filter-value]").forEach(function (btn) {
			btn.addEventListener("click", function () {
				applyFilter(btn.getAttribute("data-gallery-filter-value") || "");
				closeFilterSheet();
			});
		});

		if (clearBtn) {
			clearBtn.addEventListener("click", function () {
				applyFilter("");
			});
		}

		applyFilter("");
	})();

	/* ---------- Add memory upload ---------- */
	(function bindUpload() {
		var sheet = document.getElementById("sg-gallery-sheet");
		var form = document.querySelector("[data-gallery-form]");
		if (!sheet || !form) return;

		var openBtn = document.querySelector("[data-gallery-open]");
		var programme = form.querySelector("[data-gallery-programme]");
		var hint = form.querySelector("[data-gallery-hint]");
		var status = form.querySelector("[data-gallery-status]");
		var submitBtn = form.querySelector(".sg-gallery-submit");
		var panel = sheet.querySelector(".sg-gallery-sheet-panel");
		var slotFiles = [null, null, null];

		function openSheet() {
			sheet.hidden = false;
			sheet.setAttribute("aria-hidden", "false");
			document.body.classList.add("sg-gallery-sheet-open");
		}

		function closeSheet() {
			sheet.hidden = true;
			sheet.setAttribute("aria-hidden", "true");
			document.body.classList.remove("sg-gallery-sheet-open");
		}

		function setStatus(message, kind) {
			if (!status) return;
			status.hidden = !message;
			status.textContent = message || "";
			status.className = "sg-gallery-status" + (kind ? " is-" + kind : "");
		}

		function selectedCount() {
			return slotFiles.filter(Boolean).length;
		}

		function updateHint() {
			if (!hint) return;
			var count = selectedCount();
			hint.textContent =
				count >= MAX_PHOTOS
					? "Maximum of 3 photos reached."
					: "Tap a box to add a photo. " + count + " of " + MAX_PHOTOS + " selected.";
		}

		function renderSlot(index) {
			var slot = form.querySelector('[data-gallery-slot="' + index + '"]');
			if (!slot) return;
			var empty = slot.querySelector(".sg-gallery-slot-empty");
			var img = slot.querySelector(".sg-gallery-slot-preview");
			var remove = slot.querySelector("[data-gallery-slot-remove]");
			var file = slotFiles[index];

			if (file && img) {
				if (img.dataset.objectUrl) URL.revokeObjectURL(img.dataset.objectUrl);
				var url = URL.createObjectURL(file);
				img.dataset.objectUrl = url;
				img.src = url;
				img.hidden = false;
				if (empty) empty.hidden = true;
				if (remove) remove.hidden = false;
				slot.classList.add("has-photo");
			} else {
				if (img) {
					if (img.dataset.objectUrl) URL.revokeObjectURL(img.dataset.objectUrl);
					img.removeAttribute("src");
					img.dataset.objectUrl = "";
					img.hidden = true;
				}
				if (empty) empty.hidden = false;
				if (remove) remove.hidden = true;
				slot.classList.remove("has-photo");
			}
		}

		function clearSlots() {
			for (var i = 0; i < MAX_PHOTOS; i++) {
				slotFiles[i] = null;
				var input = form.querySelector('[data-gallery-slot-input="' + i + '"]');
				if (input) input.value = "";
				renderSlot(i);
			}
			updateHint();
		}

		function setSlotFile(index, file) {
			if (!file) return;
			if (!/^image\/(jpeg|jpg|png|webp)$/i.test(file.type || "")) {
				setStatus("Only JPG, PNG or WEBP photos are allowed.", "error");
				return;
			}
			slotFiles[index] = file;
			renderSlot(index);
			updateHint();
			setStatus("", "");
		}

		form.querySelectorAll("[data-gallery-slot-input]").forEach(function (input) {
			input.addEventListener("change", function () {
				var index = parseInt(input.getAttribute("data-gallery-slot-input"), 10);
				var file = input.files && input.files[0] ? input.files[0] : null;
				if (!file) return;
				setSlotFile(index, file);
				input.value = "";
			});
		});

		form.querySelectorAll("[data-gallery-slot-remove]").forEach(function (btn) {
			btn.addEventListener("click", function (event) {
				event.preventDefault();
				event.stopPropagation();
				var index = parseInt(btn.getAttribute("data-gallery-slot-remove"), 10);
				slotFiles[index] = null;
				renderSlot(index);
				updateHint();
			});
		});

		if (openBtn) {
			openBtn.addEventListener("click", function (event) {
				event.preventDefault();
				event.stopPropagation();
				openSheet();
			});
		}

		sheet.querySelectorAll("[data-gallery-close]").forEach(function (el) {
			el.addEventListener("click", closeSheet);
		});

		/* Backdrop closes; panel interactions stay on the form */
		if (panel) {
			panel.addEventListener("click", function (event) {
				event.stopPropagation();
			});
		}

		form.addEventListener("submit", function (event) {
			event.preventDefault();
			setStatus("", "");

			if (!programme || !String(programme.value || "").trim()) {
				setStatus("Please select a programme.", "error");
				if (programme) programme.focus();
				return;
			}

			var files = slotFiles.filter(Boolean).slice(0, MAX_PHOTOS);
			if (!files.length) {
				setStatus("Please add at least one photo.", "error");
				return;
			}

			var token = document.querySelector('meta[name="csrf-token"]');
			var body = new FormData();
			body.append("_token", token ? token.getAttribute("content") : "");
			body.append("programme", String(programme.value || "").trim());
			var caption = form.querySelector('input[name="caption"]');
			if (caption && caption.value) body.append("caption", caption.value);
			files.forEach(function (file) {
				body.append("images[]", file, file.name || "photo.jpg");
			});

			if (submitBtn) {
				submitBtn.disabled = true;
				submitBtn.textContent = "Saving...";
			}
			setStatus("Uploading...", "pending");

			fetch(form.getAttribute("action"), {
				method: "POST",
				headers: {
					Accept: "application/json",
					"X-Requested-With": "XMLHttpRequest",
					"X-CSRF-TOKEN": token ? token.getAttribute("content") : "",
				},
				body: body,
				credentials: "same-origin",
			})
				.then(function (res) {
					return res.json().then(function (data) {
						return { ok: res.ok, data: data };
					});
				})
				.then(function (result) {
					if (!result.ok || !result.data || !result.data.ok) {
						var msg =
							(result.data && (result.data.message || (result.data.errors && Object.values(result.data.errors)[0]))) ||
							"Could not save memory.";
						if (Array.isArray(msg)) msg = msg[0];
						throw new Error(msg);
					}
					clearSlots();
					window.location.reload();
				})
				.catch(function (err) {
					setStatus(err.message || "Could not save memory.", "error");
					if (submitBtn) {
						submitBtn.disabled = false;
						submitBtn.textContent = "Save memory";
					}
				});
		});

		clearSlots();
	})();
})();
