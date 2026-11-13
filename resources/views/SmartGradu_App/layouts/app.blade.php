<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<title>@yield('title', config('app.name') . ' | MoCU')</title>
	<meta name="theme-color" content="#056fe7">
	<meta name="application-name" content="{{ config('app.name') }}">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}">
	<link rel="icon" type="image/png" href="{{ asset('SmartGradu_App/assets/img/favicon.png') }}">
	<link rel="shortcut icon" type="image/png" href="{{ asset('SmartGradu_App/assets/img/favicon.png') }}">
	<link rel="apple-touch-icon" href="{{ asset('SmartGradu_App/assets/img/favicon.png') }}">
	<link rel="stylesheet" href="{{ asset('SmartGradu_App/assets/bootstrap/css/bootstrap.min.css') }}">
	<link href="https://fonts.googleapis.com/css2?family=Exo:wght@300;400;500;600;700;800;900&family=Great+Vibes&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="{{ asset('SmartGradu_App/assets/fonts/font-awesome.min.css') }}">
	<link rel="stylesheet" href="{{ asset('SmartGradu_App/assets/css/fonts.css') }}">
	<link rel="stylesheet" href="{{ asset('SmartGradu_App/assets/css/menu.css?v=20') }}">
	<link rel="stylesheet" href="{{ asset('SmartGradu_App/assets/css/style.css?v=20') }}">
	<link rel="stylesheet" href="{{ asset('SmartGradu_App/assets/css/mocu-brand.css?v=127') }}">
	<link rel="stylesheet" href="{{ asset('SmartGradu_App/assets/css/responsive.css') }}">
	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
	@stack('styles')
</head>

<body class="sg-body sg-app-shell {{ request()->routeIs('home') ? 'sg-home-lock' : '' }}" data-spy="scroll" data-offset="80">

	@unless(request()->routeIs('home'))
		@include('SmartGradu_App.partials.preloader')
	@endunless
	@include('SmartGradu_App.partials.app-nav')
	@include('SmartGradu_App.partials.header')

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
	<script src="{{ asset('SmartGradu_App/assets/js/countdown.js?v=2') }}"></script>
	<script src="{{ asset('SmartGradu_App/assets/js/sg-filters.js?v=12') }}"></script>
	<script src="{{ asset('SmartGradu_App/assets/js/sg-sheet.js?v=2') }}"></script>
	<script src="{{ asset('SmartGradu_App/assets/js/sg-lang.js?v=3') }}"></script>
	@stack('scripts')
</body>

</html>
