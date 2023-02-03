<?php

namespace App\Models;

use App\Models\Model as AppModel;
use Psy\Context;

/**
 * Class CuMessageHistory
 * @property integer $message_id
 * @property integer $project_id
 * @property Context $body
 * @property integer $file_id
 * @property boolean $edit
 */
class CuMessageHistory extends AppModel
{
    protected $table = 'cu_message_history';
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
        'body',
        'file_id',
        'edit'
    ];
}
