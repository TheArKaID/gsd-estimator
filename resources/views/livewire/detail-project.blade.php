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
                            <div class="wizard-step wizard-step-active" id="story-point-tab" onclick="showTab('story-point')">
                                <div class="wizard-step-icon">
                                    <i class="fas fa-tasks"></i>
                                </div>
                                <div class="wizard-step-label">
                                    User Story Points
                                </div>
                            </div>
                            <div class="wizard-step" id="project-size-tab" onclick="showTab('project-size')">
                                <div class="wizard-step-icon">
                                    <i class="fas fa-box-open"></i>
                                </div>
                                <div class="wizard-step-label">
                                    Project Size
                                </div>
                            </div>
                            <div class="wizard-step" id="gsd-parameters-tab" onclick="showTab('gsd-parameters')">
                                <div class="wizard-step-icon">
                                    <i class="fas fa-list-alt"></i>
                                </div>
                                <div class="wizard-step-label">
                                    GSD Parameters
                                </div>
                            </div>
                            <div class="wizard-step" id="summary-tab" onclick="showTab('summary')">
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
                    <div class="tab-pane fade show active" id="story-point" role="tabpanel" aria-labelledby="story-point-tab">
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
                                                    <label for="description">Description</label>
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
                                </div>
                            </div>
                        </form>
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
                    <div class="tab-pane fade" id="project-size" role="tabpanel" aria-labelledby="project-size-tab">
                        <form class="wizard-content mt-2">
                            <div class="wizard-pane">
                                <div class="form-group row align-items-center">
                                    <label class="col-md-4 text-md-right text-left">Project Size</label>
                                    <div class="col-lg-4 col-md-6">
                                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                            <label class="btn btn-outline-primary">
                                                <input type="radio" name="project_size" value="organic" autocomplete="off"> Organic
                                            </label>
                                            <label class="btn btn-outline-primary">
                                                <input type="radio" name="project_size" value="semi-detached" autocomplete="off"> Semi-Detached
                                            </label>
                                            <label class="btn btn-outline-primary">
                                                <input type="radio" name="project_size" value="embedded" autocomplete="off"> Embedded
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row align-items-center">
                                    <div class="col-md-4 text-md-right text-left"></div>
                                    <div class="col-lg-4 col-md-6">
                                        <div id="project-size-description" class="mt-2">
                                            <p><strong>Organic:</strong> Small teams with good experience working on less rigid requirements.</p>
                                            <p><strong>Semi-Detached:</strong> Medium teams with mixed experience working on more complex requirements.</p>
                                            <p><strong>Embedded:</strong> Large teams working on projects with strict requirements and constraints.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-4"></div>
                                    <div class="col-lg-4 col-md-6 text-right">
                                        <button type="submit" class="btn btn-icon icon-right btn-primary">Save Project Size <i class="fas fa-save"></i></button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="gsd-parameters" role="tabpanel" aria-labelledby="gsd-parameters-tab">
                        <form class="wizard-content mt-2">
                            <div class="wizard-pane">
                                <div class="form-group row align-items-center">
                                    <label class="col-md-4 text-md-right text-left">GSD Parameter</label>
                                    <div class="col-lg-4 col-md-6">
                                        <select name="gsd_parameter" class="form-control" onchange="showGsdOptions(this.value)">
                                            <option value="">Select Parameter</option>
                                            <option value="parameter1">Parameter 1</option>
                                            <option value="parameter2">Parameter 2</option>
                                            <option value="parameter3">Parameter 3</option>
                                        </select>
                                    </div>
                                </div>
                                <div id="gsd-options-container" class="mt-4">
                                    <!-- Options for the selected parameter will be displayed here -->
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-4"></div>
                                    <div class="col-lg-4 col-md-6 text-right">
                                        <button type="submit" class="btn btn-icon icon-right btn-primary">Save GSD Parameters <i class="fas fa-save"></i></button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="summary" role="tabpanel" aria-labelledby="summary-tab">
                        zzz
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

    function showGsdOptions(parameter) {
        const container = document.getElementById('gsd-options-container');
        let optionsHtml = '';

        if (parameter === 'parameter1') {
            optionsHtml = `
                <div class="form-group row align-items-center">
                    <label class="col-md-4 text-md-right text-left">Option 1</label>
                    <div class="col-lg-4 col-md-6">
                        <input type="text" name="option1" class="form-control">
                    </div>
                </div>
                <div class="form-group row align-items-center">
                    <label class="col-md-4 text-md-right text-left">Option 2</label>
                    <div class="col-lg-4 col-md-6">
                        <input type="text" name="option2" class="form-control">
                    </div>
                </div>
            `;
        } else if (parameter === 'parameter2') {
            optionsHtml = `
                <div class="form-group row align-items-center">
                    <label class="col-md-4 text-md-right text-left">Option A</label>
                    <div class="col-lg-4 col-md-6">
                        <input type="text" name="optionA" class="form-control">
                    </div>
                </div>
                <div class="form-group row align-items-center">
                    <label class="col-md-4 text-md-right text-left">Option B</label>
                    <div class="col-lg-4 col-md-6">
                        <input type="text" name="optionB" class="form-control">
                    </div>
                </div>
            `;
        } else if (parameter === 'parameter3') {
            optionsHtml = `
                <div class="form-group row align-items-center">
                    <label class="col-md-4 text-md-right text-left">Option X</label>
                    <div class="col-lg-4 col-md-6">
                        <input type="text" name="optionX" class="form-control">
                    </div>
                </div>
                <div class="form-group row align-items-center">
                    <label class="col-md-4 text-md-right text-left">Option Y</label>
                    <div class="col-lg-4 col-md-6">
                        <input type="text" name="optionY" class="form-control">
                    </div>
                </div>
            `;
        }

        container.innerHTML = optionsHtml;
    }
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
    }, { once: true });
</script>
@endscript