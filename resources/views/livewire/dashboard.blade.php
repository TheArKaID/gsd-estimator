
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Dashboard</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="#">Dashboard > </a></div>
            </div>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="card card-body text-center hover-effect" style="padding: 50px;" data-toggle="modal" data-target="#addProject">
                        <i class="fas fa-plus-square" style="color: #74C0FC; font-size: 75px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>
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
                        {{-- <div class="form-group">
                            <label for="projectStatus">Project Status</label>
                            <select class="form-control" id="projectStatus" wire:model="projectStatus">
                                <option value="">Select project status</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            @error('projectStatus') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="projectStartDate">Project Start Date</label>
                            <input type="date" class="form-control" id="projectStartDate" wire:model="projectStartDate">
                            @error('projectStartDate') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="projectEndDate">Project End Date</label>
                            <input type="date" class="form-control" id="projectEndDate" wire:model="projectEndDate">
                            @error('projectEndDate') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="projectBudget">Project Budget</label>
                            <input type="number" class="form-control" id="projectBudget" wire:model="projectBudget" placeholder="Enter project budget">
                            @error('projectBudget') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="projectClient">Project Client</label>
                            <input type="text" class="form-control" id="projectClient" wire:model="projectClient" placeholder="Enter project client">
                            @error('projectClient') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group
                        ">
                            <label for="projectManager">Project Manager</label>
                            <input type="text" class="form-control" id="projectManager" wire:model="projectManager" placeholder="Enter project manager">
                            @error('projectManager') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="projectTeam">Project Team</label>
                            <input type="text" class="form-control" id="projectTeam" wire:model="projectTeam" placeholder="Enter project team">
                            @error('projectTeam') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="projectAttachment">Project Attachment</label>
                            <input type="file" class="form-control" id="projectAttachment" wire:model="projectAttachment">
                            @error('projectAttachment') <span class="text-danger">{{ $message }}</span> @enderror
                        </div> --}}
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save</button>
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
    </style>
@endpush