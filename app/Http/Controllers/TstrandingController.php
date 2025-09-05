<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TstrandingController extends Controller
{

  public function index()
  {
    return view('Layouts.Lstranding', ['editable' => false], ['tipod' => 'text']);
  }

  public function indexAdmin()
  {
    return view('Layouts.Lstranding', ['editable' => true], ['tipod' => 'text']);
  }
}
