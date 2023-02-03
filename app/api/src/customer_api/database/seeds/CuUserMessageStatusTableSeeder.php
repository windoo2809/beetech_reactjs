<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CuUserMessageStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cu_user_message_status')->truncate();
        $limit = 20;
        for ( $i = 1; $i <= $limit; $i++ ) {
            if ($i%3 == 1) $project_id = 1;
            if ($i%3 == 2) $project_id = 2;
            if ($i%3 == 0) $project_id = 3;
            DB::table('cu_user_message_status')->insert([
                'user_id' => $project_id,
                'message_id' => $i,
                'already_read' => FALSE,
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
