<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Comission;
use DB;

class MigrateComissionSeeder extends Seeder
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

            error_log("Truncating Table Comission");
            DB::table('comission')->truncate();
            $last_id = 1;

            $json = json_decode(\File::get(public_path('assets/jsons/comission.json')), true)[0]['data'];
            foreach($json as $key => $data) {
                $var = new Comission;
                $var->id = $data['id'];
                $var->trx_id = $data['trx_id'];
                $var->user_id = $data['user_id'];
                $var->formula = $data['formula'];
                $var->base_price = $data['base_price'];
                $var->comission = $data['comission'];
                $var->status = $data['status'];
                $var->username = $data['username'];
                $var->useremail = $data['useremail'];
                $var->created_at = $data['created_at'];
                $var->updated_at = $data['updated_at'];
                $var->save();
                if ($key === array_key_last($json)) {
                    $last_id = $var->id + 1;
                }
            }
            error_log("SET AUTO INCREMENT TO $last_id");
            DB::update("ALTER TABLE `comission` AUTO_INCREMENT = $last_id;");

            error_log("Enabling FOREIGN KEY CHECK");
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Exception $e) {
            error_log($e);
        }
    }
}
