<?php

namespace App\Livewire;

use App\Models\GlobalFactor;
use App\Models\Project;
use App\Models\ProjectGlobalFactor;
use App\Services\GsdEstimationService;
use Livewire\Attributes\On;
use Livewire\Component;

class DetailProject extends Component
{
    public Project $project;
    public $sessionId = null;
    public $isOwner = true;

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
    public $communicationPath = 0;
    public $baselineCommunicationPath = 45; // For a standard 10-person Scrum team
    public $communicationComplexityFactor = 1.0;
    public $communicationComplexityImpact = 0;
    public $communicationComplexityLevel = '';
    public $exceedsScrumTeamSize = false;

    // Properties for tracking communication impact separately from GSD
    public $communicationImpactDays = 0;
    public $communicationImpactPercentage = 0;
    public $formattedCommunicationImpactDays = '';

    // Add properties for estimates with only communication factors (no GSD)
    public $commOptimisticTime = 0;
    public $commMostLikelyTime = 0;
    public $commPessimisticTime = 0;
    public $commExpectedTime = 0;
    
    public $formattedCommMostLikelyTime = '';
    public $sprintCommMostLikelyTime = '';
    // Add properties for tracking GSD impact (from comm-only to final)
    public $gsdOnlyImpactDays = 0;
    public $gsdOnlyImpactPercentage = 0;
    public $formattedGsdOnlyImpactDays = '';

    // Add properties for tracking PERT impact
    public $pertImpactDays = 0;
    public $pertImpactPercentage = 0;

    // Inject GSD Estimation Service
    protected GsdEstimationService $gsdService;

    /**
     * Receive session ID from the frontend
     */
    #[On('setSessionId')]
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
        // Check if the user is the project owner after receiving the session ID
        $this->checkProjectOwnership();
    }

    public function boot(GsdEstimationService $gsdService)
    {
        $this->gsdService = $gsdService;

        // Create temporary ID for initial render
        if (!$this->sessionId) {
            $this->sessionId = 'gsd-' . uniqid();
        }
    }

    function mount($id)
    {
        $this->project = Project::with(['storyPoints', 'globalFactors'])->find($id);

        if (!$this->project) {
            return redirect()->to('/')->with('message', 'Project not found');
        }

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

    /**
     * Check if the current user is the project owner
     */
    private function checkProjectOwnership()
    {
        // Don't validate ownership if using temporary ID
        if (str_starts_with($this->sessionId, 'gsd-')) {
            $this->isOwner = false;
            return;
        }

        $this->isOwner = ($this->project->session_id === $this->sessionId);
    }

    public function render()
    {
        $this->projectGlobalFactorModels = collect($this->globalFactors)
            ->filter(function ($factor) {
                return in_array($factor->id, $this->projectGlobalFactors);
            })
            ->keyBy('id');
        
        // Calculate project metrics for summary using service
        $this->calculateEstimation();
        
        // Format all display values
        $this->formatDisplayValues();
            
        return view('livewire.detail-project');
    }

    /**
     * Verify the current user can edit this project
     */
    private function verifyOwnership()
    {
        if (!$this->isOwner) {
            $this->dispatch('showAlert', [
                'type' => 'error',
                'message' => 'You are not authorized to modify this project'
            ]);
            return false;
        }
        return true;
    }

    public function saveStoryPoint()
    {
        if (!$this->verifyOwnership()) {
            return;
        }

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
        if (!$this->verifyOwnership()) {
            return;
        }

        $this->project->storyPoints()->find($id)->delete();

        $this->dispatch('story-point-deleted', $id);
    }

    function saveProjectClarity()
    {
        if (!$this->verifyOwnership()) {
            return;
        }

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
        $ranges = $this->gsdService->getClarityRanges($clarity);
        
        $this->optimisticPercentage = $ranges['optimistic'];
        $this->pessimisticPercentage = $ranges['pessimistic'];
        $this->claritySummary = $ranges['summary'];
    }

    public function saveGsdParameters()
    {
        if (!$this->verifyOwnership()) {
            return;
        }

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
        if (!$this->verifyOwnership()) {
            return;
        }

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
        if (!$this->verifyOwnership()) {
            return;
        }

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
        if (!$this->verifyOwnership()) {
            return;
        }

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
        $typeMultiplier = $this->gsdService->getProjectTypeMultiplier($type);
        
        $parameters = [
            'organic' => ['coefficient' => 2.4, 'exponent' => 1.05], // Baseline
            'semi-detached' => ['coefficient' => 3.0, 'exponent' => 1.12],
            'embedded' => ['coefficient' => 3.6, 'exponent' => 1.20]
        ];

        $selected = $parameters[$type] ?? $parameters['organic'];
        
        $this->projectTypeCoefficient = $selected['coefficient'];
        $this->projectTypeExponent = $selected['exponent'];
        $this->projectTypeMultiplier = $typeMultiplier;
    }

    /**
     * Calculate effort estimation using the GsdEstimationService
     */
    private function calculateEstimation()
    {
        // Ensure project relationships are loaded
        $this->project->refresh()->load(['storyPoints', 'projectGlobalFactors.globalFactorCriteria']);
        
        // Use the service to calculate all estimation data
        $estimationData = $this->gsdService->calculateProjectEstimation($this->project);
        
        // Update total story points data
        $this->totalStoryPoints = $estimationData['total_story_points'];
        $this->totalStoryPointsProjectTypeMultiplied = $estimationData['adjusted_story_points'];
        
        // Update base estimates
        $this->baseOptimisticTime = $estimationData['base']['optimistic'];
        $this->baseMostLikelyTime = $estimationData['base']['most_likely'];
        $this->basePessimisticTime = $estimationData['base']['pessimistic'];
        $this->baseExpectedTime = $estimationData['base']['expected'];
        
        // Update communication-adjusted estimates
        $this->commOptimisticTime = $estimationData['with_communication']['optimistic'];
        $this->commMostLikelyTime = $estimationData['with_communication']['most_likely'];
        $this->commPessimisticTime = $estimationData['with_communication']['pessimistic'];
        $this->commExpectedTime = $estimationData['with_communication']['expected'];
        
        // Update final estimates with GSD factors
        $this->optimisticTime = $estimationData['final']['optimistic'];
        $this->mostLikelyTime = $estimationData['final']['most_likely'];
        $this->pessimisticTime = $estimationData['final']['pessimistic'];
        $this->expectedTime = $estimationData['final']['expected'];
        $this->standardDeviation = $estimationData['final']['standard_deviation'];
        
        // Update communication complexity data
        $this->communicationPath = $estimationData['communication']['channels'];
        $this->communicationComplexityFactor = $estimationData['communication']['factor'];
        $this->communicationComplexityImpact = $estimationData['communication']['impact'];
        $this->communicationComplexityLevel = $estimationData['communication']['level'];
        $this->exceedsScrumTeamSize = $estimationData['communication']['exceeds_baseline'];
        
        // Calculate impact percentages and days
        if ($this->baseMostLikelyTime > 0) {
            // Communication impact
            $this->communicationImpactPercentage = (($this->commMostLikelyTime - $this->baseMostLikelyTime) / $this->baseMostLikelyTime) * 100;
            $this->communicationImpactDays = $this->commMostLikelyTime - $this->baseMostLikelyTime;
            
            // GSD-only impact
            $this->gsdOnlyImpactDays = $this->mostLikelyTime - $this->commMostLikelyTime;
            $this->gsdOnlyImpactPercentage = ($this->commMostLikelyTime > 0) ? 
                (($this->mostLikelyTime - $this->commMostLikelyTime) / $this->commMostLikelyTime) * 100 : 0;
            
            // PERT impact
            $this->pertImpactDays = $this->expectedTime - $this->mostLikelyTime;
            $this->pertImpactPercentage = ($this->mostLikelyTime > 0) ?
                (($this->expectedTime - $this->mostLikelyTime) / $this->mostLikelyTime) * 100 : 0;
            
            // Total GSD impact
            $this->gsdImpactPercentage = (($this->expectedTime - $this->baseMostLikelyTime) / $this->baseMostLikelyTime) * 100;
            $this->gsdImpactDays = $this->expectedTime - $this->baseMostLikelyTime;
        } else {
            $this->resetImpactValues();
        }
    }
    
    /**
     * Reset impact values when base time is zero or not available
     */
    private function resetImpactValues()
    {
        $this->communicationImpactPercentage = 0;
        $this->communicationImpactDays = 0;
        $this->gsdOnlyImpactDays = 0;
        $this->gsdOnlyImpactPercentage = 0;
        $this->pertImpactDays = 0;
        $this->pertImpactPercentage = 0;
        $this->gsdImpactPercentage = 0;
        $this->gsdImpactDays = 0;
    }

    /**
     * Format all display values after calculation
     */
    private function formatDisplayValues()
    {
        // Format day-based values for base estimates
        $this->formattedBaseOptimisticTime = number_format($this->baseOptimisticTime);
        $this->formattedBaseMostLikelyTime = number_format($this->baseMostLikelyTime);
        $this->formattedBasePessimisticTime = number_format($this->basePessimisticTime);
        $this->formattedBaseExpectedTime = number_format($this->baseExpectedTime);
        
        $this->formattedCommMostLikelyTime = number_format($this->commMostLikelyTime);
        // Format day-based values for final estimates
        $this->formattedOptimisticTime = number_format($this->optimisticTime);
        $this->formattedMostLikelyTime = number_format($this->mostLikelyTime);
        $this->formattedPessimisticTime = number_format($this->pessimisticTime);
        $this->formattedExpectedTime = number_format($this->expectedTime);
        
        // Format impact values
        $this->formattedCommunicationImpactDays = number_format(abs($this->communicationImpactDays));
        $this->formattedGsdOnlyImpactDays = number_format(abs($this->gsdOnlyImpactDays));
        
        // Calculate confidence intervals
        $confidenceInterval68Low = $this->expectedTime - $this->standardDeviation;
        $confidenceInterval68High = $this->expectedTime + $this->standardDeviation;
        $confidenceInterval95Low = $this->expectedTime - (2 * $this->standardDeviation);
        $confidenceInterval95High = $this->expectedTime + (2 * $this->standardDeviation);
        
        $this->confidenceInterval68Low = number_format($confidenceInterval68Low);
        $this->confidenceInterval68High = number_format($confidenceInterval68High);
        $this->confidenceInterval95Low = number_format($confidenceInterval95Low);
        $this->confidenceInterval95High = number_format($confidenceInterval95High);

        // Calculate sprint-based values
        $sprintLength = max(1, $this->smSprintLength);
        
        // Sprint-based values for base estimates
        $this->sprintBaseOptimisticTime = number_format($this->baseOptimisticTime / $sprintLength);
        $this->sprintBaseMostLikelyTime = number_format($this->baseMostLikelyTime / $sprintLength);
        $this->sprintBasePessimisticTime = number_format($this->basePessimisticTime / $sprintLength);
        $this->sprintBaseExpectedTime = number_format($this->baseExpectedTime / $sprintLength);
        
        $this->sprintCommMostLikelyTime = number_format($this->commMostLikelyTime / $sprintLength);
        // Sprint-based values for final estimates
        $this->sprintOptimisticTime = number_format($this->optimisticTime / $sprintLength);
        $this->sprintMostLikelyTime = number_format($this->mostLikelyTime / $sprintLength);
        $this->sprintPessimisticTime = number_format($this->pessimisticTime / $sprintLength);
        $this->sprintExpectedTime = number_format($this->expectedTime / $sprintLength);
        
        // Sprint-based confidence intervals
        $this->sprintConfidenceInterval68Low = number_format($confidenceInterval68Low / $sprintLength);
        $this->sprintConfidenceInterval68High = number_format($confidenceInterval68High / $sprintLength);
        $this->sprintConfidenceInterval95Low = number_format($confidenceInterval95Low / $sprintLength);
        $this->sprintConfidenceInterval95High = number_format($confidenceInterval95High / $sprintLength);
    }

    /**
     * Calculate communication complexity based on team size
     * This is for backward compatibility, now using the service for calculations
     */
    private function calculateCommunicationComplexity()
    {
        $commData = $this->gsdService->calculateCommunicationComplexity($this->smEmployee);
        
        $this->communicationPath = $commData['channels'];
        $this->communicationComplexityFactor = $commData['factor'];
        $this->communicationComplexityImpact = $commData['impact'];
        $this->communicationComplexityLevel = $commData['level'];
        $this->exceedsScrumTeamSize = $commData['exceeds_baseline'];
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
