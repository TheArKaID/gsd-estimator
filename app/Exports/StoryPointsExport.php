<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StoryPointsExport implements FromView, WithTitle, ShouldAutoSize, WithStyles
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
        $storyPointsData = [];
        
        foreach ($this->projectsData as $data) {
            $project = $data['project'];
            
            foreach ($project->storyPoints as $storyPoint) {
                $storyPointsData[] = [
                    'project_name' => $project->name,
                    'id' => $storyPoint->id,
                    'name' => $storyPoint->name,
                    'description' => $storyPoint->description,
                    'value' => $storyPoint->value,
                    'created_at' => $storyPoint->created_at->format('Y-m-d'),
                ];
            }
        }
        
        return view('exports.story-points', [
            'storyPointsData' => $storyPointsData
        ]);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'User Stories';
    }
    
    /**
     * Apply styles to the Excel document
     *
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A1:F1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFD3D3D3');
    }
}
