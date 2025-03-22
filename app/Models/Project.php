<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @property string $id
 * @property string $session_id
 * @property string $name
 * @property string $description
 * @property string $project_type
 * @property string $project_clarity
 * @property string $team_size
 * @property string $velocity
 * @property string $created_at
 * @property string $updated_at
 * @property StoryPoint[] $storyPoints
 * @property GlobalFactor[] $globalFactors
 * @property ProjectGlobalFactor[] $projectGlobalFactors
 */
class Project extends Model
{
    use HasUuids;

    /**
     * The "type" of the auto-incrementing ID.
     * 
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     * 
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = ['session_id', 'name', 'description', 'project_type', 'project_clarity', 'team_size', 'velocity', 'created_at', 'updated_at'];

    /**
     * Get all of the storyPoints for the Project
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function storyPoints(): HasMany
    {
        return $this->hasMany(StoryPoint::class);
    }

    /**
     * Get all of the globalFactors for the Project
     *
     * @return HasManyThrough
     */
    public function globalFactors(): HasManyThrough
    {
        return $this->hasManyThrough(GlobalFactor::class, ProjectGlobalFactor::class, 'project_id', 'id', 'id', 'global_factor_id');
    }

    /**
     * Get all of the projectGlobalFactors for the Project
     *
     * @return HasMany
     */
    public function projectGlobalFactors(): HasMany
    {
        return $this->hasMany(ProjectGlobalFactor::class);
    }
}
