(function () {
	"use strict";

	var labels = {
		en: { name: "English", flag: "🇺🇸" },
		sw: { name: "Kiswahili", flag: "🇹🇿" },
	};

	var dict = {
		en: {
			appName: "MoCU Graduation",
			uniName: "Moshi Co-operative University (MoCU)",
			gradDay: "Graduation Day · 11 Dec 2026",
			home: "Home",
			graduands: "Graduands",
			bestStudents: "Best Students",
			timetable: "Timetable",
			timetableTitle: "Ceremony Timetable",
			help: "Help",
			helpTitle: "Graduation Help",
			live: "Live Ceremony",
			liveShort: "Live",
			venues: "Venue Guide",
			venuesShort: "Venues",
			language: "Language",
			days: "Days",
			hours: "Hours",
			minutes: "Minutes",
			seconds: "Seconds",
			captionCeremony: "Ceremony Lists",
			captionBest: "Top Performers",
			captionSchedule: "Schedule",
			captionSupport: "Support",
			captionWatch: "Watch Online",
			captionCampus: "Campus Map",
			ceremonyList: "Ceremony list",
			topPerformers: "Top performers",
			quickContacts: "Quick contacts",
			searchName: "Search name...",
			searchEvents: "Search events or venues...",
			search: "Search...",
			clear: "Clear",
			loadMore: "Load more",
			allLevels: "All levels",
			allProgrammes: "All programmes",
			studyLevel: "Study level",
			programme: "Programme",
			allEventTypes: "All event types",
			availableStream: "Available stream",
			openYoutube: "Open in YouTube",
			selectFrom: "Select start location",
			selectTo: "Select destination",
			showing: "Showing",
			graduandsAll: "Graduands All",
			bestAll: "Best Students All",
		},
		sw: {
			appName: "Mahafali ya MoCU",
			uniName: "Chuo Kikuu cha Ushirika Moshi (MoCU)",
			gradDay: "Siku ya Mahafali · 11 Des 2026",
			home: "Nyumbani",
			graduands: "Wahitimu",
			bestStudents: "Wanafunzi Bora",
			timetable: "Ratiba",
			timetableTitle: "Ratiba ya Mahafali",
			help: "Msaada",
			helpTitle: "Msaada wa Mahafali",
			live: "Mahafali Moja kwa Moja",
			liveShort: "Moja kwa Moja",
			venues: "Mwongozo wa Maeneo",
			venuesShort: "Maeneo",
			language: "Lugha",
			days: "Siku",
			hours: "Saa",
			minutes: "Dakika",
			seconds: "Sekunde",
			captionCeremony: "Orodha za Mahafali",
			captionBest: "Wenye Utendaji Bora",
			captionSchedule: "Ratiba",
			captionSupport: "Msaada",
			captionWatch: "Tazama Mtandaoni",
			captionCampus: "Ramani ya Chuo",
			ceremonyList: "Orodha ya mahafali",
			topPerformers: "Wenye utendaji bora",
			quickContacts: "Mawasiliano ya haraka",
			searchName: "Tafuta jina...",
			searchEvents: "Tafuta matukio au maeneo...",
			search: "Tafuta...",
			clear: "Futa",
			loadMore: "Pakia zaidi",
			allLevels: "Ngazi zote",
			allProgrammes: "Programu zote",
			studyLevel: "Ngazi ya masomo",
			programme: "Programu",
			allEventTypes: "Aina zote za matukio",
			availableStream: "Mtiririko unaopatikana",
			openYoutube: "Fungua YouTube",
			selectFrom: "Chagua mahali pa kuanzia",
			selectTo: "Chagua unakoenda",
			showing: "Inaonyesha",
			graduandsAll: "Wahitimu Wote",
			bestAll: "Wanafunzi Bora Wote",
		},
	};

	function getSavedLang() {
		try {
			return localStorage.getItem("sg-lang") || "en";
		} catch (e) {
			return "en";
		}
	}

	function syncTriggers(lang) {
		var meta = labels[lang] || labels.en;
		document.querySelectorAll("[data-lang-switcher]").forEach(function (switcher) {
			var flag = switcher.querySelector("[data-lang-flag]");
			var label = switcher.querySelector("[data-lang-label]");
			var openBtn = switcher.querySelector("[data-lang-open]");
			if (flag) flag.textContent = meta.flag;
			if (label) label.textContent = meta.name;
			if (openBtn) openBtn.setAttribute("aria-expanded", "false");
		});
	}

	function syncSheet(lang) {
		var sheet = document.getElementById("sg-lang-sheet");
		if (!sheet) return;
		var title = document.getElementById("sg-lang-sheet-title");
		var pack = dict[lang] || dict.en;
		if (title) title.textContent = pack.language || "Language";
		sheet.querySelectorAll(".sg-sheet-option[data-lang]").forEach(function (btn) {
			var selected = btn.getAttribute("data-lang") === lang;
			btn.classList.toggle("is-selected", selected);
			btn.setAttribute("aria-selected", selected ? "true" : "false");
		});
	}

	function applyPlaceholders(pack) {
		document.querySelectorAll("[data-i18n-placeholder]").forEach(function (el) {
			var key = el.getAttribute("data-i18n-placeholder");
			if (pack[key]) el.setAttribute("placeholder", pack[key]);
		});

		document.querySelectorAll("[data-i18n-data-placeholder]").forEach(function (el) {
			var key = el.getAttribute("data-i18n-data-placeholder");
			if (!pack[key]) return;
			el.setAttribute("data-placeholder", pack[key]);
			var empty = el.querySelector('option[value=""]');
			if (empty) empty.textContent = pack[key];
			var trigger = el.closest(".sg-sheet-field");
			if (trigger) {
				var valueEl = trigger.querySelector(".sg-sheet-trigger-value");
				if (valueEl && !el.value) valueEl.textContent = pack[key];
			}
		});

		document.querySelectorAll("[data-i18n-sheet-title]").forEach(function (el) {
			var key = el.getAttribute("data-i18n-sheet-title");
			if (pack[key]) el.setAttribute("data-sheet-title", pack[key]);
		});
	}

	function applyLang(lang) {
		if (!dict[lang]) lang = "en";
		var pack = dict[lang];
		document.documentElement.lang = lang === "sw" ? "sw" : "en";

		document.querySelectorAll("[data-i18n]").forEach(function (el) {
			var key = el.getAttribute("data-i18n");
			if (pack[key]) el.textContent = pack[key];
		});

		applyPlaceholders(pack);
		syncTriggers(lang);
		syncSheet(lang);

		try {
			localStorage.setItem("sg-lang", lang);
		} catch (e) {}

		document.dispatchEvent(new CustomEvent("sg:langchange", { detail: { lang: lang, pack: pack } }));
	}

	function closeSheet() {
		var sheet = document.getElementById("sg-lang-sheet");
		if (!sheet) return;
		sheet.hidden = true;
		sheet.setAttribute("aria-hidden", "true");
		document.body.classList.remove("sg-sheet-open");
		document.querySelectorAll("[data-lang-open]").forEach(function (btn) {
			btn.setAttribute("aria-expanded", "false");
		});
	}

	function openSheet(trigger) {
		var sheet = document.getElementById("sg-lang-sheet");
		if (!sheet) return;
		syncSheet(getSavedLang());
		sheet.hidden = false;
		sheet.setAttribute("aria-hidden", "false");
		document.body.classList.add("sg-sheet-open");
		if (trigger) trigger.setAttribute("aria-expanded", "true");
	}

	function boot() {
		var switchers = document.querySelectorAll("[data-lang-switcher]");
		if (!switchers.length) return;

		applyLang(getSavedLang());

		document.querySelectorAll("[data-lang-open]").forEach(function (btn) {
			btn.addEventListener("click", function () {
				openSheet(btn);
			});
		});

		var sheet = document.getElementById("sg-lang-sheet");
		if (!sheet) return;

		sheet.querySelectorAll("[data-lang-close]").forEach(function (el) {
			el.addEventListener("click", closeSheet);
		});

		sheet.querySelectorAll(".sg-sheet-option[data-lang]").forEach(function (btn) {
			btn.addEventListener("click", function () {
				applyLang(btn.getAttribute("data-lang") || "en");
				closeSheet();
			});
		});

		document.addEventListener("keydown", function (event) {
			if (event.key === "Escape" && sheet && !sheet.hidden) closeSheet();
		});
	}

	window.sgGetLangPack = function () {
		return dict[getSavedLang()] || dict.en;
	};

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", boot);
	} else {
		boot();
	}
})();
