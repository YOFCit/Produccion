<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\tsheating;
use Illuminate\Support\Carbon;
use App\Exports\TsheatingExport;
use Maatwebsite\Excel\Facades\Excel;

class TsheatingComponent extends Component
{
  public $equipos = ['SH-01', 'SH-02', 'SH-03', 'SH-04', 'SH-05'];
  public $turnos = ['1', '2', '3'];
  public $tipos = ['1', 'MTTO', 'RMK'];
  public $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
  public $values = [];
  public $editable = false;
  public $tipod;
  public $isAdmin = false;
  public $diaajustable = [];


  public function render()
  {
    return view('livewire.tsheating-component');
  }

  public function export()
  {
    return Excel::download(new TsheatingExport, 'tsheating.xlsx');
  }

  private function getDiaNombre($date)
  {
    $carbonDate = Carbon::parse($date);
    $dias = [
      1 => 'Lunes',
      2 => 'Martes',
      3 => 'Miércoles',
      4 => 'Jueves',
      5 => 'Viernes',
      6 => 'Sábado',
      0 => 'Domingo',
    ];

    return $dias[$carbonDate->dayOfWeek] ?? 'Lunes';
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
      '1' => ['1' => 1, 'MTTO' => 4, 'RMK' => 7],
      '2' => ['1' => 2, 'MTTO' => 5, 'RMK' => 8],
      '3' => ['1' => 3, 'MTTO' => 6, 'RMK' => 9]
    ];
  }

  public function mount($editable, $tipod)
  {
    $startOfWeek = Carbon::now()->startOfWeek();

    $registros = tsheating::whereBetween('dia', [
      $startOfWeek,
      $startOfWeek->copy()->addDays(5),
    ])->get();

    foreach ($registros as $rec) {
      $diaNombre = $this->getDiaNombre($rec->dia);
      $this->values[$rec->equipo][$diaNombre][$rec->turno][$rec->tipo] = [
        'plan' => $rec->plan,
        'c' => $rec->c,
        'planuser' => $rec->planuser ?? 0,
        'cuser' => $rec->cuser ?? 0,
        'priority' => $rec->priority ?? $this->getPriorityMap()[$rec->turno][$rec->tipo] ?? 1,
        'diaajustable' => $rec->diaajustable ?? $this->getDateForDia($diaNombre)->day, // 👈 agregado
      ];
    }

    // Inicializar valores faltantes
    foreach ($this->equipos as $equipo) {
      foreach ($this->diasSemana as $dia) {
        foreach ($this->turnos as $turno) {
          foreach ($this->tipos as $tipo) {
            if (!isset($this->values[$equipo][$dia][$turno][$tipo])) {
              $this->values[$equipo][$dia][$turno][$tipo] = [
                'plan' => 0,
                'c' => 0,
                'planuser' => 0,
                'cuser' => 0,
                'priority' => $this->getPriorityMap()[$turno][$tipo] ?? 1,
                'diaajustable' => $this->getDateForDia($dia)->day, // 👈 agregado
              ];
            }
          }
        }
      }
    }

    $this->editable = $editable;
    $this->tipod = $tipod;
    $this->isAdmin = $tipod === 'admin';
  }

  public function save()
  {
    foreach ($this->values as $equipo => $dias) {
      foreach ($dias as $diaNombre => $turnos) {
        $dia = $this->getDateForDia($diaNombre);
        foreach ($turnos as $turno => $tipos) {
          foreach ($tipos as $tipo => $datos) {
            Tsheating::updateOrCreate(
              [
                'equipo' => $equipo,
                'dia' => $dia,
                'turno' => $turno,
                'tipo' => $tipo,
              ],
              [
                'plan' => $datos['plan'] ?? 0,
                'c' => $datos['c'] ?? 0,
                'planuser' => $datos['planuser'] ?? null,
                'cuser' => $datos['cuser'] ?? null,
                'priority' => $datos['priority'] ?? $this->getPriorityMap()[$turno][$tipo] ?? 1,
                'diaajustable' => $datos['diaajustable'] ?? $dia->day, // 👈 agregado
              ]
            );
          }
        }
      }
    }

    session()->flash('message', 'Datos guardados correctamente.');
  }
}
