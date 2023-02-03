<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CuMessageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cu_message')->truncate();
        $limit = 200;
        for ( $i = 1; $i <= $limit; $i++ ) {
            if ($i%5 == 1) $project_id = 1;
            if ($i%5 == 2) $project_id = 2;
            if ($i%5 == 3) $project_id = 3;
            if ($i%5 == 4) $project_id = 4;
            if ($i%5 == 5) $project_id = 5;
            DB::table('cu_message')->insert([
                'message_id' => $i,
                'project_id' => $project_id,
                'body' => "body". $project_id,
                'customer_id' => $project_id,
                'file_id' => $i,
                'edit' => true,
                'create_date' => Carbon::now()->format('Y-m-d H:i:s'),
                'create_user_id' => 1,
                'create_system_type' => 2,
                'update_date' => Carbon::now()->format('Y-m-d H:i:s'),
                'update_user_id' => 1,
                'update_system_type' => 2,
                'status' => 1
            ]);
        }
    }
}
