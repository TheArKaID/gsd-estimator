<?php

namespace App\Livewire;

use App\Models\Project;
use Livewire\Component;

class Dashboard extends Component
{
    public $newProjectName = '';
    public $newProjectDescription = '';

    public function render()
    {
        return view('livewire.dashboard');
    }

    function saveNewProject()
    {
        $this->validate([
            'newProjectName' => 'required',
            'newProjectDescription' => 'required',
        ]);

        $project = new Project();
        $project->name = $this->newProjectName;
        $project->description = $this->newProjectDescription;
        $project->save();

        $this->newProjectName = '';
        $this->newProjectDescription = '';
    }
}
