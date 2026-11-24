@php
	/*
	| Home shows 6 main cards. Live + Help stay on the bottom tab only.
	*/
	$allCards = [
		['route' => 'graduands', 'icon' => 'fa-graduation-cap', 'tone' => 'blue', 'i18n' => 'graduands', 'label' => 'Graduands'],
		['route' => 'best-students', 'icon' => 'fa-trophy', 'tone' => 'blue', 'i18n' => 'bestStudents', 'label' => 'Best Students'],
		['route' => 'timetable', 'icon' => 'fa-calendar', 'tone' => 'blue', 'i18n' => 'timetable', 'label' => 'Timetable'],
		['route' => 'leaders', 'icon' => 'fa-users', 'tone' => 'blue', 'i18n' => 'leaders', 'label' => 'Leadership'],
		['route' => 'gallery', 'icon' => 'fa-camera', 'tone' => 'blue', 'i18n' => 'gallery', 'label' => 'Gallery'],
		['route' => 'venues', 'icon' => 'fa-map-marker', 'tone' => 'blue', 'i18n' => 'venuesShort', 'label' => 'Venues'],
		['route' => 'live', 'icon' => 'fa-video-camera', 'tone' => 'blue', 'i18n' => 'liveShort', 'label' => 'Live'],
		['route' => 'help', 'icon' => 'fa-question-circle', 'tone' => 'blue', 'i18n' => 'help', 'label' => 'Help'],
	];

	$homeOnly = ! empty($homeOnly);
	$cards = $homeOnly ? array_slice($allCards, 0, 6) : $allCards;
@endphp

<div class="sg-dash-grid">
	@foreach($cards as $card)
		<a class="sg-dash-card" href="{{ route($card['route']) }}">
			<span class="sg-dash-icon sg-dash-icon-{{ $card['tone'] }}"><i class="fa {{ $card['icon'] }}"></i></span>
			<span class="sg-dash-label" data-i18n="{{ $card['i18n'] }}">{{ $card['label'] }}</span>
		</a>
	@endforeach
</div>
