@extends('SmartGradu_App.layouts.app')

@section('title', 'Help | ' . config('app.name'))

@section('content')
<section class="sg-page-top sg-hero-help">
	<div class="container">
		<div class="sg-section-title">
			<span class="sg-caption">Support</span>
			<h1>{{ $help->title }}</h1>
		</div>
	</div>
</section>

<section class="sg-section">
	<div class="container">
		<div class="row sg-help-layout">
			<div class="col-lg-7 mb-4">
				<div class="sg-accordion" id="sg-faq-accordion">
					@forelse($help->faqs as $index => $faq)
						@php $isOpen = $index === 0; @endphp
						<div class="sg-acc-item {{ $isOpen ? 'is-open' : '' }}">
							<button type="button" class="sg-acc-trigger" aria-expanded="{{ $isOpen ? 'true' : 'false' }}">
								<span class="sg-acc-icon" aria-hidden="true"></span>
								<span class="sg-acc-title">{{ $faq->question }}</span>
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
			<div class="col-lg-5 mb-4">
				<div class="sg-help-contacts-panel">
					<h3>Contact details</h3>
					<div class="sg-help-contacts-grid">
						@forelse($help->contacts as $contact)
							@php $tel = preg_replace('/\s+/', '', $contact->value); @endphp
							<div class="sg-help-contact-row">
								<span class="sg-help-contact-label">{{ $contact->label }}</span>
								@if(($contact->type ?? '') === 'phone' || ($contact->type ?? '') === 'fax')
									<a href="tel:{{ $tel }}">{{ $contact->value }}</a>
								@elseif(($contact->type ?? '') === 'email')
									<a href="mailto:{{ $contact->value }}">{{ $contact->value }}</a>
								@else
									<span>{{ $contact->value }}</span>
								@endif
							</div>
						@empty
							<p>No contacts published yet.</p>
						@endforelse
					</div>
				</div>
				<div class="sg-help-care-wrap sg-help-care-right">
					<img class="sg-help-care-img" src="{{ asset('SmartGradu_App/assets/img/sg-help-care.jpg') }}" alt="Customer care support">
					<span class="sg-help-care-overlay" aria-hidden="true"></span>
				</div>
			</div>
		</div>
	</div>
</section>
@endsection
