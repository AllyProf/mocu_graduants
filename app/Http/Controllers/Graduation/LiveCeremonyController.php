<?php

namespace App\Http\Controllers\Graduation;

use App\Http\Controllers\Controller;
use App\Services\GraduationData;

class LiveCeremonyController extends Controller
{
    public function __invoke(GraduationData $data)
    {
        return view('SmartGradu_App.live', [
            'live' => $data->live(),
        ]);
    }
}
