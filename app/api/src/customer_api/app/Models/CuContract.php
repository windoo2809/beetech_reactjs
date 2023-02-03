<?php

namespace App\Models;

use App\Models\Model as AppModel;

/**
 * Class CuContract
 * @property integer    $contract_id
 * @property integer    $core_id
 * @property integer    $project_id
 * @property integer    $estimate_id
 * @property integer    $parking_id
 * @property integer    $branch_id
 * @property integer    $contract_status
 * @property integer    $quote_capacity_qty
 * @property integer    $quote_subtotal_amt
 * @property integer    $quote_tax_amt
 * @property integer    $quote_total_amt
 * @property integer    $purchase_order_register_type
 * @property integer    $purchase_order_check_flag
 * @property datetime   $order_process_date
 * @property datetime   $quote_available_start_date
 * @property datetime   $quote_available_end_date
 * @property integer    $extension_type
 * @property timestamp  $created_at
 * @property timestamp  $updated_at
 */
class CuContract extends AppModel
{
    protected $table = 'cu_contract';
    /**
     * Define primary key.
     *
     * @var string
     */
    protected $primaryKey = 'contract_id';
    /**
     * The attributes that are mass assignable.
     */

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */

    protected $fillable = [
        'contract_id',
        'core_id',
        'project_id',
        'estimate_id',
        'parking_id',
        'branch_id',
        'contract_status',
        'parking_name',
        'parking_name_kana',
        'quote_capacity_qty',
        'quote_subtotal_amt',
        'quote_tax_amt',
        'quote_total_amt',
        'purchase_order_upload_date',
        'purchase_order_register_type',
        'purchase_order_check_flag',
        'purchase_order_check_date',
        'order_process_date',
        'quote_available_start_date',
        'quote_available_end_date',
        'extension_type',
        'update_date'
    ];
}
