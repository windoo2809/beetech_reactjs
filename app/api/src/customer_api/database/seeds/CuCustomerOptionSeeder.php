<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CuCustomerOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     * @throws Exception
     */
    public function run()
    {
        DB::table('cu_customer_option')->truncate();
        $limit = 100;

        for ( $i = 1; $i <= $limit; $i++ ) {
            DB::table('cu_customer_option')->insert([
                'core_id' => 1,
                'start_date' => date('Y-m-d H:i:s'),
                'end_date' => \Carbon\Carbon::now()->addDay(),
                'plan_type' => 1,
                'admin_user_id' => rand(1, 20),
                'user_lock' => false,
                'data_scope' => rand(0, 2),
                'create_date' => date('Y-m-d H:i:s'),
                'create_user_id' => 1,
                'create_system_type' => rand(1, 2),
                'update_date' => date('Y-m-d H:i:s'),
                'update_user_id' => 1,
                'update_system_type' => rand(1, 2),
                'status' => true,
            ]);
        }
    }
}
