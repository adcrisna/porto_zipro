<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use DB;
use Validator;
use App\Models\AdiraTransaction;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\Product;
use App\Models\Cart;
use App\Models\InquiryMv;

use App\Jobs\ResubmitJob;
use App\Jobs\PosMikroMail;
use App\Jobs\OrderJob;
use App\Jobs\ComissionJob;
use App\Jobs\PostmikroJob;
use App\Jobs\FinishOrderJob;
use App\Jobs\PosMudikJob;
use App\Jobs\CreatePolicy;
use App\Jobs\PerjalananJob;
use App\Jobs\TravelJob;
use App\Jobs\OfferingTravelJob;
use Carbon;


class RetryController extends Controller
{
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
            }
            else{
                $order = Order::find($order_id);
                $product = Product::find($order->product_id);
                $inquiry = InquiryMv::where("order_id", $order_id)->orderBy('id', "desc")->first();
                OrderJob::dispatch($order, $inquiry, $product);
                // dispatch(new OrderJob($order->id, $order->data, $order->additional_data[0]["copy"], $order->start_date." 00:00:00.000", $inquiry->id, $order->product, 0));
                $res[][$order_id] = "success";
            }
        }

        return response()->json($res);
    }

    public function retryfinish(Request $request){
        // return 'ini';
        $validator = Validator::make($request->all() ,[
            "order_id" => 'required'
        ]);
        if($validator->fails()) {
            return response(['status' => false, 'message' => $validator->errors()], 422);
        }
        $trx = [$request->order_id];
        $stat = [];
        foreach($trx as $trans){
            $transaction = Transaction::where('order_id',$trans)->first();
            // dispatch(new FinishOrderJob($transaction->order))->delay(now()->addMinutes(15));
            // return $transaction;
            dispatch(new FinishOrderJob($transaction->cart));
            $stat[][$trans]= "success";
        }
        return response()->json($stat);
    }

    public function retryMikro(Request $request){
        // return "sini tod";
        $arr = [$request->transaction_id];

        foreach($arr as $array){
            $transaction = Transaction::find($array);
            if ($transaction->order->data[37]['data'] == 'Pria') {
                $gender = "Male";
            }else{
                $gender = "Female";
            }
            dispatch(new PostmikroJob($transaction->order->id, $transaction->order->data, $transaction->order->start_date, $transaction->order->product, $gender));
        }
        return response()->json('done');
    }

    public function retryPerjalanan(Request $request)
    {
        // return "masuk tod";
        $order = Order::find($request->order_id);
        if(empty($order)) {
            return response([
                "status" => false,
                "message" => "Order not found"
            ], 404);
        }
        $del = AdiraTransaction::where("order_id",$order->id)->delete();
        dispatch(new PerjalananJob($order));
        return response()->json('done');
    }

    public function retryTravel(Request $request){
        // return "haha";
        $arr = [$request->order_id];
        foreach($arr as $array){
            // return $array;
            $order = Order::find($array);
            $adira_trx = AdiraTransaction::where('order_id', $order->id)->first();
            if(!empty($adira_trx)) {
                $adira_trx->delete();
            }
            dispatch(new TravelJob($order));
        }
        return response()->json('done');
    }

    public function retryResponse(Request $request,$id)
    {
        try {
            // return $request;
            // return 'masuk tod';
            $order = Order::find($id);
            abort_if($order == null, 404);
            if($request->request_number == null) {
                return response(["status" => false, "message" => "Request number is required"], 422);
            }

            $json = '{"status":"success","data":{"requestNumber":"PA1896-PR047-2210070457","personalIdType":"KTP","personalIdNumber":"3578100707870001","name":"Willy Alben Ludong","email":"ezraawibisono@gmai.com","mobileNumber":"082288882403","address":"Jl. kahuripan no 1","provinceCode":"Jawa Timur","cityCode":"Kota Surabaya","emailSent":true,"calculateData":{"totalSumInsured":240000001,"totalPremium":4286180,"adminFee":0,"dutyFee":10000,"commissionPercentage":10,"discountPercentage":15,"totalDiscountAmount":754620,"rates":[{"type":"CASCO","rate":1.79,"premium":4510800.018795,"ano":121572333,"discountAmount":676620,"premiumCalculation":"240,000,001.00 x 1.87950000%"},{"type":"TSFHL","rate":0.075,"premium":180000.00075,"ano":121572333,"discountAmount":27000,"premiumCalculation":"240,000,001.00 x 0.07500000%"},{"type":"TPL","rate":1,"premium":100000,"ano":121572333,"discountAmount":15000,"premiumCalculation":"10,000,000.00 x 1.00000000%"},{"type":"ATPM","rate":0.1,"premium":240000.001,"ano":121572333,"discountAmount":36000,"premiumCalculation":"240,000,001.00 x 0.10000000%"}],"loading":{"rate":5,"ano":121572333},"fee":{"amount":10000,"ano":121572333},"deductibles":[{"ano":121572333,"code":"MVR07.01","remarks":"Partial Loss - Autocillin Garage & Partner Workshop non ATPM : IDR 300,000.00 for any one accident (Private\/Operational)  "},{"ano":121572333,"code":"MVR07.04","remarks":"Partial Loss - Autocillin Garage & Non ATPM : IDR 400,000.00 for any one accident (Commercial)  "},{"ano":121572333,"code":"MVR07.08","remarks":"Partial Loss - Workshop for ATPM Partners (without the expansion of ATPM guarantee) : IDR 500,000.00 for any one accident (Private\/Operational)  "},{"ano":121572333,"code":"MVR13.11","remarks":"Total Loss : 10% of claim, minimum : IDR 1.000,000.00 (Bus, Truck, Pick Up)  \r\n"},{"ano":121572333,"code":"MVR13.14","remarks":"Total Loss : 5% of claim, minimum : IDR 1,000,000.00 (Non Bus Non Truck)  "},{"ano":121572333,"code":"MVR22.01","remarks":"Angin Topan, Badai, Hujan Es, Banjir, Tanah Longsor : 10% dari nilai klaim, minimum : IDR 500,000.00 untuk setiap kejadian \/ Typhoon, Storm, Hail, Flood, Landslide : 10% of claim, minimum : IDR 500,000.00 for any one accident"},{"ano":121572333,"code":"MVR23.03","remarks":"Kerugian Sebagian - Bengkel Rekanan ATPM (dengan perluasan jaminan ATPM)  : IDR 300.000,00 untuk setiap kejadian (Pribadi\/Dinas)  \/Partial Loss - ATPM Partner Workshop (with extended ATPM guarantee) : IDR 300.000,00 for any one accident (Private\/Operation"}],"clauses":[{"ano":121572333,"orderNo":"AAUI-AAD01","description":"DISPUTE CLAUSE"},{"ano":121572333,"orderNo":"D05","description":"DEFERRED PREMIUM CLAUSE"},{"ano":121572333,"orderNo":"K0201","description":"WINDSTORM, TEMPEST, HAIL, FLOOD AND\/OR LANDSLIDE CLAUSE"},{"ano":121572333,"orderNo":"K0202","description":"KLAUSUL BANK PT BANK CENTRAL ASIA TBK \r\n"},{"ano":121572333,"orderNo":"K0207","description":"Pre-existing Damage CLAUSE"},{"ano":121572333,"orderNo":"K0215","description":"COMPLETELY BUILT-UP (CBU) CLAUSE OF MOTOR VEHICLE"},{"ano":121572333,"orderNo":"K0217","description":"KLAUSUL KERUGIAN TOTAL SAJA"},{"ano":121572333,"orderNo":"K0221","description":"MODIFICATION AND PAIRED PART\/SET CLAUSE"},{"ano":121572333,"orderNo":"K0224","description":"KLAUSUL NO. 2 (SEPEDA MOTOR)"},{"ano":121572333,"orderNo":"K0234","description":"KLAUSUL PENGECUALIAN PERLENGKAPAN TAMBAHAN DAN MODIFIKASI"},{"ano":121572333,"orderNo":"K0236","description":"WAIVER CLAUSE"},{"ano":121572333,"orderNo":"K0237","description":"SPAREPART REPLACEMENT CLAUSE"},{"ano":121572333,"orderNo":"K0249","description":"LEGAL LIABILITY AGAINST THIRD PARTY ONLY (GUARANTEE E) CLAUSE"},{"ano":121572333,"orderNo":"K0243","description":"KLAUSUL PERSELISIHAN"}]},"totalPremium":4286180,"adminFee":0,"dutyFee":10000,"totalDiscountAmount":754620,"rates":[{"type":"CASCO","rate":1.79,"premium":4510800.018795,"ano":121572333,"discountAmount":676620,"premiumCalculation":"240,000,001.00 x 1.87950000%"},{"type":"TSFHL","rate":0.075,"premium":180000.00075,"ano":121572333,"discountAmount":27000,"premiumCalculation":"240,000,001.00 x 0.07500000%"},{"type":"TPL","rate":1,"premium":100000,"ano":121572333,"discountAmount":15000,"premiumCalculation":"10,000,000.00 x 1.00000000%"},{"type":"ATPM","rate":0.1,"premium":240000.001,"ano":121572333,"discountAmount":36000,"premiumCalculation":"240,000,001.00 x 0.10000000%"}]}}';
            $decode = json_decode($json,true);
            $decode["data"]["requestNumber"] = $request->request_number;
            $decode["data"]["personalIdType"] = "KTP";
            $decode["data"]["personalIdNumber"] = $order->data["33"]["data"];
            $decode["data"]["name"] = $order->data["1"]["data"];
            $decode["data"]["email"] = $order->data["9"]["data"];
            $decode["data"]["mobileNumber"] = $order->data["13"]["data"];
            $decode["data"]["address"] = $order->data["36"]["data"];
            $decode["data"]["provinceCode"] = $order->data["54"]["data"];
            $decode["data"]["cityCode"] = $order->data["53"]["data"];
            $decode["data"]["address"] = $order->data["36"]["data"];

            $adira = AdiraTransaction::where('order_id',$id)->first();
            if(empty($adira)) {
                $adira = new AdiraTransaction;
            }
            $adira->order_id = $id;
            $adira->adira_response = $decode;
            $adira->status = "success";
            $adira->request_number = $request->request_number;
            $adira->save();

            return response($decode, 200);

        } catch (\Exception $e) {
            return response($e->getMessage(), 500);
        }
    }

    public function retrypolicyMikro($id)
    {
        // return "masuk tod";
        $order = Order::find($id);
        dispatch(new PosMikroMail($order->adira_trx->id));
        return response()->json('done');
    }

    public function retrypolicy($id)
    {
        // return "masuk tod";
        $order = Order::find($id);
        $transaction = AdiraTransaction::where('order_id', $order->id)->first();
        $policy_no = $transaction->polling_response['data']['policy']['carePolicyNo'];
        dispatch(new CreatePolicy($order, $policy_no, $order['data'][1]['data']));
        return response()->json('done');
    }

    public function retryrenewal(Request $request)
    {
        // return "masuk tod";
        $order = Order::find($request->order_id);
        if (!empty($order)) {
           $product = Product::find($order->product_id);
            $inquiry = InquiryMv::where("order_id",1083)->orderBy('id',"desc")->first();
            dispatch(new OrderJob($order, $inquiry, $product));
            
            return response()->json('done');
        }else{
            return response()->json('order not found');
        }
    }

    public function retryVa(Request $request) {
        // return 'ngapain?';
        if($request->salvus_code == 'SALVUS69'){
            if (!empty($request->bank)) {
                $data = Transaction::where('order_id', $request->order_id)->first();
                if (!empty($data)) {
                    $cart = Cart::find($data->cart_id);
                    if (!empty($cart)) {
                        if ($data->order->product->flow == "web") {
                            $datenow = new \DateTime(date("Y-m-d H:i:s"));
                            $expect = new \DateTime(date("Y-m-d")." 23:59:00");
                            $invoice_duration = $expect->getTimestamp() - $datenow->getTimestamp();
                        } else {
                            $invoice_duration = 259200;
                        }
                        
                        $params = [
                            'for-user-id' => env("XENDIT_USER_ID"),
                            'external_id' => (string)$cart->id,
                            'payer_email' => $cart->user->email,
                            'description' => "Pembayaran ".$data->order->product->name,
                            'amount' => doubleval($cart->total),
                            'payment_methods' => [$request->bank],
                            'invoice_duration' => $invoice_duration
                            ];
                            // return $params;
                            \Xendit\Xendit::setApiKey(env("XENDIT_API_KEY"));
                            $createInvoice = \Xendit\Invoice::create($params);
                            
                            $cart->pg_status = 1;
                            $link = $createInvoice['invoice_url'];
                            $cart->pg_method = $request->bank;
                            $cart->pg_link = $link;
                            $cart->pg_callback = $createInvoice;
                            $data->expiry_date = Carbon\Carbon::parse($createInvoice["expiry_date"])->setTimezone(env("APP_TIMEZONE"))->toDateTimeString();
                            $cart->save();
                            $data->save();
                            return response()->json($cart, 200);
                    } else {
                            return 'order tidak ditemukan';
                    }
                } else {
                    return 'order tidak ditemukan';
                }
            }else{
                $data = Transaction::where('order_id', $request->order_id)->first();
                
                if (!empty($data)) {
                    $cart = Cart::find($data->cart_id);
                    // return $cart->pg_callback['bank_code'];
                    if (!empty($cart)) {
                        if ($data->order->product->flow == "web") {
                            $datenow = new \DateTime(date("Y-m-d H:i:s"));
                            $expect = new \DateTime(date("Y-m-d")." 23:59:00");
                            $invoice_duration = $expect->getTimestamp() - $datenow->getTimestamp();
                        } else {
                            $invoice_duration = 259200;
                        }
                        
                        $params = [
                            'for-user-id' => env("XENDIT_USER_ID"),
                            'external_id' => (string)$cart->id,
                            'payer_email' => $cart->user->email,
                            'description' => "Pembayaran ".$data->order->product->name,
                            'amount' => doubleval($cart->total),
                            'payment_methods' => [$cart->pg_callback['bank_code']],
                            'invoice_duration' => $invoice_duration
                            ];
                            // return $params;
                            \Xendit\Xendit::setApiKey(env("XENDIT_API_KEY"));
                            $createInvoice = \Xendit\Invoice::create($params);
                            
                            $cart->pg_status = 1;
                            $link = $createInvoice['invoice_url']; 
                            $cart->pg_link = $link;
                            $cart->pg_callback = $createInvoice;
                            $data->expiry_date = Carbon\Carbon::parse($createInvoice["expiry_date"])->setTimezone(env("APP_TIMEZONE"))->toDateTimeString();
                            $cart->save();
                            $data->save();
                            return response()->json($cart, 200);
                    } else {
                        return response()->json('order ga ada');
                    }
                } else {
                    return response()->json('order ga ada');
                }
            }
        }else{
            return response()->json('code salah');
        }
    }

    public function retryBackorder(Request $request)
    {
        // return 'sini';
        $order = Order::find($request->order_id);
        $inquiry_id = $order->inquiry->id;
        $policyType = $order->additional_data[0]["copy"];
        // return json_encode($order->data);
        dispatch(new ResubmitJob($order->id, $order->data, $policyType, $order->start_date, $order->inquiry->id));
        return response()->json('done');
    }

    function retryOfferingTravel() {
        // return 'sini';
        $orders = Order::find(17978);
        dispatch(new OfferingTravelJob($orders));
        return response()->json('done');
    }
    public function createCart() {
                    $cekOrder = Order::find();
                    $cekInquiry = InquiryMv::find();
                    $getProduct = Product::find($cekOrder->product_id);
                    if ($cekOrder->is_offering == 1) {
                        $pdf[$cekInquiry->id ?? $cekOrder->id] = env('APP_URL') . '/uploads/pdf/surat_penawaran_asuransi_' . md5($cekOrder->id) . '.pdf';
                    } else {
                        $pdf = null;
                    }

                    $cartbody[0]["inquiry_id"] = $cekInquiry->id ?? null;
                    $cartbody[0]["product_id"] = $cekOrder->product_id;
                    $cartbody[0]["total"] = $cekOrder->total;
                    $cartbody[0]["product_data"] = $getProduct;
                    $cartbody[0]["order_id"] = $cekOrder->id;

                    $newCart = new Cart;
                    $newCart->user_id = $cekOrder->user_id;
                    $newCart->name = 'Cart '.$cekOrder->id;
                    $newCart->total = $cekOrder->total;
                    $newCart->data = $cartbody;
                    $newCart->is_checkout = 0;
                    $newCart->is_ref = 0;
                    $newCart->admin_fee = 0;
                    $newCart->pg_method = null;
                    $newCart->pg_status = null;
                    $newCart->pg_callback = null;
                    $newCart->pg_link = null;
                    $newCart->deleted_at = @$cekOrder->deleted_at;
                    $newCart->created_at = @$cekOrder->created_at;
                    $newCart->updated_at = @$cekOrder->updated_at;
                    $newCart->is_offering = @$cekOrder->is_offering;
                    $newCart->offering_email = @$cekOrder->is_offering;
                    $newCart->offering_name = @$cekOrder->offering_name;
                    $newCart->offering_telp = @$cekOrder->offering_telp;
                    $newCart->pdf_link = @$pdf;
                    $newCart->save();
                }
}
