@extends('SmartGradu_App.layouts.app')

@section('title', config('app.name') . ' | Moshi Co-operative University')

@section('content')
<section class="sg-hero" id="sg-hero-slider">
	<div class="sg-hero-slides">
		<div class="sg-hero-slide is-active" style="background-image: url('{{ asset('SmartGradu_App/assets/img/mocu_image1.JPG') }}');"></div>
		<div class="sg-hero-slide" style="background-image: url('{{ asset('SmartGradu_App/assets/img/mocu_image_5.jpg') }}');"></div>
		<div class="sg-hero-slide" style="background-image: url('{{ asset('SmartGradu_App/assets/img/mocu_image6.jpg') }}');"></div>
		<div class="sg-hero-slide" style="background-image: url('{{ asset('SmartGradu_App/assets/img/mocu_image3.jpeg') }}');"></div>
	</div>
	<div class="container sg-hero-content">
		<div class="sg-section-title">
			<span class="sg-caption">Moshi Co-operative University</span>
			<h1>Graduation Companion</h1>
		</div>
		@include('SmartGradu_App.partials.countdown')
		<div>
			<a href="{{ route('graduands') }}" class="sg-btn sg-btn-primary">Browse Graduands</a>
			<a href="{{ route('live') }}" class="sg-btn sg-btn-outline">Live Ceremony</a>
		</div>
	</div>
	<div class="sg-hero-dots" aria-label="Hero slides">
		<button type="button" class="is-active" data-slide="0" aria-label="Slide 1"></button>
		<button type="button" data-slide="1" aria-label="Slide 2"></button>
		<button type="button" data-slide="2" aria-label="Slide 3"></button>
		<button type="button" data-slide="3" aria-label="Slide 4"></button>
	</div>
</section>

<section class="sg-section">
	<div class="container">
		<div class="sg-section-title">
			<span class="sg-caption">Graduation</span>
			<h2>At a Glance</h2>
		</div>
		<div class="sg-feature-grid">
			<div class="sg-feature">
				<img class="sg-feature-media" src="{{ asset('SmartGradu_App/assets/img/mocu_image3.jpeg') }}" alt="Graduands">
				<div class="sg-feature-body">
					<h3>Graduands</h3>
					<p>Search and filter the full list by study level and programme.</p>
					<a href="{{ route('graduands') }}">Open list →</a>
				</div>
			</div>
			<div class="sg-feature">
				<img class="sg-feature-media" src="{{ asset('SmartGradu_App/assets/img/mocu_image4.jpeg') }}" alt="Best students">
				<div class="sg-feature-body">
					<h3>Best Students</h3>
					<p>See top performers and their awards for this ceremony.</p>
					<a href="{{ route('best-students') }}">View awards →</a>
				</div>
			</div>
			<div class="sg-feature">
				<img class="sg-feature-media" src="{{ asset('SmartGradu_App/assets/img/mocu_image2.jpeg') }}" alt="Timetable">
				<div class="sg-feature-body">
					<h3>Timetable</h3>
					<p>@if($nextEvent) Next: {{ $nextEvent->title }} @else Ceremony schedule and session times. @endif</p>
					<a href="{{ route('timetable') }}">View timetable →</a>
				</div>
			</div>
			<div class="sg-feature">
				<img class="sg-feature-media" src="{{ asset('SmartGradu_App/assets/img/mocu_image1.JPG') }}" alt="Venue guide">
				<div class="sg-feature-body">
					<h3>Venue Guide</h3>
					<p>Find assembly points, the arena, parking, and help desks.</p>
					<a href="{{ route('venues') }}">Get directions →</a>
				</div>
			</div>
			<div class="sg-feature">
				<img class="sg-feature-media" src="{{ asset('SmartGradu_App/assets/img/mocu_image2.jpeg') }}" alt="Live ceremony">
				<div class="sg-feature-body">
					<h3>Live Ceremony</h3>
					<p>{{ optional($live->streams->first())->is_live ? 'Broadcast is live now.' : 'Watch when the stream goes live.' }}</p>
					<a href="{{ route('live') }}">Open live page →</a>
				</div>
			</div>
			<div class="sg-feature">
				<img class="sg-feature-media" src="{{ asset('SmartGradu_App/assets/img/mocu_image_5.jpg') }}" alt="Help">
				<div class="sg-feature-body">
					<h3>Help</h3>
					<p>FAQs and contacts for graduands, parents, and guests.</p>
					<a href="{{ route('help') }}">Get help →</a>
				</div>
			</div>
		</div>
	</div>
</section>
@endsection
