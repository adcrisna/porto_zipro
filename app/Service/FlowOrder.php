<?php

namespace App\Service;

use GuzzleHttp\Client;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Support\Str;

class FlowOrder
{
    static public function getStatus($cart_id)
    {
        $orders = Order::where('cart_id', $cart_id)->get();
        $cart = Cart::find($cart_id);
        $result['status'] = true;
        $result['message'] = "success";
        $result['data'] = [
            "webview" => false,
            "webview_link" => null
        ];
        $count_order = count($orders);
        $null_data = 0;
        if($count_order > 0) {
            foreach ($orders as $kOrder => $order) {
                if($order->data == null) {
                    $null_data = $null_data + 1;
                    if($order->product->flow == "web") {
                        $dname = strtoupper($order->product->name);
                        if(Str::contains($dname, ['TRAVEL', 'TRAVELLING', 'TRAVELLIN', 'ZTI'])) {
                            $result['data'] = [
                                "webview" => true,
                                "webview_link" => route('travel.penutupan', $order->id)
                            ];
                        } elseif (Str::contains($dname, ['PA MUDIK', 'MUDIK', "PERJALANAN", "PA PERJALANAN", "PERSONAL", "ACCIDENT"])) {
                            $result['data'] = [
                                "webview" => true,
                                "webview_link" => route('perjalanan.index')
                            ];
                        }
                    }
                }
            }
        }
        if($count_order !== count($cart->data)) {
            $result['status'] = false;
            $result['message'] = "Terdapat data yang belum di isi";
        }

        return [
            "null_data" => $null_data,
            "count_order" => $count_order,
            "result" => $result
        ];
    }
}
