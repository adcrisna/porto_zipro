<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AdiraTransaction;
use DB;

class MigrateAdiraTrxSeeder extends Seeder
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
            DB::table('adira_transactions')->truncate();
            $last_id = 1;

            $json = json_decode(\File::get(public_path('assets/jsons/adira_transactions.json')), true)[0]['data'];
            foreach($json as $key => $data) {
                $var = new AdiraTransaction;
                $var->id = $data['id'];
                $var->order_id = $data['order_id'];
                $var->adira_status = json_decode($data['adira_status'], true);
                $var->adira_response = json_decode($data['adira_response'], true);
                $var->polling_response = json_decode($data['polling_response'], true);
                $var->resubmit_response = json_decode($data['resubmit_response'], true);
                $var->postmikro_response = json_decode($data['postmikro_response'], true);
                $var->post_finish = json_decode($data['post_finish'], true);
                $var->document_policy = json_decode($data['document_policy'], true);
                $var->cover_note = json_decode($data['cover_note'], true);
                $var->status = $data['status'];
                $var->log_api = json_decode($data['log_api'], true);
                $var->request_number = $data['request_number'];
                $var->created_at = $data['created_at'];
                $var->updated_at = $data['updated_at'];
                $var->ref_number = $data['ref_number'];

                $var->save();
                if ($key === array_key_last($json)) {
                    $last_id = $var->id + 1;
                }
            }
            error_log("SET AUTO INCREMENT TO $last_id");
            DB::update("ALTER TABLE `adira_transactions` AUTO_INCREMENT = $last_id;");

            error_log("Enabling FOREIGN KEY CHECK");
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Exception $e) {
            error_log($e);
        }
    }
}
