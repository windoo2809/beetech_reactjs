<?php

namespace App\Models;

use App\Models\Model as AppModel;

/**
 * Class CuBranch
 * @property integer    $branch_id
 * @property integer    $core_id
 * @property string     $branch_name
 * @property string     $prefecture
 * @property string     $city
 * @property string     $address
 * @property string     $tel
 * @property string     $fax
 * @property string     $zip_code
 * @property string     $bank_account
 */
class CuBranch extends AppModel
{
    protected $table = 'cu_branch';
    /**
     * Define primary key.
     *
     * @var string
     */
    protected $primaryKey = 'branch_id';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'branch_id',
        'core_id',
        'branch_name',
        'prefecture',
        'city',
        'address',
        'tel',
        'fax',
        'zip_code',
        'bank_account'
    ];
}
