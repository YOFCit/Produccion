<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TcoloringController extends Controller
{
  public function index()
  {
    return view('layouts.Lcoloring', ['editable' => false]);
  }

  public function indexAdmin()
  {
    return view('layouts.Lcoloring', ['editable' => true]);
  }
}
