<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TscoatingController extends Controller
{
  public function index()
  {
    return view('Layouts.Lscoating', ['editable' => false], ['tipod' => 'text']);
  }

  public function indexAdmin()
  {
    return view('Layouts.Lscoating', ['editable' => true], ['tipod' => 'text']);
  }
}
