<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BasePrice;
use App\Models\AdiraLocation;
use App\Models\MVSub;
use App\Models\InquiryMv;
use App\Models\SchemaComission;
use App\Models\Product;
use App\Models\Cart;
use App\Models\FloodQuake;
use App\Models\Zone;
use App\Http\Controllers\CartController;
use Validator;
use Log;

class MotoController extends Controller
{
    public function initmoto()
    {
        $brand = BasePrice::select('brand', 'price')
            ->where('type', 'Motorcycle')
            ->distinct('brand')
            ->whereNotNull('price')
            ->whereNotNull('type')
            ->get();
        try {
            $data = [];
            $get_brand = [];

            if (!empty($brand)) {
                foreach ($brand as $key => $value) {
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
                if ($value == 'HONDA') {
                    $data[$key]['logo'] = asset('assets/brand/HONDA-Motorcycle.png');
                } else {
                    $data[$key]['logo'] = asset('assets/brand/' . $value . '.png');
                }
            }

            $wilayah = AdiraLocation::select(["id", "name as wilayah", "code as kode"])->get();
            $topping = MVSub::select(['id', 'name'])->where("moto", 1)->get();
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

    public function model(Request $request)
    {
        $validator = Validator::make($request->all(), ["brand" => "required"]);

        if ($validator->fails()) {
            return response(["status" => false, "message" => $validator->errors()], 422);
        }

        try {
            $req = $validator->validated();
            $model = BasePrice::select('id', 'name as model', 'price')
                ->where('type', 'Motorcycle')
                ->where('brand', $req['brand'])
                ->whereNotNull('price')
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

            return $this->getResponse(count($data) > 0 ? 200 : 404, "", $data);
        } catch (\Exception $e) {
            return $this->getResponse(500, $e->getMessage());
        }
    }

    public function year(Request $request)
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
            $year = BasePrice::select('price')
                ->where('brand', $req['brand'])
                ->where('name', $req['model'])
                ->first();

            if (empty($year)) {
                return response(["status" => false, "message" => "Brand or Model not found!"], 404);
            }

            $year = $year->price;
            krsort($year);
            $data = [];

            foreach ($year as $key => $year) {
                if ($year != 0) {
                    $data[] = [
                        "tahun" => "$key"
                    ];
                }
            }
            return $this->getResponse(count($data) > 0 ? 200 : 404, "", $data);
        } catch (\Exception $e) {
            return $this->getResponse(500, $e->getMessage());
        }
    }

    public function price(Request $request)
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
                $data = $price->price[$req['year']];
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
            $value = json_decode($request->getContent(), true);
            $fix_discount = ($value['discount_value'] == "" ? 0 : $value['discount_value']) ?? 0;

            if ($value['policy'] == 'soft') {
                $policy = 10000;
            }
            if ($value['policy'] == 'hard') {
                $policy = 50000;
            }

            $inquiryUpdate = InquiryMv::find($request->inquiry_id);
            $inquiry = InquiryMv::find($value['inquiry_id']);
            if (!$inquiry) {
                return $this->getResponse(404, "Inquiry not found");
            }
            $product = Product::find($inquiry['product_id']);

            $total = array_sum(array_column($inquiry->item, 'price'));
            $verif_des = ($total * (25 / 100));
            $fix_total = $total + $policy;
            $discount_type = $value['discount_type'] ?? "fixed";
            $productComission = $product->schemaComission;

            // return  $discount_type;
            if ($discount_type == "percent") {
                if ($productComission->comission['type'] == "percent" && $value['discount_value'] > ($productComission->comission['value'] * 100)) {
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


            $inquiry->discount = $fix_discount;
            $inquiry->total = $total;
            $inquiry->save();
            // return $inquiry;
            $discount = ($total * ($fix_discount / 100));
            $sum  = $total - $discount + $policy;

            $inquiry->sum_discount = floor($discount);
            $inquiry->total = floor($sum);
            $inquiry->policy = $policy;
            $inquiry->newcar = $inquiry->data['newcar'];

            $arrayData = $inquiry->data;
            $arrayData['policy_type'] = $value['policy'];
            $inquiryUpdate->total = $sum;
            $inquiryUpdate->data = $arrayData;

            $inquiryUpdate->save();

            // return $inquiryUpdate;
            $cart = CartController::insert($inquiry, $request->cart);

            $getResponseCart = json_decode(json_encode($cart, true), true)['original'];
            if ($getResponseCart['status'] == false) {
                return $this->getResponse(500, $getResponseCart['message']);
            }
            $inquiry->cart = $getResponseCart['cart'];

            return $this->getResponse(200, "", $inquiry);
        } catch (\Exception $e) {
            return $this->getResponse(500, $e->getMessage());
        }
    }

    private function getResponse(int $code = 500, string $message, $data = null)
    {
        switch ($code) {
            case 200:
                return response([
                    'status' => true,
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
    public function calculateMotoPerluasan(Request $request)
    {
        try {
            $value = json_decode($request->getContent(), true);
            $message_error = [];
            $datasalah = [];

            $date_req = date_create_from_format("Y", $value['tahun']);
            $diff = date("Y") - $value["tahun"];

            if ($diff > 10) {
                $message_error[] = 'Usia kendaraan di atas 10 tahun';
            }

            if (date("Y") !== $value['tahun']) {
                $value['newcar'] = false;
            }

            $mobil = BasePrice::find($value['model']);

            $tahun = $value['tahun'];
            $price = $mobil['price'][$tahun];
            $total = $price;

            if ($value['price'] > ($price + $price * 20 / 100) || $value['price'] < ($price - $price * 20 / 100) || $value['price'] >= 500000000) {
                $message_error[] = 'Harga kendaraan di atas Rp 500.000.000';
            }

            if ($value['perluasan'][5] == 1) {
                if ($value['detail_perluasan'][5]['up'] == "0") {
                    $message_error[] = 'Uang pertanggungan PA Pengemudi tidak diisi';
                }
                if ($value['detail_perluasan'][5]['up'] > 10000000) {
                    $message_error[] = 'Uang pertanggungan PA Pengemudi lebih dari Rp 10.000.000 atau diatas total uang pertanggungan (TSI)';
                }
            }

            // if ($value['perluasan'][12] == 1) {
            //     if ($value['detail_perluasan'][12]['passenger'] > 1) {
            //         $message_error[] = 'PA Penumpang tidak boleh lebih dari 1 orang';
            //     }
            // }

            if ($value['perluasan'][7] == 1) {
                if ($value['detail_perluasan'][7]['up'] == "0") {
                    $message_error[] = 'Uang pertanggungan TPL tidak diisi';
                }
                if ($value['detail_perluasan'][7]['up'] > 10000000) {
                    $message_error[] = 'Uang pertanggungan TPL lebih dari Rp 10.000.000 atau diatas total uang pertanggungan (TSI)';
                }
            }

            if (!empty($message_error)) {
                return response(['message' => $message_error, 'status' => 422], 422);
            }

            $plat = AdiraLocation::where("code", $value['kode_plat'])->first();
            $plate = $plat['area'][1];

            return $this->hitungPerluasanMotoSaja($value['perluasan'], $plate, ($value['price']), $value['detail_perluasan'], $value, $mobil);
        } catch (\Exception $e) {
            return response(["message" => 'Oops, something went wrong', 'errors' => $e->getMessage() . ' ' . $e->getLine(), 'status' => false], 500);
        }
    }

    public function hitungPerluasanMotoSaja($perluasan, $kode_plat, $total, $detail_perluasan, $value, $mobil)
    {
        $year_now = date("Y");
        $five_years_ago = $year_now - 5;

        if ($value['tahun'] < $five_years_ago) {
            $percent = 5 * ($five_years_ago - $value['tahun']);
        } else {
            $percent = 0;
        }

        $occupation = $value['okupansi'] == 'OFFICIAL' || $value['okupansi'] == 'PERSONAL' ? 'PERSONAL' : 'PERSONAL';
        $cat = $this->getCat($total);

        $carType = "CAR";
        $zone = Zone::where('occupation', $occupation)->where('type', 'MOTORCYCLE')->first();

        $policyfeet = $zone->floor['tlo'][$kode_plat - 1];
        $policy_fee_tlo = $policyfeet;

        $detail[] = array("detail" => "Total Loss Only", "price" => floor($total * ($policy_fee_tlo / 100)));

        $zone = [];
        $plan = [];
        $code = [];
        $topi = [];
        $up = [];
        foreach ($perluasan as $key => $top) {
            if ($top) {
                $topi[] = $key;
                $up[] = $top;
            }
        }

        $topping = MVSub::select(['id', 'name', 'code', 'zoning', 'plan', 'against'])->whereIn('id', $topi)->where('moto', 1)->get();
        foreach ($topping as $top) {
            if ($top->zoning == 1) {
                if (!in_array($kode_plat, $zone)) {
                    $zone[] = $kode_plat;
                }
            } elseif ($top->zoning == 0) {
                if (!in_array(0, $zone)) {
                    $zone[] = 0;
                }
            };

            if ($top->plan == 1) {
                if (!in_array(1, $plan)) {
                    $plan[] = 1;
                }
            } elseif ($top->plan == 0) {
                if (!in_array(3, $plan)) {
                    $plan[] = 3;
                }
            }
            $code[] = $top->code;
        };
        $rate = FloodQuake::whereIn("zone", $zone)->whereIn("plan", $plan)->get();

        foreach ($topping as $toping) {
            $rat = null;
            foreach ($rate as $ra) {
                if ($ra->type == $toping->code) {
                    $rat = $ra;
                    break;
                }
            }
            $rateasd[$toping->id] = $rat;

            if ($toping->id == 5) {
                if ($value['perluasan'][5] == 1) {
                    $price = (int) $detail_perluasan[$toping->id]['up'] * 0.5 / 100;
                    $detail[] = array("detail" => "PA Pengemudi", "price" => floor($price));
                }
            } elseif ($toping->id == 12) {
                if ($value['perluasan'][12] == 1) {
                    $pa_penumpang_price = (int) $detail_perluasan[12]['up'] * 0.1 / 100;
                    $detail[] = array("detail" => "PA Penumpang(1 orang)", "price" => floor($pa_penumpang_price));

                    $value['detail_perluasan'][12]['passenger'] = 1;
                }
            } elseif ($toping->id == 7) {
                if ($value['perluasan'][7] == 1) {
                    $tpl_price = (int) $detail_perluasan[7]['up'] * 1 / 100;
                    $detail[] = array("detail" => "TPL", "price" => floor($tpl_price));
                }
            } elseif ($toping->against == 'total') {
                $premium = $rat->rate[$rat->default_rate] / 100;
                $price = $premium * $total;
                $detail[] = array("detail" => $toping->name, "price" => floor($price));
            } else {
                $premium = $rat->rate[$rat->default_rate] / 100;
                $price = (int) $detail_perluasan[$toping->id]['up'] * $premium;
                $detail[] = array("detail" => $toping->name, "price" => floor($price));
            }
        }

        $tot = 0;
        foreach ($detail as $det) {
            $tot = $tot + $det['price'];
        }

        // $detail[] = array("detail"=>"total","price"=>floor($tot));
        // unset($inquiry->data);
        return response(["message" => 'success', 'data' => $detail, 'total' => floor($tot), 'status' => 200], 200);
    }
    public function getCat($price)
    {
        if (($price > 0) && ($price <= 125000000)) {
            return 1;
        } elseif (($price > 125000000) && ($price <= 200000000)) {
            return 2;
        } elseif (($price > 200000000) && ($price <= 400000000)) {
            return 3;
        } elseif (($price > 400000000) && ($price <= 800000000)) {
            return 4;
        } elseif (($price > 800000000)) {
            return 5;
        } else {
            return false;
        }
    }

    public function getTPLRate($type, $price)
    {
        $premi = 0;
        $arr = [
            0 => ["base" => 25000000, "rate" => 0.01],
            1 => ["base" => 25000000, "rate" => 0.005],
            2 => ["base" => 1, "rate" => 0.0025]
        ];
        $arr2 = [
            0 => ["base" => 25000000, "rate" => 0.01],
            1 => ["base" => 25000000, "rate" => 0.005]
        ];

        foreach ($type == 0 ? $arr : $arr2 as $keys => $ar) {
            if ($price > 0) {
                if ($price > $ar["base"]) {
                    $kali = ($keys == array_key_last($arr) ? $price : $ar["base"]);
                    $premi = $premi + ($kali * $ar["rate"]);
                    $price  = $price - $ar["base"];
                } else if ($price <= $ar["base"]) {
                    $premi = $premi + ($price * $ar["rate"]);
                    $price  = 0;
                }
            }
        }
        return $premi;
    }
}
