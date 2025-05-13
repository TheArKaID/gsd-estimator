<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Project Details</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="/" wire:navigate>Dashboard</a></div>
                <div class="breadcrumb-item">{{ $project->name }}</div>
            </div>
        </div>

        <!-- Show ownership status message -->
        @if(!$isOwner)
        <div class="alert alert-warning">
            <div class="alert-title">View-Only Mode</div>
            <p>You are viewing this project in read-only mode because you are not the project owner. 
               Only the user who created this project can make changes.</p>
            <p><small>Your Session ID: <span id="detail-user-session-display">{{ substr($sessionId, 0, 8) }}...</span></small></p>
            <p><small>Project Owner ID: {{ substr($project->session_id, 0, 8) }}...</small></p>
        </div>
        @endif

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
                                    Project Details
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
                                    <label class="col-md-4 text-md-right text-left">Project Clarity</label>
                                    <div class="col-lg-4 col-md-6">
                                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                            <label wire:ignore.self class="btn btn-outline-primary {{ $projectClarity === 'conceptual' ? 'active' : '' }}">
                                                <input 
                                                    type="radio"
                                                    wire:model='projectClarity' 
                                                    id="project_clarity_conceptual" 
                                                    name="project_clarity_conceptual" 
                                                    value="conceptual" 
                                                    wire:change='saveProjectClarity'
                                                    {{ $projectClarity === 'conceptual' ? 'checked' : '' }}
                                                    style="position: absolute; opacity: 0;"
                                                > Conceptual
                                            </label>
                                            <label wire:ignore.self class="btn btn-outline-primary {{ $projectClarity === 'evolving' ? 'active' : '' }}">
                                                <input 
                                                    type="radio"
                                                    wire:model='projectClarity' 
                                                    id="project_clarity_evolving" 
                                                    name="project_clarity_evolving" 
                                                    value="evolving" 
                                                    wire:change='saveProjectClarity'
                                                    {{ $projectClarity === 'evolving' ? 'checked' : '' }}
                                                    style="position: absolute; opacity: 0;"
                                                > Evolving
                                            </label>
                                            <label wire:ignore.self class="btn btn-outline-primary {{ $projectClarity === 'established' ? 'active' : '' }}">
                                                <input 
                                                    type="radio"
                                                    wire:model='projectClarity'
                                                    id="project_clarity_established" 
                                                    name="project_clarity_established" 
                                                    value="established" 
                                                    wire:change='saveProjectClarity'
                                                    {{ $projectClarity === 'established' ? 'checked' : '' }}
                                                    style="position: absolute; opacity: 0;"
                                                > Established
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row align-items-center">
                                    <div class="col-md-4 text-md-right text-left"></div>
                                    <div class="col-lg-6 col-md-8">
                                        <div id="project-type-description" class="mt-2">
                                            <p><strong>Conceptual Clarity:</strong> Requirements are not well-defined and are expected to change significantly. <br>Estimation range: -25% to +75%</p>
                                            <p><strong>Evolving Clarity:</strong> Requirements are partially defined with some expected changes. <br>Estimation range: -10% to +25%</p>
                                            <p><strong>Established Clarity:</strong> Requirements are well-defined with minimal expected changes. <br>Estimation range: -5% to +10%</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Add Project Type selection -->
                                <div class="form-group row align-items-center">
                                    <label class="col-md-4 text-md-right text-left">Project Type</label>
                                    <div class="col-lg-4 col-md-6">
                                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                            <label wire:ignore.self class="btn btn-outline-primary {{ $projectType === 'organic' ? 'active' : '' }}">
                                                <input 
                                                    type="radio"
                                                    wire:model='projectType' 
                                                    id="project_type_organic" 
                                                    name="project_type_organic" 
                                                    value="organic" 
                                                    wire:change='saveProjectType'
                                                    {{ $projectType === 'organic' ? 'checked' : '' }}
                                                    style="position: absolute; opacity: 0;"
                                                > Organic
                                            </label>
                                            <label wire:ignore.self class="btn btn-outline-primary {{ $projectType === 'semi-detached' ? 'active' : '' }}">
                                                <input 
                                                    type="radio"
                                                    wire:model='projectType' 
                                                    id="project_type_semi" 
                                                    name="project_type_semi" 
                                                    value="semi-detached" 
                                                    wire:change='saveProjectType'
                                                    {{ $projectType === 'semi-detached' ? 'checked' : '' }}
                                                    style="position: absolute; opacity: 0;"
                                                > Semi-Detached
                                            </label>
                                            <label wire:ignore.self class="btn btn-outline-primary {{ $projectType === 'embedded' ? 'active' : '' }}">
                                                <input 
                                                    type="radio"
                                                    wire:model='projectType'
                                                    id="project_type_embedded" 
                                                    name="project_type_embedded" 
                                                    value="embedded" 
                                                    wire:change='saveProjectType'
                                                    {{ $projectType === 'embedded' ? 'checked' : '' }}
                                                    style="position: absolute; opacity: 0;"
                                                > Embedded
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group row align-items-center">
                                    <div class="col-md-4 text-md-right text-left"></div>
                                    <div class="col-md-6">
                                        <strong>Project Example:</strong> {{ 
                                            $projectType == 'organic' ? 'Small in-house business systems, Batch data processing, Basic inventory control, Familiar OS/compiler, Simple Inventory/Production Control' 
                                            : ($projectType == 'semi-detached' ? 'Most transaction processing, Multi-file systems, New OS, DBMS, Ambitious Inventory/Production Control, Simple Command Control' 
                                            : 'Large/Complex real-time, Extensive transaction processing, Large OS, Avionics, Command-and-control') 
                                        }}
                                    </div>
                                </div>
                                <div class="form-group row align-items-center">
                                    <div class="col-md-4 text-md-right text-left"></div>
                                    <div class="col-lg-6 col-md-8">
                                        <div id="project-type-cocomo-description">
                                            <p><strong>COCOMO I Project Types:</strong></p>
                                            <ul>
                                                <li><strong>Organic:</strong> Small teams with good application experience working with less rigid requirements. <i>(Coefficient: 2.4, Multiplier: 1.0)</i></li>
                                                <li><strong>Semi-detached:</strong> Medium teams with mixed experience working with a mix of rigid and less rigid requirements. <i>(Coefficient: 3.0, Multiplier: 1.25)</i></li>
                                                <li><strong>Embedded:</strong> Developed within tight constraints with little flexibility. <i>(Coefficient: 3.6, Multiplier: 1.5)</i></li>
                                            </ul>
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
                                                <h4>Team Information <i class="fas fa-info-circle text-info ml-1" data-toggle="tooltip" title="These metrics impact the base estimate before applying Project Clarity ranges and PERT analysis."></i></h4>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-group row mb-4">
                                                    <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Team Size</label>
                                                    <div class="col-sm-12 col-md-7">
                                                        <i class="fas fa-info-circle text-info ml-1" data-toggle="tooltip" title="Team Size affects communication complexity. As team size increases, communication path increase exponentially by the formula n(n-1)/2."></i>
                                                        <input type="number" class="form-control" wire:model="smEmployee" min="1">
                                                        @error('smEmployee') <span class="text-danger">{{ $message }}</span> @enderror
                                                        <small class="form-text text-muted">
                                                            Enter the number of team members working on this project. Team size directly affects communication complexity.
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Team Velocity</label>
                                                    <div class="col-sm-12 col-md-7">
                                                        <i class="fas fa-info-circle text-info ml-1" data-toggle="tooltip" title="You derive your Team Velocity as: Team's Velocity = Histrory of Completed Story Point ÷ Total Sprint"></i>
                                                        <input type="number" class="form-control" wire:model="smVelocity" min="1">
                                                        @error('smVelocity') <span class="text-danger">{{ $message }}</span> @enderror
                                                        <small class="form-text text-muted">
                                                            Enter the team's story points completed per sprint/iteration.
                                                            <span class="d-block mt-1">
                                                                <strong>How it affects estimation:</strong> The formula used is: Duration = Total Story Points ÷ Velocity. Team Velocity is the number of story points a team can complete in a single iteration (sprint). It's a key metric for estimating project duration.
                                                            </span>
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-4">
                                                    <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">Sprint Length (days)</label>
                                                    <div class="col-sm-12 col-md-7">
                                                        <i class="fas fa-info-circle text-info ml-1" data-toggle="tooltip" title="The number of working days in one sprint/iteration"></i>
                                                        <input type="number" class="form-control" wire:model="smSprintLength" min="1">
                                                        @error('smSprintLength') <span class="text-danger">{{ $message }}</span> @enderror
                                                        <small class="form-text text-muted">
                                                            Enter the length of a sprint in working days (typically 10 for 2-week sprints).
                                                        </small>
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
                                        
                                        <!-- Add Communication Complexity Card -->
                                        <div class="card mt-4">
                                            <div class="card-header {{ $exceedsScrumTeamSize ? 'bg-warning text-white' : 'bg-success text-white' }}">
                                                <h4 class="text-white">Communication Complexity</h4>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="text-center p-3 border rounded">
                                                            <h5>Communication Path</h5>
                                                            <h2>{{ $communicationPath }}</h2>
                                                            <small>Formula: n(n-1)/2 where n = {{ $smEmployee }}</small>
                                                            <div class="mt-2">
                                                                <div class="d-flex justify-content-between">
                                                                    <span>Standard Scrum Team (10 people):</span>
                                                                    <strong>45 channels</strong>
                                                                </div>
                                                                <div class="progress mt-1">
                                                                    <div class="progress-bar {{ $exceedsScrumTeamSize ? 'bg-warning' : 'bg-success' }}" role="progressbar" style="width: {{ min(100, ($communicationPath / $baselineCommunicationPath) * 100) }}%" aria-valuenow="{{ $communicationPath }}" aria-valuemin="0" aria-valuemax=45></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        @if($exceedsScrumTeamSize)
                                                            <div class="text-center p-3 border rounded bg-warning text-white">
                                                                <h5>Complexity Impact</h5>
                                                                <h2>+{{ $communicationComplexityImpact }}%</h2>
                                                                <p>{{ $communicationComplexityLevel }} complexity level</p>
                                                                <small>Applied as a multiplier to your estimates</small>
                                                            </div>
                                                        @else
                                                            <div class="text-center p-3 border rounded bg-success text-white">
                                                                <h5>Complexity Impact</h5>
                                                                <h2>None</h2>
                                                                <p>Standard Scrum Team Size</p>
                                                                <small>No additional complexity factor applied</small>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="mt-4">
                                                    <p><strong>About Communication Complexity:</strong></p>
                                                    <p>Communication complexity follows the formula n(n-1)/2, creating an exponential growth in communication path as team size increases:</p>
                                                    <ul>
                                                        <li><strong>Standard Scrum Team (≤10 people):</strong> Up to 45 communication path - no additional complexity factor</li>
                                                        <li><strong>11-person Team:</strong> 55 channels - 22% increase from baseline</li>
                                                        <li><strong>12-person Team:</strong> 66 channels - 46% increase from baseline</li>
                                                        <li><strong>13-person Team:</strong> 78 channels - 67% increase from baseline</li>
                                                    </ul>
                                                    <p>When team size exceeds 10 people, the percentage increase in communication path is applied as a separate multiplier to the estimated development time.</p>
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
                                            <div class="col-md-12 text-right">
                                                {{-- Save Button --}}
                                                <div class="form-group">
                                                    <button type="button" class="btn btn-primary" wire:click='saveStoryPoint'>Add Story Point</button>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
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
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="summary" role="tabpanel" aria-labelledby="summary-tab" wire:ignore.self>
                        <div class="wizard-content mt-2">
                            <div class="wizard-pane">
                                <div class="row justify-content-center">
                                    <div class="col-md-8">
                                        {{-- Add Total Effort Required Card --}}
                                        <div class="card mb-4">
                                            <div class="card-header bg-primary">
                                                <h4 class="mb-0 text-white">Total Effort Required</h4>
                                            </div>
                                            <div class="card-body text-center">
                                                <h2 class="display-4 mb-2">{{ $formattedExpectedTime }} <small>man-days</small></h2>
                                                <p class="mb-0">
                                                    <strong>Expected total effort</strong> for this project, based on all factors and PERT analysis.<br>
                                                </p>
                                                <div class="d-flex justify-content-center mt-3">
                                                    <div class="bg-light rounded p-3 text-center mx-2">
                                                        <h5 class="mb-1">Sprints</h5>
                                                        <h3 class="mb-0">{{ $sprintExpectedTime }}</h3>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
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
                                                            <td><strong>Project Clarity</strong></td>
                                                            <td>{{ ucfirst($project->project_clarity) }} ({{ $claritySummary }})</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Team Size</strong></td>
                                                            <td>{{ $smEmployee }} members</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Communication Path</strong></td>
                                                            <td>{{ $communicationPath }} (Complexity: {{ $communicationComplexityLevel }})</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Team's Velocity</strong></td>
                                                            <td>{{ $smVelocity }} points per sprint</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Sprint Length</strong></td>
                                                            <td>{{ $smSprintLength }} days</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Total Story Points</strong></td>
                                                            <td>{{ $totalStoryPoints }} points</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Project Type</strong></td>
                                                            <td>{{ ucfirst($project->project_type) }} (COCOMO I Coefficient: {{ $projectTypeCoefficient }}, Multiplier: {{ number_format($projectTypeMultiplier, 2) }})</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Story Points (Multiplied)</strong></td>
                                                            <td>{{ $totalStoryPointsProjectTypeMultiplied }} points</td>
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
                                                    <h5>Effort Estimation (PERT Analysis with Project Clarity and COCOMO I)</h5>
                                                    <div class="card bg-light mb-3">
                                                        <div class="card-body">
                                                            <p class="mb-0">
                                                                <strong>Estimation Method:</strong> This analysis combines PERT (Program Evaluation and Review Technique) with COCOMO I and your selected project clarity level. The base estimate is influenced by your project type ({{ ucfirst($project->project_type) }}), and the three-point range is determined by your project clarity level ({{ ucfirst($project->project_clarity) }}).
                                                            </p>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Base Estimates (without any factors) -->
                                                    <div class="card mb-4">
                                                        <div class="card-header bg-secondary">
                                                            <h5 class="mb-0 text-black">Base Estimates (without any factors)</h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <div class="border rounded p-3 text-center">
                                                                        <h6>Optimistic Time</h6>
                                                                        <h3>{{ $sprintBaseOptimisticTime }} sprints</h3>
                                                                        <small>({{ $formattedBaseOptimisticTime }} days)</small>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="border rounded p-3 text-center">
                                                                        <h6>Most Likely Time</h6>
                                                                        <h3>{{ $sprintBaseMostLikelyTime }} sprints</h3>
                                                                        <small>({{ $formattedBaseMostLikelyTime }} days)</small>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="border rounded p-3 text-center">
                                                                        <h6>Pessimistic Time</h6>
                                                                        <h3>{{ $sprintBasePessimisticTime }} sprints</h3>
                                                                        <small>({{ $formattedBasePessimisticTime }} days)</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Communication Complexity Estimates -->
                                                    <div class="card mb-4">
                                                        <div class="card-header {{ $exceedsScrumTeamSize ? 'bg-warning' : 'bg-success text-white' }}">
                                                            <h5 class="mb-0 text-white">Additional Communication Complexity</h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="border rounded p-3 text-center">
                                                                        <h6>Base Most Likely Time</h6>
                                                                        <h3>{{ $sprintBaseMostLikelyTime }} sprints</h3>
                                                                        <small>({{ $formattedBaseMostLikelyTime }} days)</small>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="border rounded p-3 text-center">
                                                                        <h6>With Communication Complexity</h6>
                                                                        <h3>{{ $sprintCommMostLikelyTime }} sprints</h3>
                                                                        <small>({{ $formattedCommMostLikelyTime }} days)</small>
                                                                        @if($exceedsScrumTeamSize)
                                                                            <div class="mt-2 p-1 bg-light rounded">
                                                                                <small>Applied factor: ×{{ number_format($communicationComplexityFactor, 2) }}</small>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @if($exceedsScrumTeamSize)
                                                                <div class="row mt-3">
                                                                    <div class="col-md-12">
                                                                        <div class="alert alert-warning">
                                                                            <div class="d-flex">
                                                                                <div class="mr-3">
                                                                                    <i class="fas fa-info-circle fa-2x"></i>
                                                                                </div>
                                                                                <div>
                                                                                    <h5 class="mb-1">Communication Complexity Impact</h5>
                                                                                    <p class="mb-0">Your team size of {{ $smEmployee }} people creates {{ $communicationPath }} communication path, which is {{ $communicationComplexityImpact }}% more than a standard 7-person Scrum team. This additional complexity adds {{ $formattedCommunicationImpactDays }} days to your project timeline.</p>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Final Estimates (with GSD Factors) -->
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
                                                                            <h2>{{ $sprintOptimisticTime }} sprints</h2>
                                                                            <p>{{ $formattedOptimisticTime }} days</p>
                                                                            <small>Best case scenario</small>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="card bg-primary text-white">
                                                                        <div class="card-body text-center">
                                                                            <h6>Most Likely Time</h6>
                                                                            <h2>{{ $sprintMostLikelyTime }} sprints</h2>
                                                                            <p>{{ $formattedMostLikelyTime }} days</p>
                                                                            <small>Expected scenario</small>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="card bg-warning">
                                                                        <div class="card-body text-center">
                                                                            <h6>Pessimistic Time</h6>
                                                                            <h2>{{ $sprintPessimisticTime }} sprints</h2>
                                                                            <p>{{ $formattedPessimisticTime }} days</p>
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
                                                                            <h3 class="mb-0">{{ $sprintExpectedTime }} sprints</h3>
                                                                            <p>{{ $formattedExpectedTime }} days</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="card-footer bg-info text-white">
                                                                    <div class="row">
                                                                        <div class="col-md-8">
                                                                            <p class="mb-0">Estimation Range (based on Project Clarity)</p>
                                                                        </div>
                                                                        <div class="col-md-4 text-right">
                                                                            <p class="mb-0">{{ $sprintOptimisticTime }} - {{ $sprintPessimisticTime }} sprints</p>
                                                                            <small>{{ $formattedOptimisticTime }} - {{ $formattedPessimisticTime }} days</small>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Team Communication Impact Analysis -->
                                                    <div class="card mb-4">
                                                        <div class="card-header {{ $exceedsScrumTeamSize ? 'bg-warning' : 'bg-success text-white' }}">
                                                            <h5 class="mb-0 text-white">Communication Complexity Impact Analysis</h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row align-items-center">
                                                                <div class="col-md-6">
                                                                    <h4 class="text-center mb-4">Impact on Project Duration</h4>
                                                                    <div class="d-flex justify-content-around align-items-center">
                                                                        <div class="text-center">
                                                                            <h5>Base Duration</h5>
                                                                            <h3>{{ $sprintBaseMostLikelyTime }} sprints</h3>
                                                                            <small>{{ $formattedBaseMostLikelyTime }} days</small>
                                                                        </div>
                                                                        <div class="text-center">
                                                                            <i class="fas fa-arrow-right fa-2x"></i>
                                                                        </div>
                                                                        <div class="text-center">
                                                                            <h5>With Communication</h5>
                                                                            <h3>{{ $sprintCommMostLikelyTime }} sprints</h3>
                                                                            <small>{{ $formattedCommMostLikelyTime }} days</small>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="text-center text-white p-4 {{ $communicationImpactPercentage > 0 && $exceedsScrumTeamSize ? 'bg-warning' : 'bg-success text-white' }} rounded">
                                                                        <h5>Communication Impact</h5>
                                                                        @if($exceedsScrumTeamSize)
                                                                            <h2>+{{ number_format($communicationImpactPercentage) }}%</h2>
                                                                            <p class="mb-0">
                                                                                {{ $formattedCommunicationImpactDays }} days increase
                                                                            </p>
                                                                            <small>Multiplier: ×{{ number_format($communicationComplexityFactor, 2) }}</small>
                                                                        @else
                                                                            <h2>0%</h2>
                                                                            <p class="mb-0">Standard Scrum team size - no additional impact</p>
                                                                        @endif
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
                                                                            <h3>{{ $sprintCommMostLikelyTime }} sprints</h3>
                                                                            <small>{{ $formattedCommMostLikelyTime }} days</small>
                                                                        </div>
                                                                        <div class="text-center">
                                                                            <i class="fas fa-arrow-right fa-2x"></i>
                                                                        </div>
                                                                        <div class="text-center">
                                                                            <h5>With GSD</h5>
                                                                            <h3>{{ $sprintMostLikelyTime }} sprints</h3>
                                                                            <small>{{ $formattedMostLikelyTime }} days</small>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="text-center text-white p-4 {{ $gsdOnlyImpactPercentage > 0 ? 'bg-warning' : 'bg-success' }} rounded">
                                                                        <h5>GSD Impact</h5>
                                                                        <h2>
                                                                            {{ $gsdOnlyImpactPercentage > 0 ? '+' : '' }}{{ number_format($gsdOnlyImpactPercentage) }}%
                                                                        </h2>
                                                                        <p class="mb-0">
                                                                            {{ $formattedGsdOnlyImpactDays }} days {{ $gsdOnlyImpactPercentage > 0 ? 'increase' : 'decrease' }}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- PERT Impact Analysis -->
                                                    <div class="card mb-4">
                                                        <div class="card-header bg-info text-white">
                                                            <h5 class="mb-0">PERT Analysis Impact</h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row align-items-center">
                                                                <div class="col-md-6">
                                                                    <h4 class="text-center mb-4">Impact on Final Estimate</h4>
                                                                    <div class="d-flex justify-content-around align-items-center">
                                                                        <div class="text-center">
                                                                            <h5>Most Likely (ML)</h5>
                                                                            <h3>{{ $sprintMostLikelyTime }} sprints</h3>
                                                                            <small>{{ $formattedMostLikelyTime }} days</small>
                                                                        </div>
                                                                        <div class="text-center">
                                                                            <i class="fas fa-arrow-right fa-2x"></i>
                                                                        </div>
                                                                        <div class="text-center">
                                                                            <h5>PERT Expected</h5>
                                                                            <h3>{{ $sprintExpectedTime }} sprints</h3>
                                                                            <small>{{ $formattedExpectedTime }} days</small>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="text-center p-4 bg-info text-white rounded">
                                                                        <h5>PERT Formula</h5>
                                                                        <p class="mb-1">(O + 4ML + P) / 6</p>
                                                                        <h5 class="mt-3">Components</h5>
                                                                        <p class="mb-1">Optimistic: {{ $sprintOptimisticTime }} sprints</p>
                                                                        <p class="mb-1">Most Likely: {{ $sprintMostLikelyTime }} sprints × 4</p>
                                                                        <p class="mb-1">Pessimistic: {{ $sprintPessimisticTime }} sprints</p>
                                                                        <hr class="bg-white my-2">
                                                                        <p class="mb-0">= {{ $sprintExpectedTime }} sprints</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mt-3">
                                                        <p><strong>COCOMO I Basic Parameters for {{ ucfirst($project->project_type) }} Projects:</strong></p>
                                                        <table class="table table-bordered">
                                                            <tr>
                                                                <th>Project Type</th>
                                                                <th>Coefficient (a)</th>
                                                                <th>Exponent (b)</th>
                                                                <th>Formula</th>
                                                            </tr>
                                                            <tr>
                                                                <td>{{ ucfirst($project->project_type) }}</td>
                                                                <td>{{ $projectTypeCoefficient }}</td>
                                                                <td>{{ $projectTypeExponent }}</td>
                                                                <td>Story Point = Story Point x ({{ $projectTypeCoefficient }} × (1)<sup>{{ $projectTypeExponent }}</sup>)</td>
                                                            </tr>
                                                        </table>
                                                        <small class="text-muted">Note: 
                                                            <ul>
                                                                <li>These baseline parameters were derived from Barry Boehm's COCOMO I Basic model and adapted for story point-based estimation.</li>
                                                                <li>COCOMO I Basic model, however works with KLOC (Kilo Delivered Source Instructions) as the size metric. To adapt it for story points, we use the formula: Story Point Multiplier = (Coefficient × (1)<sup>Exponent</sup>) - (Organic Coefficient) + 1</li>
                                                                <li>While the adapted formula is not a direct translation and the exponent become unused, it results in a close approximation of the original COCOMO I Basic model.</li>
                                                            </ul>
                                                            
                                                        </small>
                                                    </div>
                                                    
                                                    <div class="mt-3">
                                                        <p><strong>PERT Analysis for {{ ucfirst($project->project_clarity) }} Clarity Projects:</strong></p>
                                                        <table class="table table-bordered">
                                                            <tr>
                                                                <th>Component</th>
                                                                <th>Value</th>
                                                                <th>Weight in PERT</th>
                                                                <th>Description</th>
                                                            </tr>
                                                            <tr>
                                                                <td>Optimistic (O)</td>
                                                                <td>{{ number_format($optimisticTime) }} days</td>
                                                                <td>1x</td>
                                                                <td>Best case scenario ({{ $optimisticPercentage }}% adjustment)</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Most Likely (M)</td>
                                                                <td>{{ number_format($mostLikelyTime) }} days</td>
                                                                <td>4x</td>
                                                                <td>Most probable scenario (base estimate)</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Pessimistic (P)</td>
                                                                <td>{{ number_format($pessimisticTime) }} days</td>
                                                                <td>1x</td>
                                                                <td>Worst case scenario (+{{ $pessimisticPercentage }}% adjustment)</td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="2"><strong>PERT Formula:</strong> (O + 4M + P) / 6</td>
                                                                <td colspan="2"><strong>Expected Duration:</strong> {{ number_format($expectedTime) }} days ({{ number_format($expectedTime / $smSprintLength) }} sprints)</td>
                                                            </tr>
                                                        </table>
                                                        <small class="text-muted">Note: PERT gives more weight (4x) to the most likely estimate, resulting in an expected duration that better reflects real-world outcomes than a simple average.</small>
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
                                                                    {{ $confidenceInterval68Low }} to 
                                                                    {{ $confidenceInterval68High }} days
                                                                    <br>
                                                                    <small>({{ $sprintConfidenceInterval68Low }} to 
                                                                    {{ $sprintConfidenceInterval68High }} sprints)</small>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>95% Confidence (±2σ)</td>
                                                                <td>
                                                                    {{ $confidenceInterval95Low }} to 
                                                                    {{ $confidenceInterval95High }} days
                                                                    <br>
                                                                    <small>({{ $sprintConfidenceInterval95Low }} to 
                                                                    {{ $sprintConfidenceInterval95High }} sprints)</small>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
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

    // Add communication path visualization
    document.addEventListener('livewire:load', function () {
        Livewire.on('software-metrics-saved', function () {
            renderCommunicationChart();
        });
    });
    
    function renderCommunicationChart() {
        const ctx = document.getElementById('communicationPathChart');
        if (!ctx) return;
        
        const teamSize = @this.smEmployee;
        const channels = @this.communicationPath;
        const baseline = @this.baselineCommunicationPath;
        const exceedsScrumTeamSize = @this.exceedsScrumTeamSize;
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: [
                    'Team Members', 
                    'Communication Path',
                    exceedsScrumTeamSize ? 'Extra Complexity' : 'Standard Baseline'
                ],
                datasets: [{
                    data: [
                        teamSize, 
                        exceedsScrumTeamSize ? baseline : channels,
                        exceedsScrumTeamSize ? channels - baseline : baseline
                    ],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        exceedsScrumTeamSize ? 'rgba(255, 99, 132, 0.7)' : 'rgba(75, 192, 192, 0.7)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        exceedsScrumTeamSize ? 'rgba(255, 99, 132, 1)' : 'rgba(75, 192, 192, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            generateLabels: function(chart) {
                                // Custom label generation to include values
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map(function(label, i) {
                                        const value = data.datasets[0].data[i];
                                        return {
                                            text: `${label}: ${value}`,
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            strokeStyle: data.datasets[0].borderColor[i],
                                            lineWidth: 1,
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                return `${label}: ${value}`;
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Initialize chart when tab is shown
    document.getElementById('summary-tab').addEventListener('click', function() {
        setTimeout(renderCommunicationChart, 100);
    });

    document.addEventListener('livewire:update', function () {
        // Reapply active class to GSD Parameters after Livewire update
        @this.projectGlobalFactors.forEach(function(factor) {
            document.querySelector(`input[value="${factor}"]`).closest('label').classList.add('active');
        });
    });
</script>
@endpush

@script
<script>
    document.addEventListener('livewire:navigated', function() {
        // Handle session ID persistence
        let userSessionId = localStorage.getItem('gsd_user_session_id');
        
        // If no session ID exists in localStorage, generate one
        if (!userSessionId) {
            userSessionId = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
            localStorage.setItem('gsd_user_session_id', userSessionId);
        }
        
        // Update display if element exists
        const displayElement = document.getElementById('detail-user-session-display');
        if (displayElement) {
            displayElement.textContent = userSessionId.substring(0, 8) + '...';
        }
        
        // Send session ID to Livewire component
        Livewire.dispatch('setSessionId', { sessionId: userSessionId });

        $wire.on('story-point-added', function () {
            iziToast.success({
                title: 'Success',
                message: 'Story point added successfully.',
                position: 'topRight'
            });
        });
        
        // Add event handler for error alerts
        $wire.on('showAlert', function(data) {
            iziToast[data[0].type]({
                title: data[0].type.charAt(0).toUpperCase() + data[0].type.slice(1),
                message: data[0].message,
                position: 'topRight'
            });
        });

        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
    }, { once: true });
</script>
@endscript