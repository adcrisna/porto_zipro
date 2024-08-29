<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Renewal;
use App\Models\AdiraTransaction;
use App\Models\BasePrice;
use App\Models\Zone;
use App\Models\AdiraLocation;
use App\Models\Arrays;
Use Illuminate\Support\Str;
use App\Models\MVSub;
use App\Models\FormRepo;
use App\Models\FormRepoCategory;
use App\Models\Objects;
use App\Models\Order;
use App\Models\Cart;
use App\Models\InquiryMv;

class RenewalController extends Controller
{
    public function init(Request $request)
    {
        try {
            if(empty($request->get('category_id'))) {
                return response([
                    "status" => false,
                    "message" => 'invalid request'
                ], 400);
            }
            $category_id = $request->get('category_id');
            if(!empty($request->get('policy_number'))) {
                return $this->search_by_policy($request->get('policy_number'), $category_id);
            }
            if(!empty($request->get('order_id'))) {
                return $this->renewal_duedate($request->get('order_id'));
            }
            return response([
                "status" => false,
                "message" => 'invalid request'
            ], 400);

        } catch (\Exception $e) {
            return response([
                "status" => false,
                "message" => $e->getMessage()
            ], 500);
        }
    }

    public function form_cart($cart_id)
    {
        try {
            $cart = Cart::find($cart_id);

            if (empty($cart)) {
                return response([
                    "status" => false,
                    "message" => "Cart not found"
                ], 404);
            }
            $is_renewal = false;
            $product = [];
            $form = null;
            $cartData = $cart->data ?? [];

            if (!empty($cartbody['product_data'])) $product[] = $cartbody['product_data']['name'];

            foreach ($cartData as $keyBody => $cartbody) {
                // return $cartbody['product_data']['category_id'];
                // unset($cart['data']);
                if(!empty($cartbody['inquiry_id'])) {
                    $getInquiry = InquiryMv::find($cartbody['inquiry_id']);
                    if(!empty($getInquiry) && !empty($getInquiry->data['policy_no'])) {
                        $is_renewal = true;
                        $body = Renewal::where('policy_no', $getInquiry->data['policy_no'])->first()->data;
                        // return $body[0];
                        // $cekOrder = Order::find($body->order_id);
                        // return $cekOrder;
                        // $renewal = $this->search_by_policy($getInquiry->data['policy_no'], $cartbody['product_data']['category_id'], true);
                        $refno = $body[67];
                        $adira_trx = AdiraTransaction::where('ref_number', $refno)->first();
                        $form = $this->getForm($body, $cartbody['product_data']['category_id'], $adira_trx);
                    }
                }
                
            }
            if($is_renewal == false) {
                return response([
                    "status" => true,
                    "data" => [],
                    "message" => "Product tidak dapat di renewal."
                ], 422);
            }

            return response([
                "status" => true,
                "form" => $form,
            ], 200);
        } catch (\Exception $e) {
            return response([
                "status" => true,
                "message" => $e->getMessage(),
            ], 500);
        }
    }

    private function search_by_policy($no_policy, $category_id, $controller = false)
    {
        try {
            $renew = Renewal::where('policy_no', $no_policy)->first();
            if(empty($renew)) {
                return response([
                    "status" => false,
                    "message" => "Nomor Polis tidak ditemukan"
                ], 404);
            }

            if($renew->order_id !== null && $renew->new_order_id !== null) {
                return response([
                    "status" => false,
                    "message" => "Nomor Polis sudah digunakan"
                ], 404);
            }
            
            $body = $renew->data;
            $refno = $body[67];
            $adira_trx = AdiraTransaction::where('ref_number', $refno)->first();
            $req = $adira_trx?->order?->data;
            $inqu = $adira_trx?->order?->inquiry;
            // return $req;
            if(!empty($inqu)) {
                $car = BasePrice::find($inqu?->data['model']);
            }else {
                $car = BasePrice::where('code',$body[37])->where('name', $body[38])->first();
            }
            if(empty($car)) {
                return response([
                    "status" => false,
                    "message" => "Kendaraan tidak ditemukan"
                ], 404);
            }

            $data = [];
            if(!empty($inqu)) {
                $location = AdiraLocation::where('code', $inqu->data['kode_plat'])->first();
                $data['okupasi'] = $inqu->data['okupansi'];
                $data['chassist'] = $inqu->data['chassis'];
                $data['insurance_type'] = $inqu->data['type'];
                $data['insurance_type_str'] = $inqu->data['type'] == 0 ? "Comprehensive" : "Total Loss Only";
                $data['insurer_name'] = $req[1]['data'];
                $get_date = ($body[22] - (25567 + 2)) * 86400 * 1000;
                $excelDate = $get_date / 1000;
                $data['start_date'] = date("Y-m-d", $excelDate);
                
                $data['vehicle_merek'] = $car->brand;
                $data['vehicle_model'] = $car->id;
                $data['vehicle_model_str'] = $car->name;
                $data['vehicle_year'] = (int)$inqu?->data['tahun'];
                if($inqu?->data['tahun'] < date('Y')) {
                    $data['vehicle_status'] = 'USED';
                }else {
                    $data['vehicle_status'] = 'NEW';
                }
                $data['vehicle_location'] = $location->code;
                $data['vehicle_plat'] = $req['23']['data'];
                $data['vehicle_machine'] = $req[52]['data'];
                $data['discount'] = $inqu->discount;
                $data['discount_type'] = "Percent";
                // ($total * ($fix_discount / 100));
                $data['discount_value'] = round($inqu->total * ($inqu->discount / 100));

            }else {
                $location = AdiraLocation::where('name', $body[45])->first();
                $data['chassist'] = $body[40];
                $body[44] = explode('/',$body[44])[0];
                if(Str::contains($body[44], ['PRIBADI', 'PRIVATE'])) {
                    $data['okupasi'] = "PERSONAL";
                }elseif(Str::contains($body[44], ['DINAS', 'OPERATIONAL'])) {
                    $data['okupasi'] = "OFFICIAL";
                }elseif(Str::contains($body[44], ['KOMERSIAL', 'COMMERCIAL'])) {
                    $data['okupasi'] = "COMMERCIAL";
                }
                $data['insurance_type'] = Str::contains(strtoupper($body[72]), "COMPREHENSIVE") ? 0 : 1;
                $data['insurance_type_str'] = Str::contains(strtoupper($body[72]), "COMPREHENSIVE") ? "Comprehensive" : "Total Loss Only";
                $data['insurer_name'] = $body[20];

                $get_date = ($body[22] - (25567 + 2)) * 86400 * 1000;
                $excelDate = $get_date / 1000;
                $data['start_date'] = date("Y-m-d", $excelDate);
                
                $data['vehicle_merek'] = $car->brand;
                $data['vehicle_model'] = $car->id;
                $data['vehicle_model_str'] = $car->name;
                $data['vehicle_year'] = (int)$body[43];
                if($body[43] < date('Y')) {
                    $data['vehicle_status'] = 'USED';
                }else {
                    $data['vehicle_status'] = 'NEW';
                }
                $data['vehicle_location'] = $location->code;
                $data['vehicle_plat'] = $body[39];
                $data['vehicle_machine'] = $body[41];
                $data['discount'] = $body[35];
                $data['discount_type'] = "Percent";
                // ($total * ($fix_discount / 100));
                $data['discount_value'] = $body[35] !== 0 ? round($body[87] * ($body[35] / 100)) : $body[35];
            }
            $data['tsi'] = floor($body[76]);

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
            if(!empty($inqu)) {
                $acc = $inqu?->data['aksesoris'];
            }else {
                // 1
                $acc[1]["merk"] = $body[108] ?? "";
                $acc[1]["harga"] = "1";
                $acc[1]["qty"] = !empty($body[118]) ? (string)$body[118] : "0";
                // 2
                $acc[2]["merk"] = $body[109] ?? "";
                $acc[2]["harga"] = !empty($body[129]) ? (string)$body[129] : "0";
                $acc[2]["qty"] = !empty($body[119]) ? (string)$body[119] : "0";
                // 3
                $acc[3]["merk"] = $body[110] ?? "";
                $acc[3]["harga"] = !empty($body[130]) ? (string)$body[130] : "0";
                $acc[3]["qty"] = !empty($body[120]) ? (string)$body[120] : "0";
                // 4
                $acc[4]["merk"] = $body[111] ?? "";
                $acc[4]["harga"] = !empty($body[131]) ? (string)$body[131] : "0";
                $acc[4]["qty"] = !empty($body[121]) ? (string)$body[121] : "0";
                // 5
                $acc[5]["merk"] = $body[112] ?? "";
                $acc[5]["harga"] = !empty($body[132]) ? (string)$body[132] : "0";
                $acc[5]["qty"] = !empty($body[122]) ? (string)$body[122] : "0";
            }

            $coverage_ex = strtoupper($body[73]);
            if(!empty($inqu)) {
                $perluasan = $inqu?->data['perluasan'];
                $detail_perluasan = $inqu?->data['detail_perluasan'];
            }else {
                foreach($mvsub as $topping) {
                    if($topping->id !== 12) {
                        $perluasan[$topping->id] = false;
                    }
                    if(Str::contains($coverage_ex, 'ANGIN')) {
                        $perluasan[1] = true;
                    }
                    if(Str::contains($coverage_ex, 'GEMPA')) {
                        $perluasan[2] = true;
                    }
                    if(Str::contains($coverage_ex, ['HURU-HARA', 'RIOT', 'KERUSUHAN', 'HURU HARA'])) {
                        $perluasan[3] = true;
                    }
                    if(Str::contains($coverage_ex, ['TERORISME', 'SABOTASE'])) {
                        $perluasan[4] = true;
                    }
                    if(Str::contains($coverage_ex, 'PENGEMUDI')) {
                        $actual_driver = 0;
                        if($body[104] > 50000000) {
                            $actual_driver = 50000000;
                        }else {
                            $actual_driver = ceil($body[104]/5E6)*5E6;
                        }
                        // return $body[104];
                        $perluasan[5] = true;
                        $detail_perluasan[5]["up"] = (string)$actual_driver ?? "0";
                    }
                    if(Str::contains($coverage_ex, 'PENUMPANG')) {
                        $perluasan[6] = true;
                        if($body[107] > 4) {
                            $body[107] = 4;
                        }else {
                            $body[107] = ceil($body[107]);
                        }
                        if($body[107] == 0) {
                            $divide = $body[105];
                        }else {
                            $divide = $body[105] / $body[107];
                        }
                        // return $divide;
                        $actual_pass = 0;
                        if($divide > 25000000) {
                            $actual_pass = 25000000;
                        }else {
                            $actual_pass = ceil($divide/5E6)*5E6; 
                        }
                        $detail_perluasan[6]["up"] = (string)$actual_pass ?? "0";
                        $detail_perluasan[6]["passenger"] = (string)$body[107]; 
                    }
                    // return $detail_perluasan;
                    if(Str::contains($coverage_ex, 'TANGGUNG')) {
                        // $body[96] = TPL
                        $premi = $body[96];
                        $tpl = 0;
                        $up = [25000000,25000000,50000000];
                        $rate = [1, 0.5 ,0.25];
                        $step = 0;
                        if($premi > 500000){
                            return 0;
                        }elseif($premi < 50000){
                            $premi = 50000;
                        }
                        foreach($up as $keys=>$u){
                            $ups = $u*($rate[$keys]/100);
                            $step = $step +1;
                            if($premi - $ups > 1){
                                    $tpl = $tpl + $u;
                                    $premi = $premi - $ups;
                            }elseif($premi - $ups <= 0){
                                    $upss = ($premi/$rate[$keys])*100;
                                    //return $premi;
                                    $premi =0;
                                    //return $ups;
                                    $tpl = $tpl + $upss;
                                    break 1;
                            }
                        }
                        $val = ceil($tpl/5E6)*5E6;
                        if($val > 100000000){
                            $tpl_total = 0;
                        }else{
                            $tpl_total = ceil($tpl/5E6)*5E6;
                        }
                        $perluasan[7] = true;
                        $detail_perluasan[7]["up"] = !empty($inqu->data['detail_perluasan'][7]) ? $inqu->data['detail_perluasan'][7]['up'] : (string)$tpl_total;
                    }
                    if(Str::contains($coverage_ex, ['AUTHORIZED', 'BENGKEL'])) {
                        $perluasan[10] = true;
                    }
                    if(Str::contains($coverage_ex, ['AUTOCILLIN', 'FITUR'])) {
                        $perluasan[11] = true;
                    }  
                }
            }
            
            $data['perluasan'] = $perluasan;
            $data['detail_perluasan'] = $detail_perluasan;
            $data['aksesoris'] = $acc;
            $data['form'] = $this->getForm($body, $category_id, $adira_trx);
            // if($controller) {
            //     return $data;
            // }
            return response([
                "status" => true,
                "message" => "Polis ditemukan",
                "data" => $data
            ], 200);

        } catch (\Exception $e) {
            return response([
                "status" => false,
                "message" => $e->getMessage().' '.$e->getLine(),
                "line" => $e->getLine()
            ], 500);
        }
    }

    private function renewal_duedate($id)
    {
        try {
            $order = Order::find($id);
            $renew = Renewal::where('order_id', $order?->id)->first();
            if(empty($order)) {
                return response([
                    "status" => false,
                    "message" => "Order tidak ditemukan atau tidak dapat diperpanjang."
                ], 404);
            }
            if(!empty($renew)) {
                if($renew->order_id !== null && $renew->new_order_id !== null) {
                    return response([
                        "status" => false,
                        "message" => "Nomor Polis sudah digunakan"
                    ], 404);
                }
            }

            $body = $renew?->data;
            $req = $order?->data;
            $inqu = $order?->inquiry;
            if(!empty($inqu)) {
                $car = BasePrice::find($inqu?->data['model']);
            }else {
                $car = BasePrice::where('code',$body[37])->where('name', $body[38])->first();
            }
            if(empty($car)) {
                return response([
                    "status" => false,
                    "message" => "Kendaraan tidak ditemukan"
                ], 404);
            }
            $data = [];
            $data['policy_number'] = $renew?->policy_no;
            if(!empty($inqu)) {
                // return "sini";
                $location = AdiraLocation::where('code', $inqu->data['kode_plat'])->first();
                $data['okupasi'] = $inqu->data['okupansi'];
                $data['chassist'] = $inqu->data['chassis'];
                $data['insurance_type'] = $inqu->data['type'];
                $data['insurance_type_str'] = $inqu->data['type'] == 0 ? "Comprehensive" : "Total Loss Only";
                $data['insurer_name'] = $req[1]['data'];
                $get_date = ($body[22] - (25567 + 2)) * 86400 * 1000;
                $excelDate = $get_date / 1000;
                $data['start_date'] = date("Y-m-d", $excelDate);
                // return $body;
                $data['vehicle_merek'] = $car->brand;
                $data['vehicle_model'] = $car->id;
                $data['vehicle_model_str'] = $car->name;
                $data['vehicle_year'] = (int)$inqu?->data['tahun'];
                $data['vehicle_status'] = (!$inqu?->data['newcar']) ? "USED" : "NEW" ?? "USED";
                $data['vehicle_location'] = $location->code;
                $data['vehicle_plat'] = $req[23]['data'] ?? null;
                $data['vehicle_machine'] = $req[52]['data'] ?? null;
                $data['discount'] = $inqu->discount;
                $data['discount_type'] = "Percent";
                // ($total * ($fix_discount / 100));
                $data['discount_value'] = round($inqu->total * ($inqu->discount / 100));

            }else {
                $location = AdiraLocation::where('name', $body[45])->first();
                $data['chassist'] = $body[40];
                $body[44] = explode('/',$body[44])[0];
                if(Str::contains($body[44], ['PRIBADI', 'PRIVATE'])) {
                    $data['okupasi'] = "PERSONAL";
                }elseif(Str::contains($body[44], ['DINAS', 'OPERATIONAL'])) {
                    $data['okupasi'] = "OFFICIAL";
                }elseif(Str::contains($body[44], ['KOMERSIAL', 'COMMERCIAL'])) {
                    $data['okupasi'] = "COMMERCIAL";
                }
                $data['insurance_type'] = Str::contains(strtoupper($body[72]), "COMPREHENSIVE") ? 0 : 1;
                $data['insurance_type_str'] = Str::contains(strtoupper($body[72]), "COMPREHENSIVE") ? "Comprehensive" : "Total Loss Only";
                $data['insurer_name'] = $body[20];

                $get_date = ($body[22] - (25567 + 2)) * 86400 * 1000;
                $excelDate = $get_date / 1000;
                $data['start_date'] = date("Y-m-d", $excelDate);
                
                $data['vehicle_merek'] = $car->brand;
                $data['vehicle_model'] = $car->id;
                $data['vehicle_model_str'] = $car->name;
                $data['vehicle_year'] = (int)$body[43];
                if($body[43] < date('Y')) {
                    $data['vehicle_status'] = 'USED';
                }else {
                    $data['vehicle_status'] = 'NEW';
                }
                $data['vehicle_location'] = $location->code;
                $data['vehicle_plat'] = $body[39];
                $data['vehicle_machine'] = $body[41];
                $data['discount'] = $body[35];
                $data['discount_type'] = "Percent";
                // ($total * ($fix_discount / 100));
                $data['discount_value'] = $body[35] !== 0 ? round($body[87] * ($body[35] / 100)) : $body[35];
            }
            $data['tsi'] = !empty($body) ? floor($body[76]) : floor($inqu?->data['price']);

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
            if(!empty($inqu)) {
                $acc = $inqu?->data['aksesoris'];
            }else {
                // 1
                $acc[1]["merk"] = $body[108] ?? "";
                $acc[1]["harga"] = "1";
                $acc[1]["qty"] = !empty($body[118]) ? (string)$body[118] : "0";
                // 2
                $acc[2]["merk"] = $body[109] ?? "";
                $acc[2]["harga"] = !empty($body[129]) ? (string)$body[129] : "0";
                $acc[2]["qty"] = !empty($body[119]) ? (string)$body[119] : "0";
                // 3
                $acc[3]["merk"] = $body[110] ?? "";
                $acc[3]["harga"] = !empty($body[130]) ? (string)$body[130] : "0";
                $acc[3]["qty"] = !empty($body[120]) ? (string)$body[120] : "0";
                // 4
                $acc[4]["merk"] = $body[111] ?? "";
                $acc[4]["harga"] = !empty($body[131]) ? (string)$body[131] : "0";
                $acc[4]["qty"] = !empty($body[121]) ? (string)$body[121] : "0";
                // 5
                $acc[5]["merk"] = $body[112] ?? "";
                $acc[5]["harga"] = !empty($body[132]) ? (string)$body[132] : "0";
                $acc[5]["qty"] = !empty($body[122]) ? (string)$body[122] : "0";
            }

            
            if(!empty($inqu)) {
                $perluasan = $inqu?->data['perluasan'];
                $detail_perluasan = $inqu?->data['detail_perluasan'];
            }else {
                $coverage_ex = strtoupper($body[73]);
                foreach($mvsub as $topping) {
                    if($topping->id !== 12) {
                        $perluasan[$topping->id] = false;
                    }
                    if(Str::contains($coverage_ex, 'ANGIN')) {
                        $perluasan[1] = true;
                    }
                    if(Str::contains($coverage_ex, 'GEMPA')) {
                        $perluasan[2] = true;
                    }
                    if(Str::contains($coverage_ex, ['HURU-HARA', 'RIOT', 'KERUSUHAN', 'HURU HARA'])) {
                        $perluasan[3] = true;
                    }
                    if(Str::contains($coverage_ex, ['TERORISME', 'SABOTASE'])) {
                        $perluasan[4] = true;
                    }
                    if(Str::contains($coverage_ex, 'PENGEMUDI')) {
                        $actual_driver = 0;
                        if($body[104] > 50000000) {
                            $actual_driver = 50000000;
                        }else {
                            $actual_driver = ceil($body[104]/5E6)*5E6;
                        }
                        // return $body[104];
                        $perluasan[5] = true;
                        $detail_perluasan[5]["up"] = (string)$actual_driver ?? "0";
                    }
                    if(Str::contains($coverage_ex, 'PENUMPANG')) {
                        $perluasan[6] = true;
                        if($body[107] > 4) {
                            $body[107] = 4;
                        }else {
                            $body[107] = ceil($body[107]);
                        }
                        if($body[107] == 0) {
                            $divide = $body[105];
                        }else {
                            $divide = $body[105] / $body[107];
                        }
                        // return $divide;
                        $actual_pass = 0;
                        if($divide > 25000000) {
                            $actual_pass = 25000000;
                        }else {
                            $actual_pass = ceil($divide/5E6)*5E6; 
                        }
                        $detail_perluasan[6]["up"] = (string)$actual_pass ?? "0";
                        $detail_perluasan[6]["passenger"] = (string)$body[107]; 
                    }
                    // return $detail_perluasan;
                    if(Str::contains($coverage_ex, 'TANGGUNG')) {
                        // $body[96] = TPL
                        $premi = $body[96];
                        $tpl = 0;
                        $up = [25000000,25000000,50000000];
                        $rate = [1, 0.5 ,0.25];
                        $step = 0;
                        if($premi > 500000){
                            return 0;
                        }elseif($premi < 50000){
                            $premi = 50000;
                        }
                        foreach($up as $keys=>$u){
                            $ups = $u*($rate[$keys]/100);
                            $step = $step +1;
                            if($premi - $ups > 1){
                                    $tpl = $tpl + $u;
                                    $premi = $premi - $ups;
                            }elseif($premi - $ups <= 0){
                                    $upss = ($premi/$rate[$keys])*100;
                                    //return $premi;
                                    $premi =0;
                                    //return $ups;
                                    $tpl = $tpl + $upss;
                                    break 1;
                            }
                        }
                        $val = ceil($tpl/5E6)*5E6;
                        if($val > 100000000){
                            $tpl_total = 0;
                        }else{
                            $tpl_total = ceil($tpl/5E6)*5E6;
                        }
                        // if(!empty($inqu->data['perluasan']) && $inqu->data['perluasan'][7] == true) {
                        // }
                        $perluasan[7] = true;
                        $detail_perluasan[7]["up"] = !empty($inqu->data['detail_perluasan'][7]) ? $inqu->data['detail_perluasan'][7]['up'] : (string)$tpl_total;
                    }
                    if(Str::contains($coverage_ex, ['AUTHORIZED', 'BENGKEL'])) {
                        $perluasan[10] = true;
                    }
                    if(Str::contains($coverage_ex, 'AUTOCILLIN', 'FITUR')) {
                        $perluasan[11] = true;
                    }  
                }
            }
            
            $data['perluasan'] = $perluasan;
            $data['detail_perluasan'] = $detail_perluasan;
            $data['aksesoris'] = $acc;
            $data['form'] = $this->getForm($body, $order->product->category_id);

            return response([
                "status" => true,
                "message" => "Polis dapat diperpanjang",
                "data" => $data
            ], 200);
        } catch (\Exception $e) {
            return response([
                "status" => false,
                "message" => $e->getMessage()." ". $e->getLine()
            ], 500);
        }
    }

    private function getForm($body = [], $id, AdiraTransaction $adira_trx = null)
    {
        $refno = $body[67] ?? null;
        $getRenewal = Renewal::where('policy_no',$body[0])->first();
        $getOrder = Order::find($getRenewal->order_id);
        // return $getOrder->end_date;
        $unixExcel = ($getRenewal->data[22] - 25569) * 86400;
        $newStartDate = date("Y-m-d", $unixExcel);
        $req = $adira_trx?->order?->data;
        $pro = [];
        $new = [];
        $objects = Objects::all();
        $form_contract = FormRepoCategory::where('category_id', $id)->first();
        $images = [14, 28, 29, 30, 31, 55, 42, 58, 62, 63, 64, 65, 27];
        if (empty($form_contract->form_json)) {
            return [];
        }
        $contract = $form_contract->form_json['contract'];
        foreach($objects as $keyObject => $object) {
            $new[$keyObject] = [
                "name" => $object->display_name
            ];
            $form_object = $object->form_json;
            foreach($form_object as $keyObj => $obj) {
                if (!empty($form_contract->form_validation)) {
                    foreach ($form_contract->form_validation['contract'] as $key_valid => $form_validation) {
                        if(!empty($form_validation['contract'])) {
                            $valid[$form_validation['contract']] = $form_validation ;
                        }elseif(!empty($form_validation['form_contract'])) {
                            $valid[$form_validation['form_contract']] = $form_validation ;
                        }
                    }
                }
                if(in_array($obj, $contract)) {
                    $formrepo = FormRepo::find($obj);
                    if($formrepo->form_type == "text" || $formrepo->form_type == "number" || $formrepo->form_type == "images"){
                        $validation = [
                            "is_required" => !empty($valid[$formrepo->id]['required']) ? true : false,
                            "min_length" => !empty($valid[$formrepo->id]['minlength']) ? (int) $valid[$formrepo->id]['minlength'] : NULL,
                            "max_length"=> !empty($valid[$formrepo->id]['maxlength']) ? (int) $valid[$formrepo->id]['maxlength'] : NULL
                        ];
                    }else{
                        $validation = ["is_required" => !empty($valid[$formrepo->id]['required']) ? true : false];
                    }
                    $value = $this->getValueForm($formrepo, $body, $req);
                    $new[$keyObject]["details"][] = [
                        "id" => (String)$formrepo->id,
                        "type" => $formrepo->form_type,
                        "text" => $formrepo->name,
                        "validator" => $validation,
                        "validate_link" => $formrepo->validate_link, 
                        "value" => ($value == "" ? null : $value) 
                    ];
                }
            }
            if($keyObject == 1) {
                $new[1]["details"][] = [
                    "id" => "start_date",
                    "type" => "date",
                    "text" => "TANGGAL MULAI",
                    "validator" => [
                        "is_required" => true
                    ],
                    "validate_link" => null, 
                    "value" => $newStartDate ?? @$getOrder->end_date,
                ];
            }
            if(empty($new[$keyObject]["details"])) {
                unset($new[$keyObject]);
            }
        }
        return array_values(array_reverse($new));
    }

    public function getValueForm($formrepo, $body, $req)
    {
        try {
            if($formrepo->value != null){
                $values = Arrays::find($formrepo->value);
                $value = $values->value;
            }else{
                $value = null;
            }
            switch ($formrepo->id) {
                case 1:
                    return (empty($req[1]['data']) ? $body[20] : $req[1]['data']) ?? null;
                    break;
                case 5:
                    $excelDate = null;
                    if(!empty($body[31])) {
                        $date = date_create_from_format("m/d/Y", $body[31]);
                        $excelDate = date_format($date, "Y-m-d");
                    }
                    return (empty($req[5]['data']) ? (string)$excelDate : $req[5]['data']) ?? null;
                    break;

                case 44:
                    return (empty($req[44]['data']) ? (string)$body[32] : $req[44]['data']) ?? null;
                    break;
                
                case 53:
                    return (empty($req[53]['data']) ? $body[64] : $req[53]['data']) ?? null;
                    break;

                case 54:
                    return $req[54]['data'] ?? null;
                    break;

                case 37:
                    $gender = null;
                    if(!empty($req)) {
                        $gender = $req[37]['data'] ? "Pria" : "Wanita"; 
                    }
                    return $gender;
                    break;

                case 13:
                    return (empty($req[13]['data']) ? (string)$body[27] : $req[13]['data']) ?? null;
                    break;

                case 9:
                    $email = null;
                    if(!empty($req[9]['data'])) {
                        $email = $req[9]['data'];
                    }elseif(empty($req[9]) && !empty($body[29])) {
                        $email = $body[29];
                    }
                    return $email;
                    break;

                case 36:
                    return (empty($req[36]['data']) ? $body[24] : $req[36]['data']) ?? null;
                    break;

                case 67:
                    return (empty($req[67]['data']) ? $body[24] : $req[67]['data']) ?? null;
                    break;

                case 33:
                    return (empty($req[33]['data']) ? (string)$body[33] : $req[33]['data']) ?? null;
                    break;

                case 23:
                    return (empty($req[23]['data']) ? (string)$body[39] : $req[23]['data']) ?? null;
                    break;

                case 51:
                    return (!empty($body) ? $body[40] : null);
                    break;

                case 52:
                    return (empty($req[52]['data']) ? (string)$body[41] : $req[52]['data']) ?? null;
                    break;

                case 68:
                    if(!empty($req[68])) {
                        $val = $req[68]['data'];
                    }elseif(empty($req[68]) && !empty($body)) {
                        $val = $body[64];
                    }else {
                        $val = null;
                    }
                    return $val;
                    break;
                case 69:
                    return $req[69]['data'] ?? null;
                    break;
                
                default:
                    return $value ?? null;
                    break;
            }

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function listPolicyRenewal()
    {
        $renewal = Renewal::all()->pluck('policy_no');
        return response([
            "status" => true,
            "message" => "Data founded. Only for DEV",
            "data" => $renewal
        ], 200);
    }
    
}
