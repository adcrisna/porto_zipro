<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Comission;
use Xendit\Xendit;
use Xendit\Invoice;
use App\Models\Cart;
use App\Models\FormRepoCategory;
use App\Models\FormRepo;
use App\Models\Point;
use App\Models\Order;
use App\Models\Voucher;
use App\Models\Arrays;
use App\Models\InquiryMv;
use App\Models\Orscheme;
Use Illuminate\Support\Str;
use App\Models\AdiraCommission;
use App\Models\OrComission;
use App\Jobs\FinishOrderJob;
use App\Jobs\ComissionJob;
use App\Jobs\TravelJob;
use App\Jobs\PostmikroJob;
use App\Jobs\PerjalananJob;
use App\Jobs\PaymentMailJob;
use App\Jobs\SpectrumJob;
use DB;
use Carbon;
use Log;

class TransactionController extends Controller
{
    public function initTransaction(Request $request)
    {
        $cart = Cart::select('id','name','total', 'data')->with('orders')->find($request->cart_id);
        if(!$cart) {
            return response([
                "status" => false,
                "message" => "Cart does not exist"
            ], 404);
        }

        try {
            
            $data = [];
            foreach($cart->data as $keycart => $cartbody) {
                $repo_category = FormRepoCategory::where('category_id', $cartbody['product_data']['category_id'])->first();
                $form = [];

                $apply = $repo_category->form_json['apply'];
                
                $formrepo = FormRepo::whereIn('id', $apply)->orderByRaw('FIELD(id,'. implode(", ",$apply).')')->get();
                // return $repo_category;
                if (!empty($repo_category->form_validation)) {
                    foreach ($repo_category->form_validation['apply'] as $key_valid => $form_validation) {
                        $valid[$form_validation['apply']] = $form_validation;
                    }
                }
                foreach ($formrepo as $key => $repo) {
                    if($repo->value != null){
                        $value = Arrays::find($repo->value)?->value;
                    }

                    if($repo->form_type == "text" || $repo->form_type == "number" || $repo->form_type == "images"){
                        $validation = [
                            "is_required" => !empty($valid[$repo->id]['required']) ? true : false,
                            "min_length" => !empty($valid[$repo->id]['minlength']) ? (int) $valid[$repo->id]['minlength'] : NULL,
                            "max_length"=> !empty($valid[$repo->id]['maxlength']) ? (int) $valid[$repo->id]['maxlength'] : NULL
                        ];
                    }else{
                        $validation = ["is_required" => !empty($valid[$repo->id]['required']) ? true : false];
                    }

                    $form[] = [
                        "id" => (String)$repo->id,
                        "type" => $repo->form_type,
                        "text" => $repo->name,
                        "validator" => $validation,
                        "validate_link" => $repo->validate_link, 
                        "value" => $value ?? null
                    ];
                }
                $data['forms'] = $form;

                foreach($cart->orders as $order) {
                    if($order->product_id == $cartbody['product_data']['id']) {
                        // return $order;
                        if ($cartbody['product_data']['period_days'] > 360 && $cartbody['product_data']['period_days'] < 367) {
                            $period = "1 TAHUN";
                        }else if($cartbody['product_data']['period_days'] > 180 && $cartbody['product_data']['period_days'] < 185){
                            $period = "6 BULAN";
                        }else{
                            $period = $cartbody['product_data']['period_days'] ?? 0 . ' HARI';
                        }
                        $data['products'][$keycart]['client_name'] = $order->data[1]['data'] ?? null;
                        $data['products'][$keycart]['period'] = $period;
                    }
                    
                }
                $totalPrice = 0;
                if ($cartbody['inquiry_id'] != null) {
                    $getInquiry = InquiryMv::find($cartbody['inquiry_id']);
                    $totalPrice = $getInquiry->total;
                }else{
                    $totalPrice = $cartbody['total'];
                }
                $data['products'][$keycart]['name'] = $cartbody['product_data']['name'];
                $data['products'][$keycart]['price'] = (int)$totalPrice;
                $data['products'][$keycart]['logo'] = asset("uploads/product/".$cartbody['product_data']['logo']);
                $data['products'][$keycart]['is_pg'] = $cartbody['product_data']['is_pg'];
            }

            $data["payment_methods"] = [
                [
                    'pg_name' => 'VIRTUAL ACCOUNT BCA',
                    'pg_code' => 'BCA',
                    'percent' => null,
                    'flat' => 4995
                ],
                [
                    'pg_name' => 'VIRTUAL ACCOUNT BNI',
                    'pg_code' => 'BNI',
                    'percent' => null,
                    'flat' => 4995
                ],
                [
                    'pg_name' => 'VIRTUAL ACCOUNT BRI',
                    'pg_code' => 'BRI',
                    'percent' => null,
                    'flat' => 4995
                ],
                [
                    'pg_name' => 'VIRTUAL ACCOUNT MANDIRI',
                    'pg_code' => 'MANDIRI',
                    'percent' => null,
                    'flat' => 4995
                ],
                [
                    'pg_name' => 'VIRTUAL ACCOUNT PERMATA',
                    'pg_code' => 'PERMATA',
                    'percent' => null,
                    'flat' => 4995
                ],
                [
                    'pg_name' => 'VISA, MASTER CARD',
                    'pg_code' => 'CREDIT_CARD',
                    'percent' => 3.50,
                    'flat' => 2220
                ],
            ];

            // return $data;

            return response([
                "status" => true,
                "data" => $data
            ], 200);
            
        } catch (\Exception $e) {
            return response([
                "status" => false,
                "message" => $e->getMessage(). " ".$e->getLine()
            ], 500);
        }
    }

    public function total()
    {
        try {
            $komisi = Comission::where('user_id', auth('api')->user()->id)->where('status', 'paid')->sum('comission');
            $transaction = Transaction::where('transaction.user_id', auth('api')->user()->id)->join('cart','transaction.cart_id','=','cart.id')->where('cart.pg_status', 2)->sum('transaction.total');

            return response([
                "status" => true,
                "data" => [
                    "commission" => $komisi,
                    "transaction" => $transaction
                ]
            ], 200);
        } catch (\Exception $e) {
            return response([
                "status" => false,
                "message" => $e->getMessage()
            ], 500);
        }
    }

    public function sendtransaction(Request $request)
    {
        $data = $request->all();
        // return $data[8];
        try {
            $cart = Cart::find($data['cart_id']);
            if(!$cart) {
                return response([
                    "status" => false,
                    "message" => "Cart doesn`t found"
                ], 404);
            }
            switch ($data['pg_code']) {
                case 'BCA':
                    $admin = 4995;
                    break;
                case 'BNI':
                    $admin = 4995;
                    break;
                case 'BRI':
                    $admin = 4995;
                    break;
                case 'MANDIRI':
                    $admin = 4995;
                    break;
                case 'PERMATA':
                    $admin = 4995;
                    break;
                case 'CREDIT_CARD':
                    $admin = (3.50/100*$cart->total) + 2220;
                    break;
                // case 'KREDIVO':
                //     $admin = 0;
                //     break;
                default:
                    $admin = 0;
                    break;
            }
            $res = null;
            DB::beginTransaction();
            foreach($cart->data as $cartbody) {
                $category = FormRepoCategory::where('category_id', $cartbody['product_data']['category_id'])->first();
                $apply = $category->form_json['apply'];
                $form_repo = FormRepo::whereIn('id', $apply)->get();
                $form = [];
                foreach ($form_repo as $key => $repo) {
                    if($request->hasFile($repo->id)){
                        $form[$repo->id] = [
                            "type" => $repo->form_type
                        ];
                        foreach($request->file($repo->id) as $keys => $file){
                            $filename = substr(md5(microtime()),rand(0,26),5)."_".$file->getClientOriginalName();
                            $form[$repo->id]["data"][] = $filename ?? "null";
                            $file->move(public_path()."/uploads/file", $filename);
                        }
                        $form[$repo->id]["name"][] = $repo->name;
                    }else{
                        $form[$repo->id] = [
                            "type" => $repo->form_type,
                            "data" => $data[$repo->id] ?? null,
                            "name" => $repo->name
                        ];
                    }
                }
                $transactions = Transaction::where('user_id',auth('api')->user()->id)->where('cart_id', $cart->id)->get();
                $length = 0;

                // return $transactions;

                if(empty($transactions[$length])) {
                    $orders = Order::where('user_id',auth('api')->user()->id)->where('cart_id', $cart->id)->get();
                    foreach($orders as $order) {
                        if($order->product_id == $cartbody['product_data']['id']) {
                            $transaction = new Transaction;
                            $transaction->order_id = $order->id;
                            $transaction->cart_id = $cart->id;
                            $transaction->user_id = auth('api')->user()->id;
                            $transaction->base_price = $order->base_price ?? $cartbody['product_data']['price'];
                            $transaction->deduct_price = $order->deduct ?? null;
                            $transaction->total = $cartbody['total'];
                            $transaction->policy_start = date('Y-m-d');
                            $transaction->policy_end = date("Y-m-d", strtotime(date("Y-m-d") . "+".$cartbody['product_data']['period_days']." days"));
                            $transaction->trx_data = $form;
                            $transaction->save();

                            $updateOrder = Order::find($order->id);
                            $updateOrder->transaction_id = $transaction->id;
                            $updateOrder->save();
                        }
                    }

                }else {
                    $transaction = $transactions[$length];
                    $order = Order::find($transaction->order_id);
                    if($order->product_id == $cartbody['product_data']['id']) {

                        $transaction->user_id = auth('api')->user()->id;
                        $transaction->base_price = $cartbody['product_data']['price'];
                        $transaction->total = $cartbody['total'];
                        $transaction->cart_id = $cart->id;
                        $transaction->trx_data = $form;
                        $transaction->policy_start = date('Y-m-d');
                        $transaction->policy_end = date("Y-m-d", strtotime(date("Y-m-d") . "+".$cartbody['product_data']['period_days']." days"));
                        $transaction->deduct_price = $order->deduct ?? null;
                        $transaction->save();
                    }

                }


                $length++;
            }
            if (empty($data[8]) && !empty($data['pg_code'])) {
                
                Xendit::setApiKey(env('XENDIT_API_KEY'));
                $param = [
                    'for-user-id' => env("XENDIT_USER_ID"),
                    'external_id' => (string)$cart->id,
                    'payer_email' => $cart->user->email,
                    'description' => "Pembayaran keranjang ".$cart->name. " -".$cart->id,
                    'amount' => $cart->total + $admin,
                    'payment_methods' => [$data['pg_code']],
                    'invoice_duration' => 259200,
                    'failure_redirect_url' => route('xendit.success'),
                    'success_redirect_url' => route('xendit.success')
                ];
                $sendPG = Invoice::create($param);

                $cart->admin_fee = (int) $admin;
                $cart->pg_method = $data['pg_code'];
                $cart->pg_status = 1;
                $cart->pg_callback = $sendPG;
                $cart->pg_link = $sendPG['invoice_url'];
                $cart->save();

                $res['transaction_id'] = $transaction->id;
                $res['pg_link'] = $sendPG['invoice_url'];
                $res['payment_method'] = $data['pg_code'];

                dispatch(new PaymentMailJob($cart));
            }else {
                $voucher = null;
                if (!in_array($cart->user->partner->id, [1166, 1206, 1227])) {
                    $kode = Voucher::where('unique',$request[8])->where("product_id",$cart->data[0]['product_id'])->where("quota",">",0);
                    $value = $kode->first();
                        
                    if(!$value){
                        return response(['message' => 'Voucher Tidak Berlaku', 'status' => false], 422);
                    }
                    $kode->update(["quota" => $value->quota-1]);
                    $voucher = $data[8];
                }
                $cekOrders = Order::where('user_id',auth('api')->user()->id)->where('cart_id', $cart->id)->get();
                foreach ($cekOrders as $key => $value) {
                    $updateTransaction =  Transaction::where('order_id', $value->id)->first();
                    if (!empty($updateTransaction)) {
                        // return 'kntl1';
                        $updateTransaction->voucher = $voucher;
                        $updateTransaction->save();
                    }
                    if ($updateTransaction->order->data[37]['data'] == 0) {
                        $gender = "Male";
                    }else{
                        $gender = "Female";
                    }

                    if ($value->product->flow == "mv" || $value->product->flow == "moto") {
                        dispatch(new FinishOrderJob($cart));
                    }
                    if ($value->product->flow == "nor" ) {
                        dispatch(new PostmikroJob($value->id, $value->data, $value->start_date, $value->product, $gender));
                    }
                    if ($value->product->flow == "web") {
                        $dname = strtoupper($value->product->name);
                        if (Str::contains($dname, ['TRAVEL', 'TRAVELLING', 'TRAVELLIN', 'ZTI'])) {
                            dispatch(new TravelJob($value));
                        }else {
                            dispatch(new PerjalananJob($value));
                        }
                    }
                    dispatch(new ComissionJob($value->id));
                    dispatch(new SpectrumJob($value->id));
                }
                $cart->pg_status = 0;
                $cart->is_checkout = 1;
                $cart->save();
            }
            // return $res;
            $cart->is_checkout = 1;
            $cart->save();
            DB::commit();

            return response([
                "status" => true,
                "message" => 'success',
                "data" => $res
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response([
                "status" => false,
                "message" => $e->getMessage(),
                "line" => $e->getLine()
            ], 500);
        }
    }

    public function callback(Request $request)
    {
        $data = json_decode($request->getContent(),true);

        $cart = Cart::find($data['external_id']);
        $transactions = Transaction::where('cart_id', $cart->id)->get();
        if(empty($cart)) {
            return response([
                "status" => false,
                "message" => "Transaction not found"
            ], 404);
        }

        $datenow = date("Y-m-d");
        
        if ($data['status'] == "PAID") {
            $cart->pg_status = 2;
            $cart->pg_callback = $data;
            // $cart->policy_start = $datenow;
            $cart->save();
            foreach ($transactions as $trx_key => $transaction) {
                $transaction->policy_end = date("Y-m-d", strtotime($datenow ."+".$transaction->order->product->period_days." days" ));
                $transaction->save();


                $point = new Point;
                $point->user_id = $transaction->user_id; 
                $point->transaction_id = $transaction->id; 
                $point->point = $transaction->order->product->point;
                $point->gwp = !empty($transaction->order->total) ? $transaction->order->total : 0;
                
                if ($transaction->order->product->flow == "mv" && isset($transaction->order->additional_data[0]['detail'])) {
                    if ($transaction->order->additional_data[0]['detail'] == "Comprehensive") {
                        $point->eligible = true;
                    }
                }else{
                    $point->eligible = 0;
                }
                $point->save();
    
                if ($transaction->order->product->flow  == "mv" || $transaction->order->product->flow == "moto") {
                    FinishOrderJob::dispatch($cart);
                }
    
                if ($transaction->order->product->flow  == "nor" ) {
                    if ($transaction->order->data[37]['data'] == 0) {
                        $gender = "Male";
                    }else{
                        $gender = "Female";
                    }
                    PostmikroJob::dispatch($transaction->order->id, $transaction->order->data, $transaction->order->start_date, $transaction->order->product, $gender);
                }

                if($transaction->order->product->flow == "web") {
                    $dname = strtoupper($transaction->order->product->name);
                    if (Str::contains($dname, ['TRAVEL', 'TRAVELLING', 'TRAVELLIN'])) {
                        dispatch(new TravelJob($transaction->order));
                    } elseif (Str::contains($dname, ['PA MUDIK', 'MUDIK', "PERJALANAN", "PA PERJALANAN", "PERSONAL", "ACCIDENT", "ASURANSI PERJALANAN", "ASURANSI PA PERJALANAN"])) {
                        dispatch(new PerjalananJob($transaction->order));
                        Log::warning("masuk travel job");
                    }
                }
                
                Log::warning($transaction->order->id);
                dispatch(new ComissionJob($transaction->order->id));
                dispatch(new SpectrumJob($transaction->order->id));
            }

        }else {
            $cart->pg_status = 3;
            $cart->save();
        }
        
        // $transaction->save();
        return 'success';
    }

    public function paymentMethod()
    {
        $params = [
            [
                'pg_name' => 'VIRTUAL ACCOUNT BCA',
                'pg_code' => 'BCA',
                'percent' => null,
                'flat' => 4995
            ],
            [
                'pg_name' => 'VIRTUAL ACCOUNT BNI',
                'pg_code' => 'BNI',
                'percent' => null,
                'flat' => 4995
            ],
            [
                'pg_name' => 'VIRTUAL ACCOUNT BRI',
                'pg_code' => 'BRI',
                'percent' => null,
                'flat' => 4995
            ],
            [
                'pg_name' => 'VIRTUAL ACCOUNT MANDIRI',
                'pg_code' => 'MANDIRI',
                'percent' => null,
                'flat' => 4995
            ],
            [
                'pg_name' => 'VIRTUAL ACCOUNT PERMATA',
                'pg_code' => 'PERMATA',
                'percent' => null,
                'flat' => 4995
            ],
            // [
            //     'pg_name' => 'VISA, MASTER CARD',
            //     'pg_code' => 'CREDIT_CARD',
            //     'percent' => 3.50,
            //     'flat' => 2220
            // ],
            // [
            //     'pg_name' => 'KREDIVO',
            //     'pg_code' => 'KREDIVO',
            //     'percent' => 0,
            //     'flat' => 0
            // ]
            //QR
        //   [
        //       'pg_name' => 'QRIS',
        //       'pg_code' => 'QRIS',
        //       'percent' => 0.70,
        //       'flat' => null
        //   ],
        ];
  
        return response([
            "status" => true,
            "data" => $params
        ], 200);
    
    }

    public function as($order_id)
    {
        // {
        //     "seller_layer": "4",
        //     "reff_layer": "2",
        //     "role": "seller",
        //     "comissions": [
        //         {
        //             "product": 1,
        //             "comission": {
        //                 "type": "percent",
        //                 "value": 10
        //             },
        //             "or_comission": [
        //                 {
        //                     "type": "percent",
        //                     "value": 10
        //                 },
        //                 {
        //                     "type": "percent",
        //                     "value": 10
        //                 }
        //             ]
        //         },
        //         {
        //             "product": 2,
        //             "comission": {
        //                 "type": "percent",
        //                 "value": 10
        //             },
        //             "or_comission": [
        //                 {
        //                     "type": "percent",
        //                     "value": 10
        //                 },
        //                 {
        //                     "type": "percent",
        //                     "value": 10
        //                 }
        //             ]
        //         }
        //     ]
        // }
        $order = Order::find($order_id);
        $agent = Orscheme::select("user_id","upline_id")->where('user_id',$order->user_id)->first();
        $json = $order->product->or_comission;

        if($order->product->flow == "mv" || $order->product->flow == "moto" ){

            $fix_dc = !empty($order->inquiry) ? $order->inquiry->discount : 0;
            $discount = $order->base_price * ($fix_dc / 100);
            $share_com = $order->base_price * ((40 - $fix_dc) / 100);
            $fix_total = $order->base_price * (40/ 100);
    
            $formula =  $order->product->comission;

            $adiraCommission = new AdiraCommission;
            $adiraCommission->transaction_id = $order->transaction_id;
            $adiraCommission->commission = $fix_total;
            $adiraCommission->discount = $discount;
            $adiraCommission->share_commission = $fix_total - $discount;
            $adiraCommission->save();
    
    
            if(isset($agent->upline_id) && $agent->upline_id != null){

                $upline = $agent->upline_id;

                if($order->user->is_sf == 1) {
                    $coms_sell = $share_com * (40/100); 
                }else {
                    if($formula['type'] == 'percent'){
                        $coms_sell = $share_com * $formula['value']; 
                    }else if($formula['type'] == 'decimal'){    
                        $coms_sell = $formula['value'];
                    }
                }

                $comission = new Comission;
                $comission->trx_id = $order->transaction_id;
                $comission->username = $order->user->name;
                $comission->useremail = $order->user->email;
                $comission->user_id = $order->user_id;
                $comission->formula = $formula;
                $comission->base_price = $order->base_price;
                $comission->comission = $coms_sell;
                $comission->status = "unpaid";
                $comission->save();

                $user = User::find($agent->upline_id);
                $coms_or = null;
                if($user->is_um == 1) {
                    $coms_or = $share_com * (6/ 100);
                }else if($user->is_recruiter == 1) {
                    $coms_or = $share_com * (10/ 100);
                }
    
                $orcom = new OrComission;
                $orcom->trx_id = $order->transaction_id;
                $orcom->from_id = $order->user_id;
                $orcom->to_id = $agent->upline_id;
                $orcom->formula = null;
                $orcom->layer = 1;
                $orcom->base_price = $order->base_price;
                $orcom->comission = $coms_or;
                $orcom->status= "unpaid";
                $orcom->save();
            }else {
                if($order->user->is_sf == 1) {
                    $coms_sell = $share_com * (50/100); 
                }else {
                    if($formula['type'] == 'percent'){
                        $coms_sell = $share_com * $formula['value']; 
                    }else if($formula['type'] == 'decimal'){    
                        $coms_sell = $formula['value'];
                    }
                }

                $comission = new Comission;
                $comission->trx_id = $order->transaction_id;
                $comission->username = $order->user->name;
                $comission->useremail = $order->user->email;
                $comission->user_id = $order->user_id;
                $comission->formula = $formula;
                $comission->base_price = $order->base_price;
                $comission->comission = $coms_sell;
                $comission->status = "unpaid";
                $comission->save();
            }
        }else {
            $fix_dc =  0;
            $discount = $order->base_price * ($fix_dc / 100);
            $share_com = $order->base_price * ((40 - $fix_dc) / 100);
            $fix_total = $order->base_price * (40/ 100) ;

            
            $formula =  $order->product->comission;
            if($formula['type'] == 'percent'){
                $coms = $share_com * $formula['value']; 
            }else if($formula['type'] == 'decimal'){    
                $coms = $formula['value'];
            }
            
            $adiraCommission = new AdiraCommission;
            $adiraCommission->transaction_id = $order->transaction_id;
            $adiraCommission->commission = $fix_total;
            $adiraCommission->discount = $discount;
            $adiraCommission->share_commission = $fix_total - $discount;
            $adiraCommission->save();
    
    
            if(isset($agent->upline_id) && $agent->upline_id != null){

                $upline = $agent->upline_id;

                if($order->user->is_sf == 1) {
                    $coms_sell = $share_com * (40/100); 
                }else {
                    if($formula['type'] == 'percent'){
                        $coms_sell = $share_com * $formula['value']; 
                    }else if($formula['type'] == 'decimal'){    
                        $coms_sell = $formula['value'];
                    }
                }

                $comission = new Comission;
                $comission->trx_id = $order->transaction_id;
                $comission->username = $order->user->name;
                $comission->useremail = $order->user->email;
                $comission->user_id = $order->user_id;
                $comission->formula = $formula;
                $comission->base_price = $order->base_price;
                $comission->comission = $coms_sell;
                $comission->status = "unpaid";
                $comission->save();

                $user = User::find($agent->upline_id);
                $coms_or = null;
                if($user->is_um == 1) {
                    $coms_or = $share_com * (6/ 100);
                }else if($user->is_recruiter == 1) {
                    $coms_or = $share_com * (10/ 100);
                }
                
                $orcom = new OrComission;
                $orcom->trx_id = $order->transaction_id;
                $orcom->from_id = $order->user_id;
                $orcom->to_id = $agent->upline_id;
                $orcom->formula = null;
                $orcom->layer = 1;
                $orcom->base_price = $order->base_price;
                $orcom->comission = $coms;
                $orcom->status = "unpaid";
                $orcom->save();
            }else {
                if($order->user->is_sf == 1) {
                    $coms_sell = $share_com * (50/100); 
                }else {
                    if($formula['type'] == 'percent'){
                        $coms_sell = $share_com * $formula['value']; 
                    }else if($formula['type'] == 'decimal'){    
                        $coms_sell = $formula['value'];
                    }
                }

                $comission = new Comission;
                $comission->trx_id = $order->transaction_id;
                $comission->username = $order->user->name;
                $comission->useremail = $order->user->email;
                $comission->user_id = $order->user_id;
                $comission->formula = $formula;
                $comission->base_price = $order->base_price;
                $comission->comission = $coms_sell;
                $comission->status = "unpaid";
                $comission->save();
            }
        }
    }

    public function successPaid(){
        //return route('xendit.success');
        return view('success');
    }
    

}
