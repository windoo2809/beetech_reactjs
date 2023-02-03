<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * This class contains shared setup, properties and methods
 * of all application models
 *
 */
class Model extends EloquentModel
{
    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const CREATED_AT = 'create_date';
    const UPDATED_AT = 'update_date';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = [
        'create_user_id',
        'create_system_type',
        'update_date',
        'update_user_id',
        'update_system_type'
    ];
}
