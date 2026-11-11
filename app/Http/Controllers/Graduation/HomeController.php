<?php

namespace App\Http\Controllers\Graduation;

use App\Http\Controllers\Controller;
use App\Services\GraduationData;

class HomeController extends Controller
{
    public function __invoke(GraduationData $data)
    {
        return view('SmartGradu_App.home', [
            'graduandCount' => $data->graduands()->count(),
            'bestCount' => $data->bestStudents()->count(),
            'nextEvent' => $data->timetable()->first(),
            'live' => $data->live(),
        ]);
    }
}
