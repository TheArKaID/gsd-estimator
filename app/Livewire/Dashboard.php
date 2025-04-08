<?php

namespace App\Livewire;

use App\Models\Project;
use App\Services\GsdEstimationService;
use Livewire\Component;

class Dashboard extends Component
{
    public $newProjectName = '';
    public $newProjectDescription = '';
    public $selectedProjects = [];
    public $allProjectsData = [];
    protected $gsdService;

    public function boot(GsdEstimationService $gsdService)
    {
        $this->gsdService = $gsdService;
    }

    public function render()
    {
        return view('livewire.dashboard', [
            'projects' => Project::orderBy('id', 'desc')->get()
        ]);
    }

    function saveNewProject()
    {
        $this->validate([
            'newProjectName' => 'required',
            'newProjectDescription' => 'required',
        ]);

        $project = new Project();
        $project->name = $this->newProjectName;
        $project->description = $this->newProjectDescription;
        $project->save();

        $this->newProjectName = '';
        $this->newProjectDescription = '';

        $this->dispatch('projectAdded');
    }
    
    public function showExportModal()
    {
        // Load all projects with their data
        $this->loadAllProjectsData();
        
        // Clear any previously selected projects
        $this->selectedProjects = [];
        
        $this->dispatch('showExportModal');
    }
    
    /**
     * Load all projects data for the export modal
     */
    private function loadAllProjectsData()
    {
        $this->allProjectsData = [];
        
        // Get all projects with their effort data
        $projects = Project::with(['storyPoints', 'projectGlobalFactors.globalFactorCriteria'])->get();
        
        foreach ($projects as $project) {
            // Calculate estimated work days using the service
            $estimationData = $this->gsdService->calculateProjectEstimation($project);
            
            // Get the final expected time (PERT)
            $gsdEstimation = $estimationData['final']['expected'];
            
            $this->allProjectsData[$project->id] = [
                'id' => $project->id,
                'name' => $project->name,
                'previous_estimation' => $project->previous_estimation,
                'actual_effort' => $project->actual_effort,
                'project_type' => $project->project_type,
                'project_clarity' => $project->project_clarity,
                'team_size' => $project->team_size,
                'velocity' => $project->velocity,
                'sprint_length' => $project->sprint_length,
                'gsd_estimation' => round($gsdEstimation, 2),
            ];
        }
    }
    
    public function saveEffortData()
    {
        if (empty($this->selectedProjects)) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'message' => 'Please select at least one project to save data.'
            ]);
            return;
        }
        
        foreach ($this->selectedProjects as $projectId) {
            $project = Project::find($projectId);
            if ($project && isset($this->allProjectsData[$projectId])) {
                $project->update([
                    'previous_estimation' => $this->allProjectsData[$projectId]['previous_estimation'],
                    'actual_effort' => $this->allProjectsData[$projectId]['actual_effort'],
                ]);
            }
        }
        
        // Update local data with saved values
        $this->loadAllProjectsData();
        
        $this->dispatch('showAlert', [
            'type' => 'success',
            'message' => 'Project effort data saved successfully'
        ]);
    }
    
    public function downloadExport()
    {
        if (empty($this->selectedProjects)) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'message' => 'Please select at least one project to export.'
            ]);
            return;
        }
        
        // First save the effort data
        $this->saveEffortData();
        
        // Prepare the export data - get the complete project data with calculations
        $selectedProjectsData = [];
        
        $projects = Project::with(['storyPoints', 'projectGlobalFactors.globalFactorCriteria'])
            ->whereIn('id', $this->selectedProjects)
            ->get();
            
        foreach ($projects as $project) {
            $estimationData = $this->gsdService->calculateProjectEstimation($project);
            $selectedProjectsData[] = [
                'project' => $project,
                'estimation' => $estimationData
            ];
        }
        
        // Store the data in the session for the controller to use
        session(['export_data' => $selectedProjectsData]);
        
        $this->dispatch('hideExportModal');
        
        // Redirect to export controller
        return redirect()->route('projects.export');
    }
}
