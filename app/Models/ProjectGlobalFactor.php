<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $project_id
 * @property string $global_factor_id
 * @property string $global_factor_criteria_id
 * @property string $created_at
 * @property string $updated_at
 * @property Project $project
 * @property GlobalFactor $globalFactor
 * @property GlobalFactorCriteria $globalFactorCriteria
 */
class ProjectGlobalFactor extends Model
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
    protected $fillable = ['project_id', 'global_factor_id', 'global_factor_criteria_id', 'created_at', 'updated_at'];

    /**
     * Get the project that owns the ProjectGSDFactor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the globalFactor that owns the ProjectglobalFactor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function globalFactor()
    {
        return $this->belongsTo(GlobalFactor::class);
    }

    /**
     * Get the globalFactorCriteria that owns the ProjectglobalFactor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function globalFactorCriteria()
    {
        return $this->belongsTo(GlobalFactorCriteria::class);
    }
}
