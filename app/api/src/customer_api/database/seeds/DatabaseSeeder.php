<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CuCustomerUserTableSeeder::class);
        $this->call(CuCustomerBranchTableSeeder::class);
        $this->call(CuCustomerTableSeeder::class);
        $this->call(CuCustomerTableSeeder::class);
        $this->call(CuProjectTableSeeder::class);
        $this->call(CuParkingTableSeeder::class);
        $this->call(CuSubcontractTableSeeder::class);
        $this->call(CuEstimateTableSeeder::class);
        $this->call(CuContractTableSeeder::class);
        $this->call(CuRequestTableSeeder::class);
        $this->call(CuFileTableSeeder::class);
        $this->call(CuInformationTableSeeder::class);
        $this->call(CuMessageTableSeeder::class);
        $this->call(CuInvoiceTableSeeder::class);
        $this->call(CuAddressSeeder::class);
        $this->call(CuRoleTableSeeder::class);
        $this->call(CuApplicationSeeder::class);
        $this->call(CuCustomerOptionSeeder::class);
        $this->call(CuUserBranchSeeder::class);
        $this->call(CuRequestParkingSeeder::class);
        $this->call(CuUserMessageStatusTableSeeder::class);
        $this->call(CuUserDataTestTableSeeder::class);
        $this->call(CuUserDataTableSeeder::class);
    }
}
