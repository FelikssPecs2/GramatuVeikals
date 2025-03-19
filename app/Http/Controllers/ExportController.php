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
        file_put_contents(storage_path('app/test_export.txt'), 'Export test successful!');
        return response()->download(storage_path('app/test_export.txt'))->deleteFileAfterSend(true);
    }
}