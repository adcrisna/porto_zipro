<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Profile;
use DB;

class MigrateProfileSeeder extends Seeder
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
            DB::table('profile')->truncate();
            $last_id = 1;

            $json = json_decode(\File::get(public_path('assets/jsons/profile.json')), true)[0]['data'];
            foreach($json as $key => $data) {
                $var = new Profile;
                $var->id = $data['id'];
                $var->user_id = $data['user_id'];
                $var->bank_id = $data['bank_id'];
                $var->address = $data['address'];
                $var->phone = $data['phone'];
                $var->another_phone = $data['another_phone'];
                $var->city = $data['city'];
                $var->bank_account = $data['bank_account'];
                $var->id_card_pic = $data['id_card_pic'];
                $var->avatar = $data['avatar'];
                $var->npwp = $data['npwp'];
                $var->bank_branch = $data['bank_branch'];
                $var->branch_location = $data['branch_location'];
                $var->created_at = $data['created_at'];
                $var->updated_at = $data['updated_at'];
                $var->save();
                if ($key === array_key_last($json)) {
                    $last_id = $var->id + 1;
                }
            }
            error_log("SET AUTO INCREMENT TO $last_id");
            DB::update("ALTER TABLE `profile` AUTO_INCREMENT = $last_id;");

            error_log("Enabling FOREIGN KEY CHECK");
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Exception $e) {
            error_log($e);
        }
    }
}
