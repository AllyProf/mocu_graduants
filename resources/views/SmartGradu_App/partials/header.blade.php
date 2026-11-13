<!-- START NAVBAR -->
<div class="site-mobile-menu site-navbar-target">
	<div class="site-mobile-menu-header">
		<div class="site-mobile-menu-logo">
			<a href="{{ route('home') }}">
				<img src="{{ asset('SmartGradu_App/assets/img/mocu_logo_nav.png') }}" alt="{{ config('app.name') }}">
			</a>
		</div>
		<div class="site-mobile-menu-close">
			<span class="js-menu-toggle" aria-label="Close menu">&times;</span>
		</div>
	</div>
	<div class="site-mobile-menu-body"></div>
	<div class="site-mobile-menu-foot">
		<span class="sg-mobile-inquiry-icon"><i class="fa fa-phone"></i></span>
		<div>
			<span class="sg-mobile-inquiry-label">To More Inquiry</span>
			<a href="tel:+255272751833">+255 27 2751833</a>
		</div>
	</div>
</div>

<header class="site-navbar js-sticky-header site-navbar-target" role="banner">
	<div class="container">
		<div class="row align-items-center">
			<div class="col-6 col-xl-2">
				<h1 class="mb-0 site-logo">
					<a href="{{ route('home') }}">
						<img src="{{ asset('SmartGradu_App/assets/img/mocu_logo_nav.png') }}" alt="{{ config('app.name') }}">
					</a>
				</h1>
			</div>
			<div class="col-12 col-md-10 d-none d-xl-block">
				<nav class="site-navigation position-relative text-right" role="navigation">
					<ul class="site-menu main-menu js-clone-nav mr-auto d-none d-lg-block">
						<li><a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a></li>
						<li><a href="{{ route('graduands') }}" class="nav-link {{ request()->routeIs('graduands') ? 'active' : '' }}">Graduands</a></li>
						<li><a href="{{ route('best-students') }}" class="nav-link {{ request()->routeIs('best-students') ? 'active' : '' }}">Best Students</a></li>
						<li><a href="{{ route('timetable') }}" class="nav-link {{ request()->routeIs('timetable') ? 'active' : '' }}">Timetable</a></li>
						<li><a href="{{ route('venues') }}" class="nav-link {{ request()->routeIs('venues') ? 'active' : '' }}">Venue Guide</a></li>
						<li><a href="{{ route('live') }}" class="nav-link {{ request()->routeIs('live') ? 'active' : '' }}">Live Ceremony</a></li>
						<li><a href="{{ route('help') }}" class="nav-link {{ request()->routeIs('help') ? 'active' : '' }}">Help</a></li>
					</ul>
				</nav>
			</div>
			<div class="col-6 d-inline-block d-xl-none ml-md-0 py-3" style="position: relative; top: 3px;">
				<a href="#" class="site-menu-toggle js-menu-toggle float-right">
					<span class="icon-menu h3"></span>
				</a>
			</div>
		</div>
	</div>
</header>
<!-- END NAVBAR-->
