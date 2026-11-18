<?php

namespace App\Http\Controllers\Graduation;

use App\Http\Controllers\Controller;
use App\Services\GraduationData;

/**
 * Ceremony graduands list page with study-level and programme filters.
 */
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
