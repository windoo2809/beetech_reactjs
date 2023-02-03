<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CuRoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cu_role')->truncate();
        $limit = 4;
        for ( $i = 0; $i <= $limit; $i++ ) {
            DB::table('cu_role')->insert([
                'role' => $i,
                'role_name' => 'role_' . $i,
            ]);
        }
    }
}
