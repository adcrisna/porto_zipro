<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\AdiraTransaction;
use App\Jobs\PosMikroMail;
use Exception, DB, Validator;
use App\Jobs\PerjalananJob;
use Illuminate\Validation\Rule;
use App\Models\Product;
use App\Models\Cart;

class PerjalananController extends Controller
{
    public function list()
    {
        $carts = Cart::select(['id', 'name', 'total', 'data', 'is_checkout'])
            ->where('user_id', auth('api')
                ->user()->id)->where('is_ref', 1)->where('data', null)
            ->whereNull('pg_status')->orderBy('id', 'DESC')
            ->get();

        if (count($carts) == 0) {
            return response([
                "status" => false,
                "message" => "Data not found"
            ], 404);
        }

        foreach ($carts as $key => $cart) {
            $carts[$key]['status_order'] = null;
            $product = [];
            $cartData = $cart->data ?? [];
            $orders = Order::where('cart_id', $cart->id)->get();
            foreach ($cartData as $keyBody => $cartbody) {
                unset($carts[$key]['data']);
                if (!empty($cartbody['product_data'])) $product[] = $cartbody['product_data']['name'];
            }
            $or = [];
            foreach ($orders as $order) {
                $or[] = $order;
            }
            $get_status = array_column($or, 'status');
            if (in_array(0, $get_status)) {
                $carts[$key]['status_order'] = 0;
            } elseif (!in_array(0, $get_status) && in_array(1, $get_status)) {
                $carts[$key]['status_order'] = 1;
            } elseif (!in_array(0, $get_status) && !in_array(1, $get_status) && in_array(2, $get_status)) {
                $carts[$key]['status_order'] = 2;
            }

            $carts[$key]["products"] = $product;
        }
        return $carts;
    }

    public function index(Request $request)
    {
        $token = $request->bearerToken();
        // $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiN2U3OWExZWZhYWQ3ZTU0MjgzZjM4MGZlNGIwYjM0ZjhhM2MxYTAxMjcxNTA1ZjI2NjM4ZWI4ZWYxZDYzZWE5ZmZlYzg2MTk0MDRiOTRhYjkiLCJpYXQiOjE3MTM3Njg0NTcuMjMxNzMzLCJuYmYiOjE3MTM3Njg0NTcuMjMxNzM3LCJleHAiOjE3NDUzMDQ0NTcuMjIwNDgsInN1YiI6IjcxMzciLCJzY29wZXMiOltdfQ.T1w6nDbXsL3Eh6NzXKvEtL0rtumvB1vG4ZVD5ALpjQfYQXAUXmk6lxG-G2dnuK0swobaJg1ToHMicfKVkjVD7Ko_JuaE1ShcxbEao4GsTRK3ukF3teOST6045oF8-1kraSd5sZGsCwEiMkh1CHSJG0gdoJfYWZax0oX4a8K_XZq5wZBRLzM7y0zhANg2Bymkx8QszDVBm_NXRQjHqhNLw_EnFOmM3ynyewLmNkJ0JID9WdQ7VagAFPyzR85-qYrXlLfSvQlr1wq7YoYxZH51CHuQGP8PZN9hJMlNiMQfYJUZnM7IAMUVdVsbQ-4waAHeM9ZAeODBlpqMexPPD4yNi0Rv983kw-RQEQgmqTwuSdGGJqmmgRCyi4jhmFr0nAm20IQqyEZiSPoBCLLK60DTjFOtTYY4grtWznipFP6ggNkFCzbT8AyPnHpusYBulykUbXhpnstyeUoQIGIl4u6lcKYkcxQ3_VnCBYB2nuGkQ8xqQC2hg1RtGYqZkDsDH6us7h86ejg8i-liIgyR-WIgOpQxwRPJDmA76hYjyoPiJYWA6Jzo2u_x_No4Jwyhiz4sxZlFLqs99sA413gTlXPoe4oF_7LAaXKhqJ7XRk3umBsCL_vxaxRGg8KlAA8dKqmzxyRoUqgN33Sf1g-tdbb2WmaeWynhlWEVEHJ7SfwRTYk";
        if (empty($token)) {
            return response([
                "status" => false,
                "message" => "Unauthorized"
            ], 401);
        }
        $product_id = $request->get('product_id');
        // $product_id = 345;
        if (empty($product_id)) {
            return response([
                "status" => false,
                "message" => "invalid request data"
            ], 500);
        }
        // $list =  $this->list();
        $list = null;
        if (!empty($request->get('cart_id'))) {
            $cartID = $request->get('cart_id');
            $checkCart = Cart::find($cartID);
            if (!empty($checkCart)) {
                $list = $checkCart;
            }else{
                $list = null;
            }
        }
        // return $list;
        return view('perjalanan.index', compact('list', 'token', 'product_id'));
    }

    public function success(Request $request)
    {
        return view('perjalanan.success');
    }

    public function getFormattedForm($data)
    {

        $insurer = [
            "1" => [
                "type" => "text",
                "data" => $data['nama'],
                "name" => "NAMA TERTANGGUNG"
            ],
            "33" => [
                "type" => "number",
                "data" => $data['passport'],
                "name" => "NOMOR IDENTITAS (KTP)"
            ],
            "5" => [
                "type" => "text",
                "data" => date("Y-m-d", strtotime($data['tanggalLahir'])) . " 00:00:00.000",
                "name" => "TANGGAL LAHIR"
            ],
            "9" => [
                "type" => "text",
                "data" => $data['email'],
                "name" => "EMAIL"
            ],
        ];

        return $insurer;
    }

    public function submit(Request $request)
    {
        // return $request->listCart;
        //return $request->namaPasangan;
        try {
            $validator = Validator::make($request->all(), [
                "startDate" => "required",
                "jenisKendaraan" => "required",
                "asal" => "required",
                "tujuan" => "required",
                "nama" => "required",
                "passport" => "required",
                "tanggalLahir" => "required",
                "email" => "required|email",
                "namaPasangan" => Rule::requiredIf(!empty($request->passportPasangan) || !empty($request->tanggalLahirPasangan)),
                "passportPasangan" => Rule::requiredIf(!empty($request->namaPasangan)),
                "tanggalLahirPasangan" => Rule::requiredIf(!empty($request->namaPasangan)),
                "namaAnak1" => Rule::requiredIf(!empty($request->passportAnak1) || !empty($request->tanggalLahirAnak1)),
                "passportAnak1" => Rule::requiredIf(!empty($request->namaAnak1)),
                "tanggalLahirAnak1" => Rule::requiredIf(!empty($request->namaAnak1)),
                "namaAnak2" => Rule::requiredIf(!empty($request->passportAnak2) || !empty($request->ttanggalLahirAnak2)),
                "passportAnak2" => Rule::requiredIf(!empty($request->namaAnak2)),
                "tanggalLahirAnak2" => Rule::requiredIf(!empty($request->namaAnak2)),
                // "listCart" => Rule::requiredIf(empty($request->newCart)),
                "newCart" => Rule::requiredIf(empty($request->listCart)),
                "alamat" => "required",
            ], [
                "startDate.required" => ":attribute wajib diisi",
                "jenisKendaraan.required" => ":attribute wajib diisi",
                "asal.required" => ":attribute wajib diisi",
                "tujuan.required" => ":attribute wajib diisi",
                "nama.required" => ":attribute wajib diisi",
                "passport.required" => ":attribute wajib diisi",
                "tanggalLahir.required" => ":attribute wajib diisi",
                "email.required" => ":attribute wajib diisi",
                "email.email" => ":attribute tidak valid",
                "namaPasangan.required" => ":attribute wajib di isi",
                "passportPasangan.required" => ":attribute wajib di isi",
                "tanggalLahirPasangan.required" => ":attribute wajib di isi",
                "namaAnak1.required" => ":attribute wajib di isi",
                "passportAnak1.required" => ":attribute wajib di isi",
                "tanggalLahirAnak1.required" => ":attribute wajib di isi",
                "namaAnak2.required" => ":attribute wajib di isi",
                "passportAnak2.required" => ":attribute wajib di isi",
                "tanggalLahirAnak2.required" => ":attribute wajib di isi",
                // "listCart.required" => ":attribute wajib di isi",
                "newCart.required" => ":attribute wajib di isi",
                "alamat.required" => ":attribute wajib di isi",
            ]);

            if ($validator->fails()) {
                return response([
                    "status" => false,
                    "message" => "Errors Validator",
                    "data" => $validator->errors()
                ], 422);
            }
            $request['salvus_product_id'] = $request->salvus_product_id;

            $insurer = $this->getFormattedForm($request->all());
            // return $insurer;
            DB::beginTransaction();

            
            if (!empty($request->listCart)) {
                $cart = Cart::find($request->listCart);
                if ($cart->is_ref != 1) {
                    return response([
                        "status" => false,
                        "message" => "Produk ini harus menggunakan Keranjang Baru"
                    ], 500);
                }else{
                    $product = Product::find($request['salvus_product_id']);
                    $data['product_id'] = $product->id;
                    $data['total'] = $request->jenisKendaraan == "Asuransi Motor" ? 35000 : 75000;
                    $data['inquiry_id'] = null;
                    $cart = CartController::insert(json_decode(json_encode($data)), $cart);
                }
                // return $cart;
            } else {
                $cart_arr['id'] = null;
                $cart_arr['name'] = $request->newCart;

                $product = Product::find($request['salvus_product_id']);
                $data['product_id'] = $product->id;
                $data['total'] = $request->jenisKendaraan == "Asuransi Motor" ? 35000 : 75000;
                $data['inquiry_id'] = null;
                $cart = CartController::insert(json_decode(json_encode($data)), $cart_arr);
            }

            $getResponseCart = json_decode(json_encode($cart, true), true)['original'];
            // return $getResponseCart;
            $orders = new Order;
            $orders->user_id = auth('api')->user()->id;
            $orders->product_id = $request['salvus_product_id'];
            $orders->data = $insurer;
            $orders->base_price = $request->jenisKendaraan == "Asuransi Motor" ? 35000 : 75000;
            $orders->deduct = 0;
            $orders->total = $request->jenisKendaraan == "Asuransi Motor" ? 35000 : 75000;
            $orders->additional_data = [$request->all()];
            $orders->start_date = date('Y-m-d', strtotime($request->startDate));
            $orders->end_date = date('Y-m-d', strtotime($request->startDate.'+ 1 year'));
            $orders->cart_id = $getResponseCart['cart']['id'];
            $orders->status = 2;
            $orders->save();
            
            $transaction = new Transaction;
            $transaction->order_id = $orders->id;
            $transaction->cart_id = $getResponseCart['cart']['id'];
            $transaction->user_id = auth('api')->user()->id;
            $transaction->base_price = $getResponseCart['cart']['id'];
            $transaction->total = $getResponseCart['cart']['id'];
            $transaction->save();

            $orders->transaction_id = $transaction->id;
            $orders->save();

            // dispatch(new PerjalananJob($orders));

            DB::commit();

            return response([
                "status" => true,
                "message" => "Order successfully created!"
            ], 200);
        } catch (\Exception $e) {
            // DB::rollback();
            return response([
                "status" => false,
                "message" => $e->getMessage()
            ], 500);
        }
    }
}
