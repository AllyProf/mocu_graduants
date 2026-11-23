/**
 * MoCU Graduation Companion — English / Kiswahili language packs.
 * Applies translations via [data-i18n] attributes and persists choice in localStorage.
 */
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
			bestShort: "Best",
			timetable: "Timetable",
			timetableTitle: "Ceremony Timetable",
			help: "Help",
			helpTitle: "Graduation Help",
			live: "Live Ceremony",
			liveShort: "Live",
			venues: "Venue Guide",
			venuesShort: "Venues",
			leaders: "Leadership Gallery",
			gallery: "Gallery",
			captionLeaders: "University leaders",
			captionGallery: "Memories",
			noLeaders: "Leaders will appear here soon.",
			noMemories: "No memories yet. Tap + to add the first one.",
			addMemory: "Add memory",
			playNames: "Play names",
			stop: "Stop",
			femaleVoice: "Girl",
			maleVoice: "Male",
			voiceOptions: "Voice settings",
			chooseVoice: "Choose voice, then play names.",
			voiceGender: "Voice",
			voiceSpeed: "Speed",
			speedSlow: "Slow",
			speedNormal: "Normal",
			speedFast: "Fast",
			selectProgramme: "Select programme",
			uploadPhotos: "Photos (max 3)",
			addPhoto: "Add",
			captionOptional: "Caption (optional)",
			captionPlaceholder: "Say something about this memory...",
			uploadHint: "Tap a box to add a photo. Maximum 3.",
			saveMemory: "Save memory",
			filterProgramme: "Filter programme",
			downloadAll: "Download all",
			downloadPhoto: "Download",
			noProgrammeMatch: "No photos for this programme.",
			routeDistance: "Straight-line distance",
			routeWalkTime: "Est. walking time",
			directions: "Directions",
			viewAll: "View all",
			allFeatures: "All Features",
			captionMenu: "All features",
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
			allYears: "All years",
			allProgrammes: "All programmes",
			studyLevel: "Study level",
			yearOfStudy: "Year of study",
			programme: "Programme",
			overall: "Overall",
			allEventTypes: "All event types",
			availableStream: "Available stream",
			openYoutube: "Open in YouTube",
			selectFrom: "Select start location",
			selectTo: "Select destination",
			showing: "Showing",
			graduandsAll: "Graduands All",
			bestAll: "Best Students All",
			pullRefresh: "Pull to refresh",
			releaseRefresh: "Release to refresh",
			refreshing: "Refreshing...",
			noNamesToRead: "No names to read. Clear filters and try again.",
			downloadPdf: "Download PDF",
			downloadPdfShort: "PDF",
			downloadingPdf: "Downloading PDF...",
			downloadingPdfPercent: "Downloading PDF… {percent}%",
			downloadingPdfShort: "...",
			downloadedPdf: "PDF downloaded",
			downloadPdfFailed: "Could not download PDF",
			nameNotFound: "Name not found?",
			nameNotFoundHint: "If you searched and your name is missing, send a complaint to the graduation desk.",
			yourFullName: "Your full name",
			registrationNumber: "Registration number",
			phone: "Phone",
			phonePlaceholder: "7XXXXXXXX",
			countryCode: "Country code",
			selectOptional: "—",
			complaintMessage: "What is missing / your message",
			sendComplaint: "Send complaint",
			sendingComplaint: "Sending...",
			complaintSent: "Your complaint has been sent. The graduation desk will follow up.",
			complaintFailed: "Could not send your complaint. Please try again.",
			noGraduandsMatch: "No graduands match your filters.",
			noBestMatch: "No best students match your filters.",
			login: "Login",
			logout: "Logout",
			signIn: "Sign in",
			signInLead: "Use your staff account to continue.",
			signingIn: "Signing in...",
			email: "Email",
			password: "Password",
			rememberMe: "Stay Signed in",
			backToApp: "Back to app",
		},
		sw: {
			appName: "Mahafali ya MoCU",
			uniName: "Chuo Kikuu cha Ushirika Moshi",
			gradDay: "Siku ya Mahafali · 11 Des 2026",
			home: "Nyumbani",
			graduands: "Wahitimu",
			bestStudents: "Wanafunzi Bora",
			bestShort: "Bora",
			timetable: "Ratiba",
			timetableTitle: "Ratiba ya Mahafali",
			help: "Msaada",
			helpTitle: "Msaada wa Mahafali",
			live: "Mahafali Moja kwa Moja",
			liveShort: "Live",
			venues: "Mwongozo wa Maeneo",
			venuesShort: "Maeneo",
			leaders: "Jumba la Uongozi",
			gallery: "Matunzio",
			captionLeaders: "Viongozi wa chuo",
			captionGallery: "Kumbukumbu",
			noLeaders: "Viongozi wataonekana hapa hivi karibuni.",
			noMemories: "Bado hakuna kumbukumbu. Gusa + kuongeza ya kwanza.",
			addMemory: "Ongeza kumbukumbu",
			playNames: "Soma majina",
			stop: "Simamisha",
			femaleVoice: "Msichana",
			maleVoice: "Mwanaume",
			voiceOptions: "Mipangilio ya sauti",
			chooseVoice: "Chagua sauti, kisha soma majina.",
			voiceGender: "Sauti",
			voiceSpeed: "Kasi",
			speedSlow: "Polepole",
			speedNormal: "Kawaida",
			speedFast: "Haraka",
			selectProgramme: "Chagua programu",
			uploadPhotos: "Picha (ziwe 3 au chini)",
			addPhoto: "Ongeza",
			captionOptional: "Maelezo (si lazima)",
			captionPlaceholder: "Andika kuhusu kumbukumbu hii...",
			uploadHint: "Gusa kisanduku kuongeza picha. Upeo ni 3.",
			saveMemory: "Hifadhi kumbukumbu",
			filterProgramme: "Chuja programu",
			downloadAll: "Pakua zote",
			downloadPhoto: "Pakua",
			noProgrammeMatch: "Hakuna picha za programu hii.",
			routeDistance: "Umbali wa moja kwa moja",
			routeWalkTime: "Makadirio ya muda wa kutembea",
			directions: "Maelekezo",
			viewAll: "Angalia zote",
			allFeatures: "Vipengele Vyote",
			captionMenu: "Vipengele vyote",
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
			allYears: "Miaka yote",
			allProgrammes: "Programu zote",
			studyLevel: "Ngazi ya masomo",
			yearOfStudy: "Mwaka wa masomo",
			programme: "Programu",
			overall: "Bora kwa ujumla",
			allEventTypes: "Aina zote za matukio",
			availableStream: "Mtiririko unaopatikana",
			openYoutube: "Fungua YouTube",
			selectFrom: "Chagua mahali pa kuanzia",
			selectTo: "Chagua unakoenda",
			showing: "Inaonyesha",
			graduandsAll: "Wahitimu Wote",
			bestAll: "Wanafunzi Bora Wote",
			pullRefresh: "Vuta kusasisha",
			releaseRefresh: "Achilia kusasisha",
			refreshing: "Inasasisha...",
			noNamesToRead: "Hakuna majina ya kusoma. Futa vichujio ujaribu tena.",
			downloadPdf: "Pakua PDF",
			downloadPdfShort: "PDF",
			downloadingPdf: "Inapakua PDF...",
			downloadingPdfPercent: "Inapakua PDF… {percent}%",
			downloadingPdfShort: "...",
			downloadedPdf: "PDF imepakuliwa",
			downloadPdfFailed: "Imeshindikana kupakua PDF",
			nameNotFound: "Jina halipatikani?",
			nameNotFoundHint: "Ukitafuta na jina lako halipo, tuma malalamiko kwa dawati la mahafali.",
			yourFullName: "Jina lako kamili",
			registrationNumber: "Namba ya usajili",
			phone: "Simu",
			phonePlaceholder: "7XXXXXXXX",
			countryCode: "Namba ya nchi",
			selectOptional: "—",
			complaintMessage: "Kilichokosekana / ujumbe wako",
			sendComplaint: "Tuma malalamiko",
			sendingComplaint: "Inatuma...",
			complaintSent: "Malalamiko yako yametumwa. Dawati la mahafali litafuatilia.",
			complaintFailed: "Imeshindikana kutuma malalamiko. Jaribu tena.",
			noGraduandsMatch: "Hakuna wahitimu wanaolingana na vichujio vyako.",
			noBestMatch: "Hakuna wanafunzi bora wanaolingana na vichujio vyako.",
			login: "Ingia",
			logout: "Toka",
			signIn: "Ingia",
			signInLead: "Tumia akaunti yako ya wafanyakazi kuendelea.",
			signingIn: "Inaingia...",
			email: "Barua pepe",
			password: "Nenosiri",
			rememberMe: "Baki umeingia",
			backToApp: "Rudi kwenye programu",
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
