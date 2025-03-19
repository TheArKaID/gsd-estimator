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
        
        // STEP 1: Calculate base estimates without GSD factors
        $this->baseOptimisticTime = $baseTimeInWeeks * $cocomoParams['optimistic']['coefficient'];
        $this->baseMostLikelyTime = $baseTimeInWeeks * $cocomoParams['nominal']['coefficient'];
        $this->basePessimisticTime = $baseTimeInWeeks * $cocomoParams['pessimistic']['coefficient'];
        $this->baseExpectedTime = ($this->baseOptimisticTime + (4 * $this->baseMostLikelyTime) + $this->basePessimisticTime) / 6;
        
        // STEP 2: Apply GSD factors to get final estimates
        $this->optimisticTime = $this->baseOptimisticTime * $adjustmentFactor['min'];
        $this->mostLikelyTime = $this->baseMostLikelyTime * $adjustmentFactor['avg'];
        $this->pessimisticTime = $this->basePessimisticTime * $adjustmentFactor['max'];
        
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
     * @return array Coefficient and exponent values for different scenarios
     */
    private function getCocomoParameters($projectType)
    {
        $projectTypes = [
            'organic' => [
                'nominal' => ['coefficient' => 2.4, 'exponent' => 1.05],
                'optimistic' => ['coefficient' => 2.0, 'exponent' => 1.0],
                'pessimistic' => ['coefficient' => 3.0, 'exponent' => 1.1]
            ],
            'semi-detached' => [
                'nominal' => ['coefficient' => 3.0, 'exponent' => 1.12],
                'optimistic' => ['coefficient' => 2.6, 'exponent' => 1.08],
                'pessimistic' => ['coefficient' => 3.6, 'exponent' => 1.16]
            ],
            'embedded' => [
                'nominal' => ['coefficient' => 3.6, 'exponent' => 1.20],
                'optimistic' => ['coefficient' => 3.2, 'exponent' => 1.15],
                'pessimistic' => ['coefficient' => 4.0, 'exponent' => 1.25]
            ]
        ];

        // Default to organic if type is not recognized
        return $projectTypes[$projectType] ?? $projectTypes['organic'];
    }
    
    /**
     * Calculate adjustment factor based on selected global factors
     * 
     * @return array Min, avg, and max adjustment factors
     */
    private function calculateAdjustmentFactor()
    {
        $result = ['min' => 1, 'avg' => 1, 'max' => 1];
        
        // If no global factors are selected, return default values
        if (empty($this->projectGlobalFactors)) {
            return $result;
        }
        
        // Get the project's global factors with their criteria
        $selectedFactors = $this->project->projectGlobalFactors()
            ->with('globalFactorCriteria')
            ->get();
        
        // Multiplicative approach - start with 1.0 and multiply with each factor
        $multiplier = 1.0;
        foreach ($selectedFactors as $factor) {
            // Get the criteria selected for this factor
            $criteriaValue = $factor->globalFactorCriteria->value ?? 0;

            // Convert to a multiplicative factor (assuming criteriaValue is a percentage-like value)
            // If criteriaValue is already a multiplier like 1.1, 1.2, etc., remove this conversion
            $factorMultiplier = 1 + ($criteriaValue / 100);

            // Multiply to get the cumulative effect
            $multiplier *= $factorMultiplier;
        }

        // Apply the multiplier with different weights for optimistic, average, and pessimistic
        // Optimistic case: slightly reduce the effect
        $result['min'] = 1 + (($multiplier - 1) * 0.8);
        
        // Average case: use the multiplier directly
        $result['avg'] = $multiplier;
        
        // Pessimistic case: slightly amplify the effect
        $result['max'] = 1 + (($multiplier - 1) * 1.2);

        return $result;
    }
}
