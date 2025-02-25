<?php

namespace App\Livewire;

use App\Models\Project;
use Livewire\Component;

class DetailProject extends Component
{
    public Project $project;
    public $spName, $spDescription, $spValue, $customSpValue;
    public $projectType;

    function mount($id)
    {
        $this->project = Project::with(['storyPoints'])->find($id);
        $this->projectType = $this->project->project_type;
    }

    public function render()
    {
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

    function saveProjectType()
    {
        $this->validate([
            'projectType' => 'required'
        ]);

        $this->project->update([
            'project_type' => $this->projectType
        ]);

        $this->dispatch('project-type-saved');
    }
}
