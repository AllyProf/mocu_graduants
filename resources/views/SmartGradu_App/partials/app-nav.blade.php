<header class="sg-app-nav" role="banner">
	<a href="{{ route('home') }}" class="sg-app-nav-brand">
		<img src="{{ asset('SmartGradu_App/assets/img/mocu_logo_nav.png') }}" alt="">
		<span>Smart Gradu App</span>
	</a>
	<nav class="sg-app-nav-links" aria-label="Main">
		<a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'is-active' : '' }}">Home</a>
		<a href="{{ route('graduands') }}" class="{{ request()->routeIs('graduands') ? 'is-active' : '' }}">Graduands</a>
		<a href="{{ route('best-students') }}" class="{{ request()->routeIs('best-students') ? 'is-active' : '' }}">Best Students</a>
		<a href="{{ route('timetable') }}" class="{{ request()->routeIs('timetable') ? 'is-active' : '' }}">Timetable</a>
		<a href="{{ route('venues') }}" class="{{ request()->routeIs('venues') ? 'is-active' : '' }}">Venues</a>
		<a href="{{ route('live') }}" class="{{ request()->routeIs('live') ? 'is-active' : '' }}">Live</a>
		<a href="{{ route('help') }}" class="{{ request()->routeIs('help') ? 'is-active' : '' }}">Help</a>
	</nav>
</header>
