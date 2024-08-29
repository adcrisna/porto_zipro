<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Partner;
use App\Models\Bank;
use DB;

class AIOSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Log::info("Disabling FOREIGN KEY CHECK");
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        \Log::notice("Truncating all Tables");
        DB::table('partners')->truncate();
        DB::table('bank')->truncate();

        \Log::notice("Inserting the data");

        Bank::create([
            "name" => "BCA",
        ]);
        Partner::create([
            "name" => "Dealer SA",
            "email" => "sa@gmail.com",
            "mo" => "KTO"
        ]);


        \Log::info("Enabling FOREIGN KEY CHECK");
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
