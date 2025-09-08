<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tstranding extends Model
{
  use HasFactory;

  protected $fillable = [
    'equipo',
    'turno',
    'tipo',
    'priority',
    'dia',
    'plan',
    'c',
    'planuser',
    'cuser',
    'diaajustable'
  ];
}
