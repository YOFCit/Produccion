<?php

namespace App\Exports;

use App\Models\tstranding;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TstrandingExport implements FromArray, WithHeadings, WithStyles
{
  protected $equipos = ['SZ-01', 'SZ-02', 'SZ-03'];
  protected $turnos = ['1', '2', '3'];
  protected $tipos = ['1', 'MTTO', 'RMK'];
  protected $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

  protected $coloresRMK = [
    'SZ-01' => 'F28282',
    'SZ-02' => '96D3CA',
    'SZ-03' => 'EBEBB1',
  ];

  protected $defaultRMK = 'EBCDCC';
  protected $defaultC = 'DBF0ED';
  protected $weekStartDate;

  public function __construct()
  {
    $this->weekStartDate = Carbon::now()->startOfWeek();
  }

  public function array(): array
  {
    $headings = ['Equipo', 'Turno', 'Tipo'];
    foreach ($this->diasSemana as $dia) {
      $headings[] = $dia . ' Plan';
      $headings[] = $dia . ' C';
    }

    $data = [];
    $sequenceCounter = 0;
    $currentNumber = 1;
    $currentEquipo = null;

    foreach ($this->equipos as $equipo) {
      // Reiniciamos contadores para cada equipo
      if ($currentEquipo !== $equipo) {
        $currentEquipo = $equipo;
        $sequenceCounter = 0;
        $currentNumber = 1;
      }

      foreach ($this->turnos as $turno) {
        foreach ($this->tipos as $tipo) {
          $row = [$equipo, $turno, ''];

          // Aplicamos la secuencia numérica
          $sequenceCounter++;
          if ($sequenceCounter % 3 === 1) {
            $row[2] = $currentNumber; // Mostrar número en primera posición
          } else {
            $row[2] = $tipo; // Mostrar MTTO o RMK
          }

          // Actualizar número después de cada grupo de 3
          if ($sequenceCounter % 3 === 0) {
            $currentNumber++;
            if ($currentNumber > 3) $currentNumber = 1;
          }

          foreach ($this->diasSemana as $diaNombre) {
            $dia = $this->getDateForDia($diaNombre)->toDateString();

            $registro = Tstranding::where('equipo', $equipo)
              ->where('turno', $turno)
              ->where('tipo', $tipo)
              ->whereDate('dia', $dia)
              ->first();

            // Para RMK usamos los valores de usuario, para otros tipos usamos los valores normales
            if ($tipo === 'RMK') {
              $plan = $registro ? ($registro->planuser ?? $registro->plan ?? 0) : 0;
              $c = $registro ? ($registro->cuser ?? $registro->c ?? 0) : 0;
            } else {
              $plan = $registro ? $registro->plan : 0;
              $c = $registro ? $registro->c : 0;
            }

            $row[] = $plan;
            $row[] = $c;
          }

          $data[] = $row;
        }
      }
    }

    return array_merge([$headings], $data);
  }

  public function headings(): array
  {
    return [];
  }

  public function styles(Worksheet $sheet)
  {
    $lastRow = $sheet->getHighestRow();
    $lastColumn = $sheet->getHighestColumn();

    // Header style
    $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
      'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
      'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F3BE3B']],
      'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
      'borders' => ['allBorders' => ['borderStyle' => 'thin']],
    ]);

    // Auto-size columns
    $highestColumnIndex = Coordinate::columnIndexFromString($lastColumn);
    for ($col = 1; $col <= $highestColumnIndex; $col++) {
      $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
    }

    // Apply styles to each row
    for ($row = 2; $row <= $lastRow; $row++) {
      $equipo = $sheet->getCell("A$row")->getValue();
      $tipo = $sheet->getCell("C$row")->getValue();

      if ($tipo === 'RMK') {
        $color = $this->coloresRMK[$equipo] ?? $this->defaultRMK;
        $sheet->getStyle("A$row:C$row")->applyFromArray([
          'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $color]],
        ]);
      } elseif ($tipo === 'MTTO') {
        $sheet->getStyle("C$row")->applyFromArray([
          'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D795BB']],
        ]);
      }

      $sheet->getStyle("A$row:{$lastColumn}$row")->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => 'thin']],
        'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
      ]);
    }

    // Color plan and C columns
    $numDias = count($this->diasSemana);
    for ($i = 0; $i < $numDias; $i++) {
      $colPlan = Coordinate::stringFromColumnIndex(4 + $i * 2);
      $colC = Coordinate::stringFromColumnIndex(5 + $i * 2);

      $sheet->getStyle("{$colPlan}2:{$colPlan}{$lastRow}")->applyFromArray([
        'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F3BE3B']],
      ]);

      $sheet->getStyle("{$colC}2:{$colC}{$lastRow}")->applyFromArray([
        'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'B8E2DC']],
      ]);
    }

    // Merge turno cells
    $this->mergeTurnoCells($sheet);
  }

  private function mergeTurnoCells(Worksheet $sheet)
  {
    $lastRow = $sheet->getHighestRow();
    $currentEquipo = null;
    $currentTurno = null;
    $startRow = 2;

    for ($row = 2; $row <= $lastRow + 1; $row++) {
      if ($row > $lastRow) {
        // Final merge if needed
        if ($startRow < $row - 1) {
          $sheet->mergeCells("B$startRow:B" . ($row - 1));
          $sheet->getStyle("B$startRow")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        }
        break;
      }

      $equipo = $sheet->getCell("A$row")->getValue();
      $turno = $sheet->getCell("B$row")->getValue();

      if ($equipo !== $currentEquipo || $turno !== $currentTurno) {
        if ($startRow < $row - 1) {
          $sheet->mergeCells("B$startRow:B" . ($row - 1));
          $sheet->getStyle("B$startRow")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        }

        $currentEquipo = $equipo;
        $currentTurno = $turno;
        $startRow = $row;
      }
    }
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

    return $this->weekStartDate->copy()->addDays($dias[$diaNombre]);
  }
}
