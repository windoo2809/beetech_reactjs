<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CuRole
 * @property integer $role
 * @property integer $role_name
 */
class CuRole extends Model
{
    protected $table = 'cu_role';
    /**
     * Define primary key.
     *
     * @var string
     */
    protected $primaryKey = 'role';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'role',
        'role_name',
    ];
}
