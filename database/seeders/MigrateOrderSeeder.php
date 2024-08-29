<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Cart;
use App\Models\Product;
use App\Models\InquiryMv;
use App\Http\Controllers\CartController;
use DB;


class MigrateOrderSeeder extends Seeder
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

            error_log("Truncating Table Orders and Cart");
            DB::table('orders')->truncate();
            DB::table('cart')->truncate();

            $last_id = 1;

            $json = json_decode(\File::get(public_path('assets/jsons/orders.json')), true)[0]['data'];
            foreach($json as $key => $data) {
                
                $product = Product::find($data['product_id']);
                $cart_arr['id'] = null;
                $cart_arr['name'] = $product->name;

                if($product->flow == "mv" || $product->flow == "moto") {
                    $inquiry = InquiryMv::where('order_id', $data['id'])->first();
                    if(!empty($inquiry)) {
                        $cart = CartController::insert($inquiry, $cart_arr, $data['user_id']);
                        $getResponseCart = json_decode(json_encode($cart, true), true)['original'];
                    }else {
                        $getResponseCart['status'] = false;
                    }
                }else {
                    $arg['product_id'] = $product->id;
                    $arg['total'] = $product->price;
                    $arg['inquiry_id'] = null;
                    $cart = CartController::insert(json_decode(json_encode($arg)), $cart_arr, $data['user_id']);
                    $getResponseCart = json_decode(json_encode($cart, true), true)['original'];
                }

                if($getResponseCart['status'] !== false) {
                    $var = new Order;
                    $var->id = $data['id'];
                    $var->user_id = $data['user_id'];
                    $var->transaction_id = $data['transaction_id'];
                    $var->product_id = $data['product_id'];
                    $var->cart_id = $getResponseCart['cart']['id'];
                    $var->data = json_decode($data['data'], true);
                    $var->base_price = $data['base_price'];
                    $var->total = $data['total'];
                    $var->coupon = $data['coupon'];
                    $var->additional_data = json_decode($data['additional_data'], true);
                    $var->status = $data['status'];
                    $var->note = $data['note'];
                    $var->validation = $data['validation'];
                    $var->start_date = $data['start_date'];
                    $var->end_date = $data['end_date'];
                    $var->is_offering = $data['is_offering'];
                    $var->is_submit = $data['is_submit'];
                    $var->offering_email = $data['offering_email'];
                    $var->offering_telp = $data['offering_telp'];
                    $var->offering_name = $data['offering_name'];
                    $var->created_at = $data['created_at'];
                    $var->updated_at = $data['updated_at'];
                    $var->deleted_at = $data['deleted_at'];
                    $var->deduct = $data['deduct'];
                    $var->save();

                    $editCart = Cart::find($getResponseCart['cart']['id']);
                    $editCart->offering_email = $data['offering_email'];
                    $editCart->offering_telp = $data['offering_telp'];
                    $editCart->offering_name = $data['offering_name'];
                    $editCart->is_offering = $data['is_offering'];
                    $editCart->save();
                }

                if ($key === array_key_last($json)) {
                    $last_id = $var->id + 1;
                }
            }

            error_log("SET AUTO INCREMENT TO $last_id");
            DB::update("ALTER TABLE `orders` AUTO_INCREMENT = $last_id;");

            error_log("Enabling FOREIGN KEY CHECK");
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Exception $e) {
            error_log($e);
        }
    }
}
