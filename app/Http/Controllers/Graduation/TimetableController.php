<?php

namespace App\Http\Controllers\Graduation;

use App\Http\Controllers\Controller;
use App\Services\GraduationData;

class TimetableController extends Controller
{
    public function __invoke(GraduationData $data)
    {
        return view('SmartGradu_App.timetable', [
            'items' => $data->timetable(),
            'eventTypes' => $data->eventTypes(),
        ]);
    }
}
