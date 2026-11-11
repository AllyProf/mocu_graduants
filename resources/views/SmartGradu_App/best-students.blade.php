@extends('SmartGradu_App.layouts.app')

@section('title', 'Best Students | ' . config('app.name'))

@section('content')
@php
	$total = $students->count();
	$avgGpa = $total ? round($students->avg('gpa'), 2) : 0;
@endphp
<section class="sg-page-top sg-hero-best sg-app-pagehead">
	<div class="container">
		<div class="sg-pagehead-bar">
			@include('SmartGradu_App.partials.page-back')
			<div class="sg-section-title">
				<span class="sg-caption">Top Performers</span>
				<h1>Best Students</h1>
			</div>
		</div>
	</div>
</section>

<section class="sg-section sg-list-page">
	<div class="container" data-filter-root data-per-page="10" data-total-all="{{ $total }}" data-avg-all="{{ $avgGpa }}">
		<div class="sg-list-summary">
			<span class="sg-list-summary-icon"><i class="fa fa-star"></i></span>
			<div class="sg-list-summary-main">
				<strong>Top performers</strong>
				<span><b data-filter-count>{{ $total }}</b> shown · Avg GPA <b data-filter-avg>{{ number_format($avgGpa, 2) }}</b></span>
			</div>
		</div>

		<div class="sg-list-tools">
			<div class="sg-list-search-row">
				<div class="sg-list-search">
					<i class="fa fa-search" aria-hidden="true"></i>
					<input type="text" id="best_q" class="form-control" data-filter-q placeholder="Search name...">
				</div>
				<button type="button" class="sg-list-clear" data-filter-clear>Clear</button>
			</div>
			<div class="sg-list-filters">
				<select id="best_programme" class="form-control sg-select2" data-filter-programme data-placeholder="All programmes" data-sheet-title="Programme">
					<option value="">All programmes</option>
					@foreach($programmes as $programme)
						<option value="{{ $programme }}">{{ $programme }}</option>
					@endforeach
				</select>
			</div>
		</div>

		<div class="sg-empty" data-filter-empty style="display:none;">No best students match your filters.</div>

		<div class="sg-people-list">
			@foreach($students as $index => $student)
				<article
					class="sg-people-item"
					data-filter-item
					data-programme="{{ $student->programme }}"
					data-gpa="{{ $student->gpa }}"
					data-search="{{ $student->full_name }} {{ $student->programme }} {{ $student->programme_short }} {{ $student->award }} {{ $student->gender }}"
				>
					<span class="sg-people-rank" data-row-num>{{ $index + 1 }}</span>
					<div class="sg-people-info">
						<strong class="sg-people-name">{{ $student->full_name }}</strong>
						<span class="sg-people-meta">{{ $student->programme_short }}</span>
						@if(!empty($student->award))
							<span class="sg-people-award">{{ $student->award }}</span>
						@endif
					</div>
					<div class="sg-people-stat">
						<strong>{{ number_format((float) $student->gpa, 2) }}</strong>
						<span>{{ $student->gender }}</span>
					</div>
				</article>
			@endforeach
		</div>

		<div class="sg-load-more-wrap" data-load-more-wrap style="display:none;">
			<button type="button" class="sg-load-more" data-load-more>Load more</button>
		</div>
	</div>
</section>
@endsection
