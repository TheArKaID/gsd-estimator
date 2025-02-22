<?php

namespace App\Livewire;

use App\Models\Project;
use Livewire\Component;

class DetailProject extends Component
{
    public $project;

    function mount($id)
    {
        $this->project = Project::find($id);
    }

    public function render()
    {
        return view('livewire.detail-project');
    }
}
