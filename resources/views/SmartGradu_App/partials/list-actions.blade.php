@php
	$listType = $listType ?? 'graduands';
@endphp
<div class="sg-complaint" data-complaint-panel style="display:none;" data-list-type="{{ $listType }}">
	<div class="sg-complaint-card">
		<div class="sg-complaint-head">
			<strong data-i18n="nameNotFound">Name not found?</strong>
			<p data-i18n="nameNotFoundHint">If you searched and your name is missing, send a complaint to the graduation desk.</p>
		</div>
		<form class="sg-complaint-form" data-complaint-form action="{{ route('complaints.store') }}" method="post">
			@csrf
			<input type="hidden" name="list_type" value="{{ $listType }}">
			<input type="hidden" name="searched_name" value="" data-complaint-searched>
			<label class="sg-complaint-field">
				<span data-i18n="yourFullName">Your full name</span>
				<input type="text" name="full_name" class="form-control" required maxlength="120" autocomplete="name">
			</label>
			<label class="sg-complaint-field">
				<span data-i18n="registrationNumber">Registration number</span>
				<input type="text" name="registration_number" class="form-control" maxlength="60" autocomplete="off">
			</label>
			<div class="sg-complaint-grid">
				<label class="sg-complaint-field">
					<span data-i18n="studyLevel">Study level</span>
					<select
						name="study_level"
						id="complaint_level_{{ $listType }}"
						class="form-control sg-select2"
						data-placeholder="—"
						data-sheet-title="Study level"
						data-i18n-data-placeholder="selectOptional"
						data-i18n-sheet-title="studyLevel"
					>
						<option value="">—</option>
						@foreach($studyLevels as $level)
							<option value="{{ $level }}">{{ $level }}</option>
						@endforeach
					</select>
				</label>
				<label class="sg-complaint-field">
					<span data-i18n="programme">Programme</span>
					<select
						name="programme"
						id="complaint_programme_{{ $listType }}"
						class="form-control sg-select2"
						data-placeholder="—"
						data-sheet-title="Programme"
						data-i18n-data-placeholder="selectOptional"
						data-i18n-sheet-title="programme"
					>
						<option value="">—</option>
						@foreach($programmes as $programme)
							<option value="{{ $programme }}">{{ $programme }}</option>
						@endforeach
					</select>
				</label>
			</div>
			<label class="sg-complaint-field">
				<span data-i18n="phone">Phone</span>
				<div class="sg-phone-row">
					<select
						name="country_code"
						id="complaint_country_{{ $listType }}"
						class="form-control sg-select2 sg-country-select"
						data-placeholder="Code"
						data-sheet-title="Country code"
						data-i18n-data-placeholder="countryCode"
						data-i18n-sheet-title="countryCode"
						required
					>
						@include('SmartGradu_App.partials.country-codes')
					</select>
					<input
						type="tel"
						name="phone"
						class="form-control sg-phone-input"
						required
						maxlength="9"
						minlength="9"
						pattern="[0-9]{9}"
						inputmode="numeric"
						autocomplete="tel-national"
						placeholder="7XXXXXXXX"
						data-i18n-placeholder="phonePlaceholder"
						data-phone-digits
					>
				</div>
			</label>
			<label class="sg-complaint-field">
				<span data-i18n="complaintMessage">What is missing / your message</span>
				<textarea name="message" class="form-control" rows="3" required maxlength="1000"></textarea>
			</label>
			<div class="sg-complaint-status" data-complaint-status hidden></div>
			<button type="submit" class="sg-complaint-submit" data-i18n="sendComplaint">Send complaint</button>
		</form>
	</div>
</div>
