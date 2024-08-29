<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Cart;
use App\Http\Controllers\CartController;

class MikroController extends Controller
{
    public function addToCart(Request $request)
    {
        // return $request;
        if (!empty($request->cart['id'])) {
            $getCart = Cart::find($request->cart['id']);
            // return $getCart;
            if (!empty($getCart) && $getCart->is_ref !== $request->is_ref) {
                return response([
                    "status" => false,
                    "message" => "Keranjang Referal tidak dapat dimasukan Product non Referal",
                ], 500);
            }
            if (!empty($getCart) && $getCart->is_checkout == 1) {
                return response([
                    "status" => false,
                    "message" => "Keranjang Sudah Mengisi Data Penutupan",
                ], 500);
            }
            if (!empty($getCart) && @$getCart->data[0]['product_data']['name'] == "ZTI") {
                return response([
                    "status" => false,
                    "message" => "Keranjang Sudah Mengisi Data Penutupan",
                ], 500);
            }
        }
        $product = Product::find($request->product_id);
        $data['product_id'] = $product->id;
        $data['total'] = $product->price;
        $data['inquiry_id'] = null;
        $cart = CartController::insert(json_decode(json_encode($data)), $request->cart);



        $getResponseCart = json_decode(json_encode($cart, true), true)['original'];
        if ($getResponseCart['status'] == false) {
            return response([
                "status" => false,
                "message" => $getResponseCart['message']
            ], 500);
        }

        return response([
            'status' => true,
            'cart' => $getResponseCart['cart'],
        ], 200);
    }
}
