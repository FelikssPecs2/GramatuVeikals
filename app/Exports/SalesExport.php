<?php
namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\Sale;
use Illuminate\Support\Facades\Response;
class SalesExport
{
    public function exportSales()
{
    try {
        \Log::info('Starting export process...');

        // Create a new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add headers
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Product Name');
        $sheet->setCellValue('C1', 'Quantity');
        $sheet->setCellValue('D1', 'Price');
        $sheet->setCellValue('E1', 'Date');

        // Fetch data from the database
        \Log::info('Fetching sales data...');
        $sales = Sale::with('book')->get();

        if ($sales->isEmpty()) {
            \Log::warning('No sales data found!');
            return response('No sales data found!', 404);
        }

        \Log::info('Populating spreadsheet...');
        $row = 2;
        foreach ($sales as $sale) {
            $sheet->setCellValue('A' . $row, $sale->id);
            $sheet->setCellValue('B' . $row, $sale->book?->title ?? 'N/A');
            $sheet->setCellValue('C' . $row, $sale->quantity);
            $sheet->setCellValue('D' . $row, $sale->book?->price ?? 'N/A');
            $sheet->setCellValue('E' . $row, $sale->sale_date);
            $row++;
        }

        // Save the spreadsheet to the storage directory
        $fileName = storage_path('app/sales.xlsx');
        \Log::info('Saving spreadsheet to: ' . $fileName);
        $writer = new Xlsx($spreadsheet);
        $writer->save($fileName);

        // Verify the file exists before downloading
        if (!file_exists($fileName)) {
            \Log::error('File not generated: ' . $fileName);
            throw new \Exception("File not generated!");
        }

        \Log::info('File generated successfully. Downloading...');
        return Response::download($fileName)->deleteFileAfterSend(true);

    } catch (\Exception $e) {
        \Log::error("Export failed: " . $e->getMessage());
        return response("Export failed: " . $e->getMessage(), 500);
    }
}
}