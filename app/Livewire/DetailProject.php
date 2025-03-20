<?php

namespace App\Livewire;

use App\Models\GlobalFactor;
use App\Models\Project;
use App\Models\ProjectGlobalFactor;
use Livewire\Component;

class DetailProject extends Component
{
    public Project $project;
    public $spName, $spDescription, $spValue, $customSpValue;
    public $projectType;
    public $globalFactors = [];
    public $projectGlobalFactors = [];
    public $projectGlobalFactorModels = [];
    public $criteriaProjectGlobalFactors = [];

    public $smUSP = 0;
    public $smEmployee = 0;
    public $smVelocity = 0;

    // Properties for effort estimation
    public $totalStoryPoints = 0;
    public $optimisticTime = 0;
    public $mostLikelyTime = 0;
    public $pessimisticTime = 0;
    public $expectedTime = 0;
    public $standardDeviation = 0;

    public $projectTypeParam;

    // Additional properties to store base estimates (without GSD factors)
    public $baseOptimisticTime = 0;
    public $baseMostLikelyTime = 0;
    public $basePessimisticTime = 0;
    public $baseExpectedTime = 0;
    
    // Property to track GSD impact
    public $gsdImpactPercentage = 0;

    // Add properties for percentage adjustments
    public $optimisticPercentage = -25; // 25% reduction for best case
    public $pessimisticPercentage = 75; // 75% increase for worst case

    function mount($id)
    {
        $this->project = Project::with(['storyPoints', 'globalFactors'])->find($id);
        $this->projectType = $this->project->project_type;

        // Load project global factors
        $this->projectGlobalFactors = collect($this->project->globalFactors)->pluck('id')->toArray();

        $this->criteriaProjectGlobalFactors = $this->project->projectGlobalFactors()
            ->pluck('global_factor_criteria_id', 'global_factor_id')
            ->toArray();

        // Load GSD parameters
        $this->globalFactors = GlobalFactor::all();
        
        // Load existing software metrics if available
        $this->smEmployee = $this->project->team_size ?? 0;
        $this->smVelocity = $this->project->velocity ?? 0;

        $this->projectTypeParam = $this->getCocomoParameters($this->projectType);
    }

    public function render()
    {
        $this->projectGlobalFactorModels = collect($this->globalFactors)
            ->filter(function ($factor) {
                return in_array($factor->id, $this->projectGlobalFactors);
            })
            ->keyBy('id');
            
        // Calculate project metrics for summary
        $this->calculateEstimation();
            
        return view('livewire.detail-project');
    }

    public function saveStoryPoint()
    {
        $this->validate([
            'spName' => 'required',
            'spDescription' => 'nullable',
            'spValue' => 'required'
        ]);

        $value = $this->spValue === 'custom' ? $this->customSpValue : $this->spValue;

        $this->project->storyPoints()->create([
            'name' => $this->spName,
            'description' => $this->spDescription,
            'value' => $value
        ]);

        $this->spName = '';
        $this->spDescription = '';
        $this->spValue = '';
        $this->customSpValue = '';

        $this->dispatch('story-point-added');
    }

    public function deleteStoryPoint($id)
    {
        $this->project->storyPoints()->find($id)->delete();

        $this->dispatch('story-point-deleted', $id);
    }

    function saveProjectType()
    {
        $this->validate([
            'projectType' => 'required'
        ]);

        $this->project->update([
            'project_type' => $this->projectType
        ]);

        $this->dispatch('project-type-saved');
    }

    public function saveGsdParameters()
    {
        $this->validate([
            'projectGlobalFactors' => 'array',
        ]);

        // First, remove all existing global factors not in the new list
        $existingFactorIds = collect($this->project->globalFactors)->pluck('id')->toArray();
        $factorsToRemove = array_diff($existingFactorIds, $this->projectGlobalFactors);

        foreach ($factorsToRemove as $factorId) {
            // Delete the intermediate record that connects Project to GlobalFactor
            ProjectGlobalFactor::where('global_factor_id', $factorId)->where('project_id', $this->project->id)->delete();
        }

        // Now add or update the selected factors
        foreach ($this->projectGlobalFactors as $factorId) {
            ProjectGlobalFactor::updateOrCreate(
                [
                    'global_factor_id' => $factorId,
                    'project_id' => $this->project->id
                ],
                [
                    'global_factor_criteria_id' => $this->criteriaProjectGlobalFactors[$factorId] ?? null
                ]
            );
        }
    }

    public function selectCriteria($factorId, $criteriaId)
    {
        ProjectGlobalFactor::updateOrCreate(
            [
                'project_id' => $this->project->id,
                'global_factor_id' => $factorId
            ],
            [
                'global_factor_criteria_id' => $criteriaId
            ]
        );

        $this->criteriaProjectGlobalFactors[$factorId] = $criteriaId;
    }

    public function saveSoftwareMetrics()
    {
        $this->validate([
            'smEmployee' => 'required|numeric|min:1',
            'smVelocity' => 'required|numeric|min:1',
        ]);

        $this->project->update([
            'team_size' => $this->smEmployee,
            'velocity' => $this->smVelocity
        ]);

        $this->dispatch('software-metrics-saved');
    }

    /**
     * Calculate effort estimation based on story points, team velocity, and other factors
     */
    public function calculateEstimation()
    {
        // Calculate total story points
        $this->totalStoryPoints = collect($this->project->storyPoints)->sum('value');
        
        // Get global factor adjustment
        $adjustmentFactor = $this->calculateAdjustmentFactor();

        // Set default values if team metrics aren't available
        $velocity = max(1, $this->smVelocity); // Prevent division by zero
        $teamSize = max(1, $this->smEmployee);
        
        // Base time calculation (in ideal days) = Story Points / Velocity
        $baseTime = $this->totalStoryPoints / $velocity;
        
        // Calculate time estimates in weeks (assuming 5 work days per week)
        // Adjusted by team size
        $baseTimeInWeeks = $baseTime / (5 * $teamSize);
        
        // Get COCOMO parameters based on project type
        $cocomoParams = $this->getCocomoParameters($this->projectType);
        
        // STEP 1: Calculate nominal estimate with COCOMO without GSD factors
        $this->baseMostLikelyTime = $baseTimeInWeeks * $cocomoParams['coefficient'];
        
        // Calculate optimistic and pessimistic using percentage adjustments
        $this->baseOptimisticTime = $this->baseMostLikelyTime * (1 + ($this->optimisticPercentage / 100));
        $this->basePessimisticTime = $this->baseMostLikelyTime * (1 + ($this->pessimisticPercentage / 100));
        
        // Base expected time using PERT formula
        $this->baseExpectedTime = ($this->baseOptimisticTime + (4 * $this->baseMostLikelyTime) + $this->basePessimisticTime) / 6;
        
        // STEP 2: Apply GSD factors to all estimates
        $this->mostLikelyTime = $this->baseMostLikelyTime * $adjustmentFactor;
        $this->optimisticTime = $this->baseOptimisticTime * $adjustmentFactor;
        $this->pessimisticTime = $this->basePessimisticTime * $adjustmentFactor;
        
        // Expected time using PERT formula: (O + 4M + P) / 6
        $this->expectedTime = ($this->optimisticTime + (4 * $this->mostLikelyTime) + $this->pessimisticTime) / 6;
        
        // Standard deviation: (P - O) / 6
        $this->standardDeviation = ($this->pessimisticTime - $this->optimisticTime) / 6;
        
        // Calculate GSD impact as percentage increase/decrease
        if ($this->baseExpectedTime > 0) {
            $this->gsdImpactPercentage = (($this->expectedTime - $this->baseExpectedTime) / $this->baseExpectedTime) * 100;
        } else {
            $this->gsdImpactPercentage = 0;
        }
    }

    /**
     * Get COCOMO parameters based on project type
     * 
     * @param string $projectType The project type (organic, semi-detached, embedded)
     * @return array Coefficient value for the project type
     */
    private function getCocomoParameters($projectType)
    {
        $parameters = [
            'organic' => ['coefficient' => 2.4, 'exponent' => 1.05],
            'semi-detached' => ['coefficient' => 3.0, 'exponent' => 1.12],
            'embedded' => ['coefficient' => 3.6, 'exponent' => 1.20]
        ];

        // Default to organic if type is not recognized
        return $parameters[$projectType] ?? $parameters['organic'];
    }
    
    /**
     * Calculate adjustment factor based on selected global factors
     * 
     * @return float The cumulative adjustment factor
     */
    private function calculateAdjustmentFactor()
    {
        $result = 1.0;
        
        // If no global factors are selected, return default values
        if (empty($this->projectGlobalFactors)) {
            return $result;
        }
        
        // Get the project's global factors with their criteria
        $selectedFactors = $this->project->projectGlobalFactors()
            ->with('globalFactorCriteria')
            ->get();

        foreach ($selectedFactors as $factor) {
            // Get the criteria selected for this factor
            $criteriaValue = $factor->globalFactorCriteria->value ?? 1.0;

            // Multiply to get the cumulative effect
            $result *= $criteriaValue;
        }
        return $result;
    }
}
