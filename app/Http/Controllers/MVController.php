<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BasePrice;
use App\Models\AdiraLocation;
use App\Models\MVSub;
use App\Models\InquiryMv;
use App\Models\Product;
use App\Models\Cart;
use App\Models\SchemaComission;
use App\Http\Controllers\CartController;
use Validator;

class MVController extends Controller
{
    public function initmv()
    {
        try {
            $brand = BasePrice::select('brand', 'price')
                ->where('type', '!=', 'Motorcycle')
                ->distinct('brand')
                ->whereNotNull('price')
                ->whereNotNull('type')
                ->orderBy('brand', 'ASC')
                ->get();

            $data = [];
            $get_brand = [];

            if (!empty($brand)) {
                foreach ($brand as $keyBrand => $value) {

                    $total_price = 0;
                    foreach ($value->price as $key => $price) {
                        $total_price += $price;
                    }
                    if ($total_price > 0) {
                        unset($value->price);
                        $get_brand[] = $value->brand;
                    }
                }
            }
            foreach (array_values(array_unique($get_brand)) as $key => $value) {
                $data[$key]['brand'] = $value;
                $data[$key]['logo'] = asset('assets/brand/' . $value . '.png');
            }

            $wilayah = AdiraLocation::select(["id", "name as wilayah", "code as kode"])->get();
            $topping = MVSub::select(['id', 'name'])->where("mv", 1)->get();

            $res = [
                "perluasan" => $topping,
                "brand" => $data,
                "wilayah" => $wilayah
            ];

            return $this->getResponse(200, "", $res);
        } catch (\Exception $e) {
            return $this->getResponse(500, $e->getMessage());
        }
    }

    public function getModel(Request $request)
    {
        $validator = Validator::make($request->all(), ["brand" => "required"]);

        if ($validator->fails()) {
            return response(["status" => false, "message" => $validator->errors()], 422);
        }

        try {
            $req = $validator->validated();

            $model = BasePrice::select('id', 'name as model', 'price')
                ->where('type', '!=', 'Motorcycle')
                ->where('brand', $req['brand'])
                ->whereNotNull('price')
                ->orderBy('model', 'ASC')
                ->get();

            $data = [];
            if (!empty($model)) {
                foreach ($model as $key => $value) {
                    $total_price = 0;
                    foreach ($value->price as $key => $price) {
                        $total_price += $price;
                    }
                    if ($total_price > 0) {
                        $data[] = $value;
                    }
                }
            }

            $counter = count($data);

            return $this->getResponse(($counter > 0) ? 200 : 404, "", $data);
        } catch (\Exception $e) {
            return $this->getResponse(500, $e->getMessage());
        }
    }

    public function getyear(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "brand" => "required",
            "model" => "required"
        ]);

        if ($validator->fails()) {
            return response(["status" => false, "message" => $validator->errors()], 422);
        }

        try {

            $req = $validator->validated();
            $data = [];

            $years = BasePrice::select('price')
                ->where('brand', $req['brand'])
                ->where('name', $req['model'])
                ->first();


            if (isset($years)) {
                $years = $years->price;
                krsort($years);

                foreach ($years as $key => $year) {
                    if ($year != 0) {
                        $data[] = ["tahun" => "$key"];
                    }
                }
            }

            $counter = count($data);

            return $this->getResponse(($counter > 0) ? 200 : 404, "", $data);
        } catch (\Exception $e) {
            return $this->getResponse(500, $e->getMessage());
        }
    }

    public function getprice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "brand" => "required",
            "model" => "required",
            "year" => "required"
        ]);

        if ($validator->fails()) {
            return response(["status" => false, "message" => $validator->errors()], 422);
        }

        try {

            $req = $validator->validated();
            $data = 0;
            $price = BasePrice::where("brand", $req['brand'])->where("name", $req['model'])->first();

            if (isset($price)) {
                $data = $price->price[$request->year];
            }

            return $this->getResponse(200, "", $data);
        } catch (\Exception $e) {
            return $this->getResponse(500, $e->getMessage());
        }
    }

    public function sendInquiry(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // "discount_type" => "required",
            // "discount_value" => "required",
            "policy" => "required",
        ]);
        if ($validator->fails()) {
            return response(["status" => false, "message" => $validator->errors()], 422);
        }
        try {
            $getCart = Cart::find($request->cart['id']);
            if (!empty($getCart) && $getCart->is_ref !== $request->is_ref) {
                return $this->getResponse(500, "Keranjang Referal tidak dapat dimasukan Product non Referal");
            }
            if (!empty($getCart) && $getCart->is_checkout == 1) {
                return $this->getResponse(500, "Keranjang Sudah Mengisi Data Penutupan");
            }
            $value = $validator->validated();
            // $fix_discount = ($value['discount_value'] == "" ? 0 : $value['discount_value']) ?? 0;
            if (empty($request['discount_value'])) {
                $fix_discount = 0;
            } else {
                $fix_discount = $request['discount_value'];
            }

            if ($request['policy'] == 'soft') {
                $policy = 10000;
            }
            if ($request['policy'] == 'hard') {
                $policy = 50000;
            }

            $inquiryUpdate = InquiryMv::find($request->inquiry_id);
            $inquiry = InquiryMv::find($request->inquiry_id);

            if (!$inquiry) {
                return $this->getResponse(404, "Inquiry not found");
            }
            $product = Product::find($inquiry->product_id);
            $total = array_sum(array_column($inquiry->item, 'price'));
            $verif_des = ($total * (25 / 100));
            $discount_type = $request['discount_type'] ?? "fixed";
            $productComission = $product->schemaComission;

            if ($discount_type == "percent") {
                if ($productComission->comission['type'] == "percent" && $request['value_discount'] > ($productComission->comission['value'] * 100)) {
                    return response(['message' => ['Diskon tidak boleh lebih dari komisi'], 'status' => 422], 422);
                }
                if ($fix_discount > 25) {
                    return response(['message' => ['Diskon tidak boleh lebih dari 25%'], 'status' => 422], 422);
                }
                if ($fix_discount < 0) {
                    return response(['message' => ['Diskon tidak boleh kurang dari 0%'], 'status' => 422], 422);
                }
            } else {
                if ($fix_discount > $verif_des) {
                    return response(['message' => ['Diskon tidak boleh lebih dari 25%'], 'status' => 422], 422);
                }
                $fix_discount = $fix_discount / $total * 100;
            }

            if ($productComission->comission['type'] == "decimal" &&  $productComission->comission['value'] > $verif_des) {
                return response(['message' => ['Diskon tidak boleh lebih dari komisi'], 'status' => 422], 422);
            }
            $fix_total = $total + $policy;
            $inquiry->discount = $fix_discount;
            $inquiry->total = $total;
            $inquiry->save();

            $discount = ($total * ($fix_discount / 100));
            $sum  = $total - $discount + $policy;
            $inquiryUpdate->total = $sum;
            $inquiry->sum_discount = floor($discount);
            $inquiry->total = floor($sum);
            $inquiry->policy = $policy;
            $inquiry->newcar = $inquiry->data['newcar'];
            $arrayData = $inquiry->data;
            $arrayData['policy_type'] = $request['policy'];
            $inquiryUpdate->data = $arrayData;
            $inquiry->product = $product;

            $inquiryUpdate->save();
            $cart = CartController::insert($inquiry, $request->cart);

            $getResponseCart = json_decode(json_encode($cart, true), true)['original'];
            if ($getResponseCart['status'] == false) {
                return $this->getResponse(500, $getResponseCart['message']);
            }
            $inquiry->cart = $getResponseCart['cart'];
            return $this->getResponse(200, "", $inquiry);
        } catch (\Exception $e) {
            return $this->getResponse(500, $e->getMessage() . " " . $e->getLine());
        }
    }

    private function getResponse(int $code = 500, string $message, $data = null)
    {
        switch ($code) {
            case 200:
                return response([
                    'status' => true,
                    'message' => $message,
                    'data' => $data,
                ], $code);
                break;

            case 500:
                return response([
                    "status" => false,
                    "message" => $message
                ], $code);
                break;

            case 404:
                return response([
                    "status" => false,
                    'message' => $message,
                    "data" => $data
                ], $code);
                break;

            default:
                return response([
                    "status" => false,
                    "message" => $message
                ], $code);
                break;
        }
    }
}
