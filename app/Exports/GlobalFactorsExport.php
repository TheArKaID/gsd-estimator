<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GlobalFactorsExport implements FromView, WithTitle, ShouldAutoSize, WithStyles
{
    protected $projectsData;

    public function __construct($projectsData)
    {
        $this->projectsData = $projectsData;
    }

    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function view(): View
    {
        $globalFactorsData = [];
        
        foreach ($this->projectsData as $data) {
            $project = $data['project'];
            
            foreach ($project->projectGlobalFactors as $projectGlobalFactor) {
                $globalFactor = $projectGlobalFactor->globalFactor;
                $criteria = $projectGlobalFactor->globalFactorCriteria;
                
                $globalFactorsData[] = [
                    'project_name' => $project->name,
                    'factor_name' => $globalFactor->name,
                    'criteria_name' => $criteria->name
                ];
            }
        }
        
        return view('exports.global-factors', [
            'globalFactorsData' => $globalFactorsData
        ]);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'GSD Factors';
    }
    
    /**
     * Apply styles to the Excel document
     *
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $sheet->getStyle('A1:C1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFD3D3D3');
    }
}
