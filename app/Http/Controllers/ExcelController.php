<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ExcelController extends Controller
{
  public function index()
  {
    $excelData = [];
    $excelFileUrl = null;

    // Usar storage_path en lugar de Storage::path
    $jsonPath = storage_path('app/public/excels/data.json');
    $excelPath = storage_path('app/public/excels/plan_produccion.xlsx');

    // Primero intentar cargar desde el JSON de Luckysheet
    if (file_exists($jsonPath)) {
      $jsonData = file_get_contents($jsonPath);
      $excelData = json_decode($jsonData, true) ?? [];
    }
    // Si no existe JSON, cargar desde Excel
    elseif (file_exists($excelPath)) {
      // Crear directorio si no existe
      $directory = dirname($excelPath);
      if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
      }

      try {
        $spreadsheet = IOFactory::load($excelPath);
        $excelData = $this->convertExcelToLuckysheet($spreadsheet);
      } catch (\Exception $e) {
        Log::error('Error loading Excel file: ' . $e->getMessage());
      }
    }

    // Generar URL
    if (file_exists($excelPath)) {
      $excelFileUrl = Storage::url('excels/plan_produccion.xlsx');
    }

    return view('excel.index', [
      'excelData' => $excelData,
      'excelFileUrl' => $excelFileUrl
    ]);
  }

  public function upload(Request $request)
  {
    $request->validate([
      'file' => 'required|mimes:xlsx,xls|max:10240',
    ]);

    $file = $request->file('file');
    $extension = $file->getClientOriginalExtension();
    $filename = 'plan_produccion.' . $extension;

    // Guardar archivo físico
    $path = $file->storeAs('public/excels', $filename);

    // Leer y convertir a formato Luckysheet
    $spreadsheet = IOFactory::load($file->getRealPath());
    $sheetsData = $this->convertExcelToLuckysheet($spreadsheet);

    // Guardar JSON para Luckysheet
    Storage::disk('public')->put('excels/data.json', json_encode($sheetsData, JSON_PRETTY_PRINT));

    return response()->json([
      'success' => true,
      'data' => $sheetsData,
      'file' => Storage::url($path)
    ]);
  }

  public function save(Request $request)
  {
    $data = $request->input('excel');

    if (!$data) {
      return response()->json(['success' => false, 'message' => 'No data received']);
    }

    // Guardar JSON completo de Luckysheet
    Storage::disk('public')->put('excels/data.json', json_encode($data, JSON_PRETTY_PRINT));

    // Convertir a Excel físico manteniendo formatos
    $this->convertLuckysheetToExcel($data);

    return response()->json([
      'success' => true,
      'file' => Storage::url('excels/plan_produccion.xlsx')
    ]);
  }

  private function convertExcelToLuckysheet($spreadsheet)
  {
    $sheetsData = [];

    foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
      $highestRow = $worksheet->getHighestRow();
      $highestColumn = $worksheet->getHighestColumn();
      $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

      $cellData = [];
      $config = [];
      $rowlen = [];
      $columnlen = [];

      for ($row = 1; $row <= $highestRow; $row++) {
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
          $cell = $worksheet->getCellByColumnAndRow($col, $row);
          $value = $cell->getFormattedValue();

          if ($value !== null && $value !== '') {
            $cellKey = $this->getCellKey($row - 1, $col - 1);

            // Obtener estilo de la celda
            $style = $worksheet->getStyleByColumnAndRow($col, $row);

            // Configurar formato de celda
            $cellConfig = [];

            // Color de fondo
            $fill = $style->getFill();
            if ($fill->getFillType() !== Fill::FILL_NONE) {
              $cellConfig['bg'] = $fill->getStartColor()->getRGB();
            }

            // Fuente
            $font = $style->getFont();
            if ($font->getBold()) {
              $cellConfig['bl'] = 1;
            }
            if ($font->getItalic()) {
              $cellConfig['it'] = 1;
            }
            if ($font->getUnderline()) {
              $cellConfig['ul'] = 1;
            }
            if ($font->getStrikethrough()) {
              $cellConfig['st'] = 1;
            }
            $cellConfig['fc'] = $font->getColor()->getRGB();
            $cellConfig['fs'] = $font->getSize();
            $cellConfig['ff'] = $font->getName();

            // Alineación
            $alignment = $style->getAlignment();
            $cellConfig['vt'] = $alignment->getVertical();
            $cellConfig['ht'] = $alignment->getHorizontal();

            // Bordes
            $borders = $style->getBorders();
            $borderStyle = [];
            if ($borders->getTop()->getBorderStyle()) {
              $borderStyle['borderTop'] = [
                'style' => $borders->getTop()->getBorderStyle(),
                'color' => $borders->getTop()->getColor()->getRGB()
              ];
            }
            if ($borders->getBottom()->getBorderStyle()) {
              $borderStyle['borderBottom'] = [
                'style' => $borders->getBottom()->getBorderStyle(),
                'color' => $borders->getBottom()->getColor()->getRGB()
              ];
            }
            if ($borders->getLeft()->getBorderStyle()) {
              $borderStyle['borderLeft'] = [
                'style' => $borders->getLeft()->getBorderStyle(),
                'color' => $borders->getLeft()->getColor()->getRGB()
              ];
            }
            if ($borders->getRight()->getBorderStyle()) {
              $borderStyle['borderRight'] = [
                'style' => $borders->getRight()->getBorderStyle(),
                'color' => $borders->getRight()->getColor()->getRGB()
              ];
            }

            if (!empty($borderStyle)) {
              $cellConfig['bs'] = $borderStyle;
            }

            // Formato numérico
            $formatCode = $style->getNumberFormat()->getFormatCode();
            $cellConfig['ct'] = [
              'fa' => $formatCode,
              't' => $this->getFormatType($formatCode)
            ];

            if (!empty($cellConfig)) {
              $config[$cellKey] = $cellConfig;
            }

            $cellData[] = [
              "r" => $row - 1,
              "c" => $col - 1,
              "v" => [
                "m" => (string)$value,
                "v" => (string)$value,
                "ct" => [
                  "fa" => $formatCode,
                  "t" => $this->getFormatType($formatCode)
                ]
              ]
            ];
          }
        }
      }

      // Configurar dimensiones de filas y columnas
      for ($i = 0; $i < $highestRow; $i++) {
        $rowHeight = $worksheet->getRowDimension($i + 1)->getRowHeight();
        if ($rowHeight != -1) {
          $rowlen[$i] = $rowHeight;
        }
      }

      for ($i = 0; $i < $highestColumnIndex; $i++) {
        $colWidth = $worksheet->getColumnDimensionByColumn($i + 1)->getWidth();
        if ($colWidth != -1) {
          $columnlen[$i] = $colWidth * 8; // Convertir a píxeles aproximados
        }
      }

      $sheetsData[] = [
        "name" => $worksheet->getTitle(),
        "celldata" => $cellData,
        "row" => $highestRow + 20,
        "column" => $highestColumnIndex + 10,
        "defaultRowHeight" => 25,
        "defaultColWidth" => 100,
        "config" => $config,
        "rowlen" => $rowlen,
        "columnlen" => $columnlen,
        "protectionPassword" => "",
        "roles" => ["admin", "supervisor"]
      ];
    }

    return $sheetsData;
  }

  private function convertLuckysheetToExcel($luckysheetData)
  {
    $spreadsheet = new Spreadsheet();
    $spreadsheet->removeSheetByIndex(0); // Remover hoja por defecto

    foreach ($luckysheetData as $index => $sheet) {
      $worksheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $sheet['name']);
      $spreadsheet->addSheet($worksheet, $index);

      // Procesar datos de celdas
      foreach ($sheet['celldata'] as $cell) {
        $row = $cell['r'] + 1;
        $col = $cell['c'] + 1;
        $value = $cell['v']['v'] ?? $cell['v']['m'] ?? '';
        $worksheet->setCellValueByColumnAndRow($col, $row, $value);

        // Aplicar formatos si existen
        $cellKey = $this->getCellKey($cell['r'], $cell['c']);
        if (isset($sheet['config'][$cellKey])) {
          $this->applyCellStyle($worksheet, $col, $row, $sheet['config'][$cellKey]);
        }
      }

      // Aplicar dimensiones de filas y columnas
      if (isset($sheet['rowlen'])) {
        foreach ($sheet['rowlen'] as $rowIndex => $height) {
          $worksheet->getRowDimension($rowIndex + 1)->setRowHeight($height);
        }
      }

      if (isset($sheet['columnlen'])) {
        foreach ($sheet['columnlen'] as $colIndex => $width) {
          $worksheet->getColumnDimensionByColumn($colIndex + 1)->setWidth($width / 8);
        }
      }
    }

    // Remover la primera hoja si se creó una extra
    if ($spreadsheet->getSheetCount() > count($luckysheetData)) {
      $spreadsheet->removeSheetByIndex(0);
    }

    $filename = "excels/plan_produccion.xlsx";
    $path = storage_path("app/public/" . $filename);
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save($path);
  }

  private function applyCellStyle($worksheet, $col, $row, $style)
  {
    $cell = $worksheet->getCellByColumnAndRow($col, $row);
    $coordinate = $cell->getCoordinate();
    $cellStyle = $worksheet->getStyle($coordinate);

    // Color de fondo
    if (isset($style['bg'])) {
      $cellStyle->getFill()->setFillType(Fill::FILL_SOLID);
      $cellStyle->getFill()->getStartColor()->setRGB($style['bg']);
    }

    // Fuente
    $font = $cellStyle->getFont();
    if (isset($style['bl'])) $font->setBold(true);
    if (isset($style['it'])) $font->setItalic(true);
    if (isset($style['ul'])) $font->setUnderline(true);
    if (isset($style['st'])) $font->setStrikethrough(true);
    if (isset($style['fc'])) $font->getColor()->setRGB($style['fc']);
    if (isset($style['fs'])) $font->setSize($style['fs']);
    if (isset($style['ff'])) $font->setName($style['ff']);

    // Alineación
    $alignment = $cellStyle->getAlignment();
    if (isset($style['vt'])) $alignment->setVertical($style['vt']);
    if (isset($style['ht'])) $alignment->setHorizontal($style['ht']);

    // Bordes
    if (isset($style['bs'])) {
      $borders = $cellStyle->getBorders();
      foreach ($style['bs'] as $borderType => $borderStyle) {
        $method = 'set' . ucfirst($borderType);
        if (method_exists($borders, $method)) {
          $border = $borders->$method();
          $border->setBorderStyle($borderStyle['style']);
          $border->getColor()->setRGB($borderStyle['color']);
        }
      }
    }

    // Formato numérico
    if (isset($style['ct']['fa'])) {
      $cellStyle->getNumberFormat()->setFormatCode($style['ct']['fa']);
    }
  }

  private function getCellKey($row, $col)
  {
    $columnLetter = Coordinate::stringFromColumnIndex($col + 1);
    return $columnLetter . ($row + 1);
  }

  private function getFormatType($formatCode)
  {
    if (strpos($formatCode, '$') !== false || strpos($formatCode, '€') !== false || strpos($formatCode, '£') !== false) {
      return 'n'; // número (moneda)
    } elseif (strpos($formatCode, '%') !== false) {
      return 'n'; // número (porcentaje)
    } elseif (strpos($formatCode, 'yyyy') !== false || strpos($formatCode, 'mm') !== false || strpos($formatCode, 'dd') !== false) {
      return 'd'; // fecha
    } else {
      return 'g'; // general
    }
  }
}
