<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CuRequestTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cu_request')->truncate();
        $limit = 40;
        for ( $i = 1; $i <= $limit; $i++ ) {
            DB::table('cu_request')->insert([
                'project_id' => $i > 20 ? $i - 20 : $i,
                'request_date' => Carbon::now()->format('Y-m-d H:i:s'),
                'estimate_deadline' => Carbon::now()->format('Y-m-d H:i:s'),
                'want_start_date' => Carbon::now()->format('Y-m-d H:i:s'),
                'want_end_date' => Carbon::now()->format('Y-m-d H:i:s'),
                'request_type' => "1",
                'car_qty' => "1",
                'light_truck_qty' => "1",
                'truck_qty' => "1",
                'other_car_qty' => "1",
                'other_car_detail' => null,
                'request_status' => 1,
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
