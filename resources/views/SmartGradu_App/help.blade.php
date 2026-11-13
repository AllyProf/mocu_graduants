@extends('SmartGradu_App.layouts.app')

@section('title', 'Help | ' . config('app.name'))

@section('content')
<section class="sg-page-top sg-hero-help sg-app-pagehead">
	<div class="container">
		<div class="sg-pagehead-bar">
			@include('SmartGradu_App.partials.page-back')
			<div class="sg-section-title">
				<span class="sg-caption" data-i18n="captionSupport">Support</span>
				<h1 data-i18n="helpTitle">{{ $help->title }}</h1>
			</div>
		</div>
	</div>
</section>

<section class="sg-section sg-help-page">
	<div class="container">
		<div class="sg-help-block sg-help-block-contacts">
			<div class="sg-help-block-head">
				<h2 data-i18n="quickContacts">Quick contacts</h2>
			</div>
			<div class="sg-help-contact-cards">
				@forelse($help->contacts as $contact)
					@php $tel = preg_replace('/\s+/', '', $contact->value); @endphp
					@if(($contact->type ?? '') === 'phone' || ($contact->type ?? '') === 'fax')
						<a class="sg-help-card" href="tel:{{ $tel }}">
							<span class="sg-help-card-icon sg-help-card-icon-phone"><i class="fa fa-phone"></i></span>
							<span class="sg-help-card-text">
								<span class="sg-help-card-label">{{ $contact->label }}</span>
								<strong>{{ $contact->value }}</strong>
							</span>
							<i class="fa fa-angle-right sg-help-card-chevron" aria-hidden="true"></i>
						</a>
					@elseif(($contact->type ?? '') === 'email')
						<a class="sg-help-card" href="mailto:{{ $contact->value }}">
							<span class="sg-help-card-icon sg-help-card-icon-email"><i class="fa fa-envelope"></i></span>
							<span class="sg-help-card-text">
								<span class="sg-help-card-label">{{ $contact->label }}</span>
								<strong>{{ $contact->value }}</strong>
							</span>
							<i class="fa fa-angle-right sg-help-card-chevron" aria-hidden="true"></i>
						</a>
					@else
						<div class="sg-help-card is-static">
							<span class="sg-help-card-icon sg-help-card-icon-place"><i class="fa fa-map-marker"></i></span>
							<span class="sg-help-card-text">
								<span class="sg-help-card-label">{{ $contact->label }}</span>
								<strong>{{ $contact->value }}</strong>
							</span>
						</div>
					@endif
				@empty
					<div class="sg-empty">No contacts published yet.</div>
				@endforelse
			</div>
		</div>

		<div class="sg-help-block">
			<div class="sg-help-block-head">
				<h2>FAQs</h2>
				<p>Common graduation questions</p>
			</div>
			<div class="sg-accordion sg-help-faq" id="sg-faq-accordion">
				@forelse($help->faqs as $index => $faq)
					@php $isOpen = $index === 0; @endphp
					<div class="sg-acc-item {{ $isOpen ? 'is-open' : '' }}">
						<button type="button" class="sg-acc-trigger" aria-expanded="{{ $isOpen ? 'true' : 'false' }}">
							<span class="sg-acc-title">{{ $faq->question }}</span>
							<span class="sg-acc-chevron" aria-hidden="true"><i class="fa fa-angle-down"></i></span>
						</button>
						<div class="sg-acc-panel" @if(!$isOpen) hidden @endif>
							<p>{{ $faq->answer }}</p>
						</div>
					</div>
				@empty
					<div class="sg-empty">No help articles yet.</div>
				@endforelse
			</div>
		</div>
	</div>
</section>
@endsection
