<nav class="sg-tabbar" aria-label="Mobile navigation">
	<a href="{{ route('home') }}" class="sg-tabbar-item {{ request()->routeIs('home') ? 'is-active' : '' }}">
		<i class="fa fa-home" aria-hidden="true"></i>
		<span data-i18n="home">Home</span>
	</a>
	<a href="{{ route('graduands') }}" class="sg-tabbar-item {{ request()->routeIs('graduands') ? 'is-active' : '' }}">
		<i class="fa fa-graduation-cap" aria-hidden="true"></i>
		<span data-i18n="graduands">Graduands</span>
	</a>
	<a href="{{ route('live') }}" class="sg-tabbar-item sg-tabbar-center {{ request()->routeIs('live') ? 'is-active' : '' }}" aria-label="Live Ceremony">
		<span class="sg-tabbar-fab">
			<i class="fa fa-video-camera" aria-hidden="true"></i>
		</span>
		<span data-i18n="liveShort">Live</span>
	</a>
	<a href="{{ route('venues') }}" class="sg-tabbar-item {{ request()->routeIs('venues') ? 'is-active' : '' }}">
		<i class="fa fa-map-marker" aria-hidden="true"></i>
		<span data-i18n="venuesShort">Venues</span>
	</a>
	<a href="{{ route('help') }}" class="sg-tabbar-item {{ request()->routeIs('help') ? 'is-active' : '' }}">
		<i class="fa fa-question-circle" aria-hidden="true"></i>
		<span data-i18n="help">Help</span>
	</a>
</nav>
