<header class="sg-app-nav" role="banner">
	<a href="{{ route('home') }}" class="sg-app-nav-brand">
		<img src="{{ asset('SmartGradu_App/assets/img/mocu_logo_nav.png') }}" alt="">
		<span data-i18n="appName">MoCU Graduation</span>
	</a>
	<nav class="sg-app-nav-links" aria-label="Main">
		<a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'is-active' : '' }}" data-i18n="home">Home</a>
		<a href="{{ route('graduands') }}" class="{{ request()->routeIs('graduands') ? 'is-active' : '' }}" data-i18n="graduands">Graduands</a>
		<a href="{{ route('best-students') }}" class="{{ request()->routeIs('best-students') ? 'is-active' : '' }}" data-i18n="bestStudents">Best Students</a>
		<a href="{{ route('timetable') }}" class="{{ request()->routeIs('timetable') ? 'is-active' : '' }}" data-i18n="timetable">Timetable</a>
		<a href="{{ route('venues') }}" class="{{ request()->routeIs('venues') ? 'is-active' : '' }}" data-i18n="venuesShort">Venues</a>
		<a href="{{ route('live') }}" class="{{ request()->routeIs('live') ? 'is-active' : '' }}" data-i18n="liveShort">Live</a>
		<a href="{{ route('help') }}" class="{{ request()->routeIs('help') ? 'is-active' : '' }}" data-i18n="help">Help</a>
	</nav>
	@include('SmartGradu_App.partials.lang-switcher')
</header>
