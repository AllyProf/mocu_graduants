@extends('SmartGradu_App.layouts.app')

@section('title', 'Gallery | ' . config('app.name'))

@section('content')
<section class="sg-page-top sg-hero-gallery sg-app-pagehead">
	<div class="container">
		<div class="sg-pagehead-bar">
			@include('SmartGradu_App.partials.page-back')
			<div class="sg-section-title">
				<span class="sg-caption" data-i18n="captionGallery">Memories</span>
				<h1 data-i18n="gallery">Gallery</h1>
			</div>
			<div class="sg-pagehead-actions">
				<button type="button" class="sg-pagehead-filter" data-gallery-filter-open aria-label="Filter programme" title="Filter">
					<i class="fa fa-filter" aria-hidden="true"></i>
				</button>
			</div>
		</div>
	</div>
</section>

<section class="sg-section sg-gallery-page" data-gallery-page>
	<div class="container">
		@if(session('status'))
			<div class="sg-gallery-flash">{{ session('status') }}</div>
		@endif

		<div class="sg-gallery-filter-chip" data-gallery-filter-chip hidden>
			<span data-i18n="programme">Programme</span>:
			<strong data-gallery-filter-label></strong>
			<button type="button" data-gallery-filter-clear data-i18n="clear">Clear</button>
		</div>

		@if($groups->isEmpty())
			<div class="sg-empty sg-gallery-empty">
				<p data-i18n="noMemories">No memories yet. Tap + to add the first one.</p>
			</div>
		@else
			<div class="sg-gallery-groups" data-gallery-groups>
				@foreach($groups as $group)
					<section class="sg-gallery-group" data-gallery-group data-programme="{{ $group->programme }}">
						<div class="sg-gallery-group-head">
							<div>
								<strong>{{ $group->programme }}</strong>
								<span>{{ $group->count }} {{ $group->count === 1 ? 'photo' : 'photos' }}</span>
							</div>
							<a
								class="sg-gallery-download-all"
								href="{{ route('gallery.download.programme', ['programme' => $group->programme]) }}"
								data-i18n="downloadAll"
							>Download all</a>
						</div>
						<div class="sg-gallery-photos">
							@foreach($group->images as $image)
								<figure class="sg-gallery-photo">
									<button
										type="button"
										class="sg-gallery-photo-open"
										data-gallery-view
										data-gallery-src="{{ asset($image) }}"
										data-gallery-download="{{ route('gallery.download', ['file' => $image]) }}"
										aria-label="View photo"
									>
										<img src="{{ asset($image) }}" alt="{{ $group->programme }}" loading="lazy">
									</button>
									<a
										class="sg-gallery-download"
										href="{{ route('gallery.download', ['file' => $image]) }}"
										aria-label="Download photo"
										title="Download"
									>
										<i class="fa fa-download" aria-hidden="true"></i>
									</a>
								</figure>
							@endforeach
						</div>
					</section>
				@endforeach
			</div>
			<div class="sg-empty sg-gallery-empty" data-gallery-filter-empty hidden>
				<p data-i18n="noProgrammeMatch">No photos for this programme.</p>
			</div>
		@endif
	</div>
</section>

<button type="button" class="sg-gallery-fab" data-gallery-open aria-label="Add memory">
	<i class="fa fa-plus" aria-hidden="true"></i>
</button>

{{-- Fullscreen photo viewer --}}
<div class="sg-gallery-lightbox" id="sg-gallery-lightbox" hidden aria-hidden="true">
	<button type="button" class="sg-gallery-lightbox-close" data-gallery-lightbox-close aria-label="Close">&times;</button>
	<button type="button" class="sg-gallery-lightbox-nav is-prev" data-gallery-lightbox-prev aria-label="Previous">
		<i class="fa fa-chevron-left" aria-hidden="true"></i>
	</button>
	<button type="button" class="sg-gallery-lightbox-nav is-next" data-gallery-lightbox-next aria-label="Next">
		<i class="fa fa-chevron-right" aria-hidden="true"></i>
	</button>
	<img class="sg-gallery-lightbox-img" data-gallery-lightbox-img alt="">
	<a class="sg-gallery-lightbox-download" data-gallery-lightbox-download href="#" data-i18n="downloadPhoto">Download</a>
</div>

{{-- Filter programme sheet --}}
<div class="sg-gallery-sheet" id="sg-gallery-filter-sheet" hidden aria-hidden="true">
	<div class="sg-gallery-sheet-backdrop" data-gallery-filter-close></div>
	<div class="sg-gallery-sheet-panel" role="dialog" aria-modal="true" aria-labelledby="sg-gallery-filter-title">
		<div class="sg-gallery-sheet-handle" aria-hidden="true"></div>
		<div class="sg-gallery-sheet-head">
			<h3 class="sg-gallery-sheet-title" id="sg-gallery-filter-title" data-i18n="filterProgramme">Filter programme</h3>
			<button type="button" class="sg-gallery-sheet-close" data-gallery-filter-close aria-label="Close">&times;</button>
		</div>
		<div class="sg-gallery-filter-list" data-gallery-filter-list>
			<button type="button" class="sg-gallery-filter-option is-active" data-gallery-filter-value="" data-i18n="allProgrammes">All programmes</button>
			@foreach($filterProgrammes as $programme)
				<button type="button" class="sg-gallery-filter-option" data-gallery-filter-value="{{ $programme }}">{{ $programme }}</button>
			@endforeach
		</div>
	</div>
</div>

{{-- Add memory sheet --}}
<div class="sg-gallery-sheet" id="sg-gallery-sheet" hidden aria-hidden="true">
	<div class="sg-gallery-sheet-backdrop" data-gallery-close></div>
	<div class="sg-gallery-sheet-panel" role="dialog" aria-modal="true" aria-labelledby="sg-gallery-sheet-title">
		<div class="sg-gallery-sheet-handle" aria-hidden="true"></div>
		<div class="sg-gallery-sheet-head">
			<h3 class="sg-gallery-sheet-title" id="sg-gallery-sheet-title" data-i18n="addMemory">Add memory</h3>
			<button type="button" class="sg-gallery-sheet-close" data-gallery-close aria-label="Close">&times;</button>
		</div>

		<form class="sg-gallery-form" data-gallery-form action="{{ route('gallery.store') }}" method="post" enctype="multipart/form-data">
			@csrf
			<label class="sg-gallery-field">
				<span data-i18n="programme">Programme</span>
				<select
					name="programme"
					id="gallery_programme"
					class="form-control sg-select2"
					required
					data-placeholder="Select programme"
					data-sheet-title="Programme"
					data-i18n-data-placeholder="selectProgramme"
					data-i18n-sheet-title="programme"
					data-gallery-programme
				>
					<option value="">Select programme</option>
					@foreach($programmes as $programme)
						<option value="{{ $programme->short }}">{{ $programme->short }}</option>
					@endforeach
				</select>
			</label>

			<div class="sg-gallery-upload-wrap" data-gallery-upload-wrap hidden>
				<div class="sg-gallery-field">
					<span data-i18n="uploadPhotos">Photos (max 3)</span>
					<div class="sg-gallery-slots" data-gallery-slots>
						@for($i = 0; $i < 3; $i++)
							<label class="sg-gallery-slot" data-gallery-slot="{{ $i }}">
								<input type="file" accept="image/jpeg,image/png,image/webp" data-gallery-slot-input="{{ $i }}">
								<span class="sg-gallery-slot-empty">
									<i class="fa fa-camera" aria-hidden="true"></i>
									<small data-i18n="addPhoto">Add</small>
								</span>
								<img class="sg-gallery-slot-preview" alt="" hidden>
								<button type="button" class="sg-gallery-slot-remove" data-gallery-slot-remove="{{ $i }}" hidden aria-label="Remove">&times;</button>
							</label>
						@endfor
					</div>
					<p class="sg-gallery-hint" data-gallery-hint data-i18n="uploadHint">Tap a box to add a photo. Maximum 3.</p>
				</div>

				<label class="sg-gallery-field">
					<span data-i18n="captionOptional">Caption (optional)</span>
					<input type="text" name="caption" class="form-control" maxlength="200" data-i18n-placeholder="captionPlaceholder" placeholder="Say something about this memory...">
				</label>

				<div class="sg-gallery-status" data-gallery-status hidden></div>
				<button type="submit" class="sg-gallery-submit" data-i18n="saveMemory">Save memory</button>
			</div>
		</form>
	</div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('SmartGradu_App/assets/js/sg-gallery.js?v=4') }}"></script>
@endpush
