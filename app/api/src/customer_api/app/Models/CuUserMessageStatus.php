<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CuUserMessageStatus
 * @property integer    $user_id
 * @property integer    $message_id
 * @property boolean    $already_read
 */
class CuUserMessageStatus extends Model
{
    protected $table = 'cu_user_message_status';
    /**
     * Define primary key.
     *
     * @var string
     */
    protected $primaryKey = ['user_id', 'message_id'];
    public $incrementing = false;
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'message_id',
        'already_read'
    ];
}
