<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CobrancaController extends Controller
{
    public function index(Request $request)
    {
        return view('cobrancas.index');
    }
}
