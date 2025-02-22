<?php

use App\Livewire\Dashboard;
use App\Livewire\DetailProject;
use Illuminate\Support\Facades\Route;

Route::get('/', Dashboard::class);
Route::get('/project/{id}', DetailProject::class);