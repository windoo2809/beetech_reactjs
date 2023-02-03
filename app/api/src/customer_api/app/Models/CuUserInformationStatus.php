<?php

namespace App\Models;

use App\Models\Model as AppModel;

/**
 * Class CuUserInformationStatus
 * @property integer $user_id
 * @property integer $information_id
 * @property boolean  $already_read
 * @property timestamp $created_at
 * @property timestamp $updated_at
 */
class CuUserInformationStatus extends AppModel
{
    //
    protected $table = 'cu_user_information_status';
    /**
     * Define primary key.
     *
     * @var string
     */
    protected $primaryKey = ['user_id', 'information_id'];
    public $incrementing = false;
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'information_id',
        'already_read'
    ];
}
