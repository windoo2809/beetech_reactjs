<?php

namespace App\Models;

use App\Models\Model as AppModel;
/**
 * Class CuCustomer
 * @property integer    $customer_id
 * @property integer    $core_id
 * @property string     $customer_name
 * @property string     $customer_name_kana
 * @property boolean    $construction_number_require_flag
 * @property boolean    $use_cu
 */
class CuCustomer extends AppModel
{
    protected $table = 'cu_customer';
    /**
     * Define primary key.
     *
     * @var string
     */
    protected $primaryKey = 'customer_id';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'customer_id',
        'core_id',
        'customer_name',
        'customer_name_kana',
        'construction_number_require_flag',
        'use_cu'
    ];
}
