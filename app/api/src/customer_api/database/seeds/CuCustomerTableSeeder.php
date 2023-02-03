<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CuCustomerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cu_customer')->truncate();
        $limit = 10;
        for ( $i = 1; $i <= $limit; $i++ ) {
            DB::table('cu_customer')->insert([
                'customer_id' => $i,
                'core_id' => $i,
                'customer_name' => 'name' . $i,
                'customer_name_kana' => 'name_kana' . $i,
                'construction_number_require_flag' => true,
                'customer_system_use_flag' => true,
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
}
