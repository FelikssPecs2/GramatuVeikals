<?php

namespace App\Http\Controllers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\Sale;
use Illuminate\Support\Facades\Response;

class ExportController extends Controller
{
    public function exportSales()
    {
        $salesGrouped = Sale::with('book')->get()->groupBy(function ($item) {
            return $item->book_id . '-' . $item->sale_date;
        });
    
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        // Virsraksts
        $sheet->setCellValue('A1', 'Pārdošanas Atskaite')
              ->getStyle('A1')
              ->applyFromArray([
                  'font' => [
                      'bold' => true,
                      'size' => 16,
                      'color' => ['rgb' => '2C3E50']
                  ]
              ]);
    
        // Kolonnu virsraksti
        $headers = ['Grāmatas nosaukums', 'Datums', 'Daudzums', 'Vienības cena', 'Kopā'];
        $sheet->fromArray($headers, null, 'A2');
        
        $sheet->getStyle('A2:E2')
              ->applyFromArray([
                  'font' => [
                      'bold' => true,
                      'color' => ['rgb' => '2C3E50']
                  ],
                  'fill' => [
                      'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                      'startColor' => ['rgb' => 'ECF0F1']
                  ],
                  'borders' => [
                      'bottom' => [
                          'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                      ]
                  ]
              ]);
    
        // Datu rindas
        $row = 3;
        $totalSum = 0;
    
        foreach ($salesGrouped as $groupKey => $sales) {
            $book = $sales->first()->book;
            $totalQuantity = $sales->sum('quantity');
            $saleDate = $sales->first()->sale_date;
            $total = $totalQuantity * $book->price;
            $totalSum += $total;
    
            $sheet->setCellValue('A' . $row, $book->title)
                  ->setCellValue('B' . $row, $saleDate)
                  ->setCellValue('C' . $row, $totalQuantity)
                  ->setCellValue('D' . $row, number_format($book->price, 2, ',', ' '))
                  ->setCellValue('E' . $row, number_format($total, 2, ',', ' '));
    
            // Plānas aprises starp rindām
            $sheet->getStyle('A'.$row.':E'.$row)
                  ->applyFromArray([
                      'borders' => [
                          'bottom' => [
                              'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR,
                              'color' => ['rgb' => 'BDC3C7']
                          ]
                      ]
                  ]);
    
            $row++;
        }
    
        // Kopsummas rinda
        $sheet->setCellValue('D' . $row, 'KOPĀ:')
              ->setCellValue('E' . $row, number_format($totalSum, 2, ',', ' '));
        
        $sheet->getStyle('D'.$row.':E'.$row)
              ->applyFromArray([
                  'font' => [
                      'bold' => true,
                      'color' => ['rgb' => 'FFFFFF']
                  ],
                  'fill' => [
                      'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                      'startColor' => ['rgb' => '2C3E50']
                  ]
              ]);
    
        // Formāti
        $sheet->getStyle('D3:E' . $row)
              ->getNumberFormat()
              ->setFormatCode('#,##0.00\ €');
        
        $sheet->getStyle('B3:B' . $row)
              ->getNumberFormat()
              ->setFormatCode('yyyy-mm-dd');
    
        // Kolonnu platumi
        $sheet->getColumnDimension('A')->setWidth(35);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
    
        // Skaitļu izlīdzināšana pa labi
        $sheet->getStyle('C3:E' . $row)
              ->getAlignment()
              ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
    
        // Noņemam režģa līnijas
        $sheet->setShowGridlines(false);
    
        $writer = new Xlsx($spreadsheet);
        $fileName = storage_path('app/pardosanas-atskaite.xlsx');
        $writer->save($fileName);
    
        return Response::download($fileName)->deleteFileAfterSend(true);
    }
}