<?php

namespace App\Http\Controllers\Graduation;

use App\Http\Controllers\Controller;
use App\Services\GraduationData;

/**
 * University leaders page.
 */
class LeaderController extends Controller
{
    public function __invoke(GraduationData $data)
    {
        return view('SmartGradu_App.leaders', [
            'leaders' => $data->leaders(),
        ]);
    }
}
