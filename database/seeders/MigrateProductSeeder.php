<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use DB;

class MigrateProductSeeder extends Seeder
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

            error_log("Truncating Table Product");
            DB::table('product')->truncate();

            $last_id = 1;
            $json = json_decode(\File::get(public_path('assets/jsons/product.json')), true)[0]['data'];
            // return json_decode($json, true);
            foreach($json as $key => $order) {
                $var = new Product;
                $var->id = $order['id'];
                $var->name = $order['name'];
                $var->display_name = $order['display_name'];
                $var->category_id = $order['category_id'];
                $var->adira_product_id = $order['adira_product_id'];
                $var->description = $order['description'];
                $var->price = $order['price'];
                $var->logo = "logo_20221215072245AEi74.png";
                $var->learn = $order['learn'];
                $var->flow = $order['flow'];
                $var->is_enable = $order['is_enable'];
                $var->deleted_at = $order['deleted_at'];
                $var->is_pg = $order['is_pg'];
                $var->binder_id = $order['binder_id'];
                $var->wording = "polis_asuransi_20221130040747N7MnQ.pdf";
                $var->point = $order['point'];
                $var->period_days = $order['period_days'];
                $var->schema_id = 2;
                $var->schema_ref_id = 1;
                $var->save();
                
                if ($key === array_key_last($json)) {
                    $last_id = $var->id + 1;
                }
            }

            error_log("SET AUTO INCREMENT TO $last_id");
            DB::update("ALTER TABLE `product` AUTO_INCREMENT = $last_id;");

            error_log("Enabling FOREIGN KEY CHECK");
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Exception $e) {
            error_log($e);
        }
    }
}
