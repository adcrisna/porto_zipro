<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\Cart;
use DB;

class MigrateTransactionSeeder extends Seeder
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

            error_log("Truncating Table Transaction");
            DB::table('transaction')->truncate();
            $last_id = 1;

            $json = json_decode(\File::get(public_path('assets/jsons/transaction.json')), true)[0]['data'];
            foreach($json as $key => $data) {
                $order = Order::find($data['order_id']);
                if(!empty($order)) {
                    $var = new Transaction;
                    $var->id = $data['id'];
                    $var->user_id = $data['user_id'];
                    $var->order_id = $data['order_id'];
                    $var->cart_id = $order->cart_id;
                    $var->trx_data = json_decode($data['trx_data'], true);
                    $var->base_price = $data['base_price'];
                    $var->deduct_price = $data['deduct_price'];
                    $var->total = $data['total'];
                    $var->voucher = $data['voucher'];
                    $var->policy = $data['policy'];
                    $var->expiry_date = $data['expiry_date'];
                    $var->policy_start = $data['policy_start'];
                    $var->policy_end = $data['policy_end'];
                    $var->created_at = $data['created_at'];
                    $var->updated_at = $data['updated_at'];
                    $var->deleted_at = $data['deleted_at'];
                    $var->renew = $data['renew'];
                    $var->save();
                    
                    $cart = Cart::find($order->cart_id);
                    $cart->pg_link = $data['pg_link'];
                    $cart->pg_callback = json_decode($data['pg_callback'], true);
                    $cart->pg_status = $data['pg_status'];
                    $cart->pg_method = $data['pg_method'];
                    $cart->admin_fee = $data['admin_fee'];
                    $cart->is_checkout = 1;
                    $cart->save();

                }

                if ($key === array_key_last($json)) {
                    $last_id = $var->id + 1;
                }
            }
            error_log("SET AUTO INCREMENT TO $last_id");
            DB::update("ALTER TABLE `transaction` AUTO_INCREMENT = $last_id;");

            error_log("Enabling FOREIGN KEY CHECK");
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Exception $e) {
            error_log($e);
        }
    }
}
