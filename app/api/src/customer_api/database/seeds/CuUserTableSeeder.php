<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CuUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cu_user')->truncate();
        $limit = 1;
        for ( $i = 1; $i <= $limit; $i++ ) {
            DB::table('cu_user')->insert([
                'user_id' => $i,
                'customer_id' => $i,
                'login_id' => DB::table('cu_customer_user')->first()->customer_user_email ?? 'beetechtest@test.test',
                'password' => Hash::make('landmark@123'),
                'user_lock' => false,
                'access_flg' => false,
                'role' => 4,
                'customer_user_name' => "Beetech Test 0" . $i,
                'customer_user_name_kana' => "カタカナ0" . $i,
                'customer_reminder_sms_flag' => false,
                'customer_user_tel' => "0123456789012",
                'change_login_id' => null,
                'create_date' => Carbon::now()->format('Y-m-d H:i:s'),
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
