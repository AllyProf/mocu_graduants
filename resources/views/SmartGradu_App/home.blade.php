@extends('SmartGradu_App.layouts.app')

@section('title', config('app.name') . ' | Moshi Co-operative University')

@section('content')
<section class="sg-dash">
	<div class="sg-dash-header">
		<div class="sg-dash-copy">
			<h1 data-i18n="appName">MoCU Graduation</h1>
			<p data-i18n="uniName">Moshi Co-operative University (MoCU)</p>
		</div>
		<div class="sg-dash-header-actions">
			@include('SmartGradu_App.partials.lang-switcher')
		</div>
	</div>

	<div class="sg-dash-sheet">
		<div class="sg-dash-countdown">
			<div class="sg-countdown-banner">
				<img class="sg-countdown-banner-img" src="{{ asset('SmartGradu_App/assets/img/mocu_image2.jpeg') }}" alt="">
				<span class="sg-countdown-banner-shade" aria-hidden="true"></span>
				<div class="sg-countdown-banner-body">
					<span class="sg-countdown-banner-label" data-i18n="gradDay">Graduation Day · 11 Dec 2026</span>
					@include('SmartGradu_App.partials.countdown')
				</div>
			</div>
		</div>

		@include('SmartGradu_App.partials.menu-cards', ['homeOnly' => true])
	</div>
</section>
@endsection
