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
            <div class="card card-body">
                <div class="row mt-4">
                    <div class="col-12 col-lg-8 offset-lg-2">
                        <div class="wizard-steps">
                            <div class="wizard-step wizard-step-active" id="story-point-tab" onclick="showTab('story-point')">
                                <div class="wizard-step-icon">
                                    <i class="fas fa-tasks"></i>
                                </div>
                                <div class="wizard-step-label">
                                    Story Points
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
                                <div class="form-group row align-items-center">
                                    <label class="col-md-4 text-md-right text-left">Story Point Name</label>
                                    <div class="col-lg-4 col-md-6">
                                        <input type="text" name="story_point_name" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group row align-items-center">
                                    <label class="col-md-4 text-md-right text-left">Description</label>
                                    <div class="col-lg-4 col-md-6">
                                        <textarea name="description" class="form-control"></textarea>
                                    </div>
                                </div>
                                <div class="form-group row align-items-center">
                                    <label class="col-md-4 text-md-right text-left">Points</label>
                                    <div class="col-lg-4 col-md-6">
                                        <input type="number" name="points" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group row align-items-center">
                                    <label class="col-md-4 text-md-right text-left">Type</label>
                                    <div class="col-lg-4 col-md-6">
                                        <select name="type" class="form-control">
                                            <option value="epic">Epic</option>
                                            <option value="story">Story</option>
                                            <option value="task">Task</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-4"></div>
                                    <div class="col-lg-4 col-md-6 text-right">
                                        <button type="submit" class="btn btn-icon icon-right btn-primary">Add Story Point <i class="fas fa-plus"></i></button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="mt-4">
                            <h5>Added Story Points</h5>
                            <ul class="list-group" id="story-point-list">
                                <!-- Example of a story point item -->
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>
                                        <strong>Story Point Name:</strong> Example Story Point<br>
                                        <strong>Description:</strong> Example Description<br>
                                        <strong>Points:</strong> 5<br>
                                        <strong>Type:</strong> Story
                                    </span>
                                    <button class="btn btn-danger btn-sm" onclick="removeStoryPoint(this)">Remove</button>
                                </li>
                                <!-- End of example -->
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

    function addStoryPoint() {
        const container = document.getElementById('story-point-container');
        const newStoryPoint = `
            <div class="form-group row align-items-center">
                <label class="col-md-4 text-md-right text-left">Story Point Name</label>
                <div class="col-lg-4 col-md-6">
                    <input type="text" name="story_point_name[]" class="form-control">
                </div>
            </div>
            <div class="form-group row align-items-center">
                <label class="col-md-4 text-md-right text-left">Description</label>
                <div class="col-lg-4 col-md-6">
                    <textarea name="description[]" class="form-control"></textarea>
                </div>
            </div>
            <div class="form-group row align-items-center">
                <label class="col-md-4 text-md-right text-left">Points</label>
                <div class="col-lg-4 col-md-6">
                    <input type="number" name="points[]" class="form-control">
                </div>
            </div>
            <div class="form-group row align-items-center">
                <label class="col-md-4 text-md-right text-left">Type</label>
                <div class="col-lg-4 col-md-6">
                    <select name="type[]" class="form-control">
                        <option value="epic">Epic</option>
                        <option value="story">Story</option>
                        <option value="task">Task</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', newStoryPoint);
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