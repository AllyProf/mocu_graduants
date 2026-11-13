@extends('SmartGradu_App.layouts.app')

@section('title', 'Best Students | ' . config('app.name'))

@section('content')
@php
	$total = $students->count();
@endphp
<section class="sg-page-top sg-hero-best sg-app-pagehead">
	<div class="container">
		<div class="sg-pagehead-bar">
			@include('SmartGradu_App.partials.page-back')
			<div class="sg-section-title">
				<span class="sg-caption" data-i18n="captionBest">Top Performers</span>
				<h1 data-i18n="bestStudents">Best Students</h1>
			</div>
		</div>
	</div>
</section>

<section class="sg-section sg-list-page">
	<div class="container" data-filter-root data-per-page="10" data-total-all="{{ $total }}">
		<div class="sg-list-summary">
			<span class="sg-list-summary-icon"><i class="fa fa-star"></i></span>
			<div class="sg-list-summary-main">
				<strong data-i18n="topPerformers">Top performers</strong>
				<span><span data-i18n="showing">Showing</span> <b data-filter-count>{{ $total }}</b> · <span data-i18n="bestAll">Best Students All</span> <b data-filter-total>{{ $total }}</b></span>
			</div>
		</div>

		<div class="sg-list-tools">
			<div class="sg-list-search-row">
				<div class="sg-list-search">
					<i class="fa fa-search" aria-hidden="true"></i>
					<input type="text" id="best_q" class="form-control" data-filter-q data-i18n-placeholder="searchName" placeholder="Search name...">
				</div>
				<button type="button" class="sg-list-clear" data-filter-clear data-i18n="clear">Clear</button>
			</div>
			<div class="sg-list-filters">
				<select id="best_level" class="form-control sg-select2" data-filter-level data-placeholder="All levels" data-sheet-title="Study level" data-i18n-data-placeholder="allLevels" data-i18n-sheet-title="studyLevel">
					<option value="">All levels</option>
					@foreach($studyLevels as $level)
						<option value="{{ $level }}">{{ $level }}</option>
					@endforeach
				</select>
				<select id="best_programme" class="form-control sg-select2" data-filter-programme data-placeholder="All programmes" data-sheet-title="Programme" data-i18n-data-placeholder="allProgrammes" data-i18n-sheet-title="programme">
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
					class="sg-people-item sg-people-item-plain"
					data-filter-item
					data-level="{{ $student->study_level }}"
					data-programme="{{ $student->programme_short }}"
					data-search="{{ $student->full_name }} {{ $student->programme }} {{ $student->programme_short }} {{ $student->gender }}"
				>
					<span class="sg-people-rank" data-row-num>{{ $index + 1 }}</span>
					<div class="sg-people-info">
						<strong class="sg-people-name">{{ $student->full_name }}</strong>
						<span class="sg-people-meta">{{ $student->programme_short }} · {{ $student->study_level }}</span>
					</div>
					<span class="sg-people-gender">{{ $student->gender }}</span>
				</article>
			@endforeach
		</div>

		<div class="sg-load-more-wrap" data-load-more-wrap style="display:none;">
			<button type="button" class="sg-load-more" data-load-more data-i18n-load-more="loadMore">Load more</button>
		</div>
	</div>
</section>
@endsection
