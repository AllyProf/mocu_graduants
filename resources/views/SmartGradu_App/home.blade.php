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

		<div class="sg-dash-grid">
			<a class="sg-dash-card" href="{{ route('graduands') }}">
				<span class="sg-dash-icon sg-dash-icon-blue"><i class="fa fa-graduation-cap"></i></span>
				<span class="sg-dash-label" data-i18n="graduands">Graduands</span>
			</a>
			<a class="sg-dash-card" href="{{ route('best-students') }}">
				<span class="sg-dash-icon sg-dash-icon-yellow"><i class="fa fa-trophy"></i></span>
				<span class="sg-dash-label" data-i18n="bestStudents">Best Students</span>
			</a>
			<a class="sg-dash-card" href="{{ route('timetable') }}">
				<span class="sg-dash-icon sg-dash-icon-navy"><i class="fa fa-calendar"></i></span>
				<span class="sg-dash-label" data-i18n="timetable">Timetable</span>
			</a>
			<a class="sg-dash-card" href="{{ route('help') }}">
				<span class="sg-dash-icon sg-dash-icon-gray"><i class="fa fa-question-circle"></i></span>
				<span class="sg-dash-label" data-i18n="help">Help</span>
			</a>
			<a class="sg-dash-card" href="{{ route('live') }}">
				<span class="sg-dash-icon sg-dash-icon-red"><i class="fa fa-video-camera"></i></span>
				<span class="sg-dash-label" data-i18n="live">Live Ceremony</span>
			</a>
			<a class="sg-dash-card" href="{{ route('venues') }}">
				<span class="sg-dash-icon sg-dash-icon-teal"><i class="fa fa-map-marker"></i></span>
				<span class="sg-dash-label" data-i18n="venues">Venue Guide</span>
			</a>
		</div>
	</div>
</section>
@endsection
