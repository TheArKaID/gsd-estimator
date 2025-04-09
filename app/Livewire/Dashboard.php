<?php

namespace App\Livewire;

use App\Models\Project;
use App\Services\GsdEstimationService;
use Livewire\Attributes\On;
use Livewire\Component;

class Dashboard extends Component
{
    public $newProjectName = '';
    public $newProjectDescription = '';
    public $selectedProjects = [];
    public $allProjectsData = [];
    public $sessionId = null;
    protected $gsdService;

    public function boot(GsdEstimationService $gsdService)
    {
        $this->gsdService = $gsdService;
    }

    /**
     * Receive session ID from the frontend
     */
    #[On('setSessionId')]
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    public function mount()
    {
        // Create temporary ID for initial render, will be replaced by the frontend view
        if (!$this->sessionId) {
            $this->sessionId = 'gsd-' . uniqid();
        }
    }

    public function render()
    {
        $projects = collect(); // Empty collection as default
        
        // Only query projects there is a valid session ID
        if ($this->sessionId && !str_starts_with($this->sessionId, 'gsd-')) {
            $projects = Project::where('session_id', $this->sessionId)
                ->orderBy('id', 'desc')
                ->get();
        }

        return view('livewire.dashboard', [
            'projects' => $projects
        ]);
    }

    function saveNewProject()
    {
        $this->validate([
            'newProjectName' => 'required',
            'newProjectDescription' => 'required',
        ]);

        // Check there is a valid session ID
        if (!$this->sessionId || str_starts_with($this->sessionId, 'gsd-')) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'message' => 'Session ID not available. Please refresh the page and try again.'
            ]);
            return;
        }

        $project = new Project();
        $project->session_id = $this->sessionId;
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
        
        // Only proceed if we have a valid session ID
        if (!$this->sessionId || str_starts_with($this->sessionId, 'gsd-')) {
            return;
        }
        
        // Get all projects and related effort data and story points for this session
        $projects = Project::with(['storyPoints', 'projectGlobalFactors.globalFactorCriteria'])
            ->where('session_id', $this->sessionId)
            ->get();
        
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
        
        // Check if session ID valid
        if (!$this->sessionId || str_starts_with($this->sessionId, 'gsd-')) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'message' => 'Session ID not available. Please refresh the page and try again.'
            ]);
            return;
        }
        
        // First save the effort data
        $this->saveEffortData();
        
        // Prepare the export data - get the complete project data with calculations
        $selectedProjectsData = [];
        
        // Eager load story points
        $projects = Project::with(['storyPoints', 'projectGlobalFactors.globalFactorCriteria'])
            ->whereIn('id', $this->selectedProjects)
            ->where('session_id', $this->sessionId)
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
        
        $this->dispatch('showAlert', [
            'type' => 'success',
            'message' => 'Project data prepared for export successfully'
        ]);

        // Redirect to export controller
        return redirect()->route('projects.export');
    }
}
