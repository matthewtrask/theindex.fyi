<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ColophonController extends Controller
{
    public function __invoke(): View
    {
        return view('colophon');
    }
}
