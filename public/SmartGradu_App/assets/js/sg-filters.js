(function () {
	function normalize(value) {
		return String(value || "")
			.toLowerCase()
			.trim();
	}

	function getMatchedRows(root) {
		var q = normalize(root.querySelector("[data-filter-q]") && root.querySelector("[data-filter-q]").value);
		var year = normalize(root.querySelector("[data-filter-year]") && root.querySelector("[data-filter-year]").value);
		var level = normalize(root.querySelector("[data-filter-level]") && root.querySelector("[data-filter-level]").value);
		var programme = normalize(root.querySelector("[data-filter-programme]") && root.querySelector("[data-filter-programme]").value);
		var type = normalize(root.querySelector("[data-filter-type]") && root.querySelector("[data-filter-type]").value);
		var matched = [];

		root.querySelectorAll("[data-filter-item]").forEach(function (row) {
			var hay = normalize(row.getAttribute("data-search"));
			var rowYear = normalize(row.getAttribute("data-year"));
			var rowLevel = normalize(row.getAttribute("data-level"));
			var rowProgramme = normalize(row.getAttribute("data-programme"));
			var rowType = normalize(row.getAttribute("data-type"));
			var rowAward = normalize(row.getAttribute("data-award"));

			var matchQ = !q || hay.indexOf(q) !== -1;
			var matchYear = !year || rowYear === year;
			var matchLevel = !level || rowLevel === level;
			// Overall best stays visible for Third Year even when a programme is selected,
			// unless a different year is filtered.
			var matchProgramme =
				!programme ||
				rowProgramme === programme ||
				(rowAward === "overall" && (!year || year === "third year"));
			var matchType = !type || rowType === type;

			if (matchQ && matchYear && matchLevel && matchProgramme && matchType) {
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
		var pack = window.sgGetLangPack ? window.sgGetLangPack() : null;
		var label = (pack && pack.loadMore) || btn.getAttribute("data-i18n-load-more-label") || "Load more";
		btn.textContent = label + " (" + remaining + ")";
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

		var totalAll = parseInt(root.getAttribute("data-total-all") || "0", 10);
		if (!totalAll) {
			totalAll = root.querySelectorAll("[data-filter-item]").length;
		}
		root.querySelectorAll("[data-filter-total]").forEach(function (el) {
			el.textContent = String(totalAll);
		});

		var avgEl = root.querySelector("[data-filter-avg]");
		if (avgEl) {
			avgEl.textContent = gpaCount ? (gpaSum / gpaCount).toFixed(2) : "0.00";
		}

		var empty = root.querySelector("[data-filter-empty]");
		if (empty) {
			empty.style.display = matched.length ? "none" : "";
		}

		syncComplaintPanel(root, matched.length, q);
		syncPdfLink(root);

		renderLoadMore(root, visibleCount, matched.length);
	}

	function syncComplaintPanel(root, matchedCount, q) {
		var panel = root.querySelector("[data-complaint-panel]");
		if (!panel) return;

		var show = matchedCount === 0 && !!q;
		panel.style.display = show ? "" : "none";

		var searched = panel.querySelector("[data-complaint-searched]");
		if (searched) {
			var rawQ = root.querySelector("[data-filter-q]");
			searched.value = rawQ ? String(rawQ.value || "").trim() : "";
		}

		if (show) {
			var nameInput = panel.querySelector('input[name="full_name"]');
			if (nameInput && !nameInput.value) {
				var raw = root.querySelector("[data-filter-q]");
				nameInput.value = raw ? String(raw.value || "").trim() : "";
			}
		}
	}

	function syncPdfLink(root) {
		var links = Array.prototype.slice.call(
			document.querySelectorAll(".sg-pagehead-pdf[data-pdf-download], [data-pdf-download]")
		);
		if (!links.length) return;

		var params = new URLSearchParams();
		var qEl = root.querySelector("[data-filter-q]");
		var levelEl = root.querySelector("[data-filter-level]");
		var programmeEl = root.querySelector("[data-filter-programme]");
		var yearEl = root.querySelector("[data-filter-year]");

		if (qEl && qEl.value.trim()) params.set("q", qEl.value.trim());
		if (levelEl && levelEl.value) params.set("level", levelEl.value);
		if (programmeEl && programmeEl.value) params.set("programme", programmeEl.value);
		if (yearEl && yearEl.value) params.set("year", yearEl.value);

		var query = params.toString();

		links.forEach(function (link) {
			var base = link.getAttribute("data-pdf-base") || link.getAttribute("href") || "";
			if (!base || base === "#") return;
			link.href = query ? base + (base.indexOf("?") === -1 ? "?" : "&") + query : base;
		});
	}

	function pdfLang(key, fallback) {
		var pack = window.sgGetLangPack ? window.sgGetLangPack() : null;
		return (pack && pack[key]) || fallback;
	}

	function ensurePdfStatusAlert() {
		var el = document.getElementById("sg-pdf-status");
		if (el) return el;

		el = document.createElement("div");
		el.id = "sg-pdf-status";
		el.className = "sg-status-alert";
		el.setAttribute("aria-live", "polite");
		el.innerHTML =
			'<span class="sg-status-alert-inner">' +
			'<i class="fa fa-refresh" aria-hidden="true" data-pdf-status-icon></i>' +
			'<span class="sg-status-alert-label" data-pdf-status-label></span>' +
			"</span>";
		document.body.appendChild(el);
		return el;
	}

	function showPdfStatus(message, state) {
		var el = ensurePdfStatusAlert();
		var label = el.querySelector("[data-pdf-status-label]");
		var icon = el.querySelector("[data-pdf-status-icon]");

		el.className = "sg-status-alert is-visible" + (state ? " is-" + state : "");
		el.style.transform = "translate(-50%, 64px)";
		if (label) label.textContent = message;

		if (icon) {
			icon.className =
				state === "ok"
					? "fa fa-check"
					: state === "error"
						? "fa fa-exclamation-circle"
						: "fa fa-refresh";
		}
	}

	function hidePdfStatusSoon(delay) {
		var el = document.getElementById("sg-pdf-status");
		if (!el) return;
		window.clearTimeout(window.__sgPdfStatusTimer);
		window.__sgPdfStatusTimer = window.setTimeout(function () {
			el.classList.remove("is-visible", "is-pending", "is-ok", "is-error");
			el.style.transform = "";
		}, delay || 1600);
	}

	function filenameFromDisposition(header, fallback) {
		if (!header) return fallback;
		var utf = header.match(/filename\*=UTF-8''([^;]+)/i);
		if (utf && utf[1]) return decodeURIComponent(utf[1]);
		var plain = header.match(/filename=\"?([^\";]+)\"?/i);
		if (plain && plain[1]) return plain[1];
		return fallback;
	}

	function startPdfDownload(link) {
		if (!link || link.getAttribute("aria-busy") === "true") return;

		var root = document.querySelector("[data-filter-root]");
		if (root) syncPdfLink(root);

		var url = link.getAttribute("href") || link.getAttribute("data-pdf-base") || "";
		if (!url || url === "#") return;

		link.setAttribute("aria-busy", "true");
		showPdfStatus(pdfLang("downloadingPdf", "Downloading PDF..."), "pending");

		fetch(url, {
			method: "GET",
			credentials: "same-origin",
			headers: { Accept: "application/pdf" },
			cache: "no-store",
		})
			.then(function (res) {
				if (!res.ok) throw new Error("download-failed");
				var ctype = String(res.headers.get("content-type") || "").toLowerCase();
				var fallbackName =
					url.indexOf("best-students") !== -1
						? "mocu-best-students.pdf"
						: "mocu-graduands.pdf";
				var filename = filenameFromDisposition(
					res.headers.get("content-disposition"),
					fallbackName
				);
				return res.blob().then(function (blob) {
					var type = String(blob.type || ctype || "").toLowerCase();
					if (
						type &&
						type.indexOf("pdf") === -1 &&
						type.indexOf("octet-stream") === -1
					) {
						throw new Error("not-pdf");
					}
					return {
						blob:
							type.indexOf("pdf") === -1
								? new Blob([blob], { type: "application/pdf" })
								: blob,
						filename: filename,
					};
				});
			})
			.then(function (result) {
				var objectUrl = URL.createObjectURL(result.blob);
				var a = document.createElement("a");
				a.href = objectUrl;
				a.download = result.filename || "download.pdf";
				a.rel = "noopener";
				a.style.display = "none";
				document.body.appendChild(a);
				a.click();
				a.remove();
				window.setTimeout(function () {
					URL.revokeObjectURL(objectUrl);
				}, 2000);

				showPdfStatus(pdfLang("downloadedPdf", "PDF downloaded"), "ok");
				hidePdfStatusSoon(1600);
			})
			.catch(function () {
				showPdfStatus(pdfLang("downloadPdfFailed", "Could not download PDF"), "error");
				hidePdfStatusSoon(2200);
				window.setTimeout(function () {
					window.location.assign(url);
				}, 350);
			})
			.finally(function () {
				link.setAttribute("aria-busy", "false");
			});
	}

	window.sgDownloadPdf = function (event, link) {
		if (event) {
			event.preventDefault();
			if (event.stopPropagation) event.stopPropagation();
		}
		startPdfDownload(link || (event && event.currentTarget));
		return false;
	};

	function bindPdfDownload() {
		if (document.documentElement.getAttribute("data-pdf-download-bound") === "1") return;
		document.documentElement.setAttribute("data-pdf-download-bound", "1");

		document.addEventListener(
			"click",
			function (event) {
				var link =
					event.target && event.target.closest
						? event.target.closest("[data-pdf-download]")
						: null;
				if (!link) return;
				event.preventDefault();
				event.stopPropagation();
				startPdfDownload(link);
			},
			true
		);
	}

	function bindPhoneDigits() {
		document.querySelectorAll("[data-phone-digits]").forEach(function (input) {
			if (input.getAttribute("data-phone-bound") === "1") return;
			input.setAttribute("data-phone-bound", "1");

			input.addEventListener("input", function () {
				var digits = String(input.value || "").replace(/\D+/g, "").slice(0, 9);
				if (input.value !== digits) input.value = digits;
			});

			input.addEventListener("keypress", function (event) {
				if (event.ctrlKey || event.metaKey || event.altKey) return;
				if (event.key && !/^[0-9]$/.test(event.key)) {
					event.preventDefault();
				}
			});

			input.addEventListener("paste", function (event) {
				event.preventDefault();
				var text = (event.clipboardData || window.clipboardData).getData("text") || "";
				input.value = String(text).replace(/\D+/g, "").slice(0, 9);
			});
		});
	}

	function bindComplaintForms() {
		document.querySelectorAll("[data-complaint-form]").forEach(function (form) {
			if (form.getAttribute("data-bound") === "1") return;
			form.setAttribute("data-bound", "1");

			form.addEventListener("submit", function (event) {
				event.preventDefault();

				var status = form.querySelector("[data-complaint-status]");
				var submitBtn = form.querySelector('button[type="submit"]');
				var pack = window.sgGetLangPack ? window.sgGetLangPack() : {};
				var sending = pack.sendingComplaint || "Sending...";
				var success = pack.complaintSent || "Your complaint has been sent. The graduation desk will follow up.";
				var failed = pack.complaintFailed || "Could not send your complaint. Please try again.";

				if (status) {
					status.hidden = false;
					status.className = "sg-complaint-status is-pending";
					status.textContent = sending;
				}
				if (submitBtn) submitBtn.disabled = true;

				var token =
					(form.querySelector('input[name="_token"]') && form.querySelector('input[name="_token"]').value) ||
					(document.querySelector('meta[name="csrf-token"]') &&
						document.querySelector('meta[name="csrf-token"]').getAttribute("content")) ||
					"";

				fetch(form.action, {
					method: "POST",
					headers: {
						Accept: "application/json",
						"X-Requested-With": "XMLHttpRequest",
						"X-CSRF-TOKEN": token,
					},
					body: new FormData(form),
					credentials: "same-origin",
				})
					.then(function (res) {
						return res.json().then(function (data) {
							return { ok: res.ok, data: data };
						});
					})
					.then(function (result) {
						if (!result.ok) {
							var msg = failed;
							if (result.data && result.data.message) msg = result.data.message;
							if (result.data && result.data.errors) {
								var firstKey = Object.keys(result.data.errors)[0];
								if (firstKey) msg = result.data.errors[firstKey][0];
							}
							throw new Error(msg);
						}

						if (status) {
							status.className = "sg-complaint-status is-ok";
							status.textContent = (result.data && result.data.message) || success;
						}
						form.reset();
						var country = form.querySelector('select[name="country_code"]');
						if (country) {
							country.value = "+255";
							country.dispatchEvent(new Event("change", { bubbles: true }));
							if (window.jQuery) jQuery(country).trigger("change");
						}
						var searched = form.querySelector("[data-complaint-searched]");
						var root = form.closest("[data-filter-root]");
						var qEl = root && root.querySelector("[data-filter-q]");
						if (searched && qEl) searched.value = String(qEl.value || "").trim();
					})
					.catch(function (err) {
						if (status) {
							status.className = "sg-complaint-status is-error";
							status.textContent = err && err.message ? err.message : failed;
						}
					})
					.finally(function () {
						if (submitBtn) submitBtn.disabled = false;
					});
			});
		});
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

		root.querySelectorAll("[data-filter-year], [data-filter-level], [data-filter-programme], [data-filter-type]").forEach(function (el) {
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

			root.querySelectorAll("[data-filter-year], [data-filter-level], [data-filter-programme], [data-filter-type]").forEach(function (el) {
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
		if (!select || !frame || !openBtn) return;

		function applyStream() {
			var option = select.options[select.selectedIndex];
			if (!option) return;
			var embed = option.dataset.embed || "";
			var youtube = option.dataset.youtube || "";
			var live = option.dataset.live === "1";

			frame.src = embed;
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
		// PDF first so download toast always works even if later boot steps fail.
		try {
			bindPdfDownload();
		} catch (err) {
			console.error("sg bindPdfDownload", err);
		}
		try {
			bindFilters();
		} catch (err) {
			console.error("sg bindFilters", err);
		}
		try {
			bindPhoneDigits();
		} catch (err) {
			console.error("sg bindPhoneDigits", err);
		}
		try {
			bindComplaintForms();
		} catch (err) {
			console.error("sg bindComplaintForms", err);
		}
		try {
			bindVenueRouter();
		} catch (err) {
			console.error("sg bindVenueRouter", err);
		}
		try {
			bindLiveStreams();
		} catch (err) {
			console.error("sg bindLiveStreams", err);
		}
		try {
			bindAccordions();
		} catch (err) {
			console.error("sg bindAccordions", err);
		}

		document.addEventListener("sg:langchange", function () {
			document.querySelectorAll("[data-filter-root]").forEach(function (root) {
				applyListFilter(root, false);
			});
		});
	}

	// Register PDF handler immediately (don't wait on other boot work).
	try {
		bindPdfDownload();
	} catch (err) {
		console.error("sg early bindPdfDownload", err);
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", boot);
	} else {
		boot();
	}
})();
