<?php

namespace App\Http\Controllers\Graduation;

use App\Http\Controllers\Controller;
use App\Services\GraduationData;

class BestStudentController extends Controller
{
    public function __invoke(GraduationData $data)
    {
        $students = $data->bestStudents();

        return view('SmartGradu_App.best-students', [
            'students' => $students,
            'programmes' => $data->programmes(),
            'studyLevels' => $data->studyLevels(),
        ]);
    }
}
