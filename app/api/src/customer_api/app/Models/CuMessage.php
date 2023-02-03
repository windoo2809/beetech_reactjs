<?php

namespace App\Models;

use App\Models\Model as AppModel;
use Psy\Context;

/**
 * Class CuMessage
 * @property integer $message_id
 * @property integer $project_id
 * @property integer $core_id
 * @property integer $customer_id
 * @property Context $body
 * @property integer $file_id
 * @property boolean $edit
 */
class CuMessage extends AppModel
{
    //
    protected $table = 'cu_message';
    /**
     * Define primary key.
     *
     * @var string
     */
    protected $primaryKey = 'message_id';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'message_id',
        'project_id',
        'core_id',
        'customer_id',
        'body',
        'file_id',
        'edit',
        'create_user_id',
        'create_system_type',
        'update_date',
        'update_user_id',
        'update_system_type',
    ];
}
