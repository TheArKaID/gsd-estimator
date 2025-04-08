<?php

use App\Http\Controllers\ProjectsExportController;
use App\Livewire\Dashboard;
use App\Livewire\DetailProject;
use Illuminate\Support\Facades\Route;

Route::get('/', Dashboard::class);
Route::get('/project/{id}', DetailProject::class);
Route::get('/projects/export', [ProjectsExportController::class, 'export'])->name('projects.export');