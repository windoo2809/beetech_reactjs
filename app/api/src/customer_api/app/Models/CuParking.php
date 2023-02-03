<?php

namespace App\Models;

use App\Models\Model as AppModel;

/**
 * Class CuParking
 * @property integer $parking_id
 * @property integer $core_id
 * @property string $parking_name
 * @property string $parking_name_kana
 * @property decimal $latitude
 * @property decimal $longitude
 */
class CuParking extends AppModel
{
    //
    protected $table = 'cu_parking';
    /**
     * Define primary key.
     *
     * @var string
     */
    protected $primaryKey = 'parking_id';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'parking_id',
        'core_id',
        'parking_name',
        'parking_name_kana',
        'latitude',
        'longitude'
    ];
}
