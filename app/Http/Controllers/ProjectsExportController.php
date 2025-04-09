<?php

namespace App\Http\Controllers;

use App\Exports\ProjectsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectsExportController extends Controller
{
    /**
     * Export projects data to Excel
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        // Get the export data from the session
        $exportData = session('export_data', []);
        
        if (empty($exportData)) {
            return redirect()->route('dashboard')->with('error', 'No export data found.');
        }
        
        // Generate a unique filename
        $fileName = 'project-comparison-' . now()->format('Y-m-d-H-i-s') . '.xlsx';
        
        // Create the export with the stored data
        $export = new ProjectsExport($exportData);
        
        // Store the file first instead of directly downloading, avoid the "Text file busy" error
        Excel::store($export, $fileName, 'public');
        
        // Get full path of the stored file
        $filePath = storage_path('app/public/' . $fileName);
        
        // Send the file as a response with proper headers
        $response = response()->download($filePath, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
        
        // Don't delete the file after sending (prevents text file busy error)
        if ($response instanceof BinaryFileResponse) {
            $response->deleteFileAfterSend(false);
        }
        
        return $response;
    }
}
