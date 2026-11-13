@extends('SmartGradu_App.layouts.app')

@section('title', 'Venue Guide | ' . config('app.name'))

@section('content')
<section class="sg-page-top sg-hero-campus sg-app-pagehead">
	<div class="container">
		<div class="sg-pagehead-bar">
			@include('SmartGradu_App.partials.page-back')
			<div class="sg-section-title">
				<span class="sg-caption" data-i18n="captionCampus">Campus Map</span>
				<h1 data-i18n="venues">Venue Guide</h1>
			</div>
		</div>
	</div>
</section>

<section class="sg-section">
	<div class="container">
		<div class="sg-filter">
			<div class="row">
				<div class="col-md-5 col-sm-6">
					<label for="route-from">From</label>
					<select id="route-from" class="form-control sg-select2" data-placeholder="Select start location" data-i18n-data-placeholder="selectFrom" data-i18n-sheet-title="selectFrom">
						<option value="">Select start location</option>
						@foreach($locations as $location)
							<option value="{{ $location->id }}" data-lat="{{ $location->lat }}" data-lng="{{ $location->lng }}">{{ $location->name }}</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-5 col-sm-6">
					<label for="route-to">To</label>
					<select id="route-to" class="form-control sg-select2" data-placeholder="Select destination" data-i18n-data-placeholder="selectTo" data-i18n-sheet-title="selectTo">
						<option value="">Select destination</option>
						@foreach($locations as $location)
							<option value="{{ $location->id }}" data-lat="{{ $location->lat }}" data-lng="{{ $location->lng }}">{{ $location->name }}</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-2 col-sm-12">
					<label>&nbsp;</label>
					<a id="route-open-map" class="btn btn-block sg-btn-blue disabled" href="#" target="_blank" rel="noopener">Open in Map</a>
				</div>
			</div>

			<div id="route-results" class="row mt-3" style="display:none;">
				<div class="col-md-6">
					<div class="sg-route-stat">
						<span>Route distance</span>
						<strong id="route-distance">—</strong>
					</div>
				</div>
				<div class="col-md-6">
					<div class="sg-route-stat">
						<span>Est. drive time</span>
						<strong id="route-time">—</strong>
					</div>
				</div>
				<div class="col-md-12 mt-3">
					<div class="sg-route-stat">
						<span>Directions</span>
						<strong id="route-summary">Select From and To to view the route.</strong>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
@endsection
