<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\InquiryMv;
use App\Models\Renewal;
use App\Models\BasePrice;
use App\Models\Product;
use App\Models\FloodQuake;
use App\Models\AdiraLocation;
use App\Models\Zone;
use App\Models\Cart;
use App\Models\MVSub;
use App\Models\FormRepoCategory;
use App\Models\Objects;
use App\Models\AdiraTransaction;
use App\Models\FormRepo;
use App\Models\Arrays;
use App\Jobs\ResubmitJob;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Exception;
use DB;
use Auth;
use Log;


class BackorderController extends Controller
{
    public function getInquiry($order_id)
    {
        try {
            $order = Order::find($order_id);
            if (empty($order)) {
                return response([
                    "status" => false,
                    "message" => "Data not found",
                    "data" => []
                ], 404);
            }
            $inquiry = $order?->inquiry;
            if (empty($inquiry)) {
                return response([
                    "status" => false,
                    "message" => "Order tidak dapat di Back Order atau belum pengisian data insured!",
                    "data" => []
                ], 422);
            }
            if ($inquiry->data['policy_type']) {
                $policyType = $inquiry->data['policy_type'];
            } else {
                $policyType = $data["policy_type"] = $order->additional_data[0]['copy'];
            }
            // return $data["policy_type"] = $order->additional_data[0]['copy'];
            $data = $inquiry;
            $data["policy_type"] = $order->additional_data[0]['copy'];
            $data["policy_price"] = $order->additional_data[0]['copy_price'];
            // return $data->policy_type;
            if ($data->data['newcar'] == false) {
                $statusVehicle = 'BEKAS';
            } else {
                $statusVehicle = 'BARU';
            }
            $totalPremi = 0;
            foreach ($data->item as $key => $value) {
                $totalPremi += $value['price'];
            }

            $inqu = [];
            $inqu["okupasi"] = $data->data['okupansi'];
            $inqu["chassist"] = $data->data['chassis'];
            $inqu["insurance_type"] = $data->data['type'];
            $inqu["insurance_type_str"] = $data->data['type'] == 0 ? "Comprehensive" : "Total Loss Only";
            $inqu["vehicle_merek"] = $data->data['brand'];
            $inqu["vehicle_model"] = $data->data['model'];
            $inqu["vehicle_model_str"] = $data->data['modelstr'];
            $inqu["vehicle_year"] = $data->data['tahun'];
            $inqu["vehicle_location"] = $data->data['kode_plat'];
            $inqu["discount"] = $data->discount;
            $inqu["discount_type"] = "Percent";
            $inqu["vehicle_status"] = $statusVehicle;
            $inqu["discount_value"] =  $data->discount != 0 ? $data->total - (($data->discount / 100) * $data->total) : 0;
            $inqu["tsi"] = $data->data['price'];
            $inqu["perluasan"] = $data->data['perluasan'];
            $inqu["detail_perluasan"] = $data->data['detail_perluasan'];
            $inqu["aksesoris"] = $data->data['aksesoris'];
            $inqu["policy_type"] = $policyType;
            $inqu["total_premi"] = $totalPremi;
            $grandTotal = $data->total - $data->discount != 0 ? $data->total - (($data->discount / 100) * $data->total) : 0;
            $inqu['grand_total'] = $data->total;

            $data->data = $inqu;
            return response([
                'status' => true,
                'message' => "Data found",
                'data' => $inqu
            ], 200);
        } catch (\Exception $e) {
            return response([
                'status' => false,
                'data' => [],
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function calculate(Request $request, $id)
    {
        $data = json_decode($request->getContent(), true);
        try {
            $err_msg = [];
            $acc_price = [];

            $product = Product::find($data['product']);
            $inquiry = InquiryMv::find($id);
            if (empty($inquiry)) {
                return response([
                    "status" => false,
                    "message" => "Inquiry tidak ditemukan",
                    "data" => []
                ], 404);
            }

            foreach ($data['aksesoris'] as $keyAcc => $acc) {
                $filter = array_filter($acc);
                $count_success = count($filter);
                if ($count_success != 3 && $count_success != 0) {
                    $err_msg[] = "Aksesoris ke $keyAcc tidak lengkap";
                } else {
                    $acc_price[] = (int) $acc['harga'] * (int) $acc['qty'];
                }
            }

            $acc_total = array_sum($acc_price);

            $car_date = date("Y") - $data['tahun'];

            if ($data['type'] == 0 && $car_date > 10) {
                $err_msg[] = "Usia kendaraan diatas 10 tahun";
            } else if ($data['type'] == 1 && $car_date > 15) {
                $err_msg[] = "Usia kendaraan diatas 15 tahun";
            }

            if (date("Y") !== $data['tahun']) {
                $data['newcar'] = false;
            }

            if (!empty($err_msg)) {
                return response(['message' => $err_msg, 'status' => false], 422);
            }

            $get_car = BasePrice::findorfail($data['model']);
            if (!isset($get_car)) {
                throw new \Exception("Car not found", 404);
            }

            $car_price = $get_car['price'][$data['tahun']];
            // return $car_price - $car_price * 20 /100;
            if ($data['price'] > ($car_price + $car_price * 20 / 100) || $data['price'] < ($car_price - $car_price * 20 / 100)) {
                $err_msg[] = 'Harga kendaraan tidak dapat 20% lebih rendah/melebihi harga yang ditentukan';
            }
            if (($data['price'] + $acc_total) > 2000000000) {
                $err_msg[] = 'Total uang pertanggungan (TSI) di atas Rp2.000.000.000';
            }

            if ($data['perluasan'][5] == true) {
                if ($data['detail_perluasan'][5]['up'] == "0") {
                    $err_msg[] = 'Uang pertanggungan PA Pengemudi tidak diisi';
                }
                if ($data['detail_perluasan'][5]['up'] > 50000000 || $data['detail_perluasan'][5]['up'] > ($data['price'] + $acc_total)) {
                    $err_msg[] = 'Uang pertanggungan PA Pengemudi lebih dari Rp50.000.000 atau diatas total uang pertanggungan (TSI)';
                }
            }

            if ($data['perluasan'][6] == true) {
                if ($data['detail_perluasan'][6]['up'] == "0" || $data['detail_perluasan'][6]['passenger'] == "") {
                    $err_msg[] = 'Uang pertanggungan PA Penumpang (4 orang) tidak diisi';
                }
                if ($data['detail_perluasan'][6]['passenger'] > "4") {
                    $err_msg[] = 'Jumlah penumpang pada PA Penumpang melebihi 4 orang';
                }
                if ($data['detail_perluasan'][6]['up'] > 25000000 || $data['detail_perluasan'][6]['up'] > ($data['price'] + $acc_total)) {
                    $err_msg[] = 'Uang pertanggungan PA Penumpang lebih dari Rp25.000.000 atau diatas total uang pertanggungan (TSI)';
                }
            }

            if ($data['perluasan'][7] == true) {
                if ($data['detail_perluasan'][7]['up'] == "0") {
                    $err_msg[] = 'Uang pertanggungan TPL tidak diisi';
                }
                if ($data['detail_perluasan'][7]['up'] > ($data['price'] + $acc_total)) {
                    $err_msg[] = 'Uang pertanggungan TPL diatas total uang pertanggungan (TSI)';
                }
                if ($data['type'] == 0 && $data['detail_perluasan'][7]['up'] > 100000000) {
                    $err_msg[] = 'Uang pertanggungan TPL lebih dari Rp100.000.000';
                }
                if ($data['type'] == 1 && $data['detail_perluasan'][7]['up'] > 50000000) {
                    $err_msg[] = 'Uang pertanggungan TPL lebih dari Rp50.000.000';
                }
            }

            if (!empty($err_msg)) {
                return response(['message' => $err_msg, 'status' => 422], 422);
            }
            $plat = AdiraLocation::where("code", $data['kode_plat'])->first();
            $plate = $plat['area'][1];
            $perluasan = $data['perluasan'];

            $total = ($data['price'] + $acc_total);

            unset($perluasan[7]);

            $year_now = date("Y");
            $five_years_ago = $year_now - 5;


            if ($data['tahun'] < $five_years_ago) {
                $percent = 5 * ($five_years_ago - $data['tahun']);
            } else {
                $percent = 0;
            }

            // $occupation = $data['okupansi'] == 'OFFICIAL' || $data['okupansi'] == 'PERSONAL' ? 'PERSONAL' : 'COMMERCIAL';
            switch ($data['okupansi']) {
                case 'PRIBADI':
                    $occupation = 'PERSONAL';
                    break;
                case 'DINAS':
                    $occupation = 'OFFICIAL';
                case 'KOMERSIAL':
                    $occupation = 'COMMERCIAL';
                default:
                    $occupation = 'PERSONAL';
                    break;
            }
            $cat = $this->getCat($total);
            if ($get_car->type == "Truck" || $get_car->type == "Bus") {
                $carType = $get_car->typeDetail;
                $zone = Zone::where('type', strtoupper($get_car->type))->where('occupation', $occupation)->first();
            } else {
                $carType = "CAR";
                $zone = Zone::where('code', $cat)->where('occupation', $occupation)->where('type', strtoupper($get_car->type))->first();
            }
            // return $zone;
            $policyfeet = $zone->floor['tlo'][$plate - 1];
            $policyfeea = $zone->floor['allrisk'][$plate - 1];

            $policy_fee_tlo = $policyfeet;
            $policy_fee_allrisk = (($percent / 100) * $policyfeea) + $policyfeea;

            if ($data['type'] == 0) {
                $detail[] = array("detail" => "Comprehensive", "price" => floor($total * (($policy_fee_allrisk) / 100)));
            } elseif ($data['type'] == 1) {
                $detail[] = array("detail" => "Total Loss Only", "price" => floor($total * ($policy_fee_tlo / 100)));
            }


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
            $topping = MVSub::select(['id', 'name', 'code', 'zoning', 'plan', 'against'])->whereIn('id', $topi)->where('mv', 1)->get();
            $fix_type = $data['type'] == true ? 1 : 0;

            foreach ($topping as $key_top => $top) {

                if ($top->zoning == 1) {
                    if (!in_array($plate, $zone)) {
                        $zone[] = $plate;
                    }
                } elseif ($top->zoning == 0) {
                    if (!in_array(0, $zone)) {
                        $zone[] = 0;
                    }
                };

                if ($top->plan == 1) {
                    if (!in_array($data['type'], $plan)) {
                        $plan[] = $fix_type;
                    }
                } elseif ($top->plan == 0) {
                    if (!in_array(3, $plan)) {
                        $plan[] = 3;
                    }
                }
                $code[] = $top->code;
            };

            $rate = FloodQuake::whereIn("zone", $zone)->whereIn("plan", $plan)->get();
            $detail_perluasan = $data['detail_perluasan'];

            foreach ($topping as $toping) {
                $rat = null;
                foreach ($rate as $ra) {
                    if ($ra->type == $toping->code) {
                        $rat = $ra;
                        break;
                    }
                }

                $premium = $rat->rate[$rat->default_rate] / 100;

                if ($toping->against == 'total') {
                    $price = $premium * $total;
                } else {
                    if ($toping->id == 6) {
                        $price = (int) $detail_perluasan[$toping->id]['up'] * (int) $detail_perluasan[$toping->id]['passenger'] * $premium;
                    } else {
                        $price = (int) $detail_perluasan[$toping->id]['up'] * $premium;
                    }
                }
                $detail[] = array("detail" => $toping->name, "price" => floor($price));
            }
            if ($data['perluasan'][7] == 1) {
                $rate7 = $this->getTPLRate($data['type'], (int) $data['detail_perluasan'][7]['up']);
                $detail[] = array("detail" => "TPL", "price" => floor($rate7));
            }

            $tot = 0;
            foreach ($detail as $det) {
                $tot = $tot + $det['price'];
            }


            $data['type'] = $data['type'] == true ? 1 : 0;
            // $policy = !empty($data['policy_type']) ? $data['policy_type'] : null;
            DB::beginTransaction();
            // $data['policy_type'] = $policy;

            if ($data['policy_type'] == 'soft') {
                $policy = 10000;
            }
            if ($data['policy_type'] == 'hard') {
                $policy = 50000;
            }
            
            // return $tot;
            $inquiry->item = $detail;
            $inquiry->total = $tot;
            $inquiry->product_id = $data['product'];
            $inquiry->status = false;
            $inquiry->data = $data;
            $inquiry->save();

            // $getOrder = Order::find($inquiry->order_id);
            // $getOrder->base_price = $tot;
            // $getOrder->total = $tot + $policy;
            // $getOrder->save();

            DB::commit();
            
            $detail[] = array("detail" => "total", "price" => floor($tot));
            $inquiry->acc = $data['aksesoris'];
            unset($inquiry->data);
            return response(["message" => 'success', 'data' => $inquiry, 'status' => 200], 200);
        } catch (\Exception $e) {
            return response([
                "status" => 500,
                "message" => $e->getMessage()
            ], 500);
        }
    }

    public function save_inquiry(Request $request)
    {
        try {
            $value = json_decode($request->getContent(), true);
            $fix_discount = $value['value_discount'] == "" ? 0 : $value['value_discount'];
            if ($value['policy'] == 'soft') {
                $policy = 10000;
            }else{
                $policy = 50000;
            }

            $inquiry = InquiryMv::find($value['inquiry_id']);
            if (empty($inquiry)) {
                return response([
                    "status" => false,
                    "message" => "Inquiry tidak ditemukan!",
                    "data" => []
                ], 404);
            }
            $product = Product::find($inquiry['product_id']);
            $schemaComission = $product->schemaComission;

            $total = array_sum(array_column($inquiry->item, 'price'));
            $verif_des = ($total * (25 / 100));

            if ($value['type_discount'] == "percent") {
                if ($schemaComission->comission['type'] == "percent" && $value['value_discount'] > ($schemaComission->comission['value'] * 100)) {
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

            if ($schemaComission->comission['type'] == "decimal" &&  $schemaComission->comission['value'] > $verif_des) {
                return response(['message' => ['Diskon tidak boleh lebih dari komisi'], 'status' => 422], 422);
            }
            // return $fix_discount;
            $fix_total = $total + $policy;
            $inquiry->discount = $fix_discount;
            
            $discount = $total * ($fix_discount / 100);
            $sum  = $total - $discount + $policy;
            // return $fix_discount;

            $grand_total = $total - ($total * ($fix_discount / 100));
            $inquiry->total = $grand_total + $policy;
            // return $inquiry->total;
            $inquiry->save();
            
            $getOrder = Order::find($inquiry->order_id);
            $getOrder->base_price = $total;
            $getOrder->total = $inquiry->total;
            $getOrder->save();
            
            $inquiry->policy = $policy;
            $inquiry->newcar = $inquiry->data['newcar'];

            // return $grand_total;
            // $updateAdira = AdiraTransaction::where('order_id',$inquiry->order_id)->first();
            // // $updateAdira->adira_status = null;
            // $updateAdira->save();
            // return $updateAdira;

            return response(["message" => 'Success', 'data' => $inquiry, 'status' => 200], 200);
        } catch (\Exception $e) {
            return response(["message" => 'Oops, something went wrong', 'errors' => $e->getMessage() . ' ' . $e->getLine(), 'status' => false], 500);
        }
    }

    public function initForm(Request $request, $id)
    {
        $order = Order::find($id);
        if (empty($order)) {
            return response([
                "status" => false,
                "message" => "Order tidak ditemukan"
            ], 404);
        }
        $product = $order->product;
        $data = [];
        $form = [];
        try {
            $form_contracts = FormRepoCategory::where('category_id', $product->category_id)->get();
            $form = [];
            $valid = [];
            $objects = Objects::all();
            foreach ($form_contracts as $keyContract => $form_contract) {
                if (!empty($form_contract->form_json)) {
                    $contract = $form_contract->form_json['contract'];
                    foreach ($objects as $keyObject => $object) {
                        $form[$keyObject] = [
                            "name" => $object->display_name,
                        ];
                        $form_object = $object->form_json;
                        foreach ($form_object as $keyObj => $obj) {
                            if (!empty($form_contract->form_validation)) {
                                foreach ($form_contract->form_validation['contract'] as $key_valid => $form_validation) {
                                    $valid[$form_validation['contract']] = $form_validation;
                                }
                            }
                            $t[] = $form_object;
                            if (in_array($obj, $contract)) {
                                $formrepo = FormRepo::find($obj);
                                $value = Arrays::find($formrepo->value)->value ?? null;

                                if ($formrepo->form_type == "text" || $formrepo->form_type == "number" || $formrepo->form_type == "images") {
                                    $validation = [
                                        "is_required" => !empty($valid[$formrepo->id]['required']) ? true : false,
                                        "min_length" => !empty($valid[$formrepo->id]['minlength']) ? (int) $valid[$formrepo->id]['minlength'] : NULL,
                                        "max_length" => !empty($valid[$formrepo->id]['maxlength']) ? (int) $valid[$formrepo->id]['maxlength'] : NULL
                                    ];
                                } else {
                                    $validation = ["is_required" => !empty($valid[$formrepo->id]['required']) ? true : false];
                                }
                                $placeholder = null;
                                // return $order->data[$formrepo->id]['data'];
                                if (!empty($order->data[$formrepo->id])) {
                                    $placeholder = $order->data[$formrepo->id]['data'] ?? "null";
                                } else {
                                    $placeholder = $value;
                                }

                                $form[$keyObject]["details"][] = [
                                    "id" => (string)$formrepo->id,
                                    "type" => $formrepo->form_type,
                                    "text" => $formrepo->name,
                                    "validator" => $validation,
                                    "validate_link" => $formrepo->validate_link,
                                    "value" => $placeholder
                                ];
                            }
                        }
                        if ($keyObject == 1) {
                            $form[1]["details"][] = [
                                "id" => "start_date",
                                "type" => "date",
                                "text" => "TANGGAL MULAI",
                                "validator" => [
                                    "is_required" => true
                                ],
                                "validate_link" => null,
                                "value" => $order->start_date,
                            ];
                        }
                        if (empty($form[$keyObject]["details"])) {
                            unset($form[$keyObject]);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            return response([
                "status" => false,
                "message" => 'Coba lagi nanti atau Hubungi CS'
            ], 500);
        }

        return response([
            "status" => true,
            "message" => 'data found',
            "form" => array_values(array_reverse($form)),
            "data" => $product
        ], 200);
    }

    public function submitForm(Request $request, $id)
    {
        try {
            // return $request;
            //Log::warning("request resubmit ".json_encode($request));
            $msg = null;
            if (strlen($request['33']) != 16) {
                $msg[] = "Nomor KTP Tidak Valid / Harus 16 Angka";
            }
            $client = new Client;
            $result = $client->get(env("ADIRA_ORDER_URL") . 'api/v1/prepare?apiKey=' . env('ADIRA_KEY'));
            $res = json_decode($result->getBody(), true);

            if (!empty($res['data']['provinceCities'][$request[69]])) {
                if (in_array($request[68], $res['data']['provinceCities'][$request[69]]) == false) {
                    $msg[] = 'Kota tidak ditemukan';
                }
            } else {
                $msg[] = 'Provinsi tidak ditemukan';
            }

            $order = Order::find($id);

            $order_data = $order->data;
            $req = $request->all();
            $data = [];

            foreach ($order_data as $keys => $da) {
                if (!empty($req[$keys])) {
                    if ($req[$keys] != "" && $req[$keys] != "null" && $req[$keys] != null) {
                        if ($request->hasFile($keys)) {
                            $data[$keys] = array("type" => $da["type"], "name" => $da["name"]);
                            //foreach ($request->file($keys) as $xxd => $file) {
                                $file = $request->file($keys);
                                $filename = substr(md5(microtime()), rand(0, 26), 5) . "_" . $file->getClientOriginalName();
                                $data[$keys]["data"][] = ($filename != null) ? $filename : "null";
                                $file->move(public_path() . "/uploads/file", $filename);
                            //}
                        } else {
                            if (isset($req[$keys])) {
                                $data[$keys] = array("type" => $da['type'], "data" => $req[$keys], "name" => $da["name"]);
                            }
                        }
                    }
                }
            };

            foreach ($order['data'] as $key => $hint) {
                if (!empty($data[$key])) {
                    $order_data[$key] = $data[$key];
                }
            }
            if (!empty($msg)) {
                return response(['message' => $msg, 'status' => false], 422);
            }
            $order->data = $order_data;
            $order->status = 0;
            $order->save();

            $updateAdira = AdiraTransaction::where('order_id',$order->id)->first();
            $updateAdira->adira_status = null;
            $updateAdira->save();

            $newTotalCart = 0;
            $cart = Cart::where('id',$order->cart_id)->first();
                foreach ($cart->data as $key => $value) {
                    if ($value['inquiry_id'] != null) {
                        $getInquiry = InquiryMv::find($value['inquiry_id']);
                        $newTotalCart += $getInquiry->total;
                    }else{
                        $newTotalCart += $value['total'];
                    }
                }
            $cart->total = $newTotalCart;
            $cart->save();
            Log::warning("data resubmit". json_encode($data));
            dispatch(new ResubmitJob($order->id, $data, $request['policyType'], $request['start_date'], $order->inquiry->id));
            return response(["message" => 'Success', 'status' => 200], 200);
        } catch (\Exception $e) {
            return response(["message" => 'Oops, something went wrong', 'errors' => $e->getMessage() . ' ' . $e->getLine(), 'status' => false], 500);
        }
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
