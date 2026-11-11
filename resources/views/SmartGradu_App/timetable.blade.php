@extends('SmartGradu_App.layouts.app')

@section('title', 'Timetable | ' . config('app.name'))

@section('content')
<section class="sg-page-top sg-hero-timetable sg-app-pagehead">
	<div class="container">
		<div class="sg-pagehead-bar">
			@include('SmartGradu_App.partials.page-back')
			<div class="sg-section-title">
				<span class="sg-caption">Schedule</span>
				<h1>Ceremony Timetable</h1>
			</div>
		</div>
	</div>
</section>

<section class="sg-section">
	<div class="container" data-filter-root>
		<div class="sg-filter">
			<div class="row">
				<div class="col-md-5 col-sm-6">
					<label for="event_type">Event type</label>
					<select id="event_type" class="form-control sg-select2" data-filter-type data-placeholder="All event types">
						<option value="">All event types</option>
						@foreach($eventTypes as $type)
							<option value="{{ $type }}">{{ $type }}</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-7 col-sm-6">
					<label for="timetable_q">Search</label>
					<input type="text" id="timetable_q" class="form-control" data-filter-q placeholder="Search events or venues...">
				</div>
			</div>
		</div>

		<p class="mb-3"><strong data-filter-count>{{ $items->count() }}</strong> event(s)</p>
		<div class="sg-empty" data-filter-empty style="display:none;">No events match your filters.</div>

		<div class="sg-timeline">
			@foreach($items as $item)
				<div
					class="sg-timeline-item"
					data-filter-item
					data-type="{{ $item->event_type }}"
					data-search="{{ $item->title }} {{ $item->event_type }} {{ $item->venue }} {{ $item->description }}"
				>
					<span class="time">
						{{ \Illuminate\Support\Carbon::parse($item->starts_at)->format('D, d M Y · H:i') }}
						@if(!empty($item->ends_at))
							– {{ \Illuminate\Support\Carbon::parse($item->ends_at)->format('H:i') }}
						@endif
					</span>
					<span class="sg-badge">{{ $item->event_type }}</span>
					<h3>{{ $item->title }}</h3>
					@if(!empty($item->venue))
						<p class="meta"><strong>Venue:</strong> {{ $item->venue }}</p>
					@endif
					@if(!empty($item->description))
						<p>{{ $item->description }}</p>
					@endif
				</div>
			@endforeach
		</div>
	</div>
</section>
@endsection
