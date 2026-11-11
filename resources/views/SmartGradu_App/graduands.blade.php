@extends('SmartGradu_App.layouts.app')

@section('title', 'Graduands | ' . config('app.name'))

@section('content')
<section class="sg-page-top sg-hero-graduands">
	<div class="container">
		<div class="sg-section-title">
			<span class="sg-caption">Ceremony Lists</span>
			<h1>Graduands</h1>
		</div>
	</div>
</section>

<section class="sg-section">
	<div class="container" data-filter-root data-per-page="10">
		<div class="sg-filter">
			<div class="row">
				<div class="col-md-3 col-sm-6">
					<label for="study_level">Study level</label>
					<select id="study_level" class="form-control sg-select2" data-filter-level data-placeholder="All levels">
						<option value="">All levels</option>
						@foreach($studyLevels as $level)
							<option value="{{ $level }}">{{ $level }}</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-4 col-sm-6">
					<label for="programme">Programme</label>
					<select id="programme" class="form-control sg-select2" data-filter-programme data-placeholder="All programmes">
						<option value="">All programmes</option>
						@foreach($programmes as $programme)
							<option value="{{ $programme }}">{{ $programme }}</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-5 col-sm-12">
					<label for="q">Search</label>
					<input type="text" id="q" class="form-control" data-filter-q placeholder="Type a name...">
				</div>
			</div>
		</div>

		<p class="mb-3"><strong data-filter-count>{{ $graduands->count() }}</strong> result(s)</p>
		<div class="sg-empty" data-filter-empty style="display:none;">No graduands match your filters.</div>

		@if($graduands->isNotEmpty())
			<div class="sg-table-wrap">
				<table class="table sg-table mb-0">
					<thead>
						<tr>
							<th>#</th>
							<th>Name</th>
							<th>Gender</th>
							<th>Study Level</th>
							<th>Programme</th>
							<th>Code</th>
						</tr>
					</thead>
					<tbody>
						@foreach($graduands as $index => $graduand)
							<tr
								data-filter-item
								data-level="{{ $graduand->study_level }}"
								data-programme="{{ $graduand->programme }}"
								data-search="{{ $graduand->full_name }} {{ $graduand->programme_short }} {{ $graduand->programme }} {{ $graduand->gender }}"
							>
								<td data-row-num>{{ $index + 1 }}</td>
								<td>
									{{ $graduand->full_name }}
									@if(!empty($graduand->is_best_student))
										<span class="sg-badge">Best</span>
									@endif
								</td>
								<td>{{ $graduand->gender }}</td>
								<td>{{ $graduand->study_level }}</td>
								<td>{{ $graduand->programme }}</td>
								<td><strong>{{ $graduand->programme_short }}</strong></td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
			<nav class="sg-pagination" data-pagination aria-label="Graduands pagination"></nav>
		@endif
	</div>
</section>
@endsection
