<?php

namespace App\Http\Controllers;

use App\Exports\ProjectsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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
        $exportData = session('export_data');
        
        if (empty($exportData)) {
            return back()->with('error', 'No export data found. Please select projects to export.');
        }
        
        // Clear session data after retrieving it
        session()->forget('export_data');
        
        return Excel::download(new ProjectsExport($exportData), 'gsd-project-comparison-' . date('Y-m-d') . '.xlsx');
    }
}
