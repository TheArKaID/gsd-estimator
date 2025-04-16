<?php

namespace App\Services;

use App\Models\Project;

class GsdEstimationService
{
    /**
     * Calculate complete GSD estimation for a project
     * 
     * @param Project $project The project model with necessary relationships loaded
     * @return array Calculated estimation data
     */
    public function calculateProjectEstimation(Project $project)
    {
        // Ensure we have the required relationships
        if (!$project->relationLoaded('storyPoints')) {
            $project->load('storyPoints');
        }

        if (!$project->relationLoaded('projectGlobalFactors')) {
            $project->load(['projectGlobalFactors', 'projectGlobalFactors.globalFactorCriteria']);
        }

        // Get project parameters
        $projectType = $project->project_type ?? 'organic';
        $projectClarity = $project->project_clarity ?? 'evolving';
        $teamSize = $project->team_size ?? 0;
        $velocity = $project->velocity ?? 0;
        $sprintLength = $project->sprint_length ?? 14;

        // Calculate project type multiplier
        $typeMultiplier = $this->getProjectTypeMultiplier($projectType);

        // Get clarity percentage ranges
        $clarityRanges = $this->getClarityRanges($projectClarity);
        $optimisticPercentage = $clarityRanges['optimistic'];
        $pessimisticPercentage = $clarityRanges['pessimistic'];

        // Calculate communication complexity
        $communicationData = $this->calculateCommunicationComplexity($teamSize);

        // Calculate GSD adjustment factor from global factors
        $gsdAdjustmentFactor = $this->calculateGsdAdjustmentFactor($project);

        // Calculate total story points
        $totalStoryPoints = collect($project->storyPoints)->sum('value');
        $adjustedStoryPoints = round($totalStoryPoints * $typeMultiplier);

        // Base time calculation
        $velocity = max(1, $velocity);
        $sprintLength = max(1, $sprintLength);
        $baseTime = ($adjustedStoryPoints / $velocity);
        $baseTimeInDays = $baseTime * $sprintLength;

        // Base estimates (no factors applied)
        $baseMostLikelyTime = $baseTimeInDays;
        $baseOptimisticTime = $baseMostLikelyTime * (1 + ($optimisticPercentage / 100));
        $basePessimisticTime = $baseMostLikelyTime * (1 + ($pessimisticPercentage / 100));

        // Apply communication complexity
        $commMostLikelyTime = $baseMostLikelyTime * $communicationData['factor'];
        $commOptimisticTime = $baseOptimisticTime * $communicationData['factor'];
        $commPessimisticTime = $basePessimisticTime * $communicationData['factor'];

        // Apply GSD factors
        $mostLikelyTime = $commMostLikelyTime * $gsdAdjustmentFactor;
        $optimisticTime = $commOptimisticTime * $gsdAdjustmentFactor;
        $pessimisticTime = $commPessimisticTime * $gsdAdjustmentFactor;

        // Calculate expected time using PERT formula
        $expectedTime = ($optimisticTime + (4 * $mostLikelyTime) + $pessimisticTime) / 6;
        
        // Standard deviation for confidence intervals
        $standardDeviation = ($pessimisticTime - $optimisticTime) / 6;

        // Return complete estimation data
        return [
            'project_type' => $projectType,
            'type_multiplier' => $typeMultiplier,
            'project_clarity' => $projectClarity,
            'clarity_ranges' => $clarityRanges,
            'communication' => $communicationData,
            'gsd_factor' => $gsdAdjustmentFactor,
            'total_story_points' => $totalStoryPoints,
            'adjusted_story_points' => $adjustedStoryPoints,
            'base' => [
                'optimistic' => $baseOptimisticTime,
                'most_likely' => $baseMostLikelyTime,
                'pessimistic' => $basePessimisticTime,
                'expected' => $baseMostLikelyTime, // No PERT applied
            ],
            'with_communication' => [
                'optimistic' => $commOptimisticTime,
                'most_likely' => $commMostLikelyTime,
                'pessimistic' => $commPessimisticTime,
                'expected' => $commMostLikelyTime, // No PERT applied
            ],
            'final' => [
                'optimistic' => $optimisticTime,
                'most_likely' => $mostLikelyTime,
                'pessimistic' => $pessimisticTime,
                'expected' => $expectedTime,
                'standard_deviation' => $standardDeviation,
                'confidence_68' => [
                    'low' => $expectedTime - $standardDeviation,
                    'high' => $expectedTime + $standardDeviation,
                ],
                'confidence_95' => [
                    'low' => $expectedTime - (2 * $standardDeviation),
                    'high' => $expectedTime + (2 * $standardDeviation),
                ],
            ],
            'sprint_length' => $sprintLength,
            'velocity' => $velocity,
        ];
    }

    /**
     * Get project type multiplier
     */
    public function getProjectTypeMultiplier($type)
    {
        $multipliers = [
            'organic' => 1.0,
            'semi-detached' => 1.25,
            'embedded' => 1.5
        ];
        
        return $multipliers[$type] ?? 1.0;
    }

    /**
     * Get clarity ranges for a project clarity level
     */
    public function getClarityRanges($clarity)
    {
        $ranges = [
            'conceptual' => ['optimistic' => -25, 'pessimistic' => 75, 'summary' => 'Range: -25% to +75%'],
            'evolving' => ['optimistic' => -10, 'pessimistic' => 25, 'summary' => 'Range: -10% to +25%'],
            'established' => ['optimistic' => -5, 'pessimistic' => 10, 'summary' => 'Range: -5% to +10%']
        ];

        return $ranges[$clarity] ?? $ranges['evolving'];
    }

    /**
     * Calculate communication complexity based on team size
     */
    public function calculateCommunicationComplexity($teamSize)
    {
        $teamSize = max(1, $teamSize);
        $baselineTeamSize = 10;
        $baselineChannels = 45; // 10-person team: 10(10-1)/2 = 45 communication path
        
        // Calculate communication path using the formula: n(n-1)/2
        $channels = ($teamSize * ($teamSize - 1)) / 2;
        
        $exceedsBaseline = $teamSize > $baselineTeamSize;
        $complexityFactor = 1.0;
        $impact = 0;
        $level = 'Standard';
        
        if ($exceedsBaseline) {
            // Calculate percentage increase from baseline
            $increasePercentage = (($channels - $baselineChannels) / $baselineChannels) * 100;
            $impact = round($increasePercentage);
            
            // Convert to multiplier: 25% increase = 1.25x multiplier
            $complexityFactor = 1 + ($increasePercentage / 100);
            
            // Set complexity level based on percentage increase
            if ($increasePercentage <= 50) {
                $level = 'Elevated';
            } elseif ($increasePercentage <= 100) {
                $level = 'High';
            } else {
                $level = 'Very High';
            }
        }
        
        return [
            'team_size' => $teamSize,
            'channels' => $channels,
            'exceeds_baseline' => $exceedsBaseline,
            'factor' => $complexityFactor,
            'impact' => $impact,
            'level' => $level
        ];
    }
    
    /**
     * Calculate GSD adjustment factor from selected global factors
     */
    public function calculateGsdAdjustmentFactor($project)
    {
        $result = 1.0;
        
        // Get the project's global factors with their criteria
        $selectedFactors = $project->projectGlobalFactors;
        
        if ($selectedFactors->isEmpty()) {
            return $result;
        }

        foreach ($selectedFactors as $factor) {
            // Get the criteria selected for this factor
            $criteriaValue = $factor->globalFactorCriteria->value ?? 1.0;

            // Multiply to get the cumulative effect
            $result *= $criteriaValue;
        }
        
        return $result;
    }
    
    /**
     * Calculate accuracy of an estimate compared to actual effort
     */
    public function calculateAccuracy($estimate, $actual)
    {
        if (!$estimate || !$actual) {
            return ['percent' => null, 'type' => null];
        }
        
        // Calculate absolute difference between estimate and actual
        $difference = abs($estimate - $actual);
        
        // Calculate percentage difference
        $percentDifference = round(100 - ($difference / $actual) * 100, 2);
        
        // Determine if estimate was over, under, or exact
        $type = 'exact';
        if ($estimate < $actual) {
            $type = 'under';
        } elseif ($estimate > $actual) {
            $type = 'over';
        }
        
        return ['percent' => $percentDifference, 'type' => $type];
    }
    
    /**
     * Compare two estimates against actual effort to determine which is better
     */
    public function compareEstimations($gsdEstimate, $previousEstimate, $actualEffort)
    {
        // If we don't have both estimates or actual effort, we can't compare
        if (!$gsdEstimate || !$previousEstimate || !$actualEffort) {
            return [
                'difference_percent' => null,
                'gsd_is_better' => null
            ];
        }
        
        // Calculate the difference between the estimates
        $difference = abs($gsdEstimate - $previousEstimate);
        
        // Calculate percentage difference between estimates
        $percentDifference = round(($difference / $previousEstimate) * 100, 2);
        
        // Calculate accuracy error for both estimation methods
        $gsdError = abs($gsdEstimate - $actualEffort);
        $previousError = abs($previousEstimate - $actualEffort);
        
        // Determine which estimation is closer to actual
        // true = GSD is better, false = Previous is better, null = equal
        $gsdIsBetter = null;
        
        if ($gsdError < $previousError) {
            $gsdIsBetter = true;
        } elseif ($previousError < $gsdError) {
            $gsdIsBetter = false;
        }
        
        return [
            'difference_percent' => $percentDifference,
            'gsd_is_better' => $gsdIsBetter
        ];
    }
}
