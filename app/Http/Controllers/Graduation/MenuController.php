<?php

namespace App\Http\Controllers\Graduation;

use App\Http\Controllers\Controller;

/**
 * Full feature menu (all home cards).
 */
class MenuController extends Controller
{
    public function __invoke()
    {
        return view('SmartGradu_App.menu');
    }
}
