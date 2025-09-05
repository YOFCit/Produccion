<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Tscoating;
use Illuminate\Support\Carbon;
use App\Exports\TscoatingExport;
use Maatwebsite\Excel\Facades\Excel;

class TscoatingComponent extends Component
{
  public $equipos = ['SC-01', 'SC-02', 'SC-03'];
  public $turnos = ['1', '2', '3'];
  public $tipos = ['1', 'MTTO', 'RMK'];
  public $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
  public $values = [];
  public $editable = false;
  public $tipod;

  public function export()
  {
    return Excel::download(new TscoatingExport, 'tscoating.xlsx');
  }

  public function mount($editable, $tipod)
  {
    $startOfWeek = Carbon::now()->startOfWeek();

    $registros = Tscoating::whereBetween('dia', [
      $startOfWeek->copy(),
      $startOfWeek->copy()->addDays(6)
    ])->get();

    foreach ($registros as $rec) {
      $diaNombre = $this->getDiaNombre($rec->dia);
      $this->values[$rec->equipo][$diaNombre][$rec->turno][$rec->tipo] = [
        'plan' => $rec->plan,
        'c' => $rec->c,
        'type' => $rec->type,
        'planuser' => $rec->planuser ?? 0,
        'cuser' => $rec->cuser ?? 0,
        'typeuser' => $rec->typeuser ?? 0,
        'priority' => $rec->priority ?? $this->getPriorityMap()[$rec->turno][$rec->tipo] ?? 1,
      ];
    }

    // Inicializar vacíos con prioridades
    foreach ($this->equipos as $equipo) {
      foreach ($this->diasSemana as $dia) {
        foreach ($this->turnos as $turno) {
          foreach ($this->tipos as $tipo) {
            if (!isset($this->values[$equipo][$dia][$turno][$tipo])) {
              $this->values[$equipo][$dia][$turno][$tipo] = [
                'plan' => 0,
                'c' => 0,
                'type' => 0,
                'planuser' => 0,
                'cuser' => 0,
                'typeuser' => 0,
                'priority' => $this->getPriorityMap()[$turno][$tipo] ?? 1,
              ];
            }
          }
        }
      }
    }

    $this->editable = $editable;
    $this->tipod = $tipod;
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
      0 => 'Domingo'
    ];
    return $dias[$carbonDate->dayOfWeek];
  }

  private function getDateForDia($diaNombre)
  {
    $dias = [
      'Lunes' => 0,
      'Martes' => 1,
      'Miércoles' => 2,
      'Jueves' => 3,
      'Viernes' => 4,
      'Sábado' => 5
    ];

    return Carbon::now()->startOfWeek()->addDays($dias[$diaNombre]);
  }

  public function save()
  {
    foreach ($this->values as $equipo => $dias) {
      foreach ($dias as $diaNombre => $turnos) {
        $dia = $this->getDateForDia($diaNombre);

        foreach ($turnos as $turno => $tipos) {
          foreach ($tipos as $tipo => $datos) {
            $updateData = [
              'planuser' => $datos['planuser'] ?? null,
              'cuser' => $datos['cuser'] ?? null,
              'typeuser' => $datos['typeuser'] ?? null,
              'priority' => $datos['priority'] ?? $this->getPriorityMap()[$turno][$tipo] ?? 1,
            ];

            if ($this->editable) {
              $updateData = array_merge($updateData, [
                'plan' => $datos['plan'] ?? 0,
                'c' => $datos['c'] ?? 0,
                'type' => $datos['type'] ?? 0,
              ]);
            }

            Tscoating::updateOrCreate(
              [
                'equipo' => $equipo,
                'dia' => $dia,
                'turno' => $turno,
                'tipo' => $tipo,
              ],
              $updateData
            );
          }
        }
      }
    }

    session()->flash('message', 'Datos guardados correctamente.');
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
