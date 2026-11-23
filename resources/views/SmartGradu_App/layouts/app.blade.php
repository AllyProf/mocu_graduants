{{-- MoCU Graduation Companion — main application layout --}}
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>@yield('title', config('app.name') . ' | MoCU')</title>
	<meta name="theme-color" content="#056fe7">
	<meta name="application-name" content="{{ config('app.name') }}">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}">
	<script>
		(function () {
			try {
				var ua = navigator.userAgent || "";
				var params = new URLSearchParams(window.location.search || "");
				function markApp() {
					try { sessionStorage.setItem("sg_webview", "1"); } catch (e) {}
					try {
						document.cookie = "sg_app=1;path=/;max-age=31536000;SameSite=Lax";
					} catch (e) {}
				}
				if (params.get("webview") === "1" || params.get("app") === "1") markApp();
				var flagged = false;
				try { flagged = sessionStorage.getItem("sg_webview") === "1"; } catch (e) {}
				try { flagged = flagged || /(?:^|;\s*)sg_app=1(?:;|$)/.test(document.cookie || ""); } catch (e) {}
				function detect() {
					return (
						flagged ||
						!!window.flutter_inappwebview ||
						!!window.FlutterWebView ||
						!!window.Flutter ||
						!!(window.webkit && window.webkit.messageHandlers) ||
						/; wv\)/i.test(ua) ||
						(/\bwv\b/i.test(ua) && /Android/i.test(ua)) ||
						(/Flutter|InAppWebView|WebView/i.test(ua)) ||
						(/iPhone|iPod|iPad/i.test(ua) && !/Safari\//i.test(ua))
					);
				}
				function apply() {
					if (!detect()) return false;
					markApp();
					document.documentElement.classList.add("sg-webview");
					if (document.body) document.body.classList.add("sg-webview");
					document.querySelectorAll(".sg-hide-in-webview").forEach(function (el) {
						el.style.display = "none";
						el.setAttribute("hidden", "hidden");
					});
					return true;
				}
				if (apply()) {
					document.addEventListener("DOMContentLoaded", function () {
						apply();
						@if(request()->routeIs('login'))
						window.location.replace(@json(url('/')));
						@endif
					});
				} else {
					// Flutter bridge often appears a moment after first paint.
					var tries = 0;
					var timer = window.setInterval(function () {
						tries += 1;
						if (apply() || tries > 20) window.clearInterval(timer);
					}, 150);
				}
			} catch (e) {}
		})();
	</script>
	<link rel="icon" type="image/png" href="{{ asset('SmartGradu_App/assets/img/mocu_logo_nav.png') }}">
	<link rel="shortcut icon" type="image/png" href="{{ asset('SmartGradu_App/assets/img/mocu_logo_nav.png') }}">
	<link rel="apple-touch-icon" href="{{ asset('SmartGradu_App/assets/img/mocu_logo_nav.png') }}">
	<link rel="stylesheet" href="{{ asset('SmartGradu_App/assets/bootstrap/css/bootstrap.min.css') }}">
	<link href="https://fonts.googleapis.com/css2?family=Exo:wght@300;400;500;600;700;800;900&family=Great+Vibes&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="{{ asset('SmartGradu_App/assets/fonts/font-awesome.min.css') }}">
	<link rel="stylesheet" href="{{ asset('SmartGradu_App/assets/css/fonts.css') }}">
	<link rel="stylesheet" href="{{ asset('SmartGradu_App/assets/css/menu.css?v=20') }}">
	<link rel="stylesheet" href="{{ asset('SmartGradu_App/assets/css/style.css?v=20') }}">
	<link rel="stylesheet" href="{{ asset('SmartGradu_App/assets/css/mocu-brand.css?v=202') }}">
	<link rel="stylesheet" href="{{ asset('SmartGradu_App/assets/css/responsive.css') }}">
	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
	@stack('styles')
</head>

<body class="sg-body sg-app-shell {{ request()->routeIs('home') ? 'sg-home-lock' : '' }} {{ request()->routeIs('login') ? 'sg-login-shell' : '' }}" data-spy="scroll" data-offset="80">

	@unless(request()->routeIs('home') || request()->routeIs('login'))
		@include('SmartGradu_App.partials.preloader')
	@endunless
	@include('SmartGradu_App.partials.app-nav')
	@unless(request()->routeIs('login'))
		@include('SmartGradu_App.partials.header')
	@endunless

	<main class="sg-main">
		@yield('content')
	</main>

	@include('SmartGradu_App.partials.tabbar')
	@include('SmartGradu_App.partials.sheet')
	@include('SmartGradu_App.partials.lang-sheet')

	<script src="{{ asset('SmartGradu_App/assets/js/jquery-1.12.4.min.js') }}"></script>
	<script src="{{ asset('SmartGradu_App/assets/bootstrap/js/bootstrap.min.js') }}"></script>
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
	<script src="{{ asset('SmartGradu_App/assets/js/menu.js') }}"></script>
	<script src="{{ asset('SmartGradu_App/assets/js/jquery.sticky.js') }}"></script>
	<script src="{{ asset('SmartGradu_App/assets/js/scrolltopcontrol.js') }}"></script>
	<script src="{{ asset('SmartGradu_App/assets/js/scripts.js?v=5') }}"></script>
	<script src="{{ asset('SmartGradu_App/assets/js/countdown.js?v=3') }}"></script>
	<script src="{{ asset('SmartGradu_App/assets/js/sg-filters.js?v=39') }}"></script>
	<script src="{{ asset('SmartGradu_App/assets/js/sg-voice.js?v=17') }}"></script>
	<script src="{{ asset('SmartGradu_App/assets/js/sg-sheet.js?v=5') }}"></script>
	<script src="{{ asset('SmartGradu_App/assets/js/sg-lang.js?v=34') }}"></script>
	<script src="{{ asset('SmartGradu_App/assets/js/sg-pull-refresh.js?v=1') }}"></script>
	@stack('scripts')
</body>

</html>
