<?php

use App\Models\CuEstimate;
use App\Models\CuProject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CuContractTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cu_contract')->truncate();
        $limit = 40;
        for ( $i = 1; $i <= $limit; $i++ ) {

            DB::table('cu_contract')->insert([
                'core_id' => "1",
                'project_id' => $i > 20 ? $i - 20 : $i,
                'estimate_id' => $i,
                'parking_id' => rand(1,3),
                'branch_id' => "1",
                'contract_status' => "1",
                'parking_name' => "Imperial Palace Main Gate Iron Bridge",
                'parking_name_kana' => "二重橋",
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
