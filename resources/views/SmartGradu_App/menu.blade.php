@extends('SmartGradu_App.layouts.app')

@section('title', 'All Features | ' . config('app.name'))

@section('content')
<section class="sg-page-top sg-hero-menu sg-app-pagehead">
	<div class="container">
		<div class="sg-pagehead-bar">
			@include('SmartGradu_App.partials.page-back')
			<div class="sg-section-title">
				<span class="sg-caption" data-i18n="captionMenu">All features</span>
				<h1 data-i18n="allFeatures">All Features</h1>
			</div>
		</div>
	</div>
</section>

<section class="sg-section sg-menu-page">
	<div class="container">
		@include('SmartGradu_App.partials.menu-cards')
	</div>
</section>
@endsection
