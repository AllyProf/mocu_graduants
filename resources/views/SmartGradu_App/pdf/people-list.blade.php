<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>{{ $title }}</title>
	<style>
		@page { margin: 28px 24px 32px; }
		body {
			font-family: DejaVu Sans, sans-serif;
			font-size: 10px;
			color: #222;
			margin: 0;
			position: relative;
		}
		.watermark {
			position: fixed;
			top: 28%;
			left: 18%;
			width: 64%;
			opacity: 0.08;
			z-index: -1;
			text-align: center;
		}
		.watermark img {
			width: 320px;
			height: auto;
		}
		.header {
			text-align: center;
			margin-bottom: 10px;
		}
		.header-logo {
			width: 78px;
			height: auto;
			margin: 0 auto 6px;
			display: block;
		}
		.uni-name {
			margin: 0;
			font-size: 16px;
			font-weight: 700;
			color: #056fe7;
			letter-spacing: 0.02em;
			text-transform: uppercase;
		}
		.uni-event {
			margin: 4px 0 0;
			font-size: 11px;
			font-weight: 700;
			color: #222;
		}
		.uni-address {
			margin: 3px 0 0;
			font-size: 9px;
			color: #555;
		}
		.header-line {
			border: 0;
			border-top: 2px solid #056fe7;
			margin: 10px 0 12px;
		}
		.report-title {
			margin: 0 0 10px;
			font-size: 13px;
			font-weight: 700;
			color: #222;
		}
		.stats {
			width: 100%;
			border-collapse: separate;
			border-spacing: 6px 0;
			margin: 0 0 14px;
		}
		.stat-box {
			border: 1px solid #d0d5dd;
			padding: 8px 6px;
			text-align: center;
			vertical-align: top;
			background: #fff;
		}
		.stat-value {
			display: block;
			font-size: 15px;
			font-weight: 700;
			color: #056fe7;
			line-height: 1.2;
			margin-bottom: 2px;
		}
		.stat-value.is-green { color: #067647; }
		.stat-value.is-yellow { color: #a16207; }
		.stat-value.is-navy { color: #0b3a82; }
		.stat-label {
			display: block;
			font-size: 8px;
			color: #667085;
			line-height: 1.25;
		}
		.meta {
			margin: 0 0 8px;
			font-size: 9px;
			color: #667085;
		}
		.section-title {
			margin: 0 0 6px;
			font-size: 11px;
			font-weight: 700;
			color: #222;
			text-transform: uppercase;
		}
		table.data {
			width: 100%;
			border-collapse: collapse;
		}
		table.data th,
		table.data td {
			border: 1px solid #d0d5dd;
			padding: 6px 7px;
			text-align: left;
			vertical-align: top;
		}
		table.data th {
			background: #056fe7;
			color: #fff;
			font-size: 9px;
			font-weight: 700;
			text-transform: uppercase;
			letter-spacing: 0.02em;
		}
		table.data tr:nth-child(even) td {
			background: #f5f7fb;
		}
		.num { width: 28px; text-align: center; }
		.center { text-align: center; }
		.empty {
			padding: 20px;
			text-align: center;
			color: #667085;
			border: 1px dashed #d0d5dd;
		}
		.badge {
			display: inline-block;
			padding: 1px 6px;
			border-radius: 8px;
			background: #edeb04;
			color: #6d6a00;
			font-size: 8px;
			font-weight: 700;
		}
		.badge-green {
			background: #e8f8ef;
			color: #067647;
		}
		.footer {
			position: fixed;
			left: 0;
			right: 0;
			bottom: -12px;
			font-size: 8px;
			color: #667085;
			text-align: center;
		}
	</style>
</head>
<body>
	@if(!empty($logoSrc))
		<div class="watermark">
			<img src="{{ $logoSrc }}" alt="">
		</div>
	@endif

	<div class="header">
		@if(!empty($logoSrc))
			<img class="header-logo" src="{{ $logoSrc }}" alt="MoCU">
		@endif
		<p class="uni-name">Moshi Co-operative University</p>
		<p class="uni-event">{{ $eventTitle }}</p>
		<p class="uni-address">P.O. Box 474, Moshi, Tanzania</p>
	</div>
	<hr class="header-line">

	<p class="report-title">{{ $title }}</p>

	<table class="stats">
		<tr>
			<td class="stat-box">
				<span class="stat-value">{{ $stats['total'] }}</span>
				<span class="stat-label">Total Records</span>
			</td>
			<td class="stat-box">
				<span class="stat-value is-navy">{{ $stats['male'] }}</span>
				<span class="stat-label">Male</span>
			</td>
			<td class="stat-box">
				<span class="stat-value is-green">{{ $stats['female'] }}</span>
				<span class="stat-label">Female</span>
			</td>
			<td class="stat-box">
				<span class="stat-value is-yellow">{{ $stats['best'] }}</span>
				<span class="stat-label">Best Students</span>
			</td>
			<td class="stat-box">
				<span class="stat-value">{{ $stats['programmes'] }}</span>
				<span class="stat-label">Programmes</span>
			</td>
			<td class="stat-box">
				<span class="stat-value is-navy">{{ $stats['levels'] }}</span>
				<span class="stat-label">Study Levels</span>
			</td>
		</tr>
	</table>

	<p class="meta">Generated {{ $generatedAt }}@if(!empty($filterSummary)) · {{ $filterSummary }}@endif</p>
	<p class="section-title">{{ $sectionTitle }}</p>

	@if($rows->isEmpty())
		<div class="empty">No records match the selected filters.</div>
	@else
		<table class="data">
			<thead>
				<tr>
					<th class="num">#</th>
					<th>Full Name</th>
					<th>Programme</th>
					<th>Study Level</th>
					@if($showYear)
						<th>Year</th>
					@endif
					<th>Gender</th>
					@if($showAward)
						<th>Note</th>
					@endif
				</tr>
			</thead>
			<tbody>
				@foreach($rows as $index => $row)
					@php
						$note = '';
						$badgeClass = 'badge';
						if (!empty($row->is_best_student) && ($row->best_award ?? '') === 'overall') {
							$note = 'Overall best';
							$badgeClass = 'badge badge-green';
						} elseif (!empty($row->is_best_student)) {
							$note = 'Best student';
						}
					@endphp
					<tr>
						<td class="num">{{ $index + 1 }}</td>
						<td>{{ $row->full_name }}</td>
						<td>{{ $row->programme_short }}{{ !empty($row->programme) ? ' — '.$row->programme : '' }}</td>
						<td>{{ $row->study_level }}</td>
						@if($showYear)
							<td>{{ $row->year_of_study ?? '—' }}</td>
						@endif
						<td class="center">{{ $row->gender }}</td>
						@if($showAward)
							<td>@if($note !== '')<span class="{{ $badgeClass }}">{{ $note }}</span>@else—@endif</td>
						@endif
					</tr>
				@endforeach
			</tbody>
		</table>
	@endif

	<div class="footer">Moshi Co-operative University · Graduation Companion · {{ $generatedAt }}</div>
</body>
</html>
