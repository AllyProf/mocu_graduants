<?php

namespace App\Http\Controllers\Graduation;

use App\Http\Controllers\Controller;
use App\Services\GraduationData;

/**
 * Live ceremony stream selector and embed page.
 */
class LiveCeremonyController extends Controller
{
    public function __invoke(GraduationData $data)
    {
        return view('SmartGradu_App.live', [
            'live' => $data->live(),
        ]);
    }
}
