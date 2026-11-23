/**
 * MoCU Graduation Companion — list filters, PDF download, complaints.
 *
 * Responsibilities:
 * - Client-side filtering for graduands / best students / timetable
 * - PDF download with “Downloading…” status alert
 * - Missing-name complaint form submit (AJAX)
 * - Venue distance helpers and live-stream selector
 */
(function () {
	/** Normalise filter text for case-insensitive matching. */
	function normalize(value) {
		return String(value || "")
			.toLowerCase()
			.trim();
	}

	/** Expected years by study level (smart cascading). */
	var LEVEL_YEARS = {
		certificate: ["First Year"],
		diploma: ["First Year", "Second Year"],
		"ordinary diploma": ["First Year", "Second Year"],
		"bachelor degree": ["First Year", "Second Year", "Third Year", "Fourth Year"],
		"postgraduate diploma": ["First Year", "Second Year"],
		masters: ["First Year", "Second Year"],
		phd: ["First Year", "Second Year", "Third Year"],
	};

	function setSelectOptions(select, values, emptyLabel) {
		if (!select) return;
		var current = select.value || "";
		var html = '<option value="">' + (emptyLabel || "—") + "</option>";
		values.forEach(function (value) {
			html += '<option value="' + String(value).replace(/"/g, "&quot;") + '">' + value + "</option>";
		});
		select.innerHTML = html;
		if (current && values.indexOf(current) !== -1) {
			select.value = current;
		} else {
			select.value = "";
		}
		if (window.jQuery && jQuery(select).data("select2")) {
			jQuery(select).trigger("change.select2");
		}

		var wrap = select.closest(".sg-sheet-field");
		if (wrap) {
			var valueEl = wrap.querySelector(".sg-sheet-trigger-value");
			var placeholder = select.getAttribute("data-placeholder") || emptyLabel || "Select";
			var label = select.value
				? (select.options[select.selectedIndex] && select.options[select.selectedIndex].text) || select.value
				: placeholder;
			if (valueEl) valueEl.textContent = label;
			wrap.classList.toggle("has-value", !!select.value);
		}
	}

	/** MoCU catalogue programmes for a study level (from data-programmes-by-level). */
	function catalogueProgrammesForLevel(root, levelRaw) {
		var attr = root.getAttribute("data-programmes-by-level");
		if (!attr) return [];
		var map = null;
		try {
			map = JSON.parse(attr);
		} catch (e) {
			return [];
		}
		if (!map || typeof map !== "object") return [];

		if (!levelRaw) {
			var all = {};
			Object.keys(map).forEach(function (key) {
				(map[key] || []).forEach(function (code) {
					if (code) all[code] = true;
				});
			});
			return Object.keys(all).sort(function (a, b) {
				return a.localeCompare(b, undefined, { sensitivity: "base" });
			});
		}

		var wanted = normalize(levelRaw);
		var matchKey = Object.keys(map).find(function (key) {
			return normalize(key) === wanted;
		});
		if (!matchKey) return [];
		return (map[matchKey] || []).slice().sort(function (a, b) {
			return a.localeCompare(b, undefined, { sensitivity: "base" });
		});
	}

	var smartSyncLock = false;

	/**
	 * Cascading level ↔ year ↔ programme options.
	 * Programmes follow the MoCU catalogue for the selected level (like years).
	 */
	function syncSmartFilters(root) {
		if (smartSyncLock) return;
		var levelEl = root.querySelector("[data-filter-level]");
		var programmeEl = root.querySelector("[data-filter-programme]");
		var yearEl = root.querySelector("[data-filter-year]");
		if (!levelEl && !programmeEl && !yearEl) return;

		smartSyncLock = true;
		try {
			var levelRaw = levelEl ? String(levelEl.value || "").trim() : "";
			var level = normalize(levelRaw);
			var year = normalize(yearEl && yearEl.value);
			var programme = String((programmeEl && programmeEl.value) || "").trim();
			var programmeNorm = normalize(programme);
			var items = Array.prototype.slice.call(root.querySelectorAll("[data-filter-item]"));
			var programmesInData = {};
			var yearsInData = {};

			items.forEach(function (row) {
				var rowLevel = normalize(row.getAttribute("data-level"));
				var rowYear = normalize(row.getAttribute("data-year"));
				if (level && rowLevel !== level) return;
				if (year && rowYear !== year) return;
				var p = String(row.getAttribute("data-programme") || "").trim();
				if (p) programmesInData[p] = true;
			});

			items.forEach(function (row) {
				var rowLevel = normalize(row.getAttribute("data-level"));
				var rowProgramme = normalize(row.getAttribute("data-programme"));
				if (level && rowLevel !== level) return;
				if (programmeNorm && rowProgramme !== programmeNorm) return;
				var y = String(row.getAttribute("data-year") || "").trim();
				if (y) yearsInData[y] = true;
			});

			// Prefer official catalogue for the chosen level; fall back to row data.
			var programmeList = catalogueProgrammesForLevel(root, levelRaw);
			if (!programmeList.length) {
				programmeList = Object.keys(programmesInData).sort(function (a, b) {
					return a.localeCompare(b, undefined, { sensitivity: "base" });
				});
			} else if (year) {
				// When a year is chosen, keep catalogue codes that also appear in that year of data.
				var narrowed = programmeList.filter(function (code) {
					return programmesInData[code];
				});
				if (narrowed.length) programmeList = narrowed;
			}

			var allowedYears = level && LEVEL_YEARS[level] ? LEVEL_YEARS[level].slice() : null;
			var yearList = Object.keys(yearsInData).sort(function (a, b) {
				var order = ["First Year", "Second Year", "Third Year", "Fourth Year"];
				var ai = order.indexOf(a);
				var bi = order.indexOf(b);
				if (ai === -1 && bi === -1) return a.localeCompare(b);
				if (ai === -1) return 1;
				if (bi === -1) return -1;
				return ai - bi;
			});

			if (allowedYears) {
				yearList = yearList.filter(function (y) {
					return allowedYears.indexOf(y) !== -1;
				});
				if (!yearList.length && !programmeNorm) yearList = allowedYears.slice();
			}

			var pack = window.sgGetLangPack ? window.sgGetLangPack() : null;
			if (programmeEl) {
				setSelectOptions(programmeEl, programmeList, (pack && pack.allProgrammes) || "All programmes");
			}
			if (yearEl) {
				setSelectOptions(yearEl, yearList, (pack && pack.allYears) || "All years");
			}
		} finally {
			smartSyncLock = false;
		}
	}

	/** Return rows that match the current filter controls inside root. */
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

	/** Keep the pagehead PDF href in sync with active list filters. */
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

	/** True inside Flutter / Android / iOS in-app WebViews where blob downloads fail. */
	function isEmbeddedWebView() {
		var ua = navigator.userAgent || "";
		if (window.flutter_inappwebview || window.FlutterWebView || window.Flutter) return true;
		if (/; wv\)/i.test(ua)) return true;
		if (/\bwv\b/i.test(ua) && /Android/i.test(ua)) return true;
		if (/iPhone|iPod|iPad/i.test(ua) && !/Safari/i.test(ua)) return true;
		return false;
	}

	/** Absolute PDF URL for native WebView download handlers. */
	function absolutePdfUrl(url) {
		try {
			return new URL(url, window.location.href).href;
		} catch (err) {
			return url;
		}
	}

	/**
	 * Notify Flutter (common channel names) so the host app can open/save the PDF.
	 */
	function notifyFlutterPdf(url) {
		var payload = { type: "downloadPdf", url: url };
		try {
			if (window.flutter_inappwebview && window.flutter_inappwebview.callHandler) {
				window.flutter_inappwebview.callHandler("downloadPdf", url);
				return true;
			}
		} catch (err) {}
		try {
			if (window.DownloadPdfChannel && window.DownloadPdfChannel.postMessage) {
				window.DownloadPdfChannel.postMessage(url);
				return true;
			}
		} catch (err) {}
		try {
			if (window.Flutter && typeof window.Flutter.postMessage === "function") {
				window.Flutter.postMessage(JSON.stringify(payload));
				return true;
			}
		} catch (err) {}
		return false;
	}

	/**
	 * Open PDF via direct navigation (works in Flutter WebView download interceptors).
	 */
	function openPdfInWebView(link, url) {
		var abs = absolutePdfUrl(url);
		notifyFlutterPdf(abs);
		showPdfStatus(pdfLang("downloadingPdf", "Downloading PDF..."), "pending");
		window.setTimeout(function () {
			window.location.assign(abs);
			link.setAttribute("aria-busy", "false");
		}, 280);
	}

	/**
	 * Fetch PDF as a blob on normal browsers; in Flutter WebView navigate to the PDF URL.
	 */
	function startPdfDownload(link) {
		if (!link || link.getAttribute("aria-busy") === "true") return;

		var root = document.querySelector("[data-filter-root]");
		if (root) syncPdfLink(root);

		var url = link.getAttribute("href") || link.getAttribute("data-pdf-base") || "";
		if (!url || url === "#") return;

		link.setAttribute("aria-busy", "true");
		showPdfStatus(pdfLang("downloadingPdf", "Downloading PDF..."), "pending");

		// Blob + <a download> does not open files inside Flutter WebView.
		if (isEmbeddedWebView()) {
			openPdfInWebView(link, url);
			return;
		}

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
					window.location.assign(absolutePdfUrl(url));
				}, 350);
			})
			.finally(function () {
				link.setAttribute("aria-busy", "false");
			});
	}

	/** Global entry used by the PDF anchor onclick attribute. */
	window.sgDownloadPdf = function (event, link) {
		if (event) {
			event.preventDefault();
			if (event.stopPropagation) event.stopPropagation();
		}
		startPdfDownload(link || (event && event.currentTarget));
		return false;
	};

	/** Capture-phase click listener for all [data-pdf-download] controls. */
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

	/** Wire missing-name complaint forms to POST /complaints. */
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

	/** Attach input/change handlers for every [data-filter-root] on the page. */
	function bindFilters() {
		document.querySelectorAll("[data-filter-root]").forEach(function (root) {
			root.setAttribute("data-visible-count", root.getAttribute("data-per-page") || "10");

			root.querySelectorAll("[data-filter-q]").forEach(function (el) {
				el.addEventListener("input", function () {
					applyListFilter(root, true);
				});
			});

			root.querySelectorAll("[data-filter-year], [data-filter-level], [data-filter-programme], [data-filter-type]").forEach(function (el) {
				function onFilterChange() {
					if (
						el.hasAttribute("data-filter-level") ||
						el.hasAttribute("data-filter-year") ||
						el.hasAttribute("data-filter-programme")
					) {
						syncSmartFilters(root);
					}
					applyListFilter(root, true);
				}

				el.addEventListener("change", onFilterChange);

				if (window.jQuery) {
					jQuery(el).on("select2:select select2:clear select2:unselect change", onFilterChange);
				}
			});

			root.querySelectorAll("[data-filter-clear]").forEach(function (btn) {
				btn.addEventListener("click", function () {
					clearFilters(root);
					syncSmartFilters(root);
				});
			});

			var loadMoreBtn = root.querySelector("[data-load-more]");
			if (loadMoreBtn) {
				loadMoreBtn.addEventListener("click", function () {
					loadMore(root);
				});
			}

			syncSmartFilters(root);
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
		return (meters / 1000).toFixed(2) + " km";
	}

	/** Walking time from straight-line meters (≈ 4.5 km/h campus pace). */
	function formatWalkTime(meters) {
		var minutes = Math.max(1, Math.round((meters / 1000 / 4.5) * 60));
		if (minutes < 60) return "~" + minutes + " min walk";
		var h = Math.floor(minutes / 60);
		var m = minutes % 60;
		return "~" + h + " h " + m + " min walk";
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

		/** Read location point from the selected <option> (works with Select2/sheet). */
		function selectedPoint(select) {
			var opt = select.options[select.selectedIndex];
			if (!opt || !opt.value) return null;
			var lat = parseFloat(opt.getAttribute("data-lat"));
			var lng = parseFloat(opt.getAttribute("data-lng"));
			if (!isFinite(lat) || !isFinite(lng)) return null;
			return {
				id: opt.value,
				name: String(opt.text || "").trim(),
				lat: lat,
				lng: lng,
			};
		}

		function updateRoute() {
			var a = selectedPoint(from);
			var b = selectedPoint(to);
			if (!a || !b || a.id === b.id) {
				distanceEl.textContent = "—";
				timeEl.textContent = "—";
				if (summaryEl) summaryEl.textContent = "Select From and To to view the route.";
				if (resultsEl) resultsEl.style.display = "none";
				mapBtn.href = "#";
				mapBtn.removeAttribute("data-map-ready");
				mapBtn.classList.add("disabled");
				return;
			}

			var meters = haversineMeters(a.lat, a.lng, b.lat, b.lng);
			distanceEl.textContent = formatDistance(meters);
			timeEl.textContent = formatWalkTime(meters);
			if (summaryEl) {
				summaryEl.textContent = "From " + a.name + " to " + b.name + " · based on map coordinates";
			}
			if (resultsEl) resultsEl.style.display = "";

			// Use exact lat,lng so Google Maps opens the selected points (not a name search).
			mapBtn.href =
				"https://www.google.com/maps/dir/?api=1" +
				"&origin=" + encodeURIComponent(a.lat + "," + a.lng) +
				"&destination=" + encodeURIComponent(b.lat + "," + b.lng) +
				"&travelmode=walking";
			mapBtn.setAttribute("data-map-ready", "1");
			mapBtn.classList.remove("disabled");
		}

		mapBtn.addEventListener("click", function (event) {
			if (mapBtn.classList.contains("disabled") || mapBtn.getAttribute("data-map-ready") !== "1") {
				event.preventDefault();
				return;
			}
			// Flutter / in-app WebView often blocks target=_blank — open in same view.
			if (isEmbeddedWebView()) {
				event.preventDefault();
				window.location.assign(mapBtn.href);
			}
		});

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

	/** Run a boot step without letting one failure block the rest. */
	function safeBoot(fn) {
		try {
			fn();
		} catch (err) {
			/* Keep remaining boot steps running. */
		}
	}

	/** Initialise page behaviours after DOM is ready. */
	function boot() {
		// PDF first so download toast always works even if later boot steps fail.
		safeBoot(bindPdfDownload);
		safeBoot(bindFilters);
		safeBoot(bindPhoneDigits);
		safeBoot(bindComplaintForms);
		safeBoot(bindVenueRouter);
		safeBoot(bindLiveStreams);
		safeBoot(bindAccordions);

		document.addEventListener("sg:langchange", function () {
			document.querySelectorAll("[data-filter-root]").forEach(function (root) {
				applyListFilter(root, false);
			});
		});
	}

	// Register PDF handler immediately (don't wait on other boot work).
	safeBoot(bindPdfDownload);

	/** Reveal every currently matched row (used by voice playback). */
	window.sgRevealAllMatched = function (root) {
		if (!root) return;
		root.setAttribute("data-visible-count", "99999");
		applyListFilter(root, false);
	};

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", boot);
	} else {
		boot();
	}
})();
