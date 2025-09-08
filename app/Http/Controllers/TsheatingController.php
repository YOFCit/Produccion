<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TsheatingController extends Controller
{
  public function index()
  {
    return view('layouts.Lsheating', ['editable' => false], ['tipod' => 'text']);
  }

  public function indexAdmin()
  {
    return view('layouts.Lsheating', ['editable' => true], ['tipod' => 'text']);
  }
}
