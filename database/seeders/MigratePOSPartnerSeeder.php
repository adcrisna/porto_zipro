<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\POSPartner;
use DB;

class MigratePOSPartnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            error_log("Disabling FOREIGN KEY CHECK");
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            error_log("Truncating Table POS Partner");
            DB::table('pos_partners')->truncate();

            $last_id = 1;

            $json = json_decode(\File::get(public_path('assets/jsons/pos_partners.json')), true)[0]['data'];
            foreach($json as $key => $data) {
                $var = new POSPartner;
                $var->id = $data['id'];
                $var->partner_id = $data['partner_id'];
                $var->name = $data['name'];
                $var->code = $data['code'];
                $var->created_at = $data['created_at'];
                $var->updated_at = $data['updated_at'];
                $var->deleted_at = $data['deleted_at'];
                $var->save();
                if ($key === array_key_last($json)) {
                    $last_id = $var->id + 1;
                }
            }

            error_log("SET AUTO INCREMENT TO $last_id");
            DB::update("ALTER TABLE `pos_partners` AUTO_INCREMENT = $last_id;");

            error_log("Enabling FOREIGN KEY CHECK");
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Exception $e) {
            error_log($e);
        }
    }
}
