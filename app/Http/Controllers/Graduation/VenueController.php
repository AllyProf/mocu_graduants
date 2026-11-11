<?php

namespace App\Http\Controllers\Graduation;

use App\Http\Controllers\Controller;
use App\Services\GraduationData;

class VenueController extends Controller
{
    public function __invoke(GraduationData $data)
    {
        return view('SmartGradu_App.venues', [
            'locations' => $data->locations(),
        ]);
    }
}
