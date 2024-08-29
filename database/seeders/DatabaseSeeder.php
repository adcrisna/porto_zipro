<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        // $this->call(AIOSeeder::class);
        $this->call(MigrateFormRepoSeeder::class);
        $this->call(MigrateFormRepoCategorySeeder::class);
        $this->call(MigrateUserSeeder::class);
        $this->call(MigrateProfileSeeder::class);
        $this->call(MigrateInquirySeeder::class);
        $this->call(MigrateProductSeeder::class);
        $this->call(MigratePartnerSeeder::class);
        $this->call(MigrateProductPartnerSeeder::class);
        $this->call(MigratePOSPartnerSeeder::class);
        $this->call(MigrateOrderSeeder::class);
        $this->call(MigrateTransactionSeeder::class);
        $this->call(MigrateAdiraTrxSeeder::class);
        $this->call(MigrateAdiraComissionSeeder::class);
        $this->call(MigrateComissionSeeder::class);


        
    }
}
