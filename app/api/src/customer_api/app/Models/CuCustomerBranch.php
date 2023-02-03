<?php

namespace App\Models;

use App\Models\Model as AppModel;

/**
 * Class CuCustomerBranch
 * @property integer    $customer_branch_id
 * @property integer    $customer_id
 * @property integer    $core_id
 * @property string     $customer_branch_name
 * @property string     $customer_branch_name_kana
 * @property string     $zip
 * @property string     $site_prefecture
 * @property string     $site_city
 * @property string     $site_address
 */
class CuCustomerBranch extends AppModel
{
    protected $table = 'cu_customer_branch';
    /**
     * Define primary key.
     *
     * @var string
     */
    protected $primaryKey = 'customer_branch_id';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'customer_branch_id',
        'customer_id',
        'core_id',
        'customer_branch_name',
        'customer_branch_name_kana',
        'zip',
        'prefecture',
        'city',
        'address'
    ];


    public function scopeSelectCommon($query)
    {
        return $query->select(
            'cu_customer_branch.customer_branch_id', 
            'cu_customer_branch.customer_id', 
            'cu_customer_branch.customer_branch_name', 
            'cu_customer_branch.customer_branch_name_kana', 
            'cu_customer_branch.zip', 
            'cu_customer_branch.prefecture', 
            'cu_customer_branch.city', 
            'cu_customer_branch.address'
        );
    }
}
