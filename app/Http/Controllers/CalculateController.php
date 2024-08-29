<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Models\BasePrice;
use App\Models\AdiraLocation;
use App\Models\Zone;
use App\Models\MVSub;
use App\Models\FloodQuake;
use App\Models\AdiraProvince;
use App\Models\AdiraTransaction;
use App\Models\Order;
use App\Models\InquiryMv;
use App\Models\Helpdesk;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\FormRepoCategory;
use App\Models\FormRepo;
use App\Models\Arrays;
use Log;
use DB;

class CalculateController extends Controller
{
    public function premiMv(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        try {
            $err_msg = [];
            $acc_price = [];

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
            // return $data['price'];
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
            if ($data['policy_type'] == 'soft') {
                $policy = 10000;
            }else{
                $policy = 50000;
            }

            DB::beginTransaction();
            $inquiry = new InquiryMv;
            $inquiry->item = $detail;
            $inquiry->total = $tot;
            $inquiry->product_id = $data['product'];
            $inquiry->status = false;
            $inquiry->data = $data;
            $inquiry->save();
            DB::commit();

            $detail[] = array("detail" => "total", "price" => floor($tot));
            $inquiry->acc = $acc;
            unset($inquiry->data);
            return response(["message" => 'success', 'data' => $inquiry, 'status' => 201], 201);
        } catch (\Exception $e) {
            return response([
                "status" => 500,
                "message" => $e->getMessage()
            ], 500);
        }
    }

    public function premiMoto(Request $request)
    {

        $data = json_decode($request->getContent(), true);

        try {
            $message_error = [];

            $car_date = date("Y") - $data['tahun'];

            if ($car_date > 10) {
                $err_msg[] = "Usia kendaraan diatas 10 tahun";
            }

            if (date("Y") !== $data['tahun']) {
                $data['newcar'] = false;
            }

            $moto = BasePrice::find($data['model']);

            $tahun = $data['tahun'];
            $price = $moto['price'][$tahun];
            $perluasan = $data['perluasan'];
            $detail_perluasan = $data['detail_perluasan'];

            if ($data['price'] > ($price + $price * 20 / 100) || $data['price'] < ($price - $price * 20 / 100) || $data['price'] >= 500000000) {
                $message_error[] = 'Harga kendaraan tidak dapat 20% lebih rendah/melebihi harga yang ditentukan';
            }

            if ($perluasan[5] == 1) {
                if ($detail_perluasan[5]['up'] == "0") {
                    $message_error[] = 'Uang pertanggungan PA Pengemudi tidak diisi';
                } elseif ($detail_perluasan[5]['up'] > 10000000) {
                    $message_error[] = 'Uang pertanggungan PA Pengemudi lebih dari Rp 10.000.000 atau diatas total uang pertanggungan (TSI)';
                }
            }
            if ($perluasan[7] == 1) {
                if ($detail_perluasan[7]['up'] == "0") {
                    $message_error[] = 'Uang pertanggungan TPL tidak diisi';
                } elseif ($detail_perluasan[7]['up'] > 10000000) {
                    $message_error[] = 'Uang pertanggungan TPL lebih dari Rp 10.000.000 atau diatas total uang pertanggungan (TSI)';
                }
            }
            if (!empty($message_error)) {
                return response(['status' => false, 'message' => $message_error], 422);
            }

            $adrLocation = AdiraLocation::where("code", $data['kode_plat'])->first();
            $plate = $adrLocation['area'][1];

            $year_now = date("Y");
            $five_years_ago = $year_now - 5;

            if ($data['tahun'] < $five_years_ago) {
                $percent = 5 * ($five_years_ago - $data['tahun']);
            } else {
                $percent = 0;
            }

            $total = $data['price'];
            $occupation = 'PERSONAL';
            $cat = $this->getCat($total);

            $zone = Zone::where('occupation', $occupation)->where('type', 'MOTORCYCLE')->first();

            $policyfeet = $zone->floor['tlo'][$plate - 1];
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
                    if (!in_array($plate, $zone)) {
                        $zone[] = $plate;
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
                    if ($data['perluasan'][5] == 1) {
                        $price = (int) $detail_perluasan[$toping->id]['up'] * 0.5 / 100;
                        $detail[] = array("detail" => "PA Pengemudi", "price" => floor($price));
                    }
                } elseif ($toping->id == 12) {
                    if ($data['perluasan'][12] == 1) {
                        $pa_penumpang_price = (int) $detail_perluasan[12]['up'] * 0.1 / 100;
                        $detail[] = array("detail" => "PA Penumpang(1 orang)", "price" => floor($pa_penumpang_price));

                        $data['detail_perluasan'][12]['passenger'] = 1;
                    }
                } elseif ($toping->id == 7) {
                    if ($data['perluasan'][7] == 1) {
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

            $data['type'] = $data['type'] == true ? 1 : 0;
            if ($data['policy_type'] == 'soft') {
                $policy = 10000;
            }else{
                $policy = 50000;
            }

            try {
                DB::beginTransaction();
                $inquiry = new InquiryMv;
                $inquiry->item = $detail;
                $inquiry->total = $tot;
                $inquiry->product_id = $data['product'];
                $inquiry->status = false;
                $inquiry->data = $data;
                $inquiry->save();
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                return response(["message" => $e->getMessage(), 'status' => false], 500);
            }

            $detail[] = array("detail" => "total", "price" => floor($tot));
            unset($inquiry->data);
            return response(["message" => 'success', 'data' => $inquiry, 'status' => 201], 201);
        } catch (\Exception $e) {
            return response([
                "status" => 500,
                "message" => $e->getMessage()
            ], 500);
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
