<?php

use Illuminate\Database\Seeder;

class CuUserBranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cu_user_branch')->truncate();
        $limit = 10;

        for ( $i = 1; $i <= $limit; $i++ ) {
            DB::table('cu_user_branch')->insert([
                'user_id' => $i,
                'customer_id' => $i,
                'customer_branch_id' => $i,
                'customer_user_id' => $i,
                'belong' => 1,
                'create_date' => date('Y-m-d H:i:s'),
                'create_user_id' => rand(1, $limit),
                'create_system_type' => rand(1, 2),
                'update_date' => date('Y-m-d H:i:s'),
                'update_user_id' => rand(1, $limit),
                'update_system_type' => rand(1, 2),
                'status' => true
            ]);
        }
    }
}
