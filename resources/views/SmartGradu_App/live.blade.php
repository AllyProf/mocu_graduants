@extends('SmartGradu_App.layouts.app')

@section('title', 'Live Ceremony | ' . config('app.name'))

@section('content')
<section class="sg-page-top sg-hero-live">
	<div class="container">
		<div class="sg-section-title">
			<span class="sg-caption">Watch Online</span>
			<h1>Live Ceremony</h1>
		</div>
	</div>
</section>

<section class="sg-section">
	<div class="container">
		@if($live->streams->isEmpty())
			<div class="sg-empty">No streams have been published yet.</div>
		@else
			<div class="sg-filter">
				<div class="row align-items-end sg-live-controls">
					<div class="col-lg-3 col-md-6 col-sm-12">
						<label for="stream-filter">Available stream</label>
						<select id="stream-filter" class="form-control sg-select2" data-placeholder="Available stream">
							@foreach($live->streams as $stream)
								<option
									value="{{ $stream->id }}"
									data-youtube="{{ $stream->youtube_url }}"
									data-embed="{{ $stream->embed_url }}"
									data-live="{{ !empty($stream->is_live) ? '1' : '0' }}"
									data-status="{{ $stream->status ?? 'Scheduled' }}"
								>{{ $stream->label }}</option>
							@endforeach
						</select>
					</div>
					<div class="col-lg-4 col-md-6 col-sm-12">
						<label for="stream-url">Stream URL</label>
						<input type="text" id="stream-url" class="form-control" readonly value="">
					</div>
					<div class="col-lg-3 col-md-6 col-sm-6">
						<label>&nbsp;</label>
						<a id="stream-open-youtube" class="btn btn-block sg-btn-blue sg-live-action-btn" href="#" target="_blank" rel="noopener">Open in YouTube</a>
					</div>
					<div class="col-lg-2 col-md-6 col-sm-6">
						<label>&nbsp;</label>
						<button type="button" id="stream-copy-url" class="btn btn-block btn-default sg-live-action-btn">Copy URL</button>
					</div>
				</div>
			</div>

			<div class="sg-live-box">
				<span id="stream-status" class="sg-live-status">Scheduled</span>
				<h2>{{ $live->title }}</h2>
				@if(!empty($live->message))
					<p>{{ $live->message }}</p>
				@endif
				<div class="sg-live-frame mt-4">
					<iframe id="stream-frame" src="" allowfullscreen allow="autoplay; encrypted-media; picture-in-picture"></iframe>
				</div>
			</div>
		@endif
	</div>
</section>
@endsection
