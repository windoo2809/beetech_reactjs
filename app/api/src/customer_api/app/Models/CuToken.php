<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CuToken
 * @property integer $token_id
 * @property integer $user_id
 * @property string  $token
 * @property datetime  $expire
 */
class CuToken extends Model
{
    protected $table = 'cu_token';
    /**
     * Define primary key.
     *
     * @var string
     */
    protected $primaryKey = 'token_id';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'token_id',
        'user_id',
        'token',
        'expire'
    ];
    public $timestamps = false;
}
