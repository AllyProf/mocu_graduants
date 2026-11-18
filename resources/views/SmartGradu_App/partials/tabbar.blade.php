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
	@auth
		<form method="post" action="{{ route('logout') }}" class="sg-tabbar-auth-form">
			@csrf
			<button type="submit" class="sg-tabbar-item {{ request()->routeIs('login') ? 'is-active' : '' }}">
				<i class="fa fa-sign-out" aria-hidden="true"></i>
				<span data-i18n="logout">Logout</span>
			</button>
		</form>
	@else
		<a href="{{ route('login') }}" class="sg-tabbar-item {{ request()->routeIs('login') ? 'is-active' : '' }}">
			<i class="fa fa-user" aria-hidden="true"></i>
			<span data-i18n="login">Login</span>
		</a>
	@endauth
</nav>
