<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\tstranding;
use Illuminate\Support\Carbon;
use App\Exports\TstrandingExport;
use Maatwebsite\Excel\Facades\Excel;

class TstrandingComponent extends Component
{
  public $equipos = ['SZ-01', 'SZ-02', 'SZ-03'];
  public $turnos = ['1', '2', '3'];
  public $tipos = ['1', 'MTTO', 'RMK'];
  public $priorities = ['1', '2', '3'];
  public $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
  public $values = [];
  public $diaajustable = [];
  public $editable = false;
  public $tipod;

  public function render()
  {
    return view('livewire.tstranding-component');
  }

  public function export()
  {
    return Excel::download(new TstrandingExport, 'tstranding.xlsx');
  }

  private function getPriorityMap()
  {
    return [
      '1' => ['1' => 1, 'MTTO' => 4, 'RMK' => 7],
      '2' => ['1' => 2, 'MTTO' => 5, 'RMK' => 8],
      '3' => ['1' => 3, 'MTTO' => 6, 'RMK' => 9]
    ];
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

  public function mount($editable, $tipod)
  {
    $startOfWeek = Carbon::now()->startOfWeek();

    $registros = tstranding::whereBetween('dia', [
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
    foreach ($this->diasSemana as $index => $dia) {
        $this->diaajustable[$index] = $this->getDateForDia($dia)->day;
    }

    $this->editable = $editable;
    $this->tipod = $tipod;
  }

public function save()
{
    foreach ($this->equipos as $equipo) {
        foreach ($this->diasSemana as $index => $diaNombre) {
            $dia = $this->getDateForDia($diaNombre);

            // Tomamos los datos de cualquier turno/tipo, por ejemplo el primero
            $primerTurno = $this->turnos[0];
            $primerTipo = $this->tipos[0];
            $datos = $this->values[$equipo][$diaNombre][$primerTurno][$primerTipo] ?? [];

            tstranding::updateOrCreate(
                [
                    'equipo' => $equipo,
                    'dia' => $dia,
                ],
                [
                    'plan' => $datos['plan'] ?? 0,
                    'c' => $datos['c'] ?? 0,
                    'planuser' => $datos['planuser'] ?? null,
                    'cuser' => $datos['cuser'] ?? null,
                    'priority' => $datos['priority'] ?? $this->getPriorityMap()[$primerTurno][$primerTipo] ?? 1,
                    'diaajustable' => $datos['diaajustable'] ?? $this->diaajustable[$index] ?? $dia->day,
                ]
            );
        }
    }

    session()->flash('message', 'Datos guardados correctamente.');
}
}

