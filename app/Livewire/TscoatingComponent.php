<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\tscoating;
use Illuminate\Support\Carbon;

class TscoatingComponent extends Component
{
  public $equipos = ['SC-01', 'SC-02', 'SC-03'];
  public $turnos = ['1', '2', '3'];
  public $tipos = ['1', 'MTTO', 'RMK'];
  public $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

  public $values = [];       // Valores editables
  public $diaajustable = []; // Encabezados de días
  public $editable = false;
  public $tipod;

  public function mount($editable = false, $tipod = null)
  {
    $this->editable = $editable;
    $this->tipod = $tipod;

    $startOfWeek = Carbon::now()->startOfWeek();
    $endOfWeek = $startOfWeek->copy()->addDays(5);

    $registros = tscoating::whereBetween('dia', [
      $startOfWeek->format('Y-m-d'),
      $endOfWeek->format('Y-m-d')
    ])->get();

    foreach ($this->equipos as $equipo) {
      foreach ($this->diasSemana as $diaNombre) {
        $dia = $this->getDateForDia($diaNombre);

        foreach ($this->turnos as $turno) {
          foreach ($this->tipos as $tipo) {
            $registro = $registros
              ->where('equipo', $equipo)
              ->where('dia', $dia->format('Y-m-d'))
              ->where('turno', $turno)
              ->where('tipo', $tipo)
              ->first();

            $this->values[$equipo][$diaNombre][$turno][$tipo] = [
              'diaajustable' => $registro->diaajustable ?? $dia->day,
              'priority' => $registro->priority ?? $this->getPriorityMap()[$turno][$tipo] ?? 1,
            ];
          }
        }
      }
    }

    foreach ($this->diasSemana as $index => $diaNombre) {
      $this->diaajustable[$index] = $this->getDateForDia($diaNombre)->day;
    }
  }

  public function save()
  {
    foreach ($this->equipos as $equipo) {
      foreach ($this->diasSemana as $diaNombre) {
        $dia = $this->getDateForDia($diaNombre)->format('Y-m-d');

        foreach ($this->turnos as $turno) {
          foreach ($this->tipos as $tipo) {
            $diaajustable = $this->values[$equipo][$diaNombre][$turno][$tipo]['diaajustable'] ?? $this->getDateForDia($diaNombre)->day;

            Tscoating::updateOrCreate(
              [
                'equipo' => $equipo,
                'dia' => $dia,
                'turno' => $turno,
                'tipo' => $tipo,
              ],
              [
                'diaajustable' => (int)$diaajustable, // Solo guardamos el número
              ]
            );
          }
        }
      }
    }

    session()->flash('message', 'Datos guardados correctamente.');
  }

  private function getDateForDia($diaNombre)
  {
    $dias = [
      'Lunes' => 0,
      'Martes' => 1,
      'Miércoles' => 2,
      'Jueves' => 3,
      'Viernes' => 4,
      'Sábado' => 5,
    ];

    return Carbon::now()->startOfWeek()->addDays($dias[$diaNombre] ?? 0);
  }

  private function getPriorityMap()
  {
    return [
      '1' => ['1' => 1, 'MTTO' => 3, 'RMK' => 2],
      '2' => ['1' => 4, 'MTTO' => 6, 'RMK' => 5],
      '3' => ['1' => 7, 'MTTO' => 9, 'RMK' => 8],
    ];
  }

  public function render()
  {
    return view('livewire.tscoating-component');
  }
}
