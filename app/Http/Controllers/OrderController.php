<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\FormRepo;
use App\Models\FormRepoCategory;
use App\Models\Objects;
use App\Models\InquiryMv;
use App\Models\Arrays;
use App\Models\Transaction;
use App\Jobs\OrderJob;
use App\Jobs\CancelMV;
use App\Models\Renewal;
use App\Models\Chassist;
use App\Models\AdiraProvince;
use App\Service\FlowOrder;
use Validator, DB, Str;
use Carbon\Carbon;
use Log;

class OrderController extends Controller
{
    public function getForm(Request $request)
    {
        $carts = Cart::find($request->cart_id);
        if (!isset($carts)) {
            return response([
                "status" => false,
                "message" => "Cart not found"
            ], 404);
        } elseif (isset($carts) && empty($carts->data)) {
            return response([
                "status" => false,
                "message" => "Cart is empty"
            ], 422);
        }
        $used_cars = [];
        foreach ($carts->data as $inq) {
            $inquiry = InquiryMv::find($inq['inquiry_id']);
            if (isset($inquiry)) {
                if ($inquiry->data['tahun'] == date("Y") && $inquiry->data['newcar'] == true) {
                    $used_cars[] = "NEW";
                } else {
                    $used_cars[] = "USED";
                }
            }
        }


        $data = [];
        $form = [];
        $product_ids = array_column($carts->data, 'product_id');
        try {
            $category_ids = Product::select(['id', 'category_id'])->whereIn('id', $product_ids)->get()->pluck('category_id');
            $form_contracts = FormRepoCategory::whereIn('category_id', $category_ids)->get();
            $form = [];
            $valid = [];
            $objects = Objects::all();
            // return $form_contracts;
            foreach ($form_contracts as $keyContract => $form_contract) {
                if (!empty($form_contract->form_json)) {
                    $contract = $form_contract->form_json['contract'];
                    // return $contract;
                    foreach ($objects as $keyObject => $object) {
                        $form[$keyObject] = [
                            "name" => $object->display_name,
                        ];
                        $form_object = $object->form_json;
                        foreach ($form_object as $keyObj => $obj) {
                            if (!empty($form_contract->form_validation)) {
                                foreach ($form_contract->form_validation['contract'] as $key_valid => $form_validation) {
                                    if (!empty($form_validation['contract'])) {
                                        $valid[$form_validation['contract']] = $form_validation;
                                    } elseif (!empty($form_validation['form_contract'])) {
                                        $valid[$form_validation['form_contract']] = $form_validation;
                                    }
                                }
                            }
                            
                            if (in_array($obj, $contract)) {
                                if (in_array("USED", $used_cars)) {
                                    if (!in_array($obj, json_decode(env('FORM_EXCEPT2')))) {
                                        $formrepo = FormRepo::find($obj);
                                        $value = Arrays::find($formrepo?->value)->value ?? null;
                                        
                                        if ($formrepo->form_type == "text" || $formrepo->form_type == "number" || $formrepo->form_type == "images") {
                                            $validation = [
                                                "is_required" => !empty($valid[$formrepo->id]['required']) ? true : false,
                                                "min_length" => !empty($valid[$formrepo->id]['minlength']) ? (int) $valid[$formrepo->id]['minlength'] : NULL,
                                                "max_length" => !empty($valid[$formrepo->id]['maxlength']) ? (int) $valid[$formrepo->id]['maxlength'] : NULL
                                            ];
                                        } else {
                                            $validation = ["is_required" => !empty($valid[$formrepo->id]['required']) ? true : false];
                                        }
                                        $form[$keyObject]["details"][] = [
                                            "id" => (string)$formrepo->id,
                                            "type" => $formrepo->form_type,
                                            "text" => $formrepo->name,
                                            "validator" => $validation,
                                            "validate_link" => $formrepo->validate_link,
                                            "value" => $value
                                        ];
                                    }
                                } else {
                                    if (!in_array($obj, json_decode(env('FORM_EXCEPT')))) {
                                        $formrepo = FormRepo::find($obj);
                                        $value = Arrays::find($formrepo?->value)->value ?? null;

                                        if ($formrepo->form_type == "text" || $formrepo->form_type == "number" || $formrepo->form_type == "images") {
                                            $validation = [
                                                "is_required" => !empty($valid[$formrepo->id]['required']) ? true : false,
                                                "min_length" => !empty($valid[$formrepo->id]['minlength']) ? (int) $valid[$formrepo->id]['minlength'] : NULL,
                                                "max_length" => !empty($valid[$formrepo->id]['maxlength']) ? (int) $valid[$formrepo->id]['maxlength'] : NULL
                                            ];
                                        } else {
                                            $validation = ["is_required" => !empty($valid[$formrepo->id]['required']) ? true : false];
                                        }
                                        $form[$keyObject]["details"][] = [
                                            "id" => (string)$formrepo->id,
                                            "type" => $formrepo->form_type,
                                            "text" => $formrepo->name,
                                            "validator" => $validation,
                                            "validate_link" => $formrepo->validate_link,
                                            "value" => $value
                                        ];
                                    }
                                }
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
                                "value" => null
                            ];
                        }
                        if (empty($form[$keyObject]["details"])) {
                            unset($form[$keyObject]);
                        }
                    }
                }
                
                if (isset($form[2])) {
                    $dataMicro = $form[2];
                }
            }
            if (!empty($dataMicro)) {
                $form[2] = $dataMicro;
            }
        } catch (\Exception $e) {
            return response([
                "status" => false,
                "message" => $e->getMessage() . " " . $e->getLine()
            ], 500);
        }
        // return $form;
        return response([
                "status" => true,
                "form" => array_values($form)
            ], 200);
    }

    public function post(Request $request)
    {
        try {
            // return $request;
            $carts = Cart::find($request->cart_id);

            if (empty($carts)) {
                return response([
                    "status" => false,
                    "message" => "Cart not found"
                ], 404);
            }
            $err_msg = [];
            // return $request[23];
            $category_ids = [];
            $data = [];
            $objects = Objects::all();

            if (empty($request['start_date']) || $request['start_date'] == "" || $request['start_date'] == "null") {
                $err_msg[] = "Tanggal mulai wajib diisi";
            }

            if ($request['start_date'] < date("Y-m-d")) {
                $err_msg[] = "Tanggal mulai perlindungan tidak bisa kurang dari tanggal hari ini";
            }

            if (isset($request['52'])) {
                if (strlen($request['52'])   < 6) {
                    $err_msg[] = "Nomor Mesin Minimal 6 Karakter";
                }
            }

            if (strlen($request['33']) != 16) {
                $err_msg[] = "Nomor KTP Tidak Valid / Harus 16 Angka";
                // return response(['message' => $request, 'status' => false], 422);
            }
            if (!filter_var($request['9'], FILTER_VALIDATE_EMAIL)) {
                $err_msg[] = "Format email tidak valid";
            }

            $dateOfBirth = Carbon::parse($request['5']);
            $tanggalSaatIni = Carbon::now();

            
            foreach ($carts->data as $cart) {
                DB::beginTransaction();
                $ambilInquiry = InquiryMv::find($cart['inquiry_id']);
                    if (isset($ambilInquiry->data['policy_no'])) {
                        
                    }else{
                        if ($ambilInquiry->data['newcar'] == true || $ambilInquiry->data['newcar'] == 1) { 
                            if (empty($request['14']) || $request['14'] == "" || $request['14'] == "null") {
                                $err_msg[] = "Foto KTP Wajib Diisi";
                            }
                            if (empty($request['42']) || $request['42'] == "" || $request['42'] == "null") {
                                $err_msg[] = "FOTO BASTK/FAKTUR Wajib Diisi";
                            }
                            if (empty($request['68']) || $request['68'] == "" || $request['68'] == "null") {
                                $err_msg[] = "Kota Pengiriman Wajib Diisi";
                            }
                            if (empty($request['69']) || $request['69'] == "" || $request['69'] == "null") {
                                $err_msg[] = "Provinsi Pengiriman Wajib Diisi";
                            }
                        }else{
                            if (empty($request['14']) || $request['14'] == "" || $request['14'] == "null") {
                                $err_msg[] = "Foto KTP Wajib Diisi";
                            }
                            if (empty($request['27']) || $request['27'] == "" || $request['27'] == "null") {
                                $err_msg[] = "FOTO STNK Wajib Diisi";
                            }
                            if (empty($request['68']) || $request['68'] == "" || $request['68'] == "null") {
                                $err_msg[] = "Kota Pengiriman Wajib Diisi";
                            }
                            if (empty($request['69']) || $request['69'] == "" || $request['69'] == "null") {
                                $err_msg[] = "Provinsi Pengiriman Wajib Diisi";
                            }
                            if (empty($request['55']) || $request['55'] == "" || $request['55'] == "null") {
                                $err_msg[] = "FOTO DASHBOARD DAN TRANSMISI Wajib Diisi";
                            }
                            if (empty($request['28']) || $request['28'] == "" || $request['28'] == "null") {
                                $err_msg[] = "FOTO TAMPAK DEPAN KENDARAAN Wajib Diisi";
                            }
                            if (empty($request['29']) || $request['29'] == "" || $request['29'] == "null") {
                                $err_msg[] = "FOTO TAMPAK BELAKANG KENDARAAN Wajib Diisi";
                            }
                            if (empty($request['30']) || $request['30'] == "" || $request['30'] == "null") {
                                $err_msg[] = "FOTO SAMPING KANAN KENDARAAN Wajib Diisi";
                            }
                            if (empty($request['31']) || $request['31'] == "" || $request['31'] == "null") {
                                $err_msg[] = "FOTO SAMPING KIRI KENDARAAN Wajib Diisi";
                            }
                        }
                    }
                $form_category = FormRepoCategory::where('category_id', $cart['product_data']['category_id'])->first();
                $contract = $form_category->form_json['contract'];
                $order = implode(',', $contract);
                if ($cart['product_data']['flow'] == "mv") { 
                    $ageDifference = $dateOfBirth->diffInYears($tanggalSaatIni);
                    if ($ageDifference < 17) {
                        $err_msg[] = "Usia Tertanggung Minimal 17 Tahun";
                    }
                    $d = AdiraProvince::where("province", $request['54'])->first();
                    if (!empty($d)) {
                        if (in_array($request['53'], $d["cities"]) == false) {
                            $err_msg[] = "Kota tidak ditemukan";
                        }
                    } else {
                        $err_msg[] = "Provinsi tidak ditemukan";
                    }
                }
                // return 'sini';
                // $form_repos = FormRepo::whereIn('id', $contract)->orderByRaw("FIELD(id, $order)")->get();
                foreach ($objects as $keyObject => $object) {
                    $data[$keyObject] = [
                        "name" => $object->name,
                    ];
                    
                    $form_object = $object->form_json;
                    foreach ($form_object as $keyObj => $obj) {
                        $repo = FormRepo::find($obj);
                        if ($request->hasFile($repo->id)) {
                            // return "MASUK ADA GAMBAR";
                            $validator = Validator::make($request->all(), [
                                $repo->id . ".*" => "mimes:jpeg,png,jpg,gif,svg,jfif,raw,tiff,bmp"
                            ]);
                            if ($validator->fails()) {
                                $err_msg[] = "Format gambar tidak valid.";
                            }
                            $data[$keyObject][$repo->id] = ["type" => $repo->form_type];
                            $file = $request->file($repo->id);
                            if ($cart['product_data']['flow'] != 'nor') {
                                $filename = substr(md5(microtime()), rand(0, 26), 5) . "_" . $file->getClientOriginalName();
                                $data[$keyObject][$repo->id]['data'] = $filename ?? "null";
                                $path = public_path('uploads/file/');
                                if (!is_dir($path)) {
                                    mkdir($path, 0777, true);
                                }
                                $file->move($path, $filename);
                            }

                            $data[$keyObject][$repo->id]["name"] = $repo->name;
                        } else {
                            if (isset($request[$repo->id])) {
                                if ($repo->id == 9) {
                                    $validator = Validator::make($request->all(), [
                                        $repo->id . ".*" => "email"
                                    ]);
                                    if ($validator->fails()) {
                                        $err_msg[] = "Format email tidak valid.";
                                    }
                                }
                                $data[$keyObject][$repo->id] = [
                                    "type" => $repo->form_type,
                                    "data" => str_replace(['\r', '\n', '\t'], '', $request[$repo->id]),
                                    "name" => $repo->name
                                ];
                            }
                        }
                    }
                    // return $data;
                }

                if (!empty($cart['product_data']['validation'])) {
                    foreach ($cart['product_data']['validation'] as $key_valid => $prod_valid) {
                        if (!empty($request[$key_valid])) {
                            $date1 = date_create($request[$key_valid]);
                            $date2 = date_create(date('Y-m-d H:i:s'));
                            $diff = date_diff($date1, $date2);

                            if ($diff->format("%a") < $prod_valid['min']) {
                                $err_msg[] = $prod_valid['msg'];
                            }
                            if ($diff->format("%a") > $prod_valid['max']) {
                                $err_msg[] = $prod_valid['msg'];
                            }
                        }
                    }
                }
                // return 'sini';

                // return $data;
                $inquiry = InquiryMv::find($cart['inquiry_id']);
                $mobil = [];
                $moto = [];
                $mikro = [];
                foreach ($data as $dataKey => $formData) {
                    // return $data;
                    $name = $formData['name'];
                    unset($formData['name']);
                    if ($name == "DATA KENDARAAN" || $name == "ORANG") {
                        foreach ($formData as $keyMob => $mob) {
                            if (isset($inquiry)) {
                                if ($inquiry->data['tahun'] == date("Y") && $inquiry->data['newcar'] == true) {
                                    foreach (json_decode(env('FORM_EXCEPT'), true) as $except) {
                                        unset($formData[$except]);
                                    }
                                    $mobil[$keyMob] = $mob;
                                } else {
                                    foreach (json_decode(env('FORM_EXCEPT2'), true) as $except2) {
                                        unset($formData[$except2]);
                                    }
                                    $mobil[$keyMob] = $mob;
                                }
                            }
                            // return $formData;
                        }
                    }
                    if ($name == "MOTO" || $name == "ORANG") {
                        // $moto[] = array_merge($moto, $formData);
                        foreach ($formData as $keyMot => $mot) {
                            $moto[$keyMot] = $mot;
                        }
                    }
                    if ($name == "ORANG") {
                        // return $mikro[] = array_merge($mikro, $formData);
                        // foreach ($formData as $keyMik => $mik) {
                        //     $mikro[$keyMik] = $mik;
                        // }
                    }
                    foreach ($formData as $keyM => $dataMik) {
                        $mikro[$keyM] = $dataMik;
                    }
                }
                // return $mikro;

                if (count($err_msg) > 0) {
                    return response(['message' => $err_msg, 'status' => false], 422);
                }

                if (isset($inquiry)) {
                    if (isset($inquiry->data['policy_type'])) {
                        $softcopy = $this->policyType($cart['product_data']['flow'], $inquiry->data['policy_type']);
                        $policyType = $inquiry->data['policy_type'];
                    }elseif (!empty($inquiry->order_id)) {
                        $cekOrder = Order::find($inquiry->order_id);
                        $softcopy = $this->policyType($cart['product_data']['flow'], $cekOrder->additional_data[0]['copy']);
                        $policyType = $cekOrder->additional_data[0]['copy'];
                    }

                    $inquiry_item = [];
                    $inquiry_item = $inquiry->item;
                    $inquiry_item[0]['copy'] = $policyType;
                    $inquiry_item[0]['copy_price'] = $softcopy;
                }

                // return 'disini';
                $order = new Order;
                $order->user_id = auth('api')->user()->id;
                $order->cart_id = $carts->id;
                $order->product_id = $cart['product_data']['id'];
                $order->start_date = $request['start_date'];
                $order->end_date = !empty($request['start_date']) ? date('Y-m-d', strtotime('+' . ($cart['product_data']['period_days'] ?? '0') . ' days',  strtotime($request['start_date']))) : null;
                switch ($cart['product_data']['flow']) {
                    case 'mv':
                        $order->status = 0;
                        $order->additional_data = $inquiry_item;
                        $order->data = $mobil;
                        break;
                    case 'moto':
                        $order->status = 0;
                        $order->additional_data = $inquiry_item;
                        $order->data = $mikro;
                        break;
                    case 'nor':
                        $order->status = 2;
                        $order->data = $mikro;
                        break;
                    default:
                        $order->status = 2;
                        break;
                }
                $totalBasePrice = 0;
                if (isset($inquiry)) {
                    foreach ($inquiry_item as $key => $value) {
                        $totalBasePrice += $value['price'];
                    }
                }else{
                    $totalBasePrice = $cart['total'];
                }
                
                $order->total = $inquiry->total ?? $cart['total'];
                $order->base_price = $totalBasePrice;
                $order->deduct = $inquiry->discount ?? null;
                $order->save();

                // return $order->id;
                $renew = Renewal::where('policy_no', $request['policy_no'])->first();
                if (!empty($renew)) {
                    $renew->new_order_id = $order->id;
                    $renew->save();
                }

                if (isset($inquiry)) {
                    $inquiry->order_id = $order->id;
                    $inquiry->save();
                }

                $transaction = Transaction::where('order_id', $order->id)->first();
                if ($transaction == null) {
                    $transaction = new Transaction;
                    $transaction->order_id = $order->id;
                    $transaction->cart_id = $carts->id;
                    $transaction->user_id = auth('api')->user()->id;
                    $transaction->base_price = $totalBasePrice;
                    $transaction->total = $cart['total'];
                    $transaction->deduct_price = $inquiry->discount ?? null;
                    $transaction->save();

                    $order->transaction_id = $transaction->id;
                    $order->save();
                } else {
                    return response(['status' => false, 'message' => 'Transaction Exists!'], 500);
                }
                DB::commit();
                // return $order->data;
                if (isset($inquiry)) {
                    OrderJob::dispatch($order, $inquiry, $cart['product_data']);
                }
            }
            // return $order->data;
            // return 'tt';
            $newTotalCart = 0;
            foreach ($carts->data as $key => $value) {
                if ($value['inquiry_id'] != null) {
                    $getInquiry = InquiryMv::find($value['inquiry_id']);
                    $newTotalCart += $getInquiry->total;
                }else{
                    $newTotalCart += $value['total'];
                }
            }
            $carts->total = $newTotalCart;
            $carts->is_checkout = 1;
            $carts->save();


            return response(['status' => true, 'message' => 'Order successfuly created!'], 200);
        } catch (\Exception $e) {
            Log::warning($e->getMessage()." ".$e->getLine());
            return response(['message' => $e->getMessage() . " " . $e->getLine(), 'status' => false], 422);
        }
    }

    public function OfferOrder()
    {
    }

    public function checkstatus(Request $request, $cartid)
    {
        // Buat handler untuk mengarahkan apakah Cart ini terdapat form WEBVIEW atau tidak
        // Rules: 
        // - Check didalam table Orders apakah ada field `data` yang kosong, jika iya check product flow nya.
        // - Jika flow nya Webview / others maka lempar ke webview page.
        // - Jika flow nya non Webview ( mv, nor, moto ) maka lempar ke form generator seperti biasa.
        try {

            $cart = Cart::find($cartid);
            if (empty($cart)) {
                return response([
                    "status" => false,
                    "message" => "Cart tidak ditemukan",
                    "data" => []
                ], 404);
            }

            $get_status = FlowOrder::getStatus($cartid);
            // return $get_status;
            if ($get_status['null_data'] == 0 && count($cart->data) == $get_status['count_order']) {
                return response([
                    "status" => true,
                    "message" => "Semua Order form telah terisi",
                    "data" => []
                ], 200);
            }
            return response($get_status['result'], 422);
        } catch (\Exception $e) {
            return response([
                "status" => false,
                "message" => $e->getMessage(),
                "data" => []
            ], 500);
        }
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

    public function delete($id){
        try {
            // return 'masuk';
            $user = auth('api')->user()->id;
            $data = Order::find($id);
            // return json_decode($can->getBody(),true);
            // if ($user == $data->user_id) {
            // if ($data->adira_trx['adira_status'] == "Approved") {
                if($data->cart->is_offering == 1 && empty($data->data)) {
                    $inquiryId = InquiryMv::find($data->inquiry->id);
                            $cartData = Cart::find($data->cart->id);
                            foreach ($cartData->data as $key => $value) {
                                if (($value['inquiry_id'] != null) && ($value['inquiry_id'] == $inquiryId->id)) {
                                    unset($value[$key]);
                                }
                            }
                            // return [$value];
                            $cartData->data = [$value];
                            $cartData->save();
                            $newTotalCart = 0;
                            foreach ($cartData->data as $key => $value) {
                                if ($value['inquiry_id'] != null) {
                                    $getInquiry = InquiryMv::find($value['inquiry_id']);
                                    $newTotalCart += $getInquiry->total;
                                }else{
                                    $newTotalCart += $value['total'];
                                }
                            }
                            $cartData->total = $newTotalCart;
                            $cartData->save();
                    $data->delete();
                    // return 'masuk sini';
                }else {
                    if (($data->product->flow == "mv" || $data->product->flow == "moto") && !empty($data->adira_trx['adira_response']['data']['requestNumber']) ) {
                        if(!empty($data->adira_trx) && $data->adira_trx['adira_status'] != null) {
                            // $client = new Client();
                            // return 'masuk sono';
                            
                            // $can = $client->post(env('ADIRA_ORDER_URL').'api/v1/cancel-order',[
                            //     'verify' => false,
                            //     'form_params' => [
                            //         "apiKey" => env('ADIRA_KEY'),
                            //         "sendEmail" => 0, 
                            //         "requestNumber" => !empty($data->adira_trx->adira_response['data']['requestNumber'] ) ? $data->adira_trx->adira_response['data']['requestNumber'] : '',
                            //         ]
                            //     ]
                            // );
                            dispatch(new CancelMV($data));
                            $inquiryId = InquiryMv::find($data->inquiry->id);
                            $cartData = Cart::find($data->cart->id);
                            foreach ($cartData->data as $key => $value) {
                                if (($value['inquiry_id'] != null) && ($value['inquiry_id'] == $inquiryId->id)) {
                                    unset($value[$key]);
                                }
                            }
                            // return [$value];
                            $cartData->data = [$value];
                            $cartData->save();
                            $newTotalCart = 0;
                            foreach ($cartData->data as $key => $value) {
                                if ($value['inquiry_id'] != null) {
                                    $getInquiry = InquiryMv::find($value['inquiry_id']);
                                    $newTotalCart += $getInquiry->total;
                                }else{
                                    $newTotalCart += $value['total'];
                                }
                            }
                            $cartData->total = $newTotalCart;
                            $cartData->save();
                            // return $cartData;
                            $rangka = $data->data['51']['data'] ?? null;
                            $chassist = Chassist::where('chassis', $rangka)->first();
                            $renew = Renewal::where('policy_no', $data->additional_data[0]['policy_no'])->first();
                            if(!empty($renew)) {
                                $renew->new_order_id = null;
                                $renew->save();
                            }
                            if(!empty($chassist)) {
                                $chassist->delete();
                            }
                            $data->adira_trx->adira_status = "Canceled";
                            $data->adira_trx->status = "Stop";
                            $data->adira_trx->save();
                            
                            $data->delete();
                        }else {
                            return response([
                                "status" => false,
                                "message" => "Order tidak dapat di batalkan."
                            ], 422);
                        }
                    }
                }
            // } 
                return response(['message' => 'success', 'status' => true], 200);
            // }
            // return response(['message' => 'User not allowed', 'status' => false], 200);
        }catch(\Exception $e){
            return response(['message' => $e->getMessage()." ".$e->getLine(), 'status' => false], 422);
        }
    }
}
