@extends('SmartGradu_App.layouts.app')

@section('title', 'Graduands | ' . config('app.name'))

@section('content')
@php
	$total = $graduands->count();
	$avgGpa = $total ? round($graduands->avg('gpa'), 2) : 0;
@endphp
<section class="sg-page-top sg-hero-graduands sg-app-pagehead">
	<div class="container">
		<div class="sg-pagehead-bar">
			@include('SmartGradu_App.partials.page-back')
			<div class="sg-section-title">
				<span class="sg-caption">Ceremony Lists</span>
				<h1>Graduands</h1>
			</div>
		</div>
	</div>
</section>

<section class="sg-section sg-list-page">
	<div class="container" data-filter-root data-per-page="10" data-total-all="{{ $total }}" data-avg-all="{{ $avgGpa }}">
		<div class="sg-list-summary">
			<span class="sg-list-summary-icon"><i class="fa fa-graduation-cap"></i></span>
			<div class="sg-list-summary-main">
				<strong>Ceremony list</strong>
				<span><b data-filter-count>{{ $total }}</b> shown · Avg GPA <b data-filter-avg>{{ number_format($avgGpa, 2) }}</b></span>
			</div>
		</div>

		<div class="sg-list-tools">
			<div class="sg-list-search-row">
				<div class="sg-list-search">
					<i class="fa fa-search" aria-hidden="true"></i>
					<input type="text" id="q" class="form-control" data-filter-q placeholder="Search name...">
				</div>
				<button type="button" class="sg-list-clear" data-filter-clear>Clear</button>
			</div>
			<div class="sg-list-filters">
				<select id="study_level" class="form-control sg-select2" data-filter-level data-placeholder="All levels" data-sheet-title="Study level">
					<option value="">All levels</option>
					@foreach($studyLevels as $level)
						<option value="{{ $level }}">{{ $level }}</option>
					@endforeach
				</select>
				<select id="programme" class="form-control sg-select2" data-filter-programme data-placeholder="All programmes" data-sheet-title="Programme">
					<option value="">All programmes</option>
					@foreach($programmes as $programme)
						<option value="{{ $programme }}">{{ $programme }}</option>
					@endforeach
				</select>
			</div>
		</div>

		<div class="sg-empty" data-filter-empty style="display:none;">No graduands match your filters.</div>

		@if($graduands->isNotEmpty())
			<div class="sg-people-list">
				@foreach($graduands as $index => $graduand)
					<article
						class="sg-people-item"
						data-filter-item
						data-level="{{ $graduand->study_level }}"
						data-programme="{{ $graduand->programme }}"
						data-gpa="{{ $graduand->gpa }}"
						data-search="{{ $graduand->full_name }} {{ $graduand->programme_short }} {{ $graduand->programme }} {{ $graduand->gender }}"
					>
						<span class="sg-people-rank" data-row-num>{{ $index + 1 }}</span>
						<div class="sg-people-info">
							<strong class="sg-people-name">{{ $graduand->full_name }}</strong>
							<span class="sg-people-meta">{{ $graduand->programme_short }} · {{ $graduand->study_level }}</span>
							@if(!empty($graduand->is_best_student))
								<span class="sg-people-award">Best student</span>
							@endif
						</div>
						<div class="sg-people-stat">
							<strong>{{ number_format((float) $graduand->gpa, 2) }}</strong>
							<span>{{ $graduand->gender }}</span>
						</div>
					</article>
				@endforeach
			</div>

			<div class="sg-load-more-wrap" data-load-more-wrap style="display:none;">
				<button type="button" class="sg-load-more" data-load-more>Load more</button>
			</div>
		@endif
	</div>
</section>
@endsection
