<div class="main-content">
    <section class="section">
        <p class="section-lead" style="margin: 0px"><small class="text-muted">Session ID: {{ substr(session('user_session_id'), 0, 8) }}...</small></p>
        <div class="section-header">
            <h1>Dashboard</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item">Dashboard</div>
            </div>
        </div>
        <div class="section-body">
            <div class="row mb-4">
                <div class="col-md-12 text-right">
                    <button class="btn btn-primary" wire:click="showExportModal">
                        <i class="fas fa-file-export"></i> Export Projects
                    </button>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-3">
                    <div class="card card-body text-center hover-effect d-flex align-items-center justify-content-center" style="padding: 50px; height: 250px;" data-toggle="modal" data-target="#addProject">
                        <i class="fas fa-plus-square" style="color: #74C0FC; font-size: 75px;"></i>
                    </div>
                </div>
                @foreach ($projects as $project)
                    <div class="col-md-3">
                        <div class="card card-body text-center hover-effect d-flex flex-column align-items-center justify-content-center" style="padding: 50px; height: 250px;" onclick="Livewire.navigate('/project/{{ $project->id }}')">
                            <h5>{{ $project->name }}</h5>
                            <p>{{ $project->description }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    
    <!-- Add Project Modal -->
    <div class="modal fade" wire:ignore.self id="addProject" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-labelledby="addProjectLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProjectLabel">Create New Project</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-left">
                    <form wire:submit="saveNewProject">
                        <div class="form-group">
                            <label for="newProjectName">Project Name</label>
                            <input type="text" class="form-control" id="newProjectName" wire:model="newProjectName" placeholder="Enter project name">
                            @error('newProjectName') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="newProjectDescription">Project Description</label>
                            <textarea class="form-control" id="newProjectDescription" wire:model="newProjectDescription" placeholder="Enter project description" rows="15" style="height: 130px;"></textarea>
                            @error('newProjectDescription') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Export Projects Modal -->
    <div class="modal fade" wire:ignore.self id="exportProjectsModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-labelledby="exportProjectsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportProjectsModalLabel">Export Projects</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-left">
                    <p>Select projects to export and enter previous estimation and actual effort data:</p>
                    
                    <form wire:submit.prevent="downloadExport">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th width="5%">Select</th>
                                        <th width="25%">Project Name</th>
                                        <th width="20%">GSD Estimation (days)</th>
                                        <th width="25%">Previous Estimation (days)</th>
                                        <th width="25%">Actual Effort (days)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($allProjectsData as $projectId => $data)
                                        <tr class="{{ in_array($projectId, $selectedProjects) ? 'table-active' : '' }}">
                                            <td class="text-center">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           wire:model.live="selectedProjects" 
                                                           value="{{ $projectId }}" 
                                                           id="project-{{ $projectId }}">
                                                </div>
                                            </td>
                                            <td>{{ $data['name'] }}</td>
                                            <td>{{ $data['gsd_estimation'] ?? 'N/A' }}</td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control" 
                                                       wire:model="allProjectsData.{{ $projectId }}.previous_estimation" 
                                                       placeholder="Enter previous estimation"
                                                       {{ !in_array($projectId, $selectedProjects) ? 'disabled' : '' }}>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control" 
                                                       wire:model="allProjectsData.{{ $projectId }}.actual_effort" 
                                                       placeholder="Enter actual effort"
                                                       {{ !in_array($projectId, $selectedProjects) ? 'disabled' : '' }}>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No projects available</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-info" wire:click="saveEffortData" {{ empty($selectedProjects) ? 'disabled' : '' }}>Save Data Only</button>
                            <button type="submit" class="btn btn-primary" {{ empty($selectedProjects) ? 'disabled' : '' }}>Export to Excel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <style>
        .hover-effect:hover {
            background-color: #e0e0e0;
            transition: background-color 0.3s ease-in-out;
            cursor: pointer;
            transform: scale(1.05);
        }
        
        .table-active {
            background-color: rgba(0, 123, 255, 0.1);
        }
    </style>
@endpush

@script
    <script>
        document.addEventListener('livewire:navigated', function () {
            $wire.on('projectAdded', function () {
                $('#addProject').modal('hide');

                iziToast.success({
                    title: 'Success',
                    message: 'Project added successfully',
                    position: 'topRight'
                });
            });
            
            $wire.on('showExportModal', function() {
                $('#exportProjectsModal').modal('show');
            });
            
            $wire.on('hideExportModal', function() {
                $('#exportProjectsModal').modal('hide');
            });
            
            $wire.on('showAlert', function(data) {
                iziToast[data[0].type]({
                    title: data[0].type.charAt(0).toUpperCase() + data[0].type.slice(1),
                    message: data[0].message,
                    position: 'topRight'
                });
            });
        }, { once: true });
    </script>
@endscript