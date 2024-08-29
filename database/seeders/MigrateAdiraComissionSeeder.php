<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AdiraComission;
use DB;

class MigrateAdiraComissionSeeder extends Seeder
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

            error_log("Truncating Table Profile");
            DB::table('adira_comissions')->truncate();
            $last_id = 1;

            $json = json_decode(\File::get(public_path('assets/jsons/adira_commissions.json')), true)[0]['data'];
            foreach($json as $key => $data) {
                $var = new AdiraComission;
                $var->id = $data['id'];
                $var->transaction_id = $data['transaction_id'];
                $var->comission = $data['commission'];
                $var->discount = $data['discount'];
                $var->share_comission = $data['share_commission'];
                $var->created_at = $data['created_at'];
                $var->updated_at = $data['updated_at'];
                $var->save();
                if ($key === array_key_last($json)) {
                    $last_id = $var->id + 1;
                }
            }
            error_log("SET AUTO INCREMENT TO $last_id");
            DB::update("ALTER TABLE `adira_comissions` AUTO_INCREMENT = $last_id;");

            error_log("Enabling FOREIGN KEY CHECK");
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Exception $e) {
            error_log($e);
        }
    }
}
