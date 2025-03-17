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

    function mount($id)
    {
        $this->project = Project::with(['storyPoints', 'globalFactors'])->find($id);
        $this->projectType = $this->project->project_type;

        // Load project global factors
        $this->projectGlobalFactors = collect($this->project->globalFactors)->pluck('id')->toArray();

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
        
        // Apply COCOMO adjustments based on project type for most likely estimate
        $this->mostLikelyTime = $baseTimeInWeeks * $cocomoParams['nominal']['coefficient'] * $adjustmentFactor['avg'];
        
        // Calculate optimistic and pessimistic based on COCOMO parameter ranges
        $this->optimisticTime = $baseTimeInWeeks * $cocomoParams['optimistic']['coefficient'] * $adjustmentFactor['min'];
        $this->pessimisticTime = $baseTimeInWeeks * $cocomoParams['pessimistic']['coefficient'] * $adjustmentFactor['max'];
        
        // Expected time using PERT formula: (O + 4M + P) / 6
        $this->expectedTime = ($this->optimisticTime + (4 * $this->mostLikelyTime) + $this->pessimisticTime) / 6;
        
        // Standard deviation: (P - O) / 6
        $this->standardDeviation = ($this->pessimisticTime - $this->optimisticTime) / 6;
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
        $factorValues = [];
        $result = ['min' => 1, 'avg' => 1, 'max' => 1];
        
        // If no global factors are selected, return default values
        if (empty($this->projectGlobalFactors)) {
            return $result;
        }
        
        // Get the project's global factors with their criteria
        $selectedFactors = $this->project->globalFactors()
            ->with('criterias')
            ->get();
        
        foreach ($selectedFactors as $factor) {
            // Get the criteria selected for this factor
            $criteriaValue = $factor->pivot->globalFactorCriteria->value ?? 1;
            $factorValues[] = $criteriaValue;
        }
        
        // If we have values, calculate the adjustment factors
        if (!empty($factorValues)) {
            // Calculate EAF (Effort Adjustment Factor) based on Boehm's approach
            // Each criterion contributes a multiplicative factor
            $sumOfFactors = array_sum($factorValues);
            
            // COCOMO uses multiplicative factors, but we'll use a simplified approach
            // since our criteria values might be on a different scale
            $result['min'] = 1 - ($sumOfFactors * 0.01); // Optimistic reduction
            $result['avg'] = 1 + ($sumOfFactors * 0.02); // Average adjustment
            $result['max'] = 1 + ($sumOfFactors * 0.05); // Pessimistic increase
            
            // Ensure minimum value is not too small
            $result['min'] = max(0.7, $result['min']);
        }
        
        return $result;
    }
}
