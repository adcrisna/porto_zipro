<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\InquiryMv;
use App\Models\ProductReferral;
use Carbon\Carbon;
use App\Models\Chassist;
use App\Models\Cart;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use App\Jobs\OfferingJob;
use App\Jobs\OrderJob;
use App\Jobs\ComissionJob;
use App\Models\User;
use App\Models\AdiraTransaction;
use App\Models\AdiraComission;
use App\Models\Transaction;
use App\Models\Profile;
use App\Models\Comission;
use GuzzleHttp\Client;
use App\Models\OrComission;
use Log, PDF, Str;

class TestController extends Controller
{   
    public function orderJob() {
        $order = Order::find(15198);
        dispatch(new ComissionJob($order->id));
        return 'dsadas';
    }
    public function tesOrderJob()
    {
        $order = Order::find(3630);
        $inquiry = InquiryMv::where('order_id', $order->id)->first();
        $cart = Cart::find($order->cart_id);
        OrderJob::dispatch($order, $inquiry, $cart['product_data']);

        return 'done';
    }
    public function testNPWP() {
        $value = [
                'nik' => '3275094508770009',
                'tujuan' => 'cek identitas wajib pajak',
            ];

            $client = new Client();
            $result = $client->post('https://openapi.pajak.io/vswp/verify/nik', 
            [
                'verify' => false,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'OWMzM2Y4MjkzNjU3NWZmYmE5NjA1MDY1ODMzZGE0MjY1NTg1YTcxZWUxZTVjYTJjMjExNWY0ODk2YjkyOGM5Mw==',
                    "Accept" => "application/json",
                    // 'API-Key' => 'MjJkMDRlNTQ0NGRjMTkyY2FhZjYwMmJkMzRhZjIwOTMzNWZlNThjYzAwMDUzZDdjNDgzZTFkMTg0Y2FlY2E4YQ==',
                ],
                'json' => $value,
            ]);
            $response = json_decode($result->getBody(), true);
            return $response;
    }
    public function tesRegexNIK(){
        $format = "/^(1[1-9]|21|[37][1-6]|5[1-3]|6[1-5]|[89][12])\d{2}\d{2}([04][1-9]|[1256][0-9]|[37][01])(0[1-9]|1[0-2])\d{2}\d{4}$/";
        $no_ktp = "3277036302010002";
        if (!preg_match($format, $no_ktp)) {
            return 'salah';
        }else{
           return 'benar';
        }
    }
    public function regexComa() {
        $string = "Jl. Pintu Besi,1 no. 5,Pasar Baru, Sawah Besar";

        $resultAlamat = preg_replace('/[,]/', ' ', $string);

        return $resultAlamat;
    }
    public function test1() {
        $sisakomisi = 0.50 - 0.45;
        return $sisakomisi;
    }
    public function testDate() {
        $unix = (45098 - 25569) * 86400;
       $date = date("Y-m-d", $unix);
       return $date;
    }
    public function index(Request $request)
    {

        return "haha";
       $val = Order::all();
       foreach($val as $keys => $data){
        if(($data->base_price == $data->total) && isset($data->additional_data["detail"])){
            return $data;
        }
       }
       return $val[0];
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

    public function migrations(Request $request)
    {
        try {
            $json = \File::get($request->file('json'));
            // return json_decode($json, true);
            foreach (json_decode($json, true)[0]['data'] as $order) {
                $product = new Product;
                $product->name = $order['name'];
                $product->display_name = $order['display_name'];
                $product->category_id = $order['category_id'];
                $product->adira_product_id = $order['adira_product_id'];
                $product->description = $order['description'];
                $product->price = $order['price'];
                $product->logo = "logo_20221215072245AEi74.png";
                $product->learn = $order['learn'];
                $product->flow = $order['flow'];
                $product->is_enable = $order['is_enable'];
                $product->deleted_at = $order['deleted_at'];
                $product->is_pg = $order['is_pg'];
                $product->binder_id = $order['binder_id'];
                $product->wording = "polis_asuransi_20221130040747N7MnQ.pdf";
                $product->point = $order['point'];
                $product->period_days = $order['period_days'];
                $product->schema_id = 2;
                $product->schema_ref_id = 1;
                $product->save();
            }
            return "done";
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function testRollbackOrderRef() {
        $dateNow = Carbon::now();
                $startDate = Carbon::parse(date('Y-m-d H:i:s', strtotime($dateNow)));
                $endDate = Carbon::parse(date('Y-m-d H:i:s', strtotime('2024-04-23 1:28:27')));
                $timeDifference  = $endDate->diffInMinutes($startDate);
                $resultDate = $timeDifference /60;
                return $resultDate;
                
        $cartSf = Cart::where('is_checkout',0)->where('user_id','!=', null)->get();
        foreach ($cartSf as $key => $value) {
            $updateRef = ProductReferral::where('cart_id',$value->id)->first();
            if (!empty($updateRef)) {
                Log::warning("data order sf ada ". $value->id);
                $dateNow = Carbon::now();
                $startDate = Carbon::parse(date('Y-m-d H:i:s', strtotime($dateNow)));
                $endDate = Carbon::parse(date('Y-m-d H:i:s', strtotime($value->updated_at)));
                $timeDifference  = $endDate->diffInMinutes($startDate);
                $resultDate = $timeDifference;
                return $resultDate;
                Log::warning("total hours ". $resultDate);
                $getOrder = Order::where('cart_id',$value->id)->first();
                    if (empty($getOrder)) {
                        if ($resultDate >= 24) {
                        $updateCart = Cart::find($value->id);
                        $updateCart->user_id = null;
                        $updateCart->data = null;
                        $updateCart->save();

                        $updateRef = ProductReferral::where('cart_id',$value->id)->first();
                        $updateRef->picked_user_id = null;
                        $updateRef->status = 0;
                        $updateRef->save();
                        Log::warning("data order sf update");
                    }
                }
            }
        }
    }

    function testttt() {
        $data = '{"orcomission": {
            "id": 8965,
            "trx_id": 6969,
            "from_id": 6464,
            "to_id": 3158,
            "formula": {
            "type": "percent",
            "value": "0.00"
            },
            "layer": 1,
            "base_price": 10890000,
            "comission": 0,
            "status": "unpaid",
            "created_at": "2024-03-22 17:17:09",
            "updated_at": "2024-03-22 17:17:09",
            "from_email": null,
            "to_email": null
        },
        "profile": {
            "id": 6354,
            "user_id": 6464,
            "address": "permata biru R2-93 Rt010\/029 cimekar Cileunyi bandung",
            "phone": "081218832678",
            "another_phone": null,
            "city": "Bandung",
            "bank_id": 2,
            "bank_account": "096601026500533",
            "id_card_pic": "id_card_20221208171755PdIHb.jpg",
            "avatar": "avatar_20221208171755afxiv.jpg",
            "npwp": "57.274.948.9-027.000",
            "no_ktp": null,
            "real_name": null,
            "npwp_valid": null,
            "created_at": "2022-12-08T17:17:56.000000Z",
            "updated_at": "2022-12-08T17:17:56.000000Z",
            "bank_branch": "Cempaka putih",
            "branch_location": "jakarta"
        },
        "user": {
            "id": 6464,
            "uuid": null,
            "name": "rusdi",
            "email": "rusdia2000@gmail.com",
            "verified_at": null,
            "referrer_email": "auto2000cibiru@app.com",
            "partner_id": 402,
            "created_at": "2022-12-08T17:17:54.000000Z",
            "updated_at": "2023-12-09T08:48:52.000000Z",
            "deleted_at": null,
            "cooldown": null,
            "tax": 2.5,
            "email_verified_at": null,
            "fcm_token": "eE4vJEmdQMCatwajxrIi-Q:APA91bG6VRApwR6pqs-f3aABUsPKlnVPMrdKOHeUCGOOP3j4A7N7SJW0BwXvE_kiO825JBt5cmCkQHM1E8vKwiTG9znrQmsaDSkopZ7cL0rTf1tkghUJe00Lwp1Fc3Jw4pN0TySsetFm",
            "version": "11",
            "is_production": null,
            "oauth_token": null,
            "platform": "Android",
            "last_login": "2023-06-12 18:02:38"
        },
        "transaction": {
            "id": 6969,
            "order_id": 16969,
            "user_id": 6464,
            "trx_data": {
            "2": {
                "type": "radio",
                "data": "Klik bayar untuk melanjutkan ke halaman pembayaran (aktif 3x24 jam)",
                "name": "Catatan"
            }
            },
            "base_price": "10890000.00",
            "deduct_price": "0.00",
            "total": "10944995.00",
            "voucher": null,
            "created_at": "2024-03-21T08:53:33.000000Z",
            "updated_at": "2024-03-22T10:17:10.000000Z",
            "deleted_at": null,
            "policy": null,
            "pg_status": "2",
            "pg_callback": {
            "id": "65fb931df5833107e7777701",
            "user_id": "61a430303e71a633f3b5bd5c",
            "external_id": "5175",
            "is_high": false,
            "status": "PAID",
            "merchant_name": "ZIPRO ZURICH SALVUS",
            "amount": 10944995,
            "created": "2024-03-21T01:53:33.704Z",
            "updated": "2024-03-22T03:17:04.077Z",
            "payer_email": "rusdia2000@gmail.com",
            "description": "Pembayaran AUTOCILLIN",
            "payment_id": "8895fdf4-6dd0-42d7-b11c-1b88dde54e6f",
            "paid_amount": 10944995,
            "payment_method": "BANK_TRANSFER",
            "bank_code": "BCA",
            "currency": "IDR",
            "paid_at": "2024-03-22T03:16:59.000Z",
            "payment_channel": "BCA",
            "payment_destination": "700701501638261487"
            },
            "pg_link": "https:\/\/checkout.xendit.co\/web\/65fb931df5833107e7777701",
            "expiry_date": "2024-03-24 08:53:33",
            "policy_start": "2024-03-22",
            "policy_end": "2025-03-22",
            "admin_fee": 4995,
            "pg_method": "invoice",
            "user_platform": "Android",
            "platform_version": "11",
            "renew": 0,
            "submit_renew": 0,
            "contract_id": "575941",
            "momentic_log": "{\"status\":\"success\",\"message\":\"Data sucessfully saved!.\",\"contract_id\":575941}"
        }
        }';

        $data2 = json_decode($data, true);
        return $data2['user']['id'];
    }

    public function syncTransaction(Request $request)
    {
            $data = json_decode(json_decode($request->getContent(), true), true);
            Log::warning("sync transaction finish");
            #users
                $cekUser = User::find($data['user']['id']);
                if (empty($cekUser)) {
                    $newUser = new User;
                    $newUser->id = $data['user']['id'];
                    $newUser->uuid = $data['user']['uuid'];
                    $newUser->name = $data['user']['name'];
                    $newUser->email = $data['user']['email'];
                    $newUser->password = $data['user']['password'];
                    $newUser->referrer_email = $data['user']['referrer_email'];
                    $newUser->partner_id = $data['user']['partner_id'];
                    $newUser->created_at = $data['user']['created_at'];
                    $newUser->updated_at = $data['user']['updated_at'];
                    $newUser->deleted_at = $data['user']['deleted_at'];
                    $newUser->cooldown = $data['user']['cooldown'];
                    $newUser->tax = $data['user']['tax'];
                    $newUser->fcm_token = $data['user']['fcm_token'];
                    $newUser->version = $data['user']['version'];
                    $newUser->is_production = $data['user']['is_production'];
                    $newUser->oauth_token = $data['user']['oauth_token'];
                    $newUser->platform = $data['user']['platform'];
                    $newUser->last_login = $data['user']['last_login'];
                    $newUser->remember_token = $data['user']['remember_token'];
                    $newUser->verified_at = $data['user']['verified_at'];
                    $newUser->email_verified_at = $data['user']['email_verified_at'];
                    $newUser->is_sf = 0;
                    $newUser->is_web = 0;
                    $newUser->save();

                    $newProfile = new Profile;
                    $newProfile->id = $data['profile']['id'];
                    $newProfile->user_id = $data['profile']['user_id'];
                    $newProfile->address = $data['profile']['address'];
                    $newProfile->phone = $data['profile']['phone'];
                    $newProfile->another_phone = $data['profile']['another_phone'];
                    $newProfile->city = $data['profile']['city'];
                    $newProfile->bank_id = $data['profile']['bank_id'];
                    $newProfile->bank_account = $data['profile']['bank_account'];
                    $newProfile->id_card_pic = $data['profile']['id_card_pic'];
                    $newProfile->avatar = $data['profile']['avatar'];
                    $newProfile->npwp = $data['profile']['npwp'];
                    $newProfile->created_at = $data['profile']['created_at'];
                    $newProfile->updated_at = $data['profile']['updated_at'];
                    $newProfile->bank_branch = $data['profile']['bank_branch'];
                    $newProfile->branch_location = $data['profile']['branch_location'];
                    $newProfile->no_ktp = null;
                    $newProfile->real_name = null;
                    $newProfile->npwp_valid = 0;
                    $newProfile->save();
                }

                #order
                $cekOrder = Order::find($data['order']['id']);
                    if (!empty($cekOrder)) {
                        $getProduct = Product::find($data['order']['product_id']);

                        if ($data['order']['is_offering'] == 1) {
                            $pdf[$data['inquiry_mv']['id'] ?? $data['order']['id']] = env('APP_URL') . '/uploads/pdf/surat_penawaran_asuransi_' . md5($order->id) . '.pdf';
                        } else {
                            $pdf = null;
                        }

                        $cartbody[0]["inquiry_id"] = $data['inquiry_mv']['id'] ?? null;
                        $cartbody[0]["product_id"] = $data['order']['product_id'];
                        $cartbody[0]["total"] = $data['order']['total'];
                        $cartbody[0]["product_data"] = $getProduct;
                        $cartbody[0]["order_id"] = $data['order']['id'];

                        $newCart = Cart::find($cekOrder->cart_id);
                        $newCart->user_id = $data['order']['user_id'];
                        $newCart->name = 'Cart '.$data['order']['id'];
                        $newCart->total = $data['order']['total'];
                        $newCart->data = $cartbody;
                        $newCart->is_checkout = 1;
                        $newCart->is_ref = 0;
                        $newCart->admin_fee = @$data['transaction']['admin_fee'];
                        $newCart->pg_method = @$data['transaction']['pg_method'];
                        $newCart->pg_status = @$data['transaction']['pg_status'];
                        $newCart->pg_callback = @$data['transaction']['pg_callback'];
                        $newCart->pg_link = @$data['transaction']['pg_link'];
                        $newCart->deleted_at = @$data['order']['deleted_at'];
                        $newCart->created_at = @$data['order']['created_at'];
                        $newCart->updated_at = @$data['order']['updated_at'];
                        $newCart->is_offering = @$data['order']['is_offering'];
                        $newCart->offering_email = @$data['order']['offering_email'];
                        $newCart->offering_name = @$data['order']['offering_name'];
                        $newCart->offering_telp = @$data['order']['offering_telp'];
                        $newCart->pdf_link = @$pdf;
                        $newCart->save();

                        $newOrder = Order::find($data['order']['id']);
                        $newOrder->user_id = @$data['order']['user_id'];
                        $newOrder->product_id = @$data['order']['product_id'];
                        $newOrder->transaction_id = @$data['order']['transaction_id'];
                        $newOrder->data = @$data['order']['data'];
                        $newOrder->base_price = @$data['order']['base_price'] ?? 0;
                        $newOrder->cart_id = $newCart->id;
                        $newOrder->total = @$data['order']['total'];
                        $newOrder->coupon = @$data['order']['coupon'];
                        $newOrder->additional_data = @$data['order']['additional_data'];
                        $newOrder->status = @$data['order']['status'];
                        $newOrder->note = @$data['order']['note'];
                        $newOrder->validation = @$data['order']['validation'];
                        $newOrder->start_date = @$data['order']['start_date'];
                        $newOrder->end_date = @$data['order']['end_date'];
                        $newOrder->is_offering = @$data['order']['is_offering'];
                        $newOrder->is_submit = @$data['order']['is_submit'];
                        $newOrder->offering_email = @$data['order']['offering_email'];
                        $newOrder->offering_telp = @$data['order']['offering_telp'];
                        $newOrder->offering_name = @$data['order']['offering_name'];
                        $newOrder->created_at = @$data['order']['created_at'];
                        $newOrder->updated_at = @$data['order']['updated_at'];
                        $newOrder->deleted_at = @$data['order']['deleted_at'];
                        $newOrder->deduct = @$data['order']['deduct'];
                        $newOrder->save();
                    } else {
                        $getProduct = Product::find($data['order']['product_id']);

                        if ($data['order']['is_offering'] == 1) {
                            $pdf[$data['inquiry_mv']['id'] ?? $data['order']['id']] = env('APP_URL') . '/uploads/pdf/surat_penawaran_asuransi_' . md5($order->id) . '.pdf';
                        } else {
                            $pdf = null;
                        }

                        $cartbody[0]["inquiry_id"] = $data['inquiry_mv']['id'] ?? null;
                        $cartbody[0]["product_id"] = $data['order']['product_id'];
                        $cartbody[0]["total"] = $data['order']['total'];
                        $cartbody[0]["product_data"] = $getProduct;
                        $cartbody[0]["order_id"] = $data['order']['id'];

                        $newCart = new Cart;
                        $newCart->user_id = $data['order']['user_id'];
                        $newCart->name = 'Cart '.$data['order']['id'];
                        $newCart->total = $data['order']['total'];
                        $newCart->data = $cartbody;
                        $newCart->is_checkout = 1;
                        $newCart->is_ref = 0;
                        $newCart->admin_fee = @$data['transaction']['admin_fee'];
                        $newCart->pg_method = @$data['transaction']['pg_method'];
                        $newCart->pg_status = @$data['transaction']['pg_status'];
                        $newCart->pg_callback = @$data['transaction']['pg_callback'];
                        $newCart->pg_link = @$data['transaction']['pg_link'];
                        $newCart->deleted_at = @$data['order']['deleted_at'];
                        $newCart->created_at = @$data['order']['created_at'];
                        $newCart->updated_at = @$data['order']['updated_at'];
                        $newCart->is_offering = @$data['order']['is_offering'];
                        $newCart->offering_email = @$data['order']['offering_email'];
                        $newCart->offering_name = @$data['order']['offering_name'];
                        $newCart->offering_telp = @$data['order']['offering_telp'];
                        $newCart->pdf_link = @$pdf;
                        $newCart->save();

                        $newOrder = new Order;
                        $newOrder->id = @$data['order']['id'];
                        $newOrder->user_id = @$data['order']['user_id'];
                        $newOrder->product_id = @$data['order']['product_id'];
                        $newOrder->transaction_id = @$data['order']['transaction_id'];
                        $newOrder->data = @$data['order']['data'];
                        $newOrder->base_price = @$data['order']['base_price'] ?? 0;
                        $newOrder->cart_id = $newCart->id;
                        $newOrder->total = @$data['order']['total'];
                        $newOrder->coupon = @$data['order']['coupon'];
                        $newOrder->additional_data = @$data['order']['additional_data'];
                        $newOrder->status = @$data['order']['status'];
                        $newOrder->note = @$data['order']['note'];
                        $newOrder->validation = @$data['order']['validation'];
                        $newOrder->start_date = @$data['order']['start_date'];
                        $newOrder->end_date = @$data['order']['end_date'];
                        $newOrder->is_offering = @$data['order']['is_offering'];
                        $newOrder->is_submit = @$data['order']['is_submit'];
                        $newOrder->offering_email = @$data['order']['offering_email'];
                        $newOrder->offering_telp = @$data['order']['offering_telp'];
                        $newOrder->offering_name = @$data['order']['offering_name'];
                        $newOrder->created_at = @$data['order']['created_at'];
                        $newOrder->updated_at = @$data['order']['updated_at'];
                        $newOrder->deleted_at = @$data['order']['deleted_at'];
                        $newOrder->deduct = @$data['order']['deduct'];
                        $newOrder->save();
                    }

                #inquiry
                if (!empty($data['inquiry_mv'])) {
                    $cekInquiry = InquiryMv::find($data['inquiry_mv']['id']);
                    Log::warning("data iqnuiry". $cekInquiry);
                    if (!empty($cekInquiry)) {
                            $newInquiry = InquiryMv::find($data['inquiry_mv']['id']);
                            $newInquiry->item = $data['inquiry_mv']['item'];
                            $newInquiry->total = $data['inquiry_mv']['total'];
                            $newInquiry->product_id = $data['inquiry_mv']['product_id'];
                            $newInquiry->order_id = $data['inquiry_mv']['order_id'];
                            $newInquiry->status = $data['inquiry_mv']['status'];
                            $newInquiry->data = $data['inquiry_mv']['data'];
                            $newInquiry->discount = $data['inquiry_mv']['discount'];
                            $newInquiry->offering_email = $data['inquiry_mv']['offering_email'];
                            $newInquiry->updated_at = $data['inquiry_mv']['updated_at'];
                            $newInquiry->created_at = $data['inquiry_mv']['created_at'];
                            $newInquiry->save();
                            Log::warning('save update inquiry');
                    }else {
                            $newInquiry = new InquiryMv;
                            $newInquiry->id = $data['inquiry_mv']['id'];
                            $newInquiry->item = $data['inquiry_mv']['item'];
                            $newInquiry->total = $data['inquiry_mv']['total'];
                            $newInquiry->product_id = $data['inquiry_mv']['product_id'];
                            $newInquiry->order_id = $data['inquiry_mv']['order_id'];
                            $newInquiry->status = $data['inquiry_mv']['status'];
                            $newInquiry->data = $data['inquiry_mv']['data'];
                            $newInquiry->discount = $data['inquiry_mv']['discount'];
                            $newInquiry->offering_email = $data['inquiry_mv']['offering_email'];
                            $newInquiry->updated_at = $data['inquiry_mv']['updated_at'];
                            $newInquiry->created_at = $data['inquiry_mv']['created_at'];
                            $newInquiry->save();
                            Log::warning('save new inquiry');
                    }
                }
        

                #transaction
                    $cekTransaction = Transaction::find($data['transaction']['id']);
                    if (!empty($cekTransaction)) {
                        $cekUser = User::find(@$data['user']['id']);
                        $cekOrder = Order::find(@$data['order']['id']);

                        if (!empty($cekOrder)) {
                            $newTransaction = Transaction::find(@$data['transaction']['id']);
                            $newTransaction->user_id = @$data['transaction']['user_id'];
                            $newTransaction->order_id = @$data['transaction']['order_id'];
                            $newTransaction->cart_id = $cekOrder->cart_id;
                            $newTransaction->trx_data = @$data['transaction']['trx_data'];
                            $newTransaction->base_price = @$data['transaction']['base_price'] ?? 0;
                            $newTransaction->deduct_price = @$data['transaction']['deduct_price'];
                            $newTransaction->total = @$data['transaction']['total'];
                            $newTransaction->voucher = @$data['transaction']['voucher'];
                            $newTransaction->policy = @$data['transaction']['policy'];
                            $newTransaction->expiry_date = @$data['transaction']['expiry_date'];
                            $newTransaction->policy_start = @$data['transaction']['policy_start'];
                            $newTransaction->policy_end = @$data['transaction']['policy_end'];
                            $newTransaction->created_at = @$data['transaction']['created_at'];
                            $newTransaction->updated_at = @$data['transaction']['updated_at'];
                            $newTransaction->deleted_at = @$data['transaction']['deleted_at'];
                            $newTransaction->renew = @$data['transaction']['renew'];
                            $newTransaction->contract_id = @$data['transaction']['contract_id'];
                            $newTransaction->momentic_log = json_decode(@$data['transaction']['momentic_log'], true);
                            $newTransaction->save();
                        }
                    }else{
                        $cekUser = User::find(@$data['user']['id']);
                        $cekOrder = Order::find(@$data['order']['id']);

                        if (!empty($cekOrder)) {
                            $newTransaction = new Transaction;
                            $newTransaction->id = @$data['transaction']['id'];
                            $newTransaction->user_id = @$data['transaction']['user_id'];
                            $newTransaction->order_id = @$data['transaction']['order_id'];
                            $newTransaction->cart_id = $cekOrder->cart_id;
                            $newTransaction->trx_data = @$data['transaction']['trx_data'];
                            $newTransaction->base_price = @$data['transaction']['base_price'] ?? 0;
                            $newTransaction->deduct_price = @$data['transaction']['deduct_price'];
                            $newTransaction->total = @$data['transaction']['total'];
                            $newTransaction->voucher = @$data['transaction']['voucher'];
                            $newTransaction->policy = @$data['transaction']['policy'];
                            $newTransaction->expiry_date = @$data['transaction']['expiry_date'];
                            $newTransaction->policy_start = @$data['transaction']['policy_start'];
                            $newTransaction->policy_end = @$data['transaction']['policy_end'];
                            $newTransaction->created_at = @$data['transaction']['created_at'];
                            $newTransaction->updated_at = @$data['transaction']['updated_at'];
                            $newTransaction->deleted_at = @$data['transaction']['deleted_at'];
                            $newTransaction->renew = @$data['transaction']['renew'];
                            $newTransaction->contract_id = @$data['transaction']['contract_id'];
                            $newTransaction->momentic_log = json_decode(@$data['transaction']['momentic_log'], true);
                            $newTransaction->save();
                        }
                    }

                #adiratransaction
                    $cekAdira = AdiraTransaction::find(@$data['adira_transaction']['id']);
                    if (!empty($cekAdira)) {
                            $newAdira = AdiraTransaction::find(@$data['adira_transaction']['id']);
                            $newAdira->order_id = @$data['adira_transaction']['order_id'];
                            $newAdira->adira_status = @$data['adira_transaction']['adira_status'];
                            $newAdira->adira_response = json_decode(@$data['adira_transaction']['adira_response'], true);
                            $newAdira->polling_response = @$data['adira_transaction']['polling_response'];
                            $newAdira->resubmit_response = json_decode(@$data['adira_transaction']['resubmit_response'], true);
                            $newAdira->postmikro_response = @$data['adira_transaction']['postmikro_response'];
                            $newAdira->post_finish = json_decode(@$data['adira_transaction']['post_finish'], true);
                            $newAdira->document_policy = json_decode(@$data['adira_transaction']['document_policy'],true);
                            $newAdira->cover_note = json_decode(@$data['adira_transaction']['cover_note'], true);
                            $newAdira->status = @$data['adira_transaction']['status'];
                            $newAdira->log_api = json_decode(@$data['adira_transaction']['log_api'], true);
                            $newAdira->request_number = @$data['adira_transaction']['request_number'];
                            $newAdira->created_at = @$data['adira_transaction']['created_at'];
                            $newAdira->updated_at = @$data['adira_transaction']['updated_at'];
                            $newAdira->ref_number = @$data['adira_transaction']['ref_number'];
                            $newAdira->save();
                    }else{
                        $cekOrder = Order::find(@$data['adira_transaction']['order_id']);
                        if (!empty($cekOrder)) {
                            $newAdira = new AdiraTransaction;
                            $newAdira->id = @$data['adira_transaction']['id'];
                            $newAdira->order_id = @$data['adira_transaction']['order_id'];
                            $newAdira->adira_status = @$data['adira_transaction']['adira_status'];
                            $newAdira->adira_response = json_decode(@$data['adira_transaction']['adira_response'], true);
                            $newAdira->polling_response = @$data['adira_transaction']['polling_response'];
                            $newAdira->resubmit_response = json_decode(@$data['adira_transaction']['resubmit_response'], true);
                            $newAdira->postmikro_response = @$data['adira_transaction']['postmikro_response'];
                            $newAdira->post_finish = json_decode(@$data['adira_transaction']['post_finish'], true);
                            $newAdira->document_policy = json_decode(@$data['adira_transaction']['document_policy'],true);
                            $newAdira->cover_note = json_decode(@$data['adira_transaction']['cover_note'], true);
                            $newAdira->status = @$data['adira_transaction']['status'];
                            $newAdira->log_api = json_decode(@$data['adira_transaction']['log_api'], true);
                            $newAdira->request_number = @$data['adira_transaction']['request_number'];
                            $newAdira->created_at = @$data['adira_transaction']['created_at'];
                            $newAdira->updated_at = @$data['adira_transaction']['updated_at'];
                            $newAdira->ref_number = @$data['adira_transaction']['ref_number'];
                            $newAdira->save();
                        }
                    }

                #adiracomission
                    $cekAdiraComission = AdiraComission::find(@$data['adira_comission']['id']);
                    if (!empty($cekAdiraComission)) {
                        $newAdiraComission = AdiraComission::find(@$data['adira_comission']['id']);
                        $newAdiraComission->transaction_id = @$data['adira_comission']['transaction_id'];
                        $newAdiraComission->comission = @$data['adira_comission']['comission'] ?? 0;
                        $newAdiraComission->discount = @$data['adira_comission']['discount'] ?? 0;
                        $newAdiraComission->share_comission = @$data['adira_comission']['share_comission'] ?? 0;
                        $newAdiraComission->updated_at = @$data['adira_comission']['updated_at'];
                        $newAdiraComission->created_at = @$data['adira_comission']['created_at'];
                        $newAdiraComission->save();
                    }else{
                        $newAdiraComission = new AdiraComission;
                        $newAdiraComission->id = @$data['adira_comission']['id'];
                        $newAdiraComission->transaction_id = @$data['adira_comission']['transaction_id'];
                        $newAdiraComission->comission = @$data['adira_comission']['comission'] ?? 0;
                        $newAdiraComission->discount = @$data['adira_comission']['discount'] ?? 0;
                        $newAdiraComission->share_comission = @$data['adira_comission']['share_comission'] ?? 0;
                        $newAdiraComission->updated_at = @$data['adira_comission']['updated_at'];
                        $newAdiraComission->created_at = @$data['adira_comission']['created_at'];
                        $newAdiraComission->save();
                    }

                #comission
                    $cekComission = Comission::find(@$data['comission']['id']);
                    if (!empty($cekComission)) {
                        
                    }else{
                        $newComission = new Comission;
                        $newComission->id = @$data['comission']['id'];
                        $newComission->trx_id = @$data['comission']['trx_id'];
                        $newComission->user_id = @$data['comission']['user_id'];
                        $newComission->formula = json_decode(@$data['comission']['formula'], true);
                        $newComission->base_price = @$data['comission']['base_price'] ?? 0;
                        $newComission->comission = @$data['comission']['comission'] ?? 0;
                        $newComission->status = @$data['comission']['status'] ?? 'unpaid';
                        $newComission->created_at = @$data['comission']['created_at'];
                        $newComission->updated_at = @$data['comission']['updated_at'];
                        $newComission->username = @$data['comission']['username'];
                        $newComission->useremail = @$data['comission']['useremail'];
                        $newComission->save();
                    }
                #orcomission
                    $cekOrComission = OrComission::find(@$data['orcomission']['id']);
                    if (!empty($cekComission)) {
                        
                    }else{
                        $newOrComission = new OrComission;
                        $newOrComission->id = @$data['orcomission']['id'];
                        $newOrComission->trx_id = @$data['orcomission']['trx_id'];
                        $newOrComission->from_id = @$data['orcomission']['from_id'];
                        $newOrComission->to_id = @$data['orcomission']['to_id'];
                        $newOrComission->formula = json_decode(@$data['orcomission']['formula'], true);
                        $newOrComission->layer = @$data['orcomission']['layer'];
                        $newOrComission->base_price = @$data['orcomission']['base_price'] ?? 0;
                        $newOrComission->comission = @$data['orcomission']['comission'] ?? 0;;
                        $newOrComission->status = @$data['orcomission']['status'] ?? 'unpaid';
                        $newOrComission->created_at = @$data['orcomission']['created_at'];
                        $newOrComission->updated_at = @$data['orcomission']['updated_at'];
                        $newOrComission->from_email = @$data['orcomission']['from_email'];
                        $newOrComission->to_email = @$data['orcomission']['to_email'];
                        $newOrComission->save();
                    }

            
            Log::warning("success migrations transaction finish");
    }

    public function syncTransactionApproved(Request $request)
    {
            $data = json_decode(json_decode($request->getContent(), true), true);
            Log::warning("sync transaction approved");
            #users
                $cekUser = User::find($data['user']['id']);
                if (empty($cekUser)) {
                    $newUser = new User;
                    $newUser->id = $data['user']['id'];
                    $newUser->uuid = $data['user']['uuid'];
                    $newUser->name = $data['user']['name'];
                    $newUser->email = $data['user']['email'];
                    $newUser->password = $data['user']['password'];
                    $newUser->referrer_email = $data['user']['referrer_email'];
                    $newUser->partner_id = $data['user']['partner_id'];
                    $newUser->created_at = $data['user']['created_at'];
                    $newUser->updated_at = $data['user']['updated_at'];
                    $newUser->deleted_at = $data['user']['deleted_at'];
                    $newUser->cooldown = $data['user']['cooldown'];
                    $newUser->tax = $data['user']['tax'];
                    $newUser->fcm_token = $data['user']['fcm_token'];
                    $newUser->version = $data['user']['version'];
                    $newUser->is_production = $data['user']['is_production'];
                    $newUser->oauth_token = $data['user']['oauth_token'];
                    $newUser->platform = $data['user']['platform'];
                    $newUser->last_login = $data['user']['last_login'];
                    $newUser->remember_token = $data['user']['remember_token'];
                    $newUser->verified_at = $data['user']['verified_at'];
                    $newUser->email_verified_at = $data['user']['email_verified_at'];
                    $newUser->is_sf = 0;
                    $newUser->is_web = 0;
                    $newUser->save();

                    $newProfile = new Profile;
                    $newProfile->id = $data['profile']['id'];
                    $newProfile->user_id = $data['profile']['user_id'];
                    $newProfile->address = $data['profile']['address'];
                    $newProfile->phone = $data['profile']['phone'];
                    $newProfile->another_phone = $data['profile']['another_phone'];
                    $newProfile->city = $data['profile']['city'];
                    $newProfile->bank_id = $data['profile']['bank_id'];
                    $newProfile->bank_account = $data['profile']['bank_account'];
                    $newProfile->id_card_pic = $data['profile']['id_card_pic'];
                    $newProfile->avatar = $data['profile']['avatar'];
                    $newProfile->npwp = $data['profile']['npwp'];
                    $newProfile->created_at = $data['profile']['created_at'];
                    $newProfile->updated_at = $data['profile']['updated_at'];
                    $newProfile->bank_branch = $data['profile']['bank_branch'];
                    $newProfile->branch_location = $data['profile']['branch_location'];
                    $newProfile->no_ktp = null;
                    $newProfile->real_name = null;
                    $newProfile->npwp_valid = 0;
                    $newProfile->save();
                }

                #order
                $cekOrder = Order::find($data['order']['id']);
                    if (!empty($cekOrder)) {
                        $getProduct = Product::find($data['order']['product_id']);

                        if ($data['order']['is_offering'] == 1) {
                            $pdf[$data['inquiry_mv']['id'] ?? $data['order']['id']] = env('APP_URL') . '/uploads/pdf/surat_penawaran_asuransi_' . md5($order->id) . '.pdf';
                        } else {
                            $pdf = null;
                        }

                        $cartbody[0]["inquiry_id"] = $data['inquiry_mv']['id'] ?? null;
                        $cartbody[0]["product_id"] = $data['order']['product_id'];
                        $cartbody[0]["total"] = $data['order']['total'];
                        $cartbody[0]["product_data"] = $getProduct;
                        $cartbody[0]["order_id"] = $data['order']['id'];

                        $newCart = Cart::find($cekOrder->cart_id);
                        $newCart->user_id = $data['order']['user_id'];
                        $newCart->name = 'Cart '.$data['order']['id'];
                        $newCart->total = $data['order']['total'];
                        $newCart->data = $cartbody;
                        $newCart->is_checkout = 0;
                        $newCart->is_ref = 0;
                        $newCart->admin_fee = @$data['transaction']['admin_fee'];
                        $newCart->pg_method = @$data['transaction']['pg_method'];
                        $newCart->pg_status = @$data['transaction']['pg_status'];
                        $newCart->pg_callback = @$data['transaction']['pg_callback'];
                        $newCart->pg_link = @$data['transaction']['pg_link'];
                        $newCart->deleted_at = @$data['order']['deleted_at'];
                        $newCart->created_at = @$data['order']['created_at'];
                        $newCart->updated_at = @$data['order']['updated_at'];
                        $newCart->is_offering = @$data['order']['is_offering'];
                        $newCart->offering_email = @$data['order']['offering_email'];
                        $newCart->offering_name = @$data['order']['offering_name'];
                        $newCart->offering_telp = @$data['order']['offering_telp'];
                        $newCart->pdf_link = @$pdf;
                        $newCart->save();

                        $newOrder = Order::find($data['order']['id']);
                        $newOrder->user_id = @$data['order']['user_id'];
                        $newOrder->product_id = @$data['order']['product_id'];
                        $newOrder->transaction_id = @$data['order']['transaction_id'];
                        $newOrder->data = @$data['order']['data'];
                        $newOrder->base_price = @$data['order']['base_price'] ?? 0;
                        $newOrder->cart_id = $newCart->id;
                        $newOrder->total = @$data['order']['total'];
                        $newOrder->coupon = @$data['order']['coupon'];
                        $newOrder->additional_data = @$data['order']['additional_data'];
                        $newOrder->status = @$data['order']['status'];
                        $newOrder->note = @$data['order']['note'];
                        $newOrder->validation = @$data['order']['validation'];
                        $newOrder->start_date = @$data['order']['start_date'];
                        $newOrder->end_date = @$data['order']['end_date'];
                        $newOrder->is_offering = @$data['order']['is_offering'];
                        $newOrder->is_submit = @$data['order']['is_submit'];
                        $newOrder->offering_email = @$data['order']['offering_email'];
                        $newOrder->offering_telp = @$data['order']['offering_telp'];
                        $newOrder->offering_name = @$data['order']['offering_name'];
                        $newOrder->created_at = @$data['order']['created_at'];
                        $newOrder->updated_at = @$data['order']['updated_at'];
                        $newOrder->deleted_at = @$data['order']['deleted_at'];
                        $newOrder->deduct = @$data['order']['deduct'];
                        $newOrder->save();
                    } else {
                        $getProduct = Product::find($data['order']['product_id']);

                        if ($data['order']['is_offering'] == 1) {
                            $pdf[$data['inquiry_mv']['id'] ?? $data['order']['id']] = env('APP_URL') . '/uploads/pdf/surat_penawaran_asuransi_' . md5($order->id) . '.pdf';
                        } else {
                            $pdf = null;
                        }

                        $cartbody[0]["inquiry_id"] = $data['inquiry_mv']['id'] ?? null;
                        $cartbody[0]["product_id"] = $data['order']['product_id'];
                        $cartbody[0]["total"] = $data['order']['total'];
                        $cartbody[0]["product_data"] = $getProduct;
                        $cartbody[0]["order_id"] = $data['order']['id'];

                        $newCart = new Cart;
                        $newCart->user_id = $data['order']['user_id'];
                        $newCart->name = 'Cart '.$data['order']['id'];
                        $newCart->total = $data['order']['total'];
                        $newCart->data = $cartbody;
                        $newCart->is_checkout = 0;
                        $newCart->is_ref = 0;
                        $newCart->admin_fee = @$data['transaction']['admin_fee'];
                        $newCart->pg_method = @$data['transaction']['pg_method'];
                        $newCart->pg_status = @$data['transaction']['pg_status'];
                        $newCart->pg_callback = @$data['transaction']['pg_callback'];
                        $newCart->pg_link = @$data['transaction']['pg_link'];
                        $newCart->deleted_at = @$data['order']['deleted_at'];
                        $newCart->created_at = @$data['order']['created_at'];
                        $newCart->updated_at = @$data['order']['updated_at'];
                        $newCart->is_offering = @$data['order']['is_offering'];
                        $newCart->offering_email = @$data['order']['offering_email'];
                        $newCart->offering_name = @$data['order']['offering_name'];
                        $newCart->offering_telp = @$data['order']['offering_telp'];
                        $newCart->pdf_link = @$pdf;
                        $newCart->save();

                        $newOrder = new Order;
                        $newOrder->id = @$data['order']['id'];
                        $newOrder->user_id = @$data['order']['user_id'];
                        $newOrder->product_id = @$data['order']['product_id'];
                        $newOrder->transaction_id = @$data['order']['transaction_id'];
                        $newOrder->data = @$data['order']['data'];
                        $newOrder->base_price = @$data['order']['base_price'] ?? 0;
                        $newOrder->cart_id = $newCart->id;
                        $newOrder->total = @$data['order']['total'];
                        $newOrder->coupon = @$data['order']['coupon'];
                        $newOrder->additional_data = @$data['order']['additional_data'];
                        $newOrder->status = @$data['order']['status'];
                        $newOrder->note = @$data['order']['note'];
                        $newOrder->validation = @$data['order']['validation'];
                        $newOrder->start_date = @$data['order']['start_date'];
                        $newOrder->end_date = @$data['order']['end_date'];
                        $newOrder->is_offering = @$data['order']['is_offering'];
                        $newOrder->is_submit = @$data['order']['is_submit'];
                        $newOrder->offering_email = @$data['order']['offering_email'];
                        $newOrder->offering_telp = @$data['order']['offering_telp'];
                        $newOrder->offering_name = @$data['order']['offering_name'];
                        $newOrder->created_at = @$data['order']['created_at'];
                        $newOrder->updated_at = @$data['order']['updated_at'];
                        $newOrder->deleted_at = @$data['order']['deleted_at'];
                        $newOrder->deduct = @$data['order']['deduct'];
                        $newOrder->save();
                    }

                #inquiry
                if (!empty($data['inquiry_mv'])) {
                    $cekInquiry = InquiryMv::find($data['inquiry_mv']['id']);
                    if (!empty($cekInquiry)) {
                            $newInquiry = InquiryMv::find($data['inquiry_mv']['id']);
                            $newInquiry->item = $data['inquiry_mv']['item'];
                            $newInquiry->total = $data['inquiry_mv']['total'];
                            $newInquiry->product_id = $data['inquiry_mv']['product_id'];
                            $newInquiry->order_id = $data['inquiry_mv']['order_id'];
                            $newInquiry->status = $data['inquiry_mv']['status'];
                            $dataInquiry = $data['inquiry_mv']['data'];
                            if (!empty($data['inquiry_mv']['order_id'])) {
                                $cekOrder = Order::find($data['inquiry_mv']['order_id']);
                                if (!empty($cekOrder)) {
                                    $dataInquiry['policy_type'] = $cekOrder->additional_data[0]['copy'];
                                }
                            }
                            $newInquiry->data = $dataInquiry;
                            $newInquiry->discount = $data['inquiry_mv']['discount'];
                            $newInquiry->offering_email = $data['inquiry_mv']['offering_email'];
                            $newInquiry->updated_at = $data['inquiry_mv']['updated_at'];
                            $newInquiry->created_at = $data['inquiry_mv']['created_at'];
                            $newInquiry->save();
                            Log::warning('save update inquiry');
                    }else {
                            $newInquiry = new InquiryMv;
                            $newInquiry->id = $data['inquiry_mv']['id'];
                            $newInquiry->item = $data['inquiry_mv']['item'];
                            $newInquiry->total = $data['inquiry_mv']['total'];
                            $newInquiry->product_id = $data['inquiry_mv']['product_id'];
                            $newInquiry->order_id = $data['inquiry_mv']['order_id'];
                            $newInquiry->status = $data['inquiry_mv']['status'];
                            $dataInquiry = $data['inquiry_mv']['data'];
                            if (!empty($data['inquiry_mv']['order_id'])) {
                                $cekOrder = Order::find($data['inquiry_mv']['order_id']);
                                if (!empty($cekOrder)) {
                                    $dataInquiry['policy_type'] = $cekOrder->additional_data[0]['copy'];
                                }
                            }
                            $newInquiry->data = $dataInquiry;
                            $newInquiry->discount = $data['inquiry_mv']['discount'];
                            $newInquiry->offering_email = $data['inquiry_mv']['offering_email'];
                            $newInquiry->updated_at = $data['inquiry_mv']['updated_at'];
                            $newInquiry->created_at = $data['inquiry_mv']['created_at'];
                            $newInquiry->save();
                            Log::warning('save new inquiry');
                    }
                }

                #adiratransaction
                $cekAdira = AdiraTransaction::find(@$data['adira_transaction']['id']);
                if (!empty($cekAdira)) {
                        $newAdira = AdiraTransaction::find(@$data['adira_transaction']['id']);
                        $newAdira->order_id = @$data['adira_transaction']['order_id'];
                        $newAdira->adira_status = @$data['adira_transaction']['adira_status'];
                        $newAdira->adira_response = json_decode(@$data['adira_transaction']['adira_response'],true);
                        $newAdira->polling_response = @$data['adira_transaction']['polling_response'];
                        $newAdira->resubmit_response = json_decode(@$data['adira_transaction']['resubmit_response'], true);
                        $newAdira->postmikro_response = @$data['adira_transaction']['postmikro_response'];
                        $newAdira->post_finish = @$data['adira_transaction']['post_finish'];
                        $newAdira->document_policy = @$data['adira_transaction']['document_policy'];
                        $newAdira->cover_note = @$data['adira_transaction']['cover_note'];
                        $newAdira->status = @$data['adira_transaction']['status'];
                        $newAdira->log_api = json_decode(@$data['adira_transaction']['log_api'],true);
                        $newAdira->request_number = @$data['adira_transaction']['request_number'];
                        $newAdira->created_at = @$data['adira_transaction']['created_at'];
                        $newAdira->updated_at = @$data['adira_transaction']['updated_at'];
                        $newAdira->ref_number = @$data['adira_transaction']['ref_number'];
                        $newAdira->save();
                }else{
                    $cekOrder = Order::find(@$data['adira_transaction']['order_id']);
                    if (!empty($cekOrder)) {
                        $newAdira = new AdiraTransaction;
                        $newAdira->id = @$data['adira_transaction']['id'];
                        $newAdira->order_id = @$data['adira_transaction']['order_id'];
                        $newAdira->adira_status = @$data['adira_transaction']['adira_status'];
                        $newAdira->adira_response = json_decode(@$data['adira_transaction']['adira_response'],true);
                        $newAdira->polling_response = @$data['adira_transaction']['polling_response'];
                        $newAdira->resubmit_response = json_decode(@$data['adira_transaction']['resubmit_response'], true);
                        $newAdira->postmikro_response = @$data['adira_transaction']['postmikro_response'];
                        $newAdira->post_finish = @$data['adira_transaction']['post_finish'];
                        $newAdira->document_policy = @$data['adira_transaction']['document_policy'];
                        $newAdira->cover_note = @$data['adira_transaction']['cover_note'];
                        $newAdira->status = @$data['adira_transaction']['status'];
                        $newAdira->log_api = json_decode(@$data['adira_transaction']['log_api'],true);
                        $newAdira->request_number = @$data['adira_transaction']['request_number'];
                        $newAdira->created_at = @$data['adira_transaction']['created_at'];
                        $newAdira->updated_at = @$data['adira_transaction']['updated_at'];
                        $newAdira->ref_number = @$data['adira_transaction']['ref_number'];
                        $newAdira->save();
                    }
                }
                Log::warning("success migrations transaction approved");
    }
    public function revokeUsers() {
        $findUser = User::find(7137);
        $user = $findUser->deleteToken('appToken')->accessToken;
        $user->revoke();
        return 'done';
        // $userall = User::all();
        // foreach ($userall as $key => $value) {
        //     $findUser = User::find($value->id);
        //     $user = $findUser->token();
        //     $user->revoke();
        // }
    }

    public function testGetCart() {
        $getInquiry = InquiryMv::find(42696998);

        $carts = Cart::where('user_id',8063)->get();
        foreach ($carts as $key => $value) {
            foreach ($value->data as $key => $data) {
                if ($data['inquiry_id'] == $getInquiry->id) {
                    return $value->id;
                }
            }
        }
        return $carts;
    }

    public function getResponse() {
        $order = Order::find(17456);
        // abort_if($order == null, 404);
        // if($request->request_number == null) {
        //     return response(["status" => false, "message" => "Request number is required"], 422);
        // }

        $json = '{"status":"success","data":{"requestNumber":"PA1896-PR047-2210070457","personalIdType":"KTP","personalIdNumber":"3578100707870001","name":"Willy Alben Ludong","email":"ezraawibisono@gmai.com","mobileNumber":"082288882403","address":"Jl. kahuripan no 1","provinceCode":"Jawa Timur","cityCode":"Kota Surabaya","emailSent":true,"calculateData":{"totalSumInsured":240000001,"totalPremium":4286180,"adminFee":0,"dutyFee":10000,"commissionPercentage":10,"discountPercentage":15,"totalDiscountAmount":754620,"rates":[{"type":"CASCO","rate":1.79,"premium":4510800.018795,"ano":121572333,"discountAmount":676620,"premiumCalculation":"240,000,001.00 x 1.87950000%"},{"type":"TSFHL","rate":0.075,"premium":180000.00075,"ano":121572333,"discountAmount":27000,"premiumCalculation":"240,000,001.00 x 0.07500000%"},{"type":"TPL","rate":1,"premium":100000,"ano":121572333,"discountAmount":15000,"premiumCalculation":"10,000,000.00 x 1.00000000%"},{"type":"ATPM","rate":0.1,"premium":240000.001,"ano":121572333,"discountAmount":36000,"premiumCalculation":"240,000,001.00 x 0.10000000%"}],"loading":{"rate":5,"ano":121572333},"fee":{"amount":10000,"ano":121572333},"deductibles":[{"ano":121572333,"code":"MVR07.01","remarks":"Partial Loss - Autocillin Garage & Partner Workshop non ATPM : IDR 300,000.00 for any one accident (Private\/Operational)  "},{"ano":121572333,"code":"MVR07.04","remarks":"Partial Loss - Autocillin Garage & Non ATPM : IDR 400,000.00 for any one accident (Commercial)  "},{"ano":121572333,"code":"MVR07.08","remarks":"Partial Loss - Workshop for ATPM Partners (without the expansion of ATPM guarantee) : IDR 500,000.00 for any one accident (Private\/Operational)  "},{"ano":121572333,"code":"MVR13.11","remarks":"Total Loss : 10% of claim, minimum : IDR 1.000,000.00 (Bus, Truck, Pick Up)  \r\n"},{"ano":121572333,"code":"MVR13.14","remarks":"Total Loss : 5% of claim, minimum : IDR 1,000,000.00 (Non Bus Non Truck)  "},{"ano":121572333,"code":"MVR22.01","remarks":"Angin Topan, Badai, Hujan Es, Banjir, Tanah Longsor : 10% dari nilai klaim, minimum : IDR 500,000.00 untuk setiap kejadian \/ Typhoon, Storm, Hail, Flood, Landslide : 10% of claim, minimum : IDR 500,000.00 for any one accident"},{"ano":121572333,"code":"MVR23.03","remarks":"Kerugian Sebagian - Bengkel Rekanan ATPM (dengan perluasan jaminan ATPM)  : IDR 300.000,00 untuk setiap kejadian (Pribadi\/Dinas)  \/Partial Loss - ATPM Partner Workshop (with extended ATPM guarantee) : IDR 300.000,00 for any one accident (Private\/Operation"}],"clauses":[{"ano":121572333,"orderNo":"AAUI-AAD01","description":"DISPUTE CLAUSE"},{"ano":121572333,"orderNo":"D05","description":"DEFERRED PREMIUM CLAUSE"},{"ano":121572333,"orderNo":"K0201","description":"WINDSTORM, TEMPEST, HAIL, FLOOD AND\/OR LANDSLIDE CLAUSE"},{"ano":121572333,"orderNo":"K0202","description":"KLAUSUL BANK PT BANK CENTRAL ASIA TBK \r\n"},{"ano":121572333,"orderNo":"K0207","description":"Pre-existing Damage CLAUSE"},{"ano":121572333,"orderNo":"K0215","description":"COMPLETELY BUILT-UP (CBU) CLAUSE OF MOTOR VEHICLE"},{"ano":121572333,"orderNo":"K0217","description":"KLAUSUL KERUGIAN TOTAL SAJA"},{"ano":121572333,"orderNo":"K0221","description":"MODIFICATION AND PAIRED PART\/SET CLAUSE"},{"ano":121572333,"orderNo":"K0224","description":"KLAUSUL NO. 2 (SEPEDA MOTOR)"},{"ano":121572333,"orderNo":"K0234","description":"KLAUSUL PENGECUALIAN PERLENGKAPAN TAMBAHAN DAN MODIFIKASI"},{"ano":121572333,"orderNo":"K0236","description":"WAIVER CLAUSE"},{"ano":121572333,"orderNo":"K0237","description":"SPAREPART REPLACEMENT CLAUSE"},{"ano":121572333,"orderNo":"K0249","description":"LEGAL LIABILITY AGAINST THIRD PARTY ONLY (GUARANTEE E) CLAUSE"},{"ano":121572333,"orderNo":"K0243","description":"KLAUSUL PERSELISIHAN"}]},"totalPremium":4286180,"adminFee":0,"dutyFee":10000,"totalDiscountAmount":754620,"rates":[{"type":"CASCO","rate":1.79,"premium":4510800.018795,"ano":121572333,"discountAmount":676620,"premiumCalculation":"240,000,001.00 x 1.87950000%"},{"type":"TSFHL","rate":0.075,"premium":180000.00075,"ano":121572333,"discountAmount":27000,"premiumCalculation":"240,000,001.00 x 0.07500000%"},{"type":"TPL","rate":1,"premium":100000,"ano":121572333,"discountAmount":15000,"premiumCalculation":"10,000,000.00 x 1.00000000%"},{"type":"ATPM","rate":0.1,"premium":240000.001,"ano":121572333,"discountAmount":36000,"premiumCalculation":"240,000,001.00 x 0.10000000%"}]}}';
        $decode = json_decode($json,true);
        $decode["data"]["requestNumber"] = 'PA1896-PR046-2406030090';
        $decode["data"]["personalIdType"] = "KTP";
        $decode["data"]["personalIdNumber"] = $order->data["33"]["data"];
        $decode["data"]["name"] = $order->data["1"]["data"];
        $decode["data"]["email"] = $order->data["9"]["data"];
        $decode["data"]["mobileNumber"] = $order->data["13"]["data"];
        $decode["data"]["address"] = $order->data["36"]["data"];
        $decode["data"]["provinceCode"] = $order->data["54"]["data"];
        $decode["data"]["cityCode"] = $order->data["53"]["data"];
        $decode["data"]["address"] = $order->data["36"]["data"];

        return $decode;

        $adira = AdiraTransaction::where('order_id',$id)->first();
            if(empty($adira)) {
                $adira = new AdiraTransaction;
            }
            $adira->order_id = $id;
            $adira->adira_response = $decode;
            $adira->status = "success";
            $adira->request_number = $request->request_number;
            $adira->save();
    }

    public function updateInquiry() {
        $dataInquiry = InquiryMv::all();
        foreach ($dataInquiry as $key => $value) {
            if (!empty($value->order_id)) {
                $cekOrder = Order::find($value->order_id);
                if (!empty($cekOrder)) {
                    $updateData = InquiryMv::find($value->id);
                    $dataInquiry = $value->data;
                    $dataInquiry['policy_type'] = $cekOrder->additional_data[0]['copy'];
                    $updateData->data = $dataInquiry;
                    $updateData->save();
                }
            }
        }
        return 'done';
    }

    public function updateProfile() {
        $dataProfile = Profile::all();
        foreach ($dataProfile as $key => $value) {
            $updateProfile = Profile::find($value->id);
            $updateProfile->npwp_valid = 1;
            $updateProfile->save();
        }
        return 'done';
    }

    public function deletePenawaranZap2() {
        return 'sini';
        // $getOrder = Order::where('data', null)
        // ->where('is_offering',1)->where('start_date', null)->where('id', '<', 17800)
        // ->where('transaction_id', null)->orderBy('id','DESC')->get();
        // // return $getOrder;
        // foreach ($getOrder as $key => $value) {
        //     $cekAdira = AdiraTransaction::where('order_id',$value->id)->first();
        //     if (!empty($cekAdira)) {
                
        //     }else{
        //         $deleteOrder = Order::find($value->id);
        //         $deleteOrder->delete();

        //         $deleteInquiry = InquiryMv::where('order_id', $value->id)->delete();
        //     }
        // }
    }
    public function rollbackCart() {
        return 'sini';
        // $getCart = Cart::where('is_checkout', 1)->withTrashed()
        //         ->whereNotNull('deleted_at')->get();
        // return $getCart;
        // foreach ($getCart as $key => $value) {
        //     $value->restore();
        // }
    }
    public function updateDeduct() {
        $getOrder = Order::where('id','>',17000)->get();
        foreach ($getOrder as $key => $value) {
           $findInquiry = InquiryMv::where('order_id',$value->id)->first();
           if (!empty($findInquiry)) {
                $updateOrder = Order::find($value->id);
                $updateOrder->deduct = $findInquiry->discount ?? null;
                $updateOrder->save();
           }
        }
        return 'done';
        // $getTransaction = Transaction::where('id','>',5900)->where('trx_data','!=',null)->get();
        // foreach ($getTransaction as $key => $value) {
        //     $getInquiry = InquiryMv::where('order_id',$value->order_id)->first();
        //     $cekOrder = Order::find($value->order_id);
        //     if (!empty($cekOrder) && !empty($getInquiry)) {
        //         $updateOrder = Order::find($cekOrder->id);
        //         $updateOrder->deduct = $getInquiry->discount ?? null;
        //         $updateOrder->save();

        //         $updateTransaction = Transaction::find($value->id);
        //         $updateTransaction->deduct_price = $getInquiry->discount ?? null;
        //         $updateTransaction->save();
        //     }
        // }
    }

    public function updateTotal() {
        $getCart = Cart::where('id','>',14000)->where('pg_status',null)->get();
        foreach ($getCart as $key => $value) {
            $cekOrder = Order::where('cart_id',$value->id)->get();
            if (count($cekOrder) == 1) {
                $findOrder = Order::where('cart_id',$value->id)->first();
                if ($findOrder->product->flow == 'mv') {
                    $updateTotal = Cart::find($value->id);
                    $updateTotal->total = $updateTotal->data[0]['total'];
                    $updateTotal->save();
                    
                    $updateOrder = Order::where('cart_id',$value->id)->first();
                    $updateOrder->total = $updateTotal->data[0]['total'];
                    return $updateOrder;
                    $updateOrder->save();
                }
            }
        }
    }

    public function checkChasistPortal(Request $request) {
        // return 'sini';
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

        return response()->json($table, 200);
    }
}