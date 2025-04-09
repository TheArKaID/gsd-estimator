<?php

namespace App\Exports;

use App\Services\GsdEstimationService;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProjectsExport implements WithMultipleSheets
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
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        
        // Add the project summary sheet
        $sheets[] = new ProjectSummaryExport($this->projectsData, $this->gsdService);
        
        // Add the story points sheet
        $sheets[] = new StoryPointsExport($this->projectsData);
        
        return $sheets;
    }
}
