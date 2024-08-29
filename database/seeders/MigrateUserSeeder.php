<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use DB;

class MigrateUserSeeder extends Seeder
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

            error_log("Truncating Table User");
            DB::table('users')->truncate();
            $last_id = 1;
            $json = json_decode(\File::get(public_path('assets/jsons/users.json')), true)[0]['data'];
            foreach($json as $key => $data) {
                $var = new User;
                $var->id = $data['id'];
                $var->uuid = $data['uuid'];
                $var->name = $data['name'];
                $var->email = $data['email'];
                $var->password = $data['password'];
                $var->remember_token = $data['remember_token'];
                $var->referrer_email = $data['referrer_email'];
                $var->verified_at = $data['verified_at'];
                $var->partner_id = $data['partner_id'];
                $var->created_at = $data['created_at'];
                $var->updated_at = $data['updated_at'];
                $var->deleted_at = $data['deleted_at'];
                $var->cooldown = $data['cooldown'];
                $var->tax = $data['tax'];
                $var->email_verified_at = $data['email_verified_at'];
                $var->fcm_token = $data['fcm_token'];
                $var->oauth_token = $data['oauth_token'];
                $var->platform = $data['platform'];
                $var->is_production = $data['is_production'];
                $var->version = $data['version'];
                $var->last_login = $data['last_login'];
                $var->is_sf = $data['is_sf'];
                $var->flow = $data['flow'];
                $var->save();
                
                if ($key === array_key_last($json)) {
                    $last_id = $var->id + 1;
                }

            }
            error_log("SET AUTO INCREMENT TO $last_id");
            DB::update("ALTER TABLE `users` AUTO_INCREMENT = $last_id;");
            
            error_log("Enabling FOREIGN KEY CHECK");
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Exception $e) {
            error_log($e);
        }
    }
}
