<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tcoloring extends Model
{
  use HasFactory;
  protected $fillable = [
    'equipo',
    'turno',
    'dias',
    'tipofibra',
    'plan',
    'c',
    'type',
    'planuser',
    'cuser',
    'typeuser',
  ];
}
