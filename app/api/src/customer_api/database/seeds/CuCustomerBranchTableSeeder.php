<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CuCustomerBranchTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cu_customer_branch')->truncate();
        $limit = 150;
        for ( $i = 1; $i <= $limit; $i++ ) {
            DB::table('cu_customer_branch')->insert([
                'customer_branch_id' => $i,
                'customer_id' => $i,
                'core_id' => $i,
                'customer_branch_name' => "BeetechTest001" . $i,
                'customer_branch_name_kana' => "カタカナ0" . $i,
                'create_date' => \Carbon\Carbon::now()->format('Y-m-d H:i:s'),
                'create_user_id' => rand(0, $limit),
                'create_system_type' => rand(1, 2),
                'update_date' => Carbon::now()->format('Y-m-d H:i:s'),
                'update_user_id' => rand(0, $limit),
                'update_system_type' => rand(1, 2),
                'status' => 1
            ]);
        }
    }
}
