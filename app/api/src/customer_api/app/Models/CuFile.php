<?php

namespace App\Models;

use App\Models\Model as AppModel;

/**
 * Class CuFile
 * @property integer $file_id
 * @property integer $file_type
 * @property integer $file_detail_type
 * @property string  $customer_id
 * @property integer $ref_id
 * @property integer $project_id
 * @property integer $request_id
 * @property integer $estimate_id
 * @property integer $contract_id
 * @property integer $invoice_id
 * @property string  $file_path
 * @property string  $file_name
 * @property Context $remark
 * @property timestamp $created_at
 * @property timestamp $updated_at
 */
class CuFile extends AppModel
{
    protected $table = 'cu_file';
    /**
     * Define primary key.
     *
     * @var string
     */
    protected $primaryKey = 'file_id';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'file_id',
        'file_type',
        'file_detail_type',
        'customer_id',
        'ref_id',
        'project_id',
        'request_id',
        'estimate_id',
        'contract_id',
        'invoice_id',
        'file_path',
        'file_name',
        'remark',
        'create_user_id',
        'create_system_type',
        'update_date',
        'update_user_id',
        'update_system_type',
        'status'
    ];
}
