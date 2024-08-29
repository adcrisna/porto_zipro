<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Chassist;
use App\Models\AdiraProvince;
use App\Models\Order;
use App\Models\InquiryMv;
use App\Models\AdiraTransaction;
use App\Models\Product;
use App\Models\BasePrice;
use App\Models\AdiraLocation;
use App\Models\MVSub;
use App\Models\Zone;
use App\Models\FloodQuake;
use App\Jobs\OrderJob;
use GuzzleHttp\Client;

use Exception;

class AdiraController extends Controller
{
    public function chassisExisting(Request $request)
    {
        // return response(['message' => 'Nomor rangka sudah terdaftar', 'status' => 200, 'existing' => true], 200);
        $chassisExists = Chassist::where('chassis', $request->chassis_number)->first();
        if (!empty($chassisExists)) {
            return response(['message' => 'Nomor rangka sudah terdaftar', 'status' => 500, 'existing' => true], 500);
        }
        // return 'tes';
        $client = new Client();
        $result = $client->post(
            env('ADIRA_URL') . '/valencia/v1/authenticate/authtoken',
            [
                'verify' => false,
                'headers' => [
                    'Content-Type' => 'Application/Json',
                    'Authorization' => env('ADIRA_TOKEN_CHASSISS'),
                    'source' => 'PORTALMV',
                    'businesssource' => 'MV-DATAMASTER'
                ]
            ]
        );
        // ADIRA_TOKEN = "Basic U0bm6gGLpubF3WU2ELtdbZFXQvZfiFoHvgHffBZFVaQ=|U0bm6gGLpubF3WU2ELtdbZFXQvZfiFoHvgHffBZFVaQ="
        $author_token = $result->getBody();
        $data['REQUESTDATA']['QUERYNAME'] = "PMV_INQUIRYPOLICY";
        $data['REQUESTDATA']['Param1'] = $request->chassis_number;
        
        //return $author_token;
        $client_ret = new Client();
        $retrieve = $client_ret->post(
            env('ADIRA_URL') . '/valencia/v1/datamaster/retrieve',
            [
                'verify' => false,
                'headers' => [
                    'Content-Type' => 'Application/Json',
                    'Authorization' => env('ADIRA_TOKEN'),
                    'businesssource' => 'MV-DATAMASTER',
                    'token' => json_decode($author_token, true)['ResponseData']['Table1'][0]['AuthToken']
                ],
                'json' =>  $data
            ]
        );
        $table = json_decode($retrieve->getBody(), true);
        if (empty($table['ResponseData']['Table'])) {
            return response(['message' => 'Madep', 'status' => 200, 'existing' => false], 200);
        } else if (!empty($table['ResponseData']['Table'])) {
            if ($table['ResponseData']['Table'][0]['STATUS'] == "CANCEL") {
                return response(['message' => 'Madep', 'status' => 200, 'existing' => false], 200);
            } else {
                return response(['message' => 'Kendaraan sudah mempunyai polis', 'status' => 500, 'existing' => true], 500);
            }
        }
    }

    public function getProvincy()
    {
        try {
            $locations = AdiraProvince::select('id', 'province as name')->get();
            return response([
                "status" => true,
                "data" => $locations
            ], 200);
        } catch (\Exception $e) {
            return response([
                "status" => false,
                "message" => $e->getMessage()
            ], 500);
        }
    }

    public function getLocation(Request $request)
    {
        try {
            $location = AdiraProvince::find($request->province_id);
            if ($location == null) {
                throw new Exception("Province not found", 404);
            }
            $city = [];

            foreach ($location->cities as $key_city => $cities) {
                $city[] = $cities;
            }

            return response([
                "status" => true,
                "data" => $city
            ], 200);
        } catch (\Exception $e) {
            return response([
                "status" => false,
                "message" => $e->getMessage()
            ], $e->getCode() ?? 500);
        }
    }

    public function retryadira(Request $request)
    {
        // return "haha";
        $order = [$request->get('order_id')];
        foreach ($order as $order_id) {
            // return 'sini';
            $trans = AdiraTransaction::where("order_id", $order_id)->orderBy('id', 'desc')->first();
            $res = [];
            if (isset($trans->adira_response["status"])) {
                // return 'sini';
                if ($trans->adira_response["status"] == "success") {
                    $res[][$order_id] = "fail";
                } else {
                    
                    $del = AdiraTransaction::where("order_id", $order_id)->delete();
                    $order = Order::find($order_id);
                    $product = Product::find($order->product_id);
                    $inquiry = InquiryMv::where("order_id", $order_id)->orderBy('id', "desc")->first();
                    OrderJob::dispatch($order, $inquiry, $product);
                    // dispatch(new OrderJob($order->id, $order->data, $order->additional_data[0]["copy"], $order->start_date." 00:00:00.000", $inquiry->id, $order->product, 0));
                    $res[][$order_id] = "success";
                }
            }else{
                $order = Order::find($order_id);
                $product = Product::find($order->product_id);
                $inquiry = InquiryMv::where("order_id", $order_id)->orderBy('id', "desc")->first();
                OrderJob::dispatch($order, $inquiry, $product);
                // dispatch(new OrderJob($order->id, $order->data, $order->additional_data[0]["copy"], $order->start_date." 00:00:00.000", $inquiry->id, $order->product, 0));
                $res[][$order_id] = "success";
            }
        }

        return $res;
    }

    public function callback(Request $request)
    {
        try {
            $json = $request;
            $transaction = AdiraTransaction::where('request_number', $json['data']['policy']['requestNumber'])->first();

            if (!empty($transaction)) {
                $order = Order::find($transaction->order_id);
                $transaction->polling_response = $request->all();
                $transaction->adira_status = $json['data']['policy']['status'];
                $transaction->save();

                switch ($json['data']['policy']['status']) {
                    case 'Approved':

                        $order->status = 2;
                        $order->save();

                        break;

                    case 'Finished':
                        Log::info('STATUS KALO FINISH');
                        Log::info($json);
                        $name = $json['data']['policy']['requestNumber'];
                        $no_polis = $json['data']['policy']['carePolicyNo'];

                        $docPolicy = Http::post(
                            env('ADIRA_ORDER_URL') . 'api/v1/documents-policy',
                            [
                                'verify' => false,
                                'form_params' => [
                                    "apiKey" => env('ADIRA_KEY'),
                                    "requestNumber" => $transaction->request_number,
                                ]
                            ]
                        );

                        $json_policy = json_decode($docPolicy->getBody(), true);
                        if ($json_policy['status'] != 'fail') {

                            $sendPolicy = Http::post(
                                env('APP_API_URL') . 'api/trigger/create-policy',
                                [
                                    'verify' => false,
                                    'form_params' => [
                                        "order_id" => $order->id,
                                        "policy_no" => $no_polis,
                                        "name" => $name,
                                    ]
                                ]
                            );
                            $transaction->status = "send";
                        }

                        $order->status = 2;
                        $order->save();
                        break;

                    case 'Canceled':
                        $transaction->status = 'stop';
                        $transaction->save();

                        $order->status = 1;
                        $order->save();
                        break;

                    case 'Rejected':
                        $transaction->status = 'stop';
                        $transaction->save();

                        $order->status = 1;
                        $order->save();
                        break;

                    case 'Back Order':
                        $transaction->status = 'success';
                        $transaction->save();

                        $order->status = 1;
                        $order->save();
                        break;

                    default:
                        $transaction->adira_status = $json['data']['policy']['status'];
                        $transaction->save();

                        $order->status = 0;
                        $order->save();
                        break;
                }
            } else {
                return response([
                    "status" => false,
                    "message" => "Request Number " . $json['data']['policy']['requestNumber'] . " not found."
                ], 404);
            }

            return response(["status" => "success", "data" => "Request Number " . $json['data']['policy']['requestNumber'] . " has been successfuly updated."], 200);
        } catch (\Exception $th) {
            return response([
                "status" => false,
                "message" => $th->getMessage() . " " . $th->getLine()
            ], 500);
        }
    }

    public function calculatePerluasan(Request $request)
    {
        try {
            $value = json_decode($request->getContent(), true);

            $message_error = [];
            $total = [];
            $type = $value['type'];

            $date_req = date_create_from_format("Y", $value['tahun']);

            $diff = date("Y") - $value['tahun'];

            if (date("Y") !== $value['tahun']) {
                $value['newcar'] = false;
            }

            $acc = array_sum($total);

            $mobil = BasePrice::find($value['model']);
            $tahun = $value['tahun'];
            $price = $mobil['price'][$tahun];
            $total = $price;

            if ($value['perluasan'][5] == 1) {
                if ($value['detail_perluasan'][5]['up'] == "0") {
                    $message_error[] = 'Uang pertanggungan PA Pengemudi tidak diisi';
                }
                if ($value['detail_perluasan'][5]['up'] > 50000000 || $value['detail_perluasan'][5]['up'] > ($value['price'] + $acc)) {
                    $message_error[] = 'Uang pertanggungan PA Pengemudi lebih dari Rp50.000.000 atau diatas total uang pertanggungan (TSI)';
                }
            }

            if ($value['perluasan'][6] == 1) {
                if ($value['detail_perluasan'][6]['up'] == "0" || $value['detail_perluasan'][6]['passenger'] == "") {
                    $message_error[] = 'Uang pertanggungan PA Penumpang (4 orang) tidak diisi';
                }
                if ($value['detail_perluasan'][6]['passenger'] > "4") {
                    $message_error[] = 'Jumlah penumpang pada PA Penumpang melebihi 4 orang';
                }
                if ($value['detail_perluasan'][6]['up'] > 25000000 || $value['detail_perluasan'][6]['up'] > ($value['price'] + $acc)) {
                    $message_error[] = 'Uang pertanggungan PA Penumpang lebih dari Rp25.000.000 atau diatas total uang pertanggungan (TSI)';
                }
            }

            if ($value['perluasan'][7] == 1) {
                if ($value['detail_perluasan'][7]['up'] == "0") {
                    $message_error[] = 'Uang pertanggungan TPL tidak diisi';
                }
                if ($value['detail_perluasan'][7]['up'] > ($value['price'] + $acc)) {
                    $message_error[] = 'Uang pertanggungan TPL diatas total uang pertanggungan (TSI)';
                }
                if ($value['type'] == 0 && $value['detail_perluasan'][7]['up'] > 100000000) {
                    $message_error[] = 'Uang pertanggungan TPL lebih dari Rp100.000.000';
                }
                if ($value['type'] == 1 && $value['detail_perluasan'][7]['up'] > 50000000) {
                    $message_error[] = 'Uang pertanggungan TPL lebih dari Rp50.000.000';
                }
            }
            if (!empty($message_error)) {
                return response(['message' => $message_error, 'status' => 422], 422);
            }
            $plat = AdiraLocation::where("code", $value['kode_plat'])->first();

            $plate = $plat['area'][1];
            return $this->hitungPerluasanSaja($value['perluasan'], $plate, $type, ($value['price'] + $acc), $value['detail_perluasan'], $value, $acc, $mobil);
        } catch (\Exception $e) {
            return response(["message" => 'Oops, something went wrong', 'errors' => $e->getMessage() . ' ' . $e->getLine(), 'status' => false], 500);
        }
    }
    public function hitungPerluasanSaja($perluasan, $kode_plat, $type, $total, $detail_perluasan, $value, $acc,  $mobil)
    {

        unset($perluasan[7]);

        $year_now = date("Y");
        $five_years_ago = $year_now - 5;

        //LOADING RATE
        if ($value['tahun'] < $five_years_ago) {
            $percent = 5 * ($five_years_ago - $value['tahun']);
        } else {
            $percent = 0;
        }
        // return $mobil;
        // $occupation = $value['okupansi'] == 'OFFICIAL' || $value['okupansi'] == 'PERSONAL' ? 'PERSONAL' : 'COMMERCIAL';
        switch ($value['okupansi']) {
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

        if ($mobil->type == "Truck" || $mobil->type == "Bus") {
            $carType = $mobil->typeDetail;
            $zone = Zone::where('type', strtoupper($mobil->type))->where('occupation', $occupation)->first();
        } else {
            $carType = $mobil->type == null ? "CAR" : $mobil->type;
            $zone = Zone::where('code', $cat)->where('occupation', $occupation)->where('type', strtoupper($carType))->first();
        }
        // return response(["message" => $mobil], 500);
        // return $zone;
        $policyfeet = $zone->floor['tlo'][$kode_plat - 1];
        $policyfeea = $zone->floor['allrisk'][$kode_plat - 1];
        // return $policyfeet;
        $policy_fee_tlo = $policyfeet;
        $policy_fee_allrisk = (($percent / 100) * $policyfeea) + $policyfeea;
        if ($value['type'] == 0) {
            $detail[] = array("detail" => "Comprehensive", "price" => floor($total * (($policy_fee_allrisk) / 100)));
        } elseif ($value['type'] == 1) {
            $detail[] = array("detail" => "Total Loss Only", "price" => floor($total * ($policy_fee_tlo / 100)));
        }
        // return ($five_years_ago - $value['tahun']);

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
        $fix_type = $type == true ? 1 : 0;
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
                if (!in_array($type, $plan)) {
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
        // return response()->json(['topping' => $topping, 'rate' => $rate]);
        foreach ($topping as $toping) {
            $rat = null;
            foreach ($rate as $ra) {
                if ($ra->type == $toping->code) {
                    $rat = $ra;
                    break;
                }
            }
            $test_rate['rate'][] = $rat;
            $test_rate['top'][] = $toping;
            // return $rate;

            // if(!isset($rat->default_rate)){
            //     return $toping;
            // }
            $premium = $rat->rate[$rat->default_rate] / 100;
            // return $premium;
            // $detail = $premium / 100;
            if ($toping->against == 'total') {
                # code...
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
        if ($value['perluasan'][7] == 1) {
            $rate7 = $this->getTPLRate($type, (int) $value['detail_perluasan'][7]['up']);
            $detail[] = array("detail" => "TPL", "price" => floor($rate7));
        }

        $tot = 0;
        foreach ($detail as $det) {
            $tot = $tot + $det['price'];
        }

        // $detail[] = array("detail"=>"total","price"=>floor($tot));
        // $inquiry->acc = $acc;
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
        // 
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
