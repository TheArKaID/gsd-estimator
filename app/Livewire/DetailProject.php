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
    public $projectClarity;
    public $projectType; 
    public $projectTypeCoefficient;
    public $projectTypeExponent;
    public $projectTypeMultiplier; 
    public $globalFactors = [];
    public $projectGlobalFactors = [];
    public $projectGlobalFactorModels = [];
    public $criteriaProjectGlobalFactors = [];

    public $smEmployee = 0;
    public $smVelocity = 0;
    public $smSprintLength = 14; // Default sprint length of 14 days (2 weeks)

    // Properties for effort estimation
    public $totalStoryPoints = 0;
    public $totalStoryPointsProjectTypeMultiplied = 0;
    public $optimisticTime = 0;
    public $mostLikelyTime = 0;
    public $pessimisticTime = 0;
    public $expectedTime = 0;
    public $standardDeviation = 0;
    public $claritySummary = '';

    // Additional properties to store base estimates (without GSD factors)
    public $baseOptimisticTime = 0;
    public $baseMostLikelyTime = 0;
    public $basePessimisticTime = 0;
    public $baseExpectedTime = 0;

    // Additional properties to store sprint-based estimates
    public $optimisticPercentage = 0;
    public $pessimisticPercentage = 0;
    
    // Property to track GSD impact
    public $gsdImpactPercentage = 0;
    public $gsdImpactDays = 0;

    // Properties for formatted display values (in days)
    public $formattedOptimisticTime = '';
    public $formattedMostLikelyTime = '';
    public $formattedPessimisticTime = '';
    public $formattedExpectedTime = '';
    public $formattedBaseOptimisticTime = '';
    public $formattedBaseMostLikelyTime = '';
    public $formattedBasePessimisticTime = '';
    public $formattedBaseExpectedTime = '';
    public $formattedGsdImpactDays = '';
    
    // Properties for sprint-based values
    public $sprintOptimisticTime = '';
    public $sprintMostLikelyTime = '';
    public $sprintPessimisticTime = '';
    public $sprintExpectedTime = '';
    public $sprintBaseOptimisticTime = '';
    public $sprintBaseMostLikelyTime = '';
    public $sprintBasePessimisticTime = '';
    public $sprintBaseExpectedTime = '';
    
    // Properties for confidence intervals
    public $confidenceInterval68Low = '';
    public $confidenceInterval68High = '';
    public $confidenceInterval95Low = '';
    public $confidenceInterval95High = '';
    public $sprintConfidenceInterval68Low = '';
    public $sprintConfidenceInterval68High = '';
    public $sprintConfidenceInterval95Low = '';
    public $sprintConfidenceInterval95High = '';

    function mount($id)
    {
        $this->project = Project::with(['storyPoints', 'globalFactors'])->find($id);
        $this->projectClarity = $this->project->project_clarity ?? 'evolving';
        $this->projectType = $this->project->project_type ?? 'organic';  // Set default project type

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
        $this->smSprintLength = $this->project->sprint_length ?? 14;

        // Set percentage ranges based on project clarity
        $this->setClarityRanges($this->projectClarity);
        
        // Set COCOMO parameters based on project type
        $this->setProjectTypeParameters($this->projectType);
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
        
        // Format all display values
        $this->formatDisplayValues();
            
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

    function saveProjectClarity()
    {
        $this->validate([
            'projectClarity' => 'required'
        ]);

        $this->project->update([
            'project_clarity' => $this->projectClarity
        ]);

        // Update the percentage ranges based on the selected clarity
        $this->setClarityRanges($this->projectClarity);

        $this->dispatch('project-clarity-saved');
    }

    /**
     * Set the percentage ranges for optimistic and pessimistic estimates based on project clarity
     */
    private function setClarityRanges($clarity)
    {
        $ranges = [
            'conceptual' => ['optimistic' => -25, 'pessimistic' => 75, 'summary' => 'Range: -25% to +75%'],
            'evolving' => ['optimistic' => -10, 'pessimistic' => 25, 'summary' => 'Range: -10% to +25%'],
            'established' => ['optimistic' => -5, 'pessimistic' => 10, 'summary' => 'Range: -5% to +10%)']
        ];

        $selectedRange = $ranges[$clarity] ?? $ranges['evolving'];
        
        $this->optimisticPercentage = $selectedRange['optimistic'];
        $this->pessimisticPercentage = $selectedRange['pessimistic'];
        $this->claritySummary = $selectedRange['summary'];
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
            'smSprintLength' => 'required|numeric|min:1|max:30',
        ]);

        $this->project->update([
            'team_size' => $this->smEmployee,
            'velocity' => $this->smVelocity,
            'sprint_length' => $this->smSprintLength
        ]);

        $this->dispatch('software-metrics-saved');
    }

    /**
     * Save the project type selection
     */
    function saveProjectType()
    {
        $this->validate([
            'projectType' => 'required|in:organic,semi-detached,embedded'
        ]);

        $this->project->update([
            'project_type' => $this->projectType
        ]);

        // Update the COCOMO parameters
        $this->setProjectTypeParameters($this->projectType);

        $this->dispatch('project-type-saved');
    }

    /**
     * Set COCOMO parameters based on project type
     */
    private function setProjectTypeParameters($type)
    {
        $parameters = [
            'organic' => ['coefficient' => 2.4, 'exponent' => 1.05], // Baseline
            'semi-detached' => ['coefficient' => 3.0, 'exponent' => 1.12],
            'embedded' => ['coefficient' => 3.6, 'exponent' => 1.20]
        ];

        // Semi-detached: 3.0 / 2.4 = 1.25 => increase 25%
        // Embedded: 3.6 / 2.4 = 1.5 => increase 50%
        $baseMultiplier = $parameters['organic']['coefficient'] * (1 ** $parameters['organic']['exponent']);

        $selected = $parameters[$type] ?? $parameters['organic'];
        
        $this->projectTypeCoefficient = $selected['coefficient'];
        $this->projectTypeExponent = $selected['exponent'];
        $this->projectTypeMultiplier = $selected['coefficient'] / $baseMultiplier;
    }

    /**
     * Calculate effort estimation based on story points and team's avg velocity
     */
    public function calculateEstimation()
    {
        // Calculate total story points
        $this->totalStoryPoints = collect($this->project->storyPoints)->sum('value');
        // Round it to the nearest whole number
        $this->totalStoryPointsProjectTypeMultiplied = round($this->totalStoryPoints * $this->projectTypeMultiplier);

        // Get global factor adjustment
        $adjustmentFactor = $this->calculateAdjustmentFactor();

        // Set default values if team metrics aren't available
        $velocity = max(1, $this->smVelocity);
        $sprintLength = max(1, $this->smSprintLength);
        
        // Base time calculation (in sprints) = Story Points / Velocity
        $baseTime = ($this->totalStoryPointsProjectTypeMultiplied / $velocity);
        
        // Calculate time estimates in days
        $baseTimeInDays = $baseTime * $sprintLength;
        
        // S1: Calculate estimates without GSD factors
        // Using a base multiplier of 1.0 for the most likely estimate
        $this->baseMostLikelyTime = $baseTimeInDays * 1.0;
        
        // Calculate optimistic and pessimistic using percentage adjustments based on project clarity
        $this->baseOptimisticTime = $this->baseMostLikelyTime * (1 + ($this->optimisticPercentage / 100));
        $this->basePessimisticTime = $this->baseMostLikelyTime * (1 + ($this->pessimisticPercentage / 100));
        
        // Calculate expected time using PERT formula: (O + 4M + P) / 6
        $this->baseExpectedTime = ($this->baseOptimisticTime + (4 * $this->baseMostLikelyTime) + $this->basePessimisticTime) / 6;
        
        // S2: Apply GSD factors to all estimates
        $this->mostLikelyTime = $this->baseMostLikelyTime * $adjustmentFactor;
        $this->optimisticTime = $this->baseOptimisticTime * $adjustmentFactor;
        $this->pessimisticTime = $this->basePessimisticTime * $adjustmentFactor;
        
        // Calculate expected time using PERT formula for final estimate
        $this->expectedTime = ($this->optimisticTime + (4 * $this->mostLikelyTime) + $this->pessimisticTime) / 6;
        
        // Standard deviation: (P - O) / 6
        $this->standardDeviation = ($this->pessimisticTime - $this->optimisticTime) / 6;
        
        // Calculate GSD impact as percentage increase/decrease
        if ($this->baseExpectedTime > 0) {
            $this->gsdImpactPercentage = (($this->expectedTime - $this->baseExpectedTime) / $this->baseExpectedTime) * 100;
            $this->gsdImpactDays = $this->expectedTime - $this->baseExpectedTime;
        } else {
            $this->gsdImpactPercentage = 0;
            $this->gsdImpactDays = 0;
        }
    }

    /**
     * Format all display values after calculation
     */
    private function formatDisplayValues()
    {
        // Format day-based values
        $this->formattedOptimisticTime = number_format($this->optimisticTime, 1);
        $this->formattedMostLikelyTime = number_format($this->mostLikelyTime, 1);
        $this->formattedPessimisticTime = number_format($this->pessimisticTime, 1);
        $this->formattedExpectedTime = number_format($this->expectedTime, 1);
        
        $this->formattedBaseOptimisticTime = number_format($this->baseOptimisticTime, 1);
        $this->formattedBaseMostLikelyTime = number_format($this->baseMostLikelyTime, 1);
        $this->formattedBasePessimisticTime = number_format($this->basePessimisticTime, 1);
        $this->formattedBaseExpectedTime = number_format($this->baseExpectedTime, 1);
        
        $this->formattedGsdImpactDays = number_format(abs($this->gsdImpactDays), 1);
        
        // Calculate confidence intervals
        $confidenceInterval68Low = $this->expectedTime - $this->standardDeviation;
        $confidenceInterval68High = $this->expectedTime + $this->standardDeviation;
        $confidenceInterval95Low = $this->expectedTime - (2 * $this->standardDeviation);
        $confidenceInterval95High = $this->expectedTime + (2 * $this->standardDeviation);
        
        $this->confidenceInterval68Low = number_format($confidenceInterval68Low, 1);
        $this->confidenceInterval68High = number_format($confidenceInterval68High, 1);
        $this->confidenceInterval95Low = number_format($confidenceInterval95Low, 1);
        $this->confidenceInterval95High = number_format($confidenceInterval95High, 1);

        // Calculate sprint-based values
        $sprintLength = max(1, $this->smSprintLength);
        
        $this->sprintOptimisticTime = number_format($this->optimisticTime / $sprintLength, 1);
        $this->sprintMostLikelyTime = number_format($this->mostLikelyTime / $sprintLength, 1);
        $this->sprintPessimisticTime = number_format($this->pessimisticTime / $sprintLength, 1);
        $this->sprintExpectedTime = number_format($this->expectedTime / $sprintLength, 1);
        
        $this->sprintBaseOptimisticTime = number_format($this->baseOptimisticTime / $sprintLength, 1);
        $this->sprintBaseMostLikelyTime = number_format($this->baseMostLikelyTime / $sprintLength, 1);
        $this->sprintBasePessimisticTime = number_format($this->basePessimisticTime / $sprintLength, 1);
        $this->sprintBaseExpectedTime = number_format($this->baseExpectedTime / $sprintLength, 1);
        
        $this->sprintConfidenceInterval68Low = number_format($confidenceInterval68Low / $sprintLength, 1);
        $this->sprintConfidenceInterval68High = number_format($confidenceInterval68High / $sprintLength, 1);
        $this->sprintConfidenceInterval95Low = number_format($confidenceInterval95Low / $sprintLength, 1);
        $this->sprintConfidenceInterval95High = number_format($confidenceInterval95High / $sprintLength, 1);
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

    /**
     * Helper function to convert days to sprint count
     */
    public function daysToSprints($days)
    {
        $sprintLength = max(1, $this->smSprintLength);
        return $days / $sprintLength;
    }
}
