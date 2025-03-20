<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Project Details</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="/" wire:navigate>Dashboard</a></div>
                <div class="breadcrumb-item">{{ $project->name }}</div>
            </div>
        </div>
        <div class="section-body">
            <div class="card card-body" wire:loading.class='opacity-50'>
                <div class="row mt-4">
                    <div class="col-12 col-lg-8 offset-lg-2">
                        <div class="wizard-steps">
                            <div class="wizard-step wizard-step-active" id="gsd-parameters-tab" onclick="showTab('gsd-parameters')" wire:ignore.self>
                                <div class="wizard-step-icon">
                                    <i class="fas fa-list-alt"></i>
                                </div>
                                <div class="wizard-step-label">
                                    GSD Parameters
                                </div>
                            </div>
                            <div class="wizard-step" id="project-type-tab" onclick="showTab('project-type')" wire:ignore.self>
                                <div class="wizard-step-icon">
                                    <i class="fas fa-box-open"></i>
                                </div>
                                <div class="wizard-step-label">
                                    Project Type
                                </div>
                            </div>
                            <div class="wizard-step" id="software-metrics-tab" onclick="showTab('software-metrics')" wire:ignore.self>
                                <div class="wizard-step-icon">
                                    <i class="fas fa-tasks"></i>
                                </div>
                                <div class="wizard-step-label">
                                    Software Metrics
                                </div>
                            </div>
                            <div class="wizard-step" id="summary-tab" onclick="showTab('summary')" wire:ignore.self>
                                <div class="wizard-step-icon">
                                    <i class="fas fa-poll-h"></i>
                                </div>
                                <div class="wizard-step-label">
                                    Summary
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="gsd-parameters" role="tabpanel" aria-labelledby="gsd-parameters-tab" wire:ignore.self>
                        <form class="wizard-content mt-2" wire:submit.prevent="saveGsdParameters">
                            <div class="wizard-pane">
                                <div class="form-group row align-items-center justify-content-center">
                                    <div class="col-8">
                                        <div class="btn-group btn-group-toggle d-flex flex-wrap" data-toggle="buttons">
                                            @foreach ($globalFactors as $g)
                                                <label class="btn btn-outline-primary m-1 {{ in_array($g->id, $projectGlobalFactors) ? 'active' : '' }}">
                                                    <input type="checkbox" value="{{ $g->id }}" wire:click='saveGsdParameters' wire:model="projectGlobalFactors"> {{ $g->name }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div id="gsd-options-container" class="mt-4">
                                    @foreach ($projectGlobalFactors as $parameter)
                                        <div class="accordion" id="accordion-{{ $parameter }}">
                                            <div class="card">
                                                <div class="card-header" id="heading-{{ $parameter }}">
                                                    <div class="accordion-header collapsed" role="button" data-toggle="collapse" data-target="#collapse-{{ $parameter }}" aria-expanded="false" aria-controls="collapse-{{ $parameter }}" wire:ignore.self>
                                                        <h4 style="color: inherit;">
                                                            {{ ucfirst($projectGlobalFactorModels->where('id', $parameter)->first()?->name) }}
                                                        </h4>
                                                    </div>
                                                </div>
                                                <div id="collapse-{{ $parameter }}" class="collapse-body collapse" aria-labelledby="heading-{{ $parameter }}" data-parent="#accordion-{{ $parameter }}" wire:ignore.self>
                                                    <div class="card-body">
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered table-md">
                                                                <tr>
                                                                    <th width="10%">#</th>
                                                                    <th>Name</th>
                                                                    <th>Description</th>
                                                                    <th>Value</th>
                                                                </tr>
                                                                @foreach ($projectGlobalFactorModels->where('id', $parameter)->first()?->criterias as $criteria)
                                                                    <tr>
                                                                        <td>
                                                                            <div class="btn-group-toggle" data-toggle="buttons">
                                                                                <label class="btn btn-outline-primary {{ $this->project->projectGlobalFactors->where('global_factor_id', $parameter)->where('project_id', $project->id)->first()?->global_factor_criteria_id == $criteria->id ? 'active' : '' }}">
                                                                                    <input 
                                                                                        type="radio" 
                                                                                        name="criteria_{{ $parameter }}" 
                                                                                        value="{{ $criteria->id }}" 
                                                                                        wire:click="selectCriteria('{{ $parameter }}', '{{ $criteria->id }}')"
                                                                                        {{ $this->project->projectGlobalFactors->where('global_factor_id', $parameter)->where('project_id', $project->id)->first()?->global_factor_criteria_id == $criteria->id ? 'checked' : '' }}
                                                                                        style="position: absolute; opacity: 0;"
                                                                                    > Select
                                                                                </label>
                                                                            </div>
                                                                        </td>
                                                                        <td>{{ $criteria->name }}</td>
                                                                        <td>{{ $criteria->description }}</td>
                                                                        <td>{{ $criteria->value }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                    @endforeach
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="project-type" role="tabpanel" aria-labelledby="project-type-tab" wire:ignore.self>
                        <form class="wizard-content mt-2">
                            <div class="wizard-pane">
                                <div class="form-group row align-items-center">
                                    <label class="col-md-4 text-md-right text-left">Project Type</label>
                                    <div class="col-lg-4 col-md-6">
                                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                            <label class="btn btn-outline-primary" wire:ignore.self>
                                                <input type="radio" wire:model='projectType' wire:change='saveProjectType' value="organic" :class="{ 'focus active': $wire.projectType === 'organic' }" autocomplete="off"> Organic
                                            </label>
                                            <label class="btn btn-outline-primary" wire:ignore.self>
                                                <input type="radio" wire:model='projectType' wire:change='saveProjectType' value="semi-detached" :class="{ 'focus active': $wire.projectType === 'semi-detached' }" autocomplete="off"> Semi-Detached
                                            </label>
                                            <label class="btn btn-outline-primary" wire:ignore.self>
                                                <input type="radio" wire:model='projectType' wire:change='saveProjectType' value="embedded" :class="{ 'focus active': $wire.projectType === 'embedded' }" autocomplete="off"> Embedded
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row align-items-center">
                                    <div class="col-md-4 text-md-right text-left"></div>
                                    <div class="col-lg-4 col-md-6">
                                        <div id="project-type-description" class="mt-2">
                                            <p><strong>Organic:</strong> Small teams with good experience working on less rigid requirements.</p>
                                            <p><strong>Semi-Detached:</strong> Medium teams with mixed experience working on more complex requirements.</p>
                                            <p><strong>Embedded:</strong> Large teams working on projects with strict requirements and constraints.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="software-metrics" role="tabpanel" aria-labelledby="software-metrics-tab" wire:ignore.self>
                        <form class="wizard-content mt-2" wire:submit.prevent="saveSoftwareMetrics">
                            <div class="wizard-pane">
                                <div class="row justify-content-center">
                                    <div class="col-md-8">
                                        <div class="card">
                                            <div class="card-header">
                                                <h4>Team Information</h4>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-group row mb-4">
                                                    <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Team Size</label>
                                                    <div class="col-sm-12 col-md-7">
                                                        <input type="number" class="form-control" wire:model="smEmployee" min="1">
                                                        @error('smEmployee') <span class="text-danger">{{ $message }}</span> @enderror
                                                        <small class="form-text text-muted">Enter the number of team members working on this project</small>
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Team Velocity</label>
                                                    <div class="col-sm-12 col-md-7">
                                                        <input type="number" class="form-control" wire:model="smVelocity" min="1">
                                                        @error('smVelocity') <span class="text-danger">{{ $message }}</span> @enderror
                                                        <small class="form-text text-muted">Enter the average story points completed per sprint/iteration</small>
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3"></label>
                                                    <div class="col-sm-12 col-md-7">
                                                        <button type="submit" class="btn btn-primary">Save Team Information</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <form class="wizard-content mt-2">
                            <div class="wizard-pane">
                                <div class="row justify-content-center">
                                    <div class="col-md-8">
                                        <div class="row">
                                            <div class="card-header">
                                                <h4>Story Point</h4>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="story_point_name">Story Point Name</label>
                                                    <input id="story_point_name" type="text" wire:model='spName' class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="points">Points</label>
                                                    <div class="btn-group btn-group-toggle d-flex flex-wrap" data-toggle="buttons">
                                                        <label class="btn btn-outline-primary m-1 {{ $spValue == 1 ? 'active' : '' }}">
                                                            <input type="radio" value="1" autocomplete="off" wire:model="spValue" wire:click="$set('spValue', 1)"> 1
                                                        </label>
                                                        <label class="btn btn-outline-primary m-1 {{ $spValue == 2 ? 'active' : '' }}">
                                                            <input type="radio" value="2" autocomplete="off" wire:model="spValue" wire:click="$set('spValue', 2)"> 2
                                                        </label>
                                                        <label class="btn btn-outline-primary m-1 {{ $spValue == 3 ? 'active' : '' }}">
                                                            <input type="radio" value="3" autocomplete="off" wire:model="spValue" wire:click="$set('spValue', 3)"> 3
                                                        </label>
                                                        <label class="btn btn-outline-primary m-1 {{ $spValue == 5 ? 'active' : '' }}">
                                                            <input type="radio" value="5" autocomplete="off" wire:model="spValue" wire:click="$set('spValue', 5)"> 5
                                                        </label>
                                                        <label class="btn btn-outline-primary m-1 {{ $spValue == 8 ? 'active' : '' }}">
                                                            <input type="radio" value="8" autocomplete="off" wire:model="spValue" wire:click="$set('spValue', 8)"> 8
                                                        </label>
                                                        <label class="btn btn-outline-primary m-1 {{ $spValue == 13 ? 'active' : '' }}">
                                                            <input type="radio" value="13" autocomplete="off" wire:model="spValue" wire:click="$set('spValue', 13)"> 13
                                                        </label>
                                                        <label class="btn btn-outline-primary m-1 {{ $spValue == 21 ? 'active' : '' }}">
                                                            <input type="radio" value="21" autocomplete="off" wire:model="spValue" wire:click="$set('spValue', 21)"> 21
                                                        </label>
                                                        <label class="btn btn-outline-primary m-1 {{ $spValue == 34 ? 'active' : '' }}">
                                                            <input type="radio" value="34" autocomplete="off" wire:model="spValue" wire:click="$set('spValue', 34)"> 34
                                                        </label>
                                                        <label class="btn btn-outline-primary m-1 {{ $spValue == 55 ? 'active' : '' }}">
                                                            <input type="radio" value="55" autocomplete="off" wire:model="spValue" wire:click="$set('spValue', 55)"> 55
                                                        </label>
                                                        <label class="btn btn-outline-primary m-1 {{ $spValue == 89 ? 'active' : '' }}">
                                                            <input type="radio" value="89" autocomplete="off" wire:model="spValue" wire:click="$set('spValue', 89)"> 89
                                                        </label>
                                                        <label class="btn btn-outline-primary m-1 {{ $spValue == 144 ? 'active' : '' }}">
                                                            <input type="radio" value="144" autocomplete="off" wire:model="spValue" wire:click="$set('spValue', 144)"> 144
                                                        </label>
                                                        <label class="btn btn-outline-primary m-1 {{ $spValue == 'custom' ? 'active' : '' }}">
                                                            <input type="radio" value="custom" autocomplete="off" wire:model="spValue" wire:click="$set('spValue', 'custom')"> Custom
                                                        </label>
                                                    </div>
                                                    @if($spValue === 'custom')
                                                        <input type="number" wire:model="customSpValue" class="form-control mt-2" placeholder="Enter custom points">
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="description">Description (Optional)</label>
                                                    <textarea id="description" wire:model='spDescription' class="form-control" style="height: 120px"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 text-right">
                                        {{-- Save Button --}}
                                        <div class="form-group">
                                            <button type="button" class="btn btn-primary" wire:click='saveStoryPoint'>Add Story Point</button>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="mt-4">
                                            <h5>Added Story Points</h5>
                                            <ul class="list-group" id="story-point-list">
                                                @forelse ($project->storyPoints as $sp)
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <span>
                                                            <strong>Story Point Name:</strong> {{ $sp->name }}<br>
                                                            <strong>Description:</strong> {{ $sp->description }}<br>
                                                            <strong>Points:</strong> {{ $sp->value }}<br>
                                                        </span>
                                                        <button class="btn btn-danger btn-sm" wire:click='deleteStoryPoint("{{ $sp->id }}")'>Remove</button>
                                                    </li>
                                                @empty
                                                    <li class="list-group-item">No story points added yet.</li>
                                                @endforelse
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="summary" role="tabpanel" aria-labelledby="summary-tab" wire:ignore.self>
                        <div class="wizard-content mt-2">
                            <div class="wizard-pane">
                                <div class="row justify-content-center">
                                    <div class="col-md-8">
                                        <div class="card">
                                            <div class="card-header">
                                                <h4>Project Summary</h4>
                                            </div>
                                            <div class="card-body">
                                                <div class="summary-section">
                                                    <h5>Project Details</h5>
                                                    <table class="table">
                                                        <tr>
                                                            <td width="30%"><strong>Project Name</strong></td>
                                                            <td>{{ $project->name }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Project Type</strong></td>
                                                            <td>{{ ucfirst($project->project_type) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Team Size</strong></td>
                                                            <td>{{ $smEmployee }} members</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Team Velocity</strong></td>
                                                            <td>{{ $smVelocity }} points per iteration</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Total Story Points</strong></td>
                                                            <td>{{ $totalStoryPoints }} points</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                                
                                                <div class="summary-section mt-4">
                                                    <h5>Selected Global Factors</h5>
                                                    <ul class="list-group">
                                                        @forelse ($projectGlobalFactorModels as $factor)
                                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                {{ $factor->name }}
                                                                <span class="badge badge-primary badge-pill">
                                                                    @php
                                                                        $criteriaId = $project->projectGlobalFactors
                                                                            ->where('global_factor_id', $factor->id)
                                                                            ->first()?->global_factor_criteria_id;
                                                                        $criteriaValue = $factor->criterias
                                                                            ->where('id', $criteriaId)
                                                                            ->first()?->value ?? '0';
                                                                        $criteriaName = $factor->criterias
                                                                            ->where('id', $criteriaId)
                                                                            ->first()?->name ?? 'Not selected';
                                                                    @endphp
                                                                    {{ $criteriaName }} ({{ $criteriaValue }})
                                                                </span>
                                                            </li>
                                                        @empty
                                                            <li class="list-group-item">No global factors selected</li>
                                                        @endforelse
                                                    </ul>
                                                </div>
                                                
                                                <div class="summary-section mt-4">
                                                    <h5>Effort Estimation (PERT Analysis with COCOMO Parameters)</h5>
                                                    <div class="card bg-light mb-3">
                                                        <div class="card-body">
                                                            <p class="mb-0">
                                                                <strong>Estimation Method:</strong> This analysis combines PERT (Program Evaluation and Review Technique) with COCOMO I parameters based on your selected project type ({{ ucfirst($project->project_type) }}). The estimates account for project complexity, team velocity, and selected global factors.
                                                            </p>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Base Estimates (without GSD factors) -->
                                                    <div class="card mb-4">
                                                        <div class="card-header bg-secondary text-white">
                                                            <h5 class="mb-0">Base Estimates (without GSD Factors)</h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <div class="border rounded p-3 text-center">
                                                                        <h6>Optimistic Time</h6>
                                                                        <h3>{{ number_format($baseOptimisticTime, 1) }} weeks</h3>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="border rounded p-3 text-center">
                                                                        <h6>Most Likely Time</h6>
                                                                        <h3>{{ number_format($baseMostLikelyTime, 1) }} weeks</h3>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="border rounded p-3 text-center">
                                                                        <h6>Pessimistic Time</h6>
                                                                        <h3>{{ number_format($basePessimisticTime, 1) }} weeks</h3>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="border rounded p-3 text-center mt-3">
                                                                <h6>Expected Project Duration (without GSD Factors)</h6>
                                                                <h3>{{ number_format($baseExpectedTime, 1) }} weeks</h3>
                                                                <small>Based on PERT formula: (O + 4M + P) / 6</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Final Estimates (with GSD factors) -->
                                                    <div class="card mb-4">
                                                        <div class="card-header bg-primary text-white">
                                                            <h5 class="mb-0">Final Estimates (with GSD Factors)</h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <div class="card bg-success text-white">
                                                                        <div class="card-body text-center">
                                                                            <h6>Optimistic Time</h6>
                                                                            <h2>{{ number_format($optimisticTime, 1) }} weeks</h2>
                                                                            <small>Best case scenario</small>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="card bg-primary text-white">
                                                                        <div class="card-body text-center">
                                                                            <h6>Most Likely Time</h6>
                                                                            <h2>{{ number_format($mostLikelyTime, 1) }} weeks</h2>
                                                                            <small>Expected scenario</small>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="card bg-warning">
                                                                        <div class="card-body text-center">
                                                                            <h6>Pessimistic Time</h6>
                                                                            <h2>{{ number_format($pessimisticTime, 1) }} weeks</h2>
                                                                            <small>Worst case scenario</small>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="card bg-info text-white mt-3">
                                                                <div class="card-body">
                                                                    <div class="row">
                                                                        <div class="col-md-8">
                                                                            <h5 class="mb-0">Expected Project Duration</h5>
                                                                            <p class="mb-0">Based on PERT formula: (O + 4M + P) / 6</p>
                                                                        </div>
                                                                        <div class="col-md-4 text-right">
                                                                            <h3 class="mb-0">{{ number_format($expectedTime, 1) }} weeks</h3>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="card-footer bg-info">
                                                                    <div class="row">
                                                                        <div class="col-md-8">
                                                                            <p class="mb-0">Standard Deviation</p>
                                                                        </div>
                                                                        <div class="col-md-4 text-right">
                                                                            <p class="mb-0">± {{ number_format($standardDeviation, 1) }} weeks</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- GSD Impact Analysis -->
                                                    <div class="card mb-4">
                                                        <div class="card-header bg-dark text-white">
                                                            <h5 class="mb-0">GSD Impact Analysis</h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row align-items-center">
                                                                <div class="col-md-6">
                                                                    <h4 class="text-center mb-4">Impact on Project Duration</h4>
                                                                    <div class="d-flex justify-content-around align-items-center">
                                                                        <div class="text-center">
                                                                            <h5>Without GSD</h5>
                                                                            <h3>{{ number_format($baseExpectedTime, 1) }} weeks</h3>
                                                                        </div>
                                                                        <div class="text-center">
                                                                            <i class="fas fa-arrow-right fa-2x"></i>
                                                                        </div>
                                                                        <div class="text-center">
                                                                            <h5>With GSD</h5>
                                                                            <h3>{{ number_format($expectedTime, 1) }} weeks</h3>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="text-center p-4 {{ $gsdImpactPercentage > 0 ? 'bg-warning' : 'bg-success' }} rounded">
                                                                        <h5>GSD Impact</h5>
                                                                        <h2>
                                                                            {{ $gsdImpactPercentage > 0 ? '+' : '' }}{{ number_format($gsdImpactPercentage, 1) }}%
                                                                        </h2>
                                                                        <p class="mb-0">
                                                                            {{ abs(number_format($expectedTime - $baseExpectedTime, 1)) }} weeks {{ $gsdImpactPercentage > 0 ? 'increase' : 'decrease' }}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mt-3">
                                                        <p><strong>COCOMO I Parameters for {{ ucfirst($project->project_type) }} Projects:</strong></p>
                                                        <table class="table table-bordered">
                                                            <tr>
                                                                <th>Project Type</th>
                                                                <th>Coefficient (a)</th>
                                                                <th>Exponent (b)</th>
                                                                <th>Formula</th>
                                                            </tr>
                                                            <tr>
                                                                <td>{{ ucfirst($project->project_type) }}</td>
                                                                <td>{{ $projectTypeParam['coefficient'] }}</td>
                                                                <td>{{ $projectTypeParam['exponent'] }}</td>
                                                                <td>E = {{ $projectTypeParam['coefficient'] }} × (Size)<sup>{{ $projectTypeParam['exponent'] }}</sup></td>
                                                            </tr>
                                                        </table>
                                                        <small class="text-muted">Note: These baseline parameters were derived from Barry Boehm's COCOMO I model and adapted for story point-based estimation.</small>
                                                    </div>
                                                    
                                                    <div class="mt-3">
                                                        <p><strong>Estimation Adjustments for {{ ucfirst($project->project_type) }} Projects:</strong></p>
                                                        <table class="table table-bordered">
                                                            <tr>
                                                                <th>Scenario</th>
                                                                <th>COCOMO Base Value</th>
                                                                <th>Percentage Adjustment</th>
                                                                <th>Calculation</th>
                                                            </tr>
                                                            <tr>
                                                                <td>Optimistic (Best Case)</td>
                                                                <td>{{ $projectTypeParam['coefficient'] }}</td>
                                                                <td>{{ $optimisticPercentage }}%</td>
                                                                <td>Nominal × (1 + {{ $optimisticPercentage }}%)</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Most Likely (Nominal)</td>
                                                                <td>{{ $projectTypeParam['coefficient'] }}</td>
                                                                <td>0%</td>
                                                                <td>Nominal</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Pessimistic (Worst Case)</td>
                                                                <td>{{ $projectTypeParam['coefficient'] }}</td>
                                                                <td>+{{ $pessimisticPercentage }}%</td>
                                                                <td>Nominal × (1 + {{ $pessimisticPercentage }}%)</td>
                                                            </tr>
                                                        </table>
                                                        <small class="text-muted">Note: These estimates use Barry Boehm's COCOMO I coefficients as the baseline, with percentage adjustments for optimistic and pessimistic scenarios.</small>
                                                    </div>
                                                    
                                                    <div class="mt-3">
                                                        <p><strong>Confidence Intervals:</strong></p>
                                                        <table class="table table-bordered">
                                                            <tr>
                                                                <th>Confidence Level</th>
                                                                <th>Project Duration Range</th>
                                                            </tr>
                                                            <tr>
                                                                <td>68% Confidence (±1σ)</td>
                                                                <td>
                                                                    {{ number_format($expectedTime - $standardDeviation, 1) }} to 
                                                                    {{ number_format($expectedTime + $standardDeviation, 1) }} weeks
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>95% Confidence (±2σ)</td>
                                                                <td>
                                                                    {{ number_format($expectedTime - (2 * $standardDeviation), 1) }} to 
                                                                    {{ number_format($expectedTime + (2 * $standardDeviation), 1) }} weeks
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="text-right mt-3">
                                            <button class="btn btn-primary" onclick="window.print()">
                                                <i class="fas fa-print"></i> Print Summary
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

@push('styles')
    <style>
        .wizard-step {
            background-color: #e0e0e0;
            transition: background-color 0.3s ease-in-out;
            cursor: pointer;
            transform: scale(1.05);
        }
        .opacity-50 {
            opacity: 0.5;
        }
        .btn-group-toggle .btn.active {
            background-color: #007bff;
            color: white;
        }
        .btn-outline-primary.active {
            background-color: #007bff;
            color: white;
        }
        
        /* Print styles */
        @media print {
            .wizard-steps, .section-header, .section-header-breadcrumb, .btn {
                display: none !important;
            }
            
            .card {
                border: none !important;
                box-shadow: none !important;
            }
            
            .summary-section {
                page-break-inside: avoid;
            }
        }
    </style>
@endpush

@push('scripts')
<script>
    function showTab(tabId) {
        // Hide all tab panes
        document.querySelectorAll('.tab-pane').forEach(function(tabPane) {
            tabPane.classList.remove('show', 'active');
        });

        // Remove active class from all wizard steps
        document.querySelectorAll('.wizard-step').forEach(function(step) {
            step.classList.remove('wizard-step-active');
        });

        // Show the selected tab pane
        document.getElementById(tabId).classList.add('show', 'active');

        // Add active class to the selected wizard step
        document.getElementById(tabId + '-tab').classList.add('wizard-step-active');
    }

    function removeStoryPoint(button) {
        const listItem = button.closest('li');
        listItem.remove();
    }

    document.addEventListener('livewire:load', function () {
        @this.set('activeTab', 'story-point');
    });

    // document.addEventListener('livewire:update', function () {
    //     // Reapply active class to GSD Parameters after Livewire update
    //     @this.projectGlobalFactors.forEach(function(factor) {
    //         document.querySelector(`input[value="${factor}"]`).closest('label').classList.add('active');
    //     });
    // });
</script>
@endpush

@script
<script>
    window.addEventListener('livewire:navigated', function() {
        $wire.on('story-point-added', function () {
            iziToast.success({
                title: 'Success',
                message: 'Story point added successfully.',
                position: 'topRight'
            });
        });

        $wire.on('story-point-deleted', function () {
            iziToast.success({
                title: 'Success',
                message: 'Story point deleted successfully.',
                position: 'topRight'
            });
        });

        $wire.on('software-metrics-saved', function () {
            iziToast.success({
                title: 'Success',
                message: 'Team information saved successfully.',
                position: 'topRight'
            });
        });
    }, { once: true });
</script>
@endscript