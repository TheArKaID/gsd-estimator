<?php

namespace App\Exports;

use App\Services\GsdEstimationService;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProjectsExport implements FromView, WithTitle, ShouldAutoSize, WithStyles
{
    use Exportable;

    protected $projectsData;
    protected $gsdService;

    public function __construct($projectsData)
    {
        $this->projectsData = $projectsData;
        $this->gsdService = new GsdEstimationService();
    }

    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function view(): View
    {
        $projectData = [];
        
        foreach ($this->projectsData as $data) {
            $project = $data['project'];
            $estimation = $data['estimation'];
            
            // Get the GSD estimation value from the provided estimation data
            $gsdEstimation = $estimation['final']['expected'];
            
            // Calculate accuracy using the service
            $gsdAccuracy = $this->gsdService->calculateAccuracy($gsdEstimation, $project->actual_effort);
            $previousAccuracy = $this->gsdService->calculateAccuracy($project->previous_estimation, $project->actual_effort);
            
            // Compare which estimation is better
            $comparisonData = $this->gsdService->compareEstimations(
                $gsdEstimation, 
                $project->previous_estimation, 
                $project->actual_effort
            );
            
            $projectData[] = [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'project_type' => ucfirst($project->project_type ?? 'Unknown'),
                'project_clarity' => ucfirst($project->project_clarity ?? 'Unknown'),
                'team_size' => $project->team_size,
                'velocity' => $project->velocity,
                'sprint_length' => $project->sprint_length,
                'total_story_points' => $project->storyPoints->sum('value'),
                'gsd_estimation' => round($gsdEstimation, 2),
                'previous_estimation' => $project->previous_estimation,
                'actual_effort' => $project->actual_effort,
                'vs_estimation_percent' => $comparisonData['difference_percent'],
                'vs_estimation_better' => $comparisonData['gsd_is_better'],
                'gsd_accuracy_percent' => $gsdAccuracy['percent'],
                'gsd_accuracy_type' => $gsdAccuracy['type'],
                'previous_accuracy_percent' => $previousAccuracy['percent'],
                'previous_accuracy_type' => $previousAccuracy['type'],
            ];
        }
        
        return view('exports.projects', [
            'projectData' => $projectData
        ]);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Project Comparison';
    }
    
    /**
     * Apply styles to the Excel document
     *
     * @param Worksheet $sheet)
     */
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:P1')->getFont()->setBold(true);
        $sheet->getStyle('A1:P1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFD3D3D3');
    }
}
