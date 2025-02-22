<?php

namespace App\Livewire;

use App\Models\Project;
use Livewire\Component;

class DetailProject extends Component
{
    public Project $project;
    public $spName, $spDescription, $spValue;

    function mount($id)
    {
        $this->project = Project::with(['storyPoints'])->find($id);
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
            'spValue' => 'required|numeric'
        ]);

        $this->project->storyPoints()->create([
            'name' => $this->spName,
            'description' => $this->spDescription,
            'value' => $this->spValue
        ]);

        $this->spName = '';
        $this->spDescription = '';
        $this->spValue = '';

        $this->dispatch('story-point-added');
    }

    public function deleteStoryPoint($id)
    {
        $this->project->storyPoints()->find($id)->delete();

        $this->dispatch('story-point-deleted', $id);
    }
}
