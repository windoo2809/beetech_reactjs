<?php

namespace App\Models;

use App\Models\Model as AppModel;

/**
 * Class CuRequestParking
 * @property integer $request_id
 * @property integer $parking_id
 */
class CuRequestParking extends AppModel
{
    //
    protected $table = 'cu_request_parking';
    /**
     * Define primary key.
     *
     * @var string
     */
    protected $primaryKey = ['request_id', 'parking_id'];
    public $incrementing = false;
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'request_id',
        'parking_id',
        'create_user_id',
        'create_system_type',
        'update_date',
        'update_user_id',
        'update_system_type',
        'status',
    ];
}
