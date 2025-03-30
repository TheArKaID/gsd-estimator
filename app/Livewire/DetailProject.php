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

    // Communication complexity properties
    public $communicationChannels = 0;
    public $baselineCommunicationChannels = 21; // For a standard 7-person Scrum team
    public $communicationComplexityFactor = 1.0;
    public $communicationComplexityImpact = 0;
    public $communicationComplexityLevel = '';
    public $exceedsScrumTeamSize = false;

    // Properties for tracking communication impact separately from GSD
    public $communicationImpactDays = 0;
    public $communicationImpactPercentage = 0;
    public $formattedCommunicationImpactDays = '';

    // Add properties for estimates with only GSD factors (no communication complexity)
    public $gsdOptimisticTime = 0;
    public $gsdMostLikelyTime = 0;
    public $gsdPessimisticTime = 0;
    public $gsdExpectedTime = 0;
    
    public $formattedGsdOptimisticTime = '';
    public $formattedGsdMostLikelyTime = '';
    public $formattedGsdPessimisticTime = '';
    public $formattedGsdExpectedTime = '';
    
    public $sprintGsdOptimisticTime = '';
    public $sprintGsdMostLikelyTime = '';
    public $sprintGsdPessimisticTime = '';
    public $sprintGsdExpectedTime = '';

    // Add properties for estimates with only communication factors (no GSD)
    public $commOptimisticTime = 0;
    public $commMostLikelyTime = 0;
    public $commPessimisticTime = 0;
    public $commExpectedTime = 0;
    
    public $formattedCommOptimisticTime = '';
    public $formattedCommMostLikelyTime = '';
    public $formattedCommPessimisticTime = '';
    public $formattedCommExpectedTime = '';
    
    public $sprintCommOptimisticTime = '';
    public $sprintCommMostLikelyTime = '';
    public $sprintCommPessimisticTime = '';
    public $sprintCommExpectedTime = '';
    
    // Add properties for tracking GSD impact (from comm-only to final)
    public $gsdOnlyImpactDays = 0;
    public $gsdOnlyImpactPercentage = 0;
    public $formattedGsdOnlyImpactDays = '';

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

        // Calculate initial communication complexity
        $this->calculateCommunicationComplexity();
    }

    public function render()
    {
        $this->projectGlobalFactorModels = collect($this->globalFactors)
            ->filter(function ($factor) {
                return in_array($factor->id, $this->projectGlobalFactors);
            })
            ->keyBy('id');
            
        // Calculate communication complexity whenever needed
        $this->calculateCommunicationComplexity();
        
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

        // Recalculate communication complexity when team size changes
        $this->calculateCommunicationComplexity();

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

        // Get GSD factor adjustment (excluding communication complexity)
        $gsdAdjustmentFactor = $this->calculateGsdAdjustmentFactor();
        
        // Set default values if team metrics aren't available
        $velocity = max(1, $this->smVelocity);
        $sprintLength = max(1, $this->smSprintLength);
        
        // Base time calculation (in sprints) = Story Points / Velocity
        $baseTime = ($this->totalStoryPointsProjectTypeMultiplied / $velocity);
        
        // Calculate time estimates in days
        $baseTimeInDays = $baseTime * $sprintLength;
        
        // S1: Calculate base estimates without any factors
        $this->baseMostLikelyTime = $baseTimeInDays * 1.0;
        
        // Calculate optimistic and pessimistic using percentage adjustments based on project clarity
        $this->baseOptimisticTime = $this->baseMostLikelyTime * (1 + ($this->optimisticPercentage / 100));
        $this->basePessimisticTime = $this->baseMostLikelyTime * (1 + ($this->pessimisticPercentage / 100));
        
        // Calculate expected time using PERT formula: (O + 4M + P) / 6
        $this->baseExpectedTime = ($this->baseOptimisticTime + (4 * $this->baseMostLikelyTime) + $this->basePessimisticTime) / 6;
        
        // S2: Apply communication complexity only (no GSD factors)
        $this->commMostLikelyTime = $this->baseMostLikelyTime * $this->communicationComplexityFactor;
        $this->commOptimisticTime = $this->baseOptimisticTime * $this->communicationComplexityFactor;
        $this->commPessimisticTime = $this->basePessimisticTime * $this->communicationComplexityFactor;
        $this->commExpectedTime = ($this->commOptimisticTime + (4 * $this->commMostLikelyTime) + $this->commPessimisticTime) / 6;
        
        // S3: Apply GSD factors to communication-adjusted estimates for final values
        $this->mostLikelyTime = $this->commMostLikelyTime * $gsdAdjustmentFactor;
        $this->optimisticTime = $this->commOptimisticTime * $gsdAdjustmentFactor;
        $this->pessimisticTime = $this->commPessimisticTime * $gsdAdjustmentFactor;
        
        // Calculate expected time using PERT formula for final estimate
        $this->expectedTime = ($this->optimisticTime + (4 * $this->mostLikelyTime) + $this->pessimisticTime) / 6;
        
        // Standard deviation: (P - O) / 6
        $this->standardDeviation = ($this->pessimisticTime - $this->optimisticTime) / 6;
        
        // Calculate communication impact as percentage increase/decrease from base
        if ($this->baseExpectedTime > 0) {
            $this->communicationImpactPercentage = (($this->commExpectedTime - $this->baseExpectedTime) / $this->baseExpectedTime) * 100;
            $this->communicationImpactDays = $this->commExpectedTime - $this->baseExpectedTime;
            
            // Calculate GSD-only impact (from comm-adjusted to final)
            $this->gsdOnlyImpactDays = $this->expectedTime - $this->commExpectedTime;
            $this->gsdOnlyImpactPercentage = ($this->commExpectedTime > 0) ? 
                (($this->expectedTime - $this->commExpectedTime) / $this->commExpectedTime) * 100 : 0;
                
            // For backward compatibility, maintain the overall GSD impact (from base to final)
            $this->gsdImpactPercentage = (($this->expectedTime - $this->baseExpectedTime) / $this->baseExpectedTime) * 100;
            $this->gsdImpactDays = $this->expectedTime - $this->baseExpectedTime;
        } else {
            $this->communicationImpactPercentage = 0;
            $this->communicationImpactDays = 0;
            $this->gsdOnlyImpactDays = 0;
            $this->gsdOnlyImpactPercentage = 0;
            $this->gsdImpactPercentage = 0;
            $this->gsdImpactDays = 0;
        }
    }

    /**
     * Format all display values after calculation
     */
    private function formatDisplayValues()
    {
        // Format day-based values for base estimates
        $this->formattedBaseOptimisticTime = number_format($this->baseOptimisticTime, 1);
        $this->formattedBaseMostLikelyTime = number_format($this->baseMostLikelyTime, 1);
        $this->formattedBasePessimisticTime = number_format($this->basePessimisticTime, 1);
        $this->formattedBaseExpectedTime = number_format($this->baseExpectedTime, 1);
        
        // Format day-based values for communication-only estimates
        $this->formattedCommOptimisticTime = number_format($this->commOptimisticTime, 1);
        $this->formattedCommMostLikelyTime = number_format($this->commMostLikelyTime, 1);
        $this->formattedCommPessimisticTime = number_format($this->commPessimisticTime, 1);
        $this->formattedCommExpectedTime = number_format($this->commExpectedTime, 1);
        
        // Format day-based values for final estimates
        $this->formattedOptimisticTime = number_format($this->optimisticTime, 1);
        $this->formattedMostLikelyTime = number_format($this->mostLikelyTime, 1);
        $this->formattedPessimisticTime = number_format($this->pessimisticTime, 1);
        $this->formattedExpectedTime = number_format($this->expectedTime, 1);
        
        // Format impact values
        $this->formattedCommunicationImpactDays = number_format(abs($this->communicationImpactDays), 1);
        $this->formattedGsdOnlyImpactDays = number_format(abs($this->gsdOnlyImpactDays), 1);
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
        
        // Sprint-based values for base estimates
        $this->sprintBaseOptimisticTime = number_format($this->baseOptimisticTime / $sprintLength, 1);
        $this->sprintBaseMostLikelyTime = number_format($this->baseMostLikelyTime / $sprintLength, 1);
        $this->sprintBasePessimisticTime = number_format($this->basePessimisticTime / $sprintLength, 1);
        $this->sprintBaseExpectedTime = number_format($this->baseExpectedTime / $sprintLength, 1);
        
        // Sprint-based values for communication-only estimates
        $this->sprintCommOptimisticTime = number_format($this->commOptimisticTime / $sprintLength, 1);
        $this->sprintCommMostLikelyTime = number_format($this->commMostLikelyTime / $sprintLength, 1);
        $this->sprintCommPessimisticTime = number_format($this->commPessimisticTime / $sprintLength, 1);
        $this->sprintCommExpectedTime = number_format($this->commExpectedTime / $sprintLength, 1);
        
        // Sprint-based values for final estimates
        $this->sprintOptimisticTime = number_format($this->optimisticTime / $sprintLength, 1);
        $this->sprintMostLikelyTime = number_format($this->mostLikelyTime / $sprintLength, 1);
        $this->sprintPessimisticTime = number_format($this->pessimisticTime / $sprintLength, 1);
        $this->sprintExpectedTime = number_format($this->expectedTime / $sprintLength, 1);
        
        // Sprint-based confidence intervals
        $this->sprintConfidenceInterval68Low = number_format($confidenceInterval68Low / $sprintLength, 1);
        $this->sprintConfidenceInterval68High = number_format($confidenceInterval68High / $sprintLength, 1);
        $this->sprintConfidenceInterval95Low = number_format($confidenceInterval95Low / $sprintLength, 1);
        $this->sprintConfidenceInterval95High = number_format($confidenceInterval95High / $sprintLength, 1);
    }

    /**
     * Calculate adjustment factor based on selected global factors (GSD only)
     * 
     * @return float The cumulative adjustment factor
     */
    private function calculateGsdAdjustmentFactor()
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
     * Calculate overall adjustment factor (for backward compatibility)
     * 
     * @return float The cumulative adjustment factor including communication complexity
     */
    private function calculateAdjustmentFactor()
    {
        $gsdFactor = $this->calculateGsdAdjustmentFactor();
        $commFactor = $this->exceedsScrumTeamSize ? $this->communicationComplexityFactor : 1.0;
        return $gsdFactor * $commFactor;
    }

    /**
     * Calculate communication complexity based on team size
     * Using a standard Scrum Team (7 people, 21 links) as baseline
     */
    private function calculateCommunicationComplexity()
    {
        $teamSize = max(1, $this->smEmployee);
        
        // Calculate communication channels using the formula: n(n-1)/2
        $this->communicationChannels = ($teamSize * ($teamSize - 1)) / 2;
        
        // Use a 7-person team (21 communication links) as the baseline
        if ($teamSize <= 7) {
            // No additional complexity for standard Scrum team size
            $this->communicationComplexityLevel = 'Standard';
            $this->communicationComplexityFactor = 1.0;
            $this->communicationComplexityImpact = 0;
            $this->exceedsScrumTeamSize = false;
        } else {
            // Calculate percentage increase from baseline (21 links)
            $increasePercentage = (($this->communicationChannels - $this->baselineCommunicationChannels) / $this->baselineCommunicationChannels) * 100;
            $this->communicationComplexityImpact = round($increasePercentage);
            
            // Convert to multiplier: 25% increase = 1.25x multiplier
            $this->communicationComplexityFactor = 1 + ($increasePercentage / 100);
            
            // Set complexity level based on percentage increase
            if ($increasePercentage <= 50) {
                $this->communicationComplexityLevel = 'Elevated';
            } elseif ($increasePercentage <= 100) {
                $this->communicationComplexityLevel = 'High';
            } else {
                $this->communicationComplexityLevel = 'Very High';
            }
            
            $this->exceedsScrumTeamSize = true;
        }
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
