<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TcoloringController extends Controller
{
  public function index()
  {
    return view('Layouts.Lcoloring', ['editable' => false]);
  }

  public function indexAdmin()
  {
    return view('Layouts.Lcoloring', ['editable' => true]);
  }
}
