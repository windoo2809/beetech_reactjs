<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CuFileTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cu_file')->truncate();
        DB::table('cu_file')->insert([
            'file_type' => "1",
            'customer_id' => "1",
            'ref_id' => '1',
            'project_id' => '1',
            'request_id' => '1',
            'estimate_id' => '1',
            'contract_id' => '1',
            'invoice_id' => '1',
            'file_path' => 'duong link 1',
            'file_name' => 'ten file 1',
            'remark' => '139.75359899364472',
            'create_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'create_user_id' => 1,
            'create_system_type' => rand(1, 2),
            'update_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'update_user_id' => 1,
            'update_system_type' => rand(1, 2),
            'status' => 1
        ]);
        DB::table('cu_file')->insert([
            'file_type' => "1",
            'customer_id' => "1",
            'ref_id' => '1',
            'project_id' => '1',
            'request_id' => '1',
            'estimate_id' => '1',
            'contract_id' => '1',
            'invoice_id' => '1',
            'file_path' => 'duong link 2',
            'file_name' => 'ten file 2',
            'remark' => '139.75359899364472',
            'create_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'create_user_id' => 1,
            'create_system_type' => rand(1, 2),
            'update_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'update_user_id' => 1,
            'update_system_type' => rand(1, 2),
            'status' => 1
        ]);
        DB::table('cu_file')->insert([
            'file_type' => "1",
            'customer_id' => "1",
            'ref_id' => '1',
            'project_id' => '1',
            'request_id' => '1',
            'estimate_id' => '1',
            'contract_id' => '1',
            'invoice_id' => '1',
            'file_path' => 'duong link 3',
            'file_name' => 'ten file 3',
            'remark' => '139.75359899364472',
            'create_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'create_user_id' => 1,
            'create_system_type' => rand(1, 2),
            'update_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'update_user_id' => 1,
            'update_system_type' => rand(1, 2),
            'status' => 1
        ]);
    }
}
