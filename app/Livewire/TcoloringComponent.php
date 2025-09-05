<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Tcoloring;
use App\Exports\TcoloringExport;
use Maatwebsite\Excel\Facades\Excel;

class TcoloringComponent extends Component
{
  public $equipos = ['CL-01', 'CL-02', 'CL-03', 'CL-04'];
  public $dias = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
  public $turnos = ['1', '2', '3'];
  public $tiposFibra = ['Blue', 'Orange', 'Green', 'Brown', 'Grey', 'White', 'Red', 'Black', 'Yellow', 'Violet', 'Pink', 'Aqua'];
  public $values = [];
  public $editable = false;
  public $initialDataLoaded = false;

  public function export()
  {
    return Excel::download(new TcoloringExport($this->values), 'tcoloring-' . now()->format('Y-m-d') . '.xlsx');
  }

  public function mount($editable)
  {
    $this->editable = $editable;
    $this->loadData();
  }

  protected function loadData()
  {
    $registros = Tcoloring::all();
    foreach ($registros as $rec) {
      $this->values[$rec->equipo][$rec->dias][$rec->turno][$rec->tipofibra] = [
        'turno_actual' => $rec->turno,
        'plan' => $rec->plan,
        'c' => $rec->c,
        'type' => $rec->type,
        'planuser' => $rec->planuser ?? '',
        'cuser' => $rec->cuser ?? '',
        'typeuser' => $rec->typeuser ?? '',
      ];
    }

    foreach ($this->equipos as $equipo) {
      foreach ($this->dias as $dia) {
        foreach ($this->turnos as $turno) {
          foreach ($this->tiposFibra as $fibra) {
            if (!isset($this->values[$equipo][$dia][$turno][$fibra])) {
              $this->values[$equipo][$dia][$turno][$fibra] = [
                'turno_actual' => $turno,
                'plan' => 0,
                'c' => 0,
                'type' => 0,
                'planuser' => '',
                'cuser' => '',
                'typeuser' => '',
              ];
            }
          }
        }
      }
    }
  }


  protected $rules = [
    'values.*.*.*.*.plan' => 'numeric|min:0',
    'values.*.*.*.*.c' => 'numeric|min:0',
    'values.*.*.*.*.type' => 'numeric|min:0',
    'values.*.*.*.*.planuser' => 'nullable|numeric',
    'values.*.*.*.*.cuser' => 'nullable|numeric',
    'values.*.*.*.*.typeuser' => 'nullable|numeric',
    'values.*.*.*.*.turno_actual' => 'required|in:1,2,3',
  ];


  public function save()
  {
    $this->validate();

    foreach ($this->values as $equipo => $dias) {
      foreach ($dias as $dia => $turnos) {
        foreach ($turnos as $turno => $tipos) {
          // Obtener el nuevo turno de la primera fibra (será el mismo para toda la fila)
          $nuevoTurno = $tipos[$this->tiposFibra[0]]['turno_actual'] ?? $turno;

          // Solo procesar si el turno cambió
          if ($nuevoTurno != $turno) {
            // Verificar si el nuevo turno ya existe para este equipo/día
            if (isset($this->values[$equipo][$dia][$nuevoTurno])) {
              session()->flash('error', 'El turno ' . $nuevoTurno . ' ya existe para ' . $equipo . ' - ' . $dia);
              continue;
            }

            // Actualizar el turno_actual para todas las fibras de esta fila
            foreach ($tipos as $tipo => &$datos) {
              $datos['turno_actual'] = $nuevoTurno;
            }

            // Mover los datos al nuevo turno
            $this->values[$equipo][$dia][$nuevoTurno] = $tipos;
            unset($this->values[$equipo][$dia][$turno]);

            // Eliminar registros antiguos de la base de datos
            Tcoloring::where([
              'equipo' => $equipo,
              'dias' => $dia,
              'turno' => $turno
            ])->delete();
          }

          // Guardar los datos para cada fibra
          foreach ($tipos as $tipo => $datos) {
            $registro = Tcoloring::firstOrNew([
              'equipo' => $equipo,
              'dias' => $dia,
              'turno' => $nuevoTurno,
              'tipofibra' => $tipo,
            ]);

            $registro->plan = $datos['plan'] ?? 0;
            $registro->c = $datos['c'] ?? 0;
            $registro->type = $datos['type'] ?? 0;
            $registro->planuser = $datos['planuser'] ?? null;
            $registro->cuser = $datos['cuser'] ?? null;
            $registro->typeuser = $datos['typeuser'] ?? null;

            $registro->save();
          }
        }
      }
    }
    session()->flash('message', 'Datos guardados correctamente.');
  }


  public function render()
  {
    return view('livewire.tcoloring-component');
  }
}
