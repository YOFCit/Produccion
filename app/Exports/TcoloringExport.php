<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Color as PhpColor;

class TcoloringExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
  protected $values;
  protected $equipos = ['CL-01', 'CL-02', 'CL-03', 'CL-04', 'CL-05'];
  protected $dias = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
  protected $turnos = ['1', '2', '3'];
  protected $tiposFibra = ['Blue', 'Orange', 'Green', 'Brown', 'Grey', 'White', 'Red', 'Black', 'Yellow', 'Violet', 'Pink', 'Aqua'];

  public function __construct(array $values)
  {
    $this->values = $values;
  }

  public function collection()
  {
    $rows = [];

    foreach ($this->equipos as $equipo) {
      foreach ($this->dias as $dia) {
        foreach ($this->turnos as $turno) {
          $row = [
            'Equipo' => $equipo,
            'Día' => $dia,
            'Turno' => $turno,
          ];

          foreach ($this->tiposFibra as $fibra) {
            $val = $this->values[$equipo][$dia][$turno][$fibra] ?? [
              'plan' => 0,
              'c' => 0,
              'type' => 0,
            ];

            $row["{$fibra} Plan"] = $val['plan'] ?? 0;
            $row["{$fibra} C"] = $val['c'] ?? 0;
            $row["{$fibra} Type"] = $val['type'] ?? 0;
          }

          $rows[] = $row;
        }
      }
    }

    return new Collection($rows);
  }

  public function headings(): array
  {
    $headings = ['Equipo', 'Día', 'Turno'];

    foreach ($this->tiposFibra as $fibra) {
      $headings[] = "{$fibra} Plan";
      $headings[] = "{$fibra} C";
      $headings[] = "{$fibra} Type";
    }

    return $headings;
  }

  public function styles(Worksheet $sheet)
  {
    $totalCols = 3 + (count($this->tiposFibra) * 3);
    $lastColLetter = Coordinate::stringFromColumnIndex($totalCols);

    // Estilos header
    $sheet->getStyle("A1:{$lastColLetter}1")->applyFromArray([
      'font' => ['bold' => true],
      'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D3D3D3']],
      'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
      'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
    ]);
  }

  public function registerEvents(): array
  {
    return [
      AfterSheet::class => function (AfterSheet $event) {
        $sheet = $event->sheet->getDelegate();
        $totalCols = 3 + (count($this->tiposFibra) * 3);
        $lastColLetter = Coordinate::stringFromColumnIndex($totalCols);
        $lastRow = $sheet->getHighestRow();

        // Bordes para toda la tabla
        $sheet->getStyle("A1:{$lastColLetter}{$lastRow}")->applyFromArray([
          'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ]);

        // Auto tamaño para todas las columnas
        for ($col = 1; $col <= $totalCols; $col++) {
          $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
        }

        // Colorear columnas de cada fibra según nombre literal
        $startCol = 4; // D

        foreach ($this->tiposFibra as $fibra) {
          $colorName = strtolower($fibra);

          // Intentar convertir nombre CSS a RGB hex para PhpSpreadsheet
          $rgb = $this->colorNameToHex($colorName);

          for ($i = 0; $i < 3; $i++) {
            $colLetter = Coordinate::stringFromColumnIndex($startCol + $i);
            $sheet->getStyle("{$colLetter}2:{$colLetter}{$lastRow}")->getFill()
              ->setFillType(Fill::FILL_SOLID)
              ->getStartColor()->setRGB($rgb);
          }

          $startCol += 3;
        }
      }
    ];
  }

  // Convierte nombre CSS a código hexadecimal para PhpSpreadsheet
  protected function colorNameToHex(string $name): string
  {
    // Lista básica con nombres CSS (puedes ampliar si quieres)
    $colors = [
      'black' => '000000',
      'white' => 'FFFFFF',
      'red' => 'FF0000',
      'green' => '008000',
      'blue' => '0000FF',
      'orange' => 'FFA500',
      'brown' => 'A52A2A',
      'grey' => '808080',
      'yellow' => 'FFFF00',
      'violet' => '8A2BE2',
      'pink' => 'FFC0CB',
      'aqua' => '00FFFF',
      // Añade más si quieres
    ];

    return $colors[$name] ?? 'FFFFFF'; // blanco si no existe
  }
}
