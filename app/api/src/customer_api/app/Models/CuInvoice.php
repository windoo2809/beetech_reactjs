<?php

namespace App\Models;

use App\Models\Model as AppModel;

/**
 * Class CuInvoice
 * @property integer $invoice_id
 * @property integer $core_id
 * @property integer $project_id
 * @property integer $contract_id
 * @property integer $customer_id
 * @property integer $customer_branch_id
 * @property integer $customer_user_id
 * @property integer $parking_id
 * @property decimal $invoice_amt
 * @property datetime $invoice_closing_date
 * @property datetime $payment_deadline
 * @property decimal $receivable_collect_total_amt
 * @property decimal $receivable_collect_finish_date
 * @property integer $payment_status
 */
class CuInvoice extends AppModel
{
    //
    protected $table = 'cu_invoice';
    /**
     * Define primary key.
     *
     * @var string
     */
    protected $primaryKey = 'invoice_id';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'invoice_id',
        'core_id',
        'project_id',
        'contract_id',
        'customer_id',
        'customer_branch_id',
        'customer_user_id',
        'parking_id',
        'invoice_amt',
        'invoice_closing_date',
        'payment_deadline',
        'receivable_collect_total_amt',
        'receivable_collect_finish_date',
        'payment_status'
    ];
}
