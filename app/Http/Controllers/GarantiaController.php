<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GarantiaController extends Controller
{
    public function index(Request $request)
    {
        return view('garantias.index');
    }
}
