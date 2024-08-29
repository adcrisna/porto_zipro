<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FormRepo;
use DB;

class MigrateFormRepoSeeder extends Seeder
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

            error_log("Truncating Table Form Repo");
            DB::table('form_repo')->truncate();
            $last_id = 1;

            $json = json_decode(\File::get(public_path('assets/jsons/form_repo.json')), true)[0]['data'];
            foreach($json as $key => $data) {
                $var = new FormRepo;
                $var->id = $data['id'];
                $var->name = $data['name'];
                $var->lang = json_decode($data['lang'], true);
                $var->value = $data['value'];
                $var->validate_link = $data['validate_link'];
                $var->form_type = $data['form_type'];
                $var->created_at = $data['created_at'];
                $var->updated_at = $data['updated_at'];
                $var->save();

                if ($key === array_key_last($json)) {
                    $last_id = $var->id + 1;
                }
            }
            error_log("SET AUTO INCREMENT TO $last_id");
            DB::update("ALTER TABLE `form_repo` AUTO_INCREMENT = $last_id;");

            error_log("Enabling FOREIGN KEY CHECK");
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Exception $e) {
            error_log($e);
        }
    }
}
