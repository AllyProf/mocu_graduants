<?php

namespace App\Http\Controllers\Graduation;

use App\Http\Controllers\Controller;
use App\Services\GraduationData;

class GraduandController extends Controller
{
    public function __invoke(GraduationData $data)
    {
        return view('SmartGradu_App.graduands', [
            'graduands' => $data->graduands(),
            'studyLevels' => $data->studyLevels(),
            'programmes' => $data->programmes(),
        ]);
    }
}
