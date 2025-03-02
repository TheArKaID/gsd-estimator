<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $global_factor_id
 * @property string $name
 * @property string $description
 * @property float $value
 * @property string $created_at
 * @property string $updated_at
 * @property GlobalFactor $globalFactor
 */
class GlobalFactorCriteria extends Model
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
    protected $fillable = ['name', 'description', 'value', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function globalFactor()
    {
        return $this->belongsTo(GlobalFactor::class);
    }
}
