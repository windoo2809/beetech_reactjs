<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CuCustomerUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cu_customer_user')->truncate();
        $limit = 150;
        for ( $i = 1; $i <= $limit; $i++ ) {
            DB::table('cu_customer_user')->insert([
                'customer_user_id' => $i,
                'customer_id' => $i,
                'customer_branch_id' => $i,
                'core_id' => $i,
                'customer_user_name' => "Beetech Test 0" . $i,
                'customer_user_name_kana' => "カタカナ0" . $i,
                'customer_user_division_name' => "Beetech Division Name Test 0" . $i,
                'customer_user_email' => "beetechtest00" .  $i  .  "@gmail.com",
                'customer_user_tel' => "0123456789012",
                'customer_reminder_sms_flag' => True,
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
