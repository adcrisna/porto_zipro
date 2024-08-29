<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\InquiryMv;
use App\Models\MVSub;
use App\Models\BasePrice;
use App\Models\Product;
use App\Models\AdrLocation;
use App\Models\Zone;
use App\Models\FloodQuake;
use App\Models\AdiraLocation;
use PDF, Log, DB;
use App\Jobs\OfferingJob;
use App\Jobs\PerjalananJob;
use App\Models\Cart;
use App\Mail\OfferingMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OfferController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function mail()
    {
        $order = Order::find(6);
        // return  $order;
        return view("mail.offer", compact('order'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function pdf()
    {
        $order = Order::find(10329);
        dispatch(new PerjalananJob($order));
        return "haha";
        $order = Order::find(10318);
        Log::warning("ini Order:");
        $inquiry = InquiryMv::where('order_id', $order->id)->first();
        $path = asset('assets/img/zipro.png');
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        $acc = [];
        for ($i = 1; $i <= 5; $i++) {
            $acc[] = !empty($inquiry->data['aksesoris']) ? $inquiry->data['aksesoris'][$i]['harga'] * (int) $inquiry->data['aksesoris'][$i]['qty'] : 0;
        }
        $total = array_sum($acc);
        $newPerluasan = $this->perluasan();
        // return $inquiry->item;
        // foreach ($inquiry->item as $key => $data) {
        //     return count($value);
        // }
        $namePDF = "surat_penawaran_asuransi_" . md5($order->id) . ".pdf";
        $savepath = storage_path('uploads/pdf/' . $namePDF);
        if (file_exists($savepath)) {
            unlink($savepath);
        }
        $pdf = PDF::loadView("pdf.offer", compact('order', 'inquiry', 'base64', 'total', 'newPerluasan'));
        $pdf->save(storage_path('uploads/pdf/' . $namePDF));

        return $pdf->stream();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        try {
            $order = Order::find(5);
            $product = $order->product;
            $inquiry = InquiryMv::where('order_id', $order->id)->first();
            $email =  $order->data[9]['data'];

            $namePDF = "Surat_penawaran_asuransi_kendaraan_" . md5($order->id) . ".pdf";
            $pdf = PDF::loadView('pdf.offer', compact('order', 'inquiry'))->setPaper('a4', 'potrait');
            $pdf->save(storage_path('uploads/pdf/' . $namePDF));
            if ($pdf) {
                Mail::to($email)->send(new OfferingMail($product, $namePDF));
            }
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function calculate(Request $request, $id)
    {
        try {
            $value = json_decode($request->getContent(), true);

            $message_error = [];
            $total = [];
            $type = $value['type'];
            // for ($i = 1; $i <= 5; $i++) {
            //     $req = 0;
            //     if ($value['aksesoris'][$i]['merk'] !== "") {
            //         $req = $req + 1;
            //     }
            //     if ($i !== 1) {
            //         if ($value['aksesoris'][$i]['merk'] !== "") {
            //             $req = $req + 1;
            //         }
            //         if ($value['aksesoris'][$i]['harga'] !== "" && $value['aksesoris'][$i]['harga'] !== "0") {
            //             $req = $req + 1;
            //         }
            //     } else {
            //         $req = $req + 1;
            //     }
            //     if ($value['aksesoris'][$i]['qty'] !== "" && $value['aksesoris'][$i]['qty'] !== "0") {
            //         $req = $req + 1;
            //     }
            //     if ($req == 2) {
            //         $message_error[] = 'Aksesoris ke ' . $i . ' tidak lengkap ';
            //     }
            //     if ($req = 3) {
            //         $total[] = (int) $value['aksesoris'][$i]['harga'] * (int) $value['aksesoris'][$i]['qty'];
            //     }
            // }

            foreach ($value['aksesoris'] as $keyAcc => $acc) {
                $filter = array_filter($acc);
                $count_success = count($filter);

                if ($count_success != 3 && $count_success != 0) {
                    $err_msg[] = "Aksesoris ke $keyAcc tidak lengkap";
                } else {
                    $acc_price[] = (int) $acc['harga'] * (int) $acc['qty'];
                }
            }

            $acc = array_sum($acc_price);
            $date_req = date_create_from_format("Y", $value['tahun']);

            $diff = date("Y") - $value['tahun'];

            if ($value['type'] == 0 && $diff > 10) {
                $message_error[] = 'Usia kendaraan diatas 10 tahun';
            }
            if ($value['type'] == 1 && $diff > 15) {
                $message_error[] = 'Usia kendaraan diatas 15 tahun';
            }

            if (date("Y") !== $value['tahun']) {
                $value['newcar'] = false;
            }

            // $acc = array_sum($total);

            $mobil = BasePrice::find($value['model']);
            $tahun = $value['tahun'];
            $price = $mobil['price'][$tahun];
            $total = $price;
            $policy_type = $value['policy_type'];

            if (($value['price']) > ($price + $price * 20 / 100) || ($value['price'])  < ($price - $price * 20 / 100)) {
                $message_error[] = 'Harga kendaraan tidak dapat 20% lebih rendah/melebihi harga yang ditentukan';
            }
            if (($value['price'] + $acc) > 2000000000) {
                $message_error[] = 'Total uang pertanggungan (TSI) di atas Rp2.000.000.000';
            }

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
            return $this->hitungPerluasan($value['perluasan'], $plate, $type, ($value['price'] + $acc), $value['detail_perluasan'], $value, $acc, $mobil, $policy_type, $id);
        } catch (\Exception $e) {
            return response(["message" => 'Oops, something went wrong', 'errors' => $e->getMessage() . ' ' . $e->getLine(), 'status' => false], 500);
        }
    }

    public function hitungPerluasan($perluasan, $kode_plat, $type, $total, $detail_perluasan, $value, $acc, $mobil, $policy_type, $inquiry_id)
    {
        unset($perluasan[7]);

        $year_now = date("Y");
        $five_years_ago = $year_now - 5;

        if ($value['tahun'] < $five_years_ago) {
            $percent = 5 * ($five_years_ago - $value['tahun']);
        } else {
            $percent = 0;
        }
        Log::warning($value['okupansi']);
        // $occupation = $value['okupansi'] == 'DINAS' || $value['okupansi'] == 'PRIBADI' ? 'PERSONAL' : 'COMMERCIAL';
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

          Log::warning($occupation);

        $cat = $this->getCat($total);
        // return $occupation;
        if ($mobil->type == "Truck" || $mobil->type == "Bus") {
            $carType = $mobil->typeDetail;
            $zone = Zone::where('type', strtoupper($mobil->type))->where('occupation', $occupation)->first();
        } else {
            $carType = "CAR";
            $zone = Zone::where('code', $cat)->where('occupation', $occupation)->where('type', strtoupper($mobil->type))->first();
        }

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
        // return $topping;
        foreach ($topping as $key_top => $top) {
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
            $data_test[$top->id] = $plan;
            $code[] = $top->code;
        };

        $rate = FloodQuake::whereIn("zone", $zone)->whereIn("plan", $plan)->get();
        // return $plan;
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
            // 159.000.000  
            // 11.495.700
        }
        if ($value['perluasan'][7] == 1) {
            $rate7 = $this->getTPLRate($type, (int) $value['detail_perluasan'][7]['up']);
            $detail[] = array("detail" => "TPL", "price" => floor($rate7));
        }

        // return $test_rate;
        $tot = 0;
        foreach ($detail as $det) {
            $tot = $tot + $det['price'];
        }

        $value['type'] = $value['type'] == true ? 1 : 0;

        $value['policy_type'] = $policy_type;
        try {
            DB::beginTransaction();

            $inquiry = InquiryMv::find($inquiry_id);
            if (isset($inquiry->data['policy_no'])) {
                // return 'masuk';
                $value['policy_no'] = $inquiry->data['policy_no'];
            }
            // return 'tidak masuk';
            // return $inquiry;
            if (!isset($inquiry)) {
                return response(["message" => 'Penawaran didnt exist', 'status' => false], 404);
            }
            $inquiry->item = $detail;
            $inquiry->total = $tot;
            $inquiry->product_id = $value['product'];
            $inquiry->status = false;
            $inquiry->data = $value;
            $inquiry->update();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return response(["message" => 'Oops, something went wrong', 'errors' => $e->getMessage() . ' ' . $e->getLine(), 'status' => false], 500);
        }
        // return "SUCCES DUIDEEE";
        $detail[] = array("detail" => "total", "price" => floor($tot));
        $inquiry->acc = $acc;
        unset($inquiry->data);
        return response(["message" => 'success', 'data' => $inquiry, 'status' => 201], 201);
    }

    public function updateInquiry(request $request)
    {
        $data = json_decode($request->getContent(), true);
        try {
            $inquiry = InquiryMv::find($data['inquiry_id']);
            if (!$inquiry) {
                return response(["message" => 'Penawaran didnt exist', 'status' => false], 404);
            }

            $order = Order::find($inquiry->order_id);
            // $product = $order->product;
            $product = Product::find($inquiry->product_id);
            $schemaComission = $product->schemaComission;
            $fix_discount = $data['value_discount'] == "" ? 0 : $data['value_discount'];
            if ($data['policy'] == 'soft') {
                $policy = 10000;
            }
            if ($data['policy'] == 'hard') {
                $policy = 50000;
            }

            if (!empty($data['type']) && $data['type'] == "MV") {

                $total = array_sum(array_column($inquiry->item, 'price'));
                $verif_des = ($total * (25 / 100));

                if ($data['type_discount'] == "percent") {
                    if ($schemaComission->comission['type'] == "percent" && $data['value_discount'] > ($schemaComission->comission['value'] * 100)) {
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
                // return $total;
                // $fix_total = $total + $policy;

                $inquiry->discount = $fix_discount;
                $inquiry->total = $total;
                $inquiry->save();

                // if (!empty($order)) {
                //     $order->base_price = $total;
                //     $order->total = $total;
                //     $order->save();
                // }

                $discount = ($total * ($fix_discount / 100));
                $sum  = $total - $discount + $policy;

                // $inquiry->sum_discount = floor($discount);
                // $inquiry->sum_total = floor($sum);
                // $inquiry->policy = $policy;
                // $inquiry->newcar = $inquiry->data['newcar'];
            } else {
                $total = array_sum(array_column($inquiry->item, 'price'));
                $verif_des = ($total * (25 / 100));
                $fix_total = $total + $policy;

                if ($data['type_discount'] == "percent") {
                    if ($schemaComission->comission['type'] == "percent" && $data['value_discount'] > ($schemaComission->comission['value'] * 100)) {
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
                
                // return $total;

                $inquiry->discount = $fix_discount;
                $inquiry->total = $total;
                $inquiry->save();

                // if (!empty($order)) {
                //     $order->base_price = $total;
                //     $order->total = $total;
                //     $order->save();
                // }

                $discount = ($total * ($fix_discount / 100));
                $sum  = $total - $discount + $policy;
                // return $sum;
                
                // $inquiry->sum_discount = floor($discount);
                // $inquiry->sum_total = floor($sum);
                // $inquiry->policy = $policy;
                // $inquiry->newcar = $inquiry->data['newcar'];
            }
           
            // return $inquiry;

            $grand_total = $inquiry->total - ($inquiry->total * ($inquiry->discount / 100));
            // return $grand_total;
            $softcopy = $this->policyType($product->flow, $data['policy']);
            // $fixTotal = $grand_total + $softcopy;

            // return $order;
            $inquiry->total = $grand_total + $softcopy;
            $inquiry->save();

            if (!empty($order)) {
                $order->base_price = $total;
                $order->total = $grand_total;
                $order->save();
            }
            // return $order;
            // return $inquiry;

            $inquiry_item = [];
            $inquiry_item =  $inquiry->item;
            $inquiry_item[0]['copy'] = $data['policy'];
            $inquiry_item[0]['copy_price'] = $softcopy;
            $inquiry_item[0]['policy_no'] = @$inquiry->data['policy_no'];

            return response(["message" => 'Success', 'data' => $inquiry, 'status' => 200], 200);
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

    private function policyType($flow, $type)
    {

        $dat = [];
        $dat["mv"]["hard"] = 50000;
        $dat["mv"]["soft"] = 10000;
        $dat["moto"]["hard"] = 50000;
        $dat["moto"]["soft"] = 10000;
        if (isset($dat[$flow][$type])) {
            return $dat[$flow][$type];
        } else {
            return 0;
        }
    }

    public function perluasan()
    {
        $perluasan["TPL"] = "Tanggung Jawab Hukum terhadap Pihak Ketiga (Third Party Liability)";
        $perluasan["TFSHL"] = "Angin Topan, Banjir, Badai, Hujan Es & Tanah Longsor (Typhoon, Storm, Flood, Hail, Landslide)";
        $perluasan["EQVET"] = "Gempa Bumi, Tsunami & Letusan Gunung Berapi (Earthquake, Tsunami & Volcanic Eruption)";
        $perluasan["SRCC"] = "Huru-hara & Kerusuhan (Strike, Riot, & Civil Commotion)";
        $perluasan["TS"] = "Terorisme & Sabotase (Terorisme & Sabotage)";
        $perluasan["PA Pengemudi"] = "Kecelakaan Diri untuk Pengemudi (Personal Accident for Driver)";
        $perluasan["PA Penumpang (4 orang)"] = "Kecelakaan Diri untuk Penumpang (Personal Accident for Passenger)";
        return $perluasan;
    }

    public function getInquiry($id)
    {
        try {
            $inquiry = InquiryMv::find($id);
            // $order = Order::find($inquiry->order_id);
            if (empty($inquiry)) {
                return response([
                    "status" => false,
                    "message" => "Inquiry tidak ditemukan atau tidak dapat diperpanjang."
                ], 404);
            }

            $body = $inquiry?->data;
            // $req = $order?->data;

            if (!empty($inquiry)) {
                $car = BasePrice::find($inquiry?->data['model']);
            } else {
                $car = BasePrice::where('code', $body[37])->where('name', $body[38])->first();
            }
            if (empty($car)) {
                return response([
                    "status" => false,
                    "message" => "Kendaraan tidak ditemukan"
                ], 404);
            }
            $data = [];

            $location = AdiraLocation::where('code', $inquiry->data['kode_plat'])->first();
            $data['okupasi'] = $inquiry->data['okupansi'];
            $data['chassist'] = $inquiry->data['chassis'];
            $data['insurance_type'] = $inquiry->data['type'];
            $data['insurance_type_str'] = $inquiry->data['type'] == 0 ? "Comprehensive" : "Total Loss Only";

            $data['vehicle_merek'] = $car->brand;
            $data['vehicle_model'] = $car->id;
            $data['vehicle_model_str'] = $car->name;
            $data['vehicle_year'] = (int)$inquiry?->data['tahun'];
            $data['vehicle_location'] = $location->code;
            $data['vehicle_status'] = (!$inquiry->data['newcar']) ? "USED" : "NEW";
            $data['discount'] = $inquiry->discount ?? 0;
            $data['discount_type'] = "Percent";
            // ($total * ($fix_discount / 100));
            $data['discount_value'] = round($inquiry->total * ($inquiry->discount / 100));
            $data['tsi'] = floor($inquiry?->data['price']);

            $mvsub = MVSub::all();
            $perluasan = [];
            $detail_perluasan = [
                5 => [
                    "up" => "0"
                ],
                6 => [
                    "up" => "0",
                    "passenger" => "0"
                ],
                7 => [
                    "up" => "0",
                ],
            ];
            $acc = [];
            
            $acc = $inquiry?->data['aksesoris'] ?? [];
            $acc = [
                1 => [
                    "merk" => $inquiry?->data['aksesoris']['1']['merk'] ?? "",
                    "harga" => $inquiry?->data['aksesoris']['1']['harga'] ?? "0",
                    "qty" => (string) $inquiry?->data['aksesoris']['1']['qty'] ?? "0"
                ],
                2 => [
                    "merk" => $inquiry?->data['aksesoris']['2']['merk'] ?? "",
                    "harga" => $inquiry?->data['aksesoris']['2']['harga'] ?? "0",
                    "qty" => (string) $inquiry?->data['aksesoris']['2']['qty'] ?? "0"
                ],
                3 => [
                    "merk" => $inquiry?->data['aksesoris']['3']['merk'] ?? "",
                    "harga" => $inquiry?->data['aksesoris']['3']['harga'] ?? "0",
                    "qty" => (string) $inquiry?->data['aksesoris']['3']['qty'] ?? "0"
                ],
                4 => [
                    "merk" => $inquiry?->data['aksesoris']['4']['merk'] ?? "",
                    "harga" => $inquiry?->data['aksesoris']['4']['harga'] ?? "0",
                    "qty" => (string) $inquiry?->data['aksesoris']['4']['qty'] ?? "0"
                ],
                5 => [
                    "merk" => $inquiry?->data['aksesoris']['5']['merk'] ?? "",
                    "harga" => $inquiry?->data['aksesoris']['5']['harga'] ?? "0",
                    "qty" => (string) $inquiry?->data['aksesoris']['5']['qty'] ?? "0"
                ],
            ];
            
            $totalPremi = 0;
            foreach ($inquiry->item as $key => $value) {
                $totalPremi += $value['price'];
            }

            // return $totalPremi;
            $perluasan = $inquiry?->data['perluasan'];
            $detail_perluasan = $inquiry?->data['detail_perluasan'];
            
            if (!empty($inquiry->order_id)) {
                $cekOrder = Order::find($inquiry->order_id);
            }

            if (isset($inquiry->data['policy_type'])) {
                if ($inquiry->data['policy_type'] == 'hard') {
                    $feePolicyType = 50000;
                }else {
                    $feePolicyType = 10000;
                }
            }else {
                $cekOrder = Order::find($inquiry->order_id);
                if ($cekOrder->additional_data[0]['copy'] == 'hard') {
                    $feePolicyType = 50000;
                }else {
                    $feePolicyType = 10000;
                }
            }
            
            $data['perluasan'] = $perluasan;
            $data['detail_perluasan'] = $detail_perluasan;
            $data['aksesoris'] = $acc;
            $data['policy_type'] = !empty($inquiry->data['policy_type']) ? $inquiry->data['policy_type'] : 'soft';
            $data['total_premi'] = $totalPremi;
            $data['grand_total'] = $inquiry->total;

            return response([
                "status" => true,
                "message" => "Data found",
                "data" => $data
            ], 200);
        } catch (\Throwable $th) {
            return response([
                "status" => false,
                "message" => $th->getMessage() . " " . $th->getLine()
            ], 500);
        }
    }

    public function sendOfferingMail(Request $request, $id)
    {
        $carts = Cart::find($id);

        if (empty($carts)) {
            return response([
                "status" => false,
                "message" => "Cart tidak ditemukan."
            ], 404);
        }

        $carts->is_offering = true;
        $carts->offering_email = $request->offering_email;
        $carts->offering_name = $request->offering_name;
        $carts->offering_telp = $request->offering_telp;
        $carts->save();

        foreach ($carts->data as $cart) {
            $inquiry = InquiryMv::find($cart['inquiry_id']);
            if (!empty($inquiry)) {
                $dataCart = $cart;
                dispatch(new OfferingJob($carts, $dataCart, $inquiry));
            }
        }
        return response([
            "status" => true,
            "message" => "successfuly send offering mails"
        ], 200);
    }
}
