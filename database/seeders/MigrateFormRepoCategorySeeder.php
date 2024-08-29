<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FormRepoCategory;
use DB;

class MigrateFormRepoCategorySeeder extends Seeder
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
            DB::table('form_repo_categories')->truncate();
            $last_id = 1;

            $json = json_decode(\File::get(public_path('assets/jsons/form_repo_categories.json')), true)[0]['data'];
            foreach($json as $key => $data) {
                $var = new FormRepoCategory;
                $var->id = $data['id'];
                $var->form_repo_id = $data['form_repo_id'];
                $var->category_id = $data['category_id'];
                $var->form_json = json_decode($data['form_json'], true);
                $var->form_confirm = json_decode($data['form_confirm'], true);
                $var->form_validation = json_decode($data['form_validation'], true);
                $var->created_at = $data['created_at'];
                $var->updated_at = $data['updated_at'];
                $var->deleted_at = $data['deleted_at'];
                $var->save();

                if ($key === array_key_last($json)) {
                    $last_id = $var->id + 1;
                }
            }
            error_log("SET AUTO INCREMENT TO $last_id");
            DB::update("ALTER TABLE `form_repo_categories` AUTO_INCREMENT = $last_id;");

            error_log("Enabling FOREIGN KEY CHECK");
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Exception $e) {
            error_log($e);
        }
    }
}
