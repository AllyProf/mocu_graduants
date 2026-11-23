@extends('SmartGradu_App.layouts.app')

@section('title', 'Governance & Leadership | ' . config('app.name'))

@section('content')
<section class="sg-page-top sg-hero-leaders sg-app-pagehead">
	<div class="container">
		<div class="sg-pagehead-bar">
			@include('SmartGradu_App.partials.page-back')
			<div class="sg-section-title">
				<span class="sg-caption" data-i18n="captionLeaders">Leadership</span>
				<h1 data-i18n="leaders">Governance & Leadership</h1>
			</div>
		</div>
	</div>
</section>

<section class="sg-section">
	<div class="container">
		@if($leaders->isEmpty())
			<div class="sg-empty">
				<p data-i18n="noLeaders">Leaders will appear here soon.</p>
			</div>
		@else
			<div class="sg-leaders-list">
				@foreach($leaders as $leader)
					<article class="sg-leader-card">
						<div class="sg-leader-photo">
							<img src="{{ asset($leader->image) }}" alt="{{ $leader->name }}" loading="lazy">
						</div>
						<div class="sg-leader-copy">
							<strong class="sg-leader-name">{{ $leader->name }}</strong>
							<span class="sg-leader-title">{{ $leader->title }}</span>
						</div>
					</article>
				@endforeach
			</div>
		@endif
	</div>
</section>
@endsection
