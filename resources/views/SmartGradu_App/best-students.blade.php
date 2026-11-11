@extends('SmartGradu_App.layouts.app')

@section('title', 'Best Students | ' . config('app.name'))

@section('content')
<section class="sg-page-top sg-hero-best">
	<div class="container">
		<div class="sg-section-title">
			<span class="sg-caption">Top Performers</span>
			<h1>Best Students</h1>
		</div>
	</div>
</section>

<section class="sg-section">
	<div class="container" data-filter-root data-per-page="10">
		<div class="sg-filter">
			<div class="row">
				<div class="col-md-5 col-sm-6">
					<label for="best_programme">Programme</label>
					<select id="best_programme" class="form-control sg-select2" data-filter-programme data-placeholder="All programmes">
						<option value="">All programmes</option>
						@foreach($programmes as $programme)
							<option value="{{ $programme }}">{{ $programme }}</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-7 col-sm-6">
					<label for="best_q">Search</label>
					<input type="text" id="best_q" class="form-control" data-filter-q placeholder="Search by name or award...">
				</div>
			</div>
		</div>

		<p class="mb-3"><strong data-filter-count>{{ $students->count() }}</strong> result(s)</p>
		<div class="sg-empty" data-filter-empty style="display:none;">No best students match your filters.</div>

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
						<th>Award</th>
					</tr>
				</thead>
				<tbody>
					@foreach($students as $index => $student)
						<tr
							data-filter-item
							data-programme="{{ $student->programme }}"
							data-search="{{ $student->full_name }} {{ $student->programme }} {{ $student->programme_short }} {{ $student->award }} {{ $student->gender }}"
						>
							<td data-row-num>{{ $index + 1 }}</td>
							<td>{{ $student->full_name }}</td>
							<td>{{ $student->gender }}</td>
							<td>{{ $student->study_level }}</td>
							<td>{{ $student->programme }}</td>
							<td><strong>{{ $student->programme_short }}</strong></td>
							<td><span class="sg-badge">{{ $student->award ?: 'Best Student' }}</span></td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
		<nav class="sg-pagination" data-pagination aria-label="Best students pagination"></nav>
	</div>
</section>
@endsection
