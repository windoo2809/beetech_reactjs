<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CuInvoiceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cu_invoice')->truncate();
        $limit = 10;
        for ( $i = 1; $i <= $limit; $i++ ) {
            DB::table('cu_invoice')->insert([
                'invoice_id' => $i,
                'core_id' => $i,
                'project_id' => $i,
                'contract_id' => $i,
                'customer_id' => 1,
                'customer_branch_id' => 1,
                'customer_user_id' => 1,
                'parking_id' => 1,
                'invoice_amt' => 120000,
                'invoice_closing_date' => '2021-04-18 00:00:00',
                'payment_deadline' => '2021-05-18 00:00:00',
                'receivable_collect_total_amt' => 130000,
                'receivable_collect_finish_date' => '2021-04-18 00:00:00',
                'payment_status' => 1,
                'reminder' => 1,
                'create_date' => Carbon::now()->format('Y-m-d H:i:s'),
                'create_user_id' => 1,
                'create_system_type' => rand(1, 2),
                'status' => 1
            ]);
        }
    }
}
