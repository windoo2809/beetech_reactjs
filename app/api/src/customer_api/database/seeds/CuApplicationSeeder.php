<?php

use Illuminate\Database\Seeder;

class CuApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cu_application')->truncate();
        $limit = 10;

        for ( $i = 1; $i <= $limit; $i++ ) {
            DB::table('cu_application')->insert([
                'estimate_id' => rand(1, $limit),
                'application_user_id' => rand(1, $limit),
                'application_date' => date('Y-m-d H:i:s'),
                'approval_user_id' => rand(1, $limit),
                'application_status' => rand(1, 3),
                'create_date' => date('Y-m-d H:i:s'),
                'update_date' => date('Y-m-d H:i:s'),
                'status' => 1
            ]);
        }
    }
}
