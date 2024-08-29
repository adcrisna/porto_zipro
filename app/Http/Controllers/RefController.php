<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductReferral;
use App\Models\Order;
use App\Models\Cart;
use DB;

class RefController extends Controller
{
    public function post(Request $request)
    {
        try {
            $data = json_decode($request->getContent());

            $arr = [
                "date" => $data->date,
                "title" => $data->title,
                "customer_name" => $data->customer_name,
                "customer_number" => $data->customer_number,
                "customer_email" => $data->customer_email,
                "categories" => $data->categories,
                "address" => $data->address,
                "salvus_friend" => $data->salvus_friend,
                "time_to_contact" => strtolower($data->time_to_contact),
                "urgency" => strtolower($data->urgency),
                "province_id" => $data->province_id,
                "city" => $data->city,
                "note" => $data->note
            ];
            // if ($validator->fails()) {
            //     return response([
            //         "status" => false,
            //         "message" => "Validations Error",
            //         "data" => $validator->errors()
            //     ], 422);
            // }
            if (!filter_var($data->customer_email, FILTER_VALIDATE_EMAIL)) {
                return response([
                    "status" => false,
                    "message" => "Format email tidak valid",
                ], 422);
            }
            DB::beginTransaction();
            $named = "Referral " . auth('api')->user()->name;
            $check = Cart::where('name', 'like', '%' . $named . '%')->get();

            $cart = new Cart;
            $cart->name = count($check) > 0 ? $named . " " . count($check) + 1 : $named;
            $cart->is_ref = 1;
            $cart->save();

            $reff = new ProductReferral;
            $reff->cart_id = $cart->id;
            $reff->user_id = auth('api')->user()->id;
            $reff->data = $arr;
            $reff->status = 0;
            $reff->save();
            DB::commit();

            return response([
                "status" => true,
                "message" => "Data successfully created"
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response([
                "status" => false,
                "message" => $e->getMessage(),
                "line" => $e->getLine()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $data = json_decode($request->getContent());

            $arr = [
                "date" => $data->date,
                "title" => $data->title,
                "customer_name" => $data->customer_name,
                "customer_number" => $data->customer_number,
                "customer_email" => $data->customer_email,
                "categories" => $data->categories,
                "address" => $data->address,
                "salvus_friend" => $data->salvus_friend,
                "time_to_contact" => strtolower($data->time_to_contact),
                "urgency" => strtolower($data->urgency),
                "province_id" => $data->province_id,
                "city" => $data->city,
                "note" => $data->note
            ];

            $reff = ProductReferral::find($id);
            $reff->data = $data;
            $reff->save();

            return response([
                "status" => true,
                "message" => "Data successfully updated"
            ], 200);
        } catch (\Exception $e) {
            return response([
                "status" => false,
                "message" => $e->getMessage(),
                "line" => $e->getLine()
            ], 500);
        }
    }

    public function detail($id)
    {
        try {
            $reff = ProductReferral::where('id', $id)->with(['cart' => function ($query) {
                $query->select('id', 'name');
            }], ['pickup' => function ($query) {
                $query->select('id', 'name', 'email');
            }])->first();

            return response([
                "status" => true,
                "data" => $reff
            ], 200);
        } catch (\Exception $e) {
            return response([
                "status" => false,
                "message" => $e->getMessage(),
                "line" => $e->getLine()
            ], 500);
        }
    }

    public function pickup(Request $request)
    {
        try {
            DB::beginTransaction();
            $reff = ProductReferral::find($request->referral_id);
            $reff->picked_user_id = auth('api')->user()->id;
            $reff->status = 1;
            $reff->save();

            $cart = Cart::find($reff->cart_id);
            $cart->user_id = auth('api')->user()->id;
            $cart->save();

            $data = $reff;
            $data['cart']['id'] = $cart->id;
            $data['cart']['name'] = $cart->name;
            DB::commit();

            return response([
                "status" => true,
                "data" => $data
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();

            return response([
                "status" => false,
                "message" => $e->getMessage(),
                "line" => $e->getLine()
            ], 500);
        }
    }

    public function list_order()
    {
        try {
            $product = ProductReferral::select('id', 'data', 'status', 'user_id')
                // ->where('user_id', auth('api')->user()->id)
                ->whereNull('picked_user_id')->where('status', 0)
                ->latest()
                ->get();

            $status_code = count($product) > 0 ? 200 : 404;
            return response([
                "status" => true,
                "message" => $status_code == 404 ? "not found" : "data found",
                "data" => $product
            ], $status_code);
        } catch (\Exception $e) {
            return response([
                "status" => false,
                "message" => $e->getMessage(),
                "line" => $e->getLine()
            ], 500);
        }
    }

    public function search_order(Request $request)
    {
        try {
            $check = $request->except('latest');
            $filter = [];
            $products = ProductReferral::select('id', 'data', 'status', 'user_id')
                ->whereNull('picked_user_id')
                ->where('status', 0);
            if ($request->get('latest') == "true") {
                $products->latest();
            }
            $data = $products->get();

            $new = [];
            if ($check) {
                foreach ($data as $key => $product) {
                    if ($request->get('category_id') !== null) {
                        foreach ($product->data as $keyCat => $getCat) {
                            if ($keyCat == "categories") {
                                foreach ($getCat as $category) {
                                    if (!empty($category['id']) && (string)$category['id'] == (string)$request->get('category_id')) {
                                        if (!in_array($product->id, array_column($new, 'id'))) {
                                            $new[] = $product;
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        // "address" => strtolower($request->get('location')),
                        $filter = [
                            "time_to_contact" => strtolower($request->get('time_contact')),
                            "urgency" => strtolower($request->get('urgency')),
                            "city" => $request->get('city')
                        ];

                        $fil = array_filter(
                            $product->data,
                            fn ($val, $ke) => isset($filter[$ke]) && strtolower($filter[$ke]) === strtolower($val),
                            ARRAY_FILTER_USE_BOTH
                        );

                        foreach ($fil as $keyFilter => $filled) {
                            $from_db = strtolower($product->data[$keyFilter]);
                            $from_filter = strtolower($filled);
                            if ($from_db == $from_filter && !empty($from_db)) {
                                $new[] = $product;
                            }
                        }
                    }
                }
            } else {
                $new = $data;
            }

            $status_code = count($new) > 0 ? 200 : 404;
            return response([
                "status" => true,
                "message" => $status_code == 404 ? "not found" : "data found",
                "data" => $new
            ], $status_code);
        } catch (\Exception $e) {
            return response([
                "status" => false,
                "message" => $e->getMessage(),
                "line" => $e->getLine()
            ], 500);
        }
    }

    public function list_pickup()
    {
        try {
            $product = ProductReferral::select('id', 'data', 'status', 'user_id')
                ->whereNotNull('picked_user_id')->where('status', 1)
                ->where('picked_user_id', auth('api')->user()->id)
                ->latest()
                ->get();

            $status_code = count($product) > 0 ? 200 : 404;
            return response([
                "status" => true,
                "message" => $status_code == 404 ? "not found" : "data found",
                "data" => $product
            ], $status_code);
        } catch (\Exception $e) {
            return response([
                "status" => false,
                "message" => $e->getMessage(),
                "line" => $e->getLine()
            ], 500);
        }
    }

    private function statusStr($status)
    {
        // siapa tau butuh h3h3
        switch ($status) {
            case '0':
                $str = "Waiting Pickup";
                break;

            case '1':
                $str = "On Proccess";
                break;

            case '2':
                $str = "Done";
                break;

            default:
                $str = "Canceled";
                break;
        }

        return $str;
    }
}
