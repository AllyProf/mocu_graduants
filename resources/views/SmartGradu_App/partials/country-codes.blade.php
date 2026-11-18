@php
	$countries = [
		['code' => '+255', 'flag' => '🇹🇿', 'name' => 'Tanzania'],
		['code' => '+254', 'flag' => '🇰🇪', 'name' => 'Kenya'],
		['code' => '+256', 'flag' => '🇺🇬', 'name' => 'Uganda'],
		['code' => '+250', 'flag' => '🇷🇼', 'name' => 'Rwanda'],
		['code' => '+257', 'flag' => '🇧🇮', 'name' => 'Burundi'],
		['code' => '+251', 'flag' => '🇪🇹', 'name' => 'Ethiopia'],
		['code' => '+253', 'flag' => '🇩🇯', 'name' => 'Djibouti'],
		['code' => '+252', 'flag' => '🇸🇴', 'name' => 'Somalia'],
		['code' => '+27', 'flag' => '🇿🇦', 'name' => 'South Africa'],
		['code' => '+234', 'flag' => '🇳🇬', 'name' => 'Nigeria'],
		['code' => '+233', 'flag' => '🇬🇭', 'name' => 'Ghana'],
		['code' => '+20', 'flag' => '🇪🇬', 'name' => 'Egypt'],
		['code' => '+971', 'flag' => '🇦🇪', 'name' => 'UAE'],
		['code' => '+966', 'flag' => '🇸🇦', 'name' => 'Saudi Arabia'],
		['code' => '+44', 'flag' => '🇬🇧', 'name' => 'United Kingdom'],
		['code' => '+1', 'flag' => '🇺🇸', 'name' => 'United States'],
		['code' => '+91', 'flag' => '🇮🇳', 'name' => 'India'],
		['code' => '+86', 'flag' => '🇨🇳', 'name' => 'China'],
	];
	$selected = $selected ?? '+255';
@endphp
@foreach($countries as $country)
	<option value="{{ $country['code'] }}" @selected($selected === $country['code'])>
		{{ $country['flag'] }} {{ $country['code'] }} {{ $country['name'] }}
	</option>
@endforeach
