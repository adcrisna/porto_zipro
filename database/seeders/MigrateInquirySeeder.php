<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\InquiryMv;
use DB;

class MigrateInquirySeeder extends Seeder
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

            error_log("Truncating Table Inquiry");
            DB::table('inquiry_mv')->truncate();

            $last_id = 1;

            $json = json_decode(\File::get(public_path('assets/jsons/inquiry_mv.json')), true)[0]['data'];
            foreach($json as $key => $data) {
                $var = new InquiryMv;
                $var->item = json_decode($data['item'], true);
                $var->total = $data['total'];
                $var->product_id = $data['product_id'];
                $var->order_id = $data['order_id'];
                $var->status = $data['status'];
                $var->data = json_decode($data['data'], true);
                $var->discount = $data['discount'];
                $var->offering_email = $data['offering_email'];
                $var->created_at = $data['created_at'];
                $var->updated_at = $data['updated_at'];
                $var->save();
                if ($key === array_key_last($json)) {
                    $last_id = $var->id + 1;
                }
            }

            error_log("SET AUTO INCREMENT TO $last_id");
            DB::update("ALTER TABLE `inquiry_mv` AUTO_INCREMENT = $last_id;");

            error_log("Enabling FOREIGN KEY CHECK");
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Exception $e) {
            error_log($e);
        }
    }
}
