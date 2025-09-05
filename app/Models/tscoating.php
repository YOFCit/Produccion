<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tscoating extends Model
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
    'type',
    'planuser',
    'cuser',
    'typeuser',
  ];
}
