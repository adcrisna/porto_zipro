<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\Cart;
use App\Models\Comission;
use App\Models\OrComission;
use App\Models\AdiraTransaction;
use App\Models\Product;

class HistoryController extends Controller
{
    public function history(Request $request)
    {
        try {
            $data = Cart::select('id','user_id','name','total','is_checkout','admin_fee','pg_method','pg_status', 'pg_link','cart.created_at')
                    ->where("user_id", auth('api')->user()->id)
                    ->where('is_checkout', true)
                    ->with(['orders' => function($q) {
                        $q->with('inquiry');
                    }])->orderBy("cart.created_at", "DESC")
                    ->get();
            $sum = 0;

            foreach($data as $key => $history) {
                $total = floor($history->total + ($history->cart->admin_fee ?? 0));
                
                if(!empty($history->pg_status) && $history->pg_status == 2){
                    $sum += $total;
                }
            }
            
            foreach ($data as $key => $value) {
                $value['admin_fee'] = (int) $value->admin_fee;
                $data[$key] = $value;
            }
            
            if(count($data) == 0) {
                return response([
                    "status" => false,
                    "message" => "Data not found"
                ], 404);
            }

            return response([
                "status" => true,
                "data" => $data,
                "total" => $sum
            ], 200);
        } catch (\Exception $e) {
            return response([
                "status" => false,
                "message" => $e->getMessage()." ".$e->getLine() 
            ], 500);
        }

    }

    public function comission(Request $request)
    {
        try {
            $status = $request->status;
            $nama = strtoupper($request->name);
            if (!empty($status)) {
                if (!empty($nama)) {
                    $data = Comission::where("comission.user_id", auth('api')->user()->id)->where("comission.status",$status)
                    ->join('transaction','comission.trx_id','=','transaction.id')
                    ->join('orders','transaction.order_id','=','orders.id')
                    ->join('product','orders.product_id','=','product.id')
                    ->join('cart','transaction.cart_id','=','cart.id')
                    ->select(["trx_id","comission.comission as comission","comission.status as status","comission.created_at as created_at", "orders.id as order_id", 'product.name as name_product','cart.name as cart_name'])
                    ->where('UPPER(name_product)','LIKE',"%{$nama}%")
                    ->orderBy("comission.created_at", "DESC");
                }else{
                    $data = Comission::where("comission.user_id", auth('api')->user()->id)->where("comission.status",$status)
                    ->join('transaction','comission.trx_id','=','transaction.id')
                    ->join('orders','transaction.order_id','=','orders.id')
                    ->join('product','orders.product_id','=','product.id')
                    ->join('cart','transaction.cart_id','=','cart.id')
                    ->select(["trx_id","comission.comission as comission","comission.status as status","comission.created_at as created_at", "orders.id as order_id", 'product.name as name_product','cart.name as cart_name'])
                    ->orderBy("comission.created_at", "DESC");
                }
            }else {
                if (!empty($nama)) {
                    $data = Comission::where("comission.user_id", auth('api')->user()->id)
                    ->join('transaction','comission.trx_id','=','transaction.id')
                    ->join('orders','transaction.order_id','=','orders.id')
                    ->join('product','orders.product_id','=','product.id')
                    ->join('cart','transaction.cart_id','=','cart.id')
                    ->select(["trx_id","comission.comission as comission","comission.status as status","comission.created_at as created_at", "orders.id as order_id", 'product.name as name_product','cart.name as cart_name'])
                    ->where('UPPER(name_product)','LIKE',"%{$nama}%")
                    ->orderBy("comission.created_at", "DESC");
                }else{
                    $data = Comission::where("comission.user_id", auth('api')->user()->id)
                    ->join('transaction','comission.trx_id','=','transaction.id')
                    ->join('orders','transaction.order_id','=','orders.id')
                    ->join('product','orders.product_id','=','product.id')
                    ->join('cart','transaction.cart_id','=','cart.id')
                    ->select(["trx_id","comission.comission as comission","comission.status as status","comission.created_at as created_at", "orders.id as order_id", 'product.name as name_product','cart.name as cart_name'])
                    ->orderBy("comission.created_at", "DESC");
                }
            }
            switch ($request->filter) {
                case "today":
                    $data->where("created_at",'>=',date('Y-m-d'));
                    break;
                case "month":
                    $data->whereMonth("created_at",'=',date('m'))->whereYear("created_at",'=',date('Y'));
                    break;
                case "year":
                    $data->whereYear("created_at",'=',date('Y'));
                    break;
                case "all":
                    $data;
                    break;
                default:
                    $data;
            }

            $arr = [];
            $sum = 0;
            $comissions = $data->get();
            $allComission = $data->get();
            // return $allComission;
            if(count($comissions) == 0) {
                return response([
                    "status" => false,
                    "message" => "Data not found"
                ], 404);
            }

            foreach ($allComission as $key => $value) {
                if(!empty($value->transaction->cart)) {
                    $tax = $value->comission * auth('api')->user()->tax / 100;

                    $value['nama_tertanggung'] = $value->transaction->order->data[1]['data'] ?? '-';
                    $value['comission'] = floor((int) $value->comission - $tax);

                    $arr[$key] = $value;
                }
            }

            foreach($comissions as $key => $comission) {
                if(!empty($comission->transaction->cart)) {                    
                    $total = floor($comission->transaction->cart->total + ($comission->transaction->cart->admin_fee ?? 0));
    
                    $tax = $total * auth('api')->user()->tax / 100;
                    $comission->comission = floor($total - $tax);

                    if($comission->transaction->cart['pg_status'] == 2){
                        $sum += $total;
                    }
                }
            }
            
            return response([
                "status" => true,
                "data" => array_values($arr),
                "total" => $sum
            ], 200);
        } catch (\Exception $e) {
            return response([
                "status" => false,
                "message" => $e->getMessage()." ".$e->getLine() 
            ], 500);
        }
    }

    public function orcomission(Request $request)
    {
        try {
            $nama = $request->name;
            if (!empty($request->status)) {
                if (!empty($nama)) {
                    $data = OrComission::where("or_comission.to_id", auth('api')->user()->id)->where("or_comission.status", $request->status)
                    ->join('transaction','or_comission.trx_id','=','transaction.id')
                    ->join('orders','transaction.order_id','=','orders.id')
                    ->join('product','orders.product_id','=','product.id')
                    ->join('cart','transaction.cart_id','=','cart.id')
                    ->select(["trx_id","or_comission.comission as comission","or_comission.status as status","or_comission.created_at as created_at", "orders.id as order_id", 'product.name as name_product','cart.name as cart_name'])
                    ->where('cart.name','LIKE',"%{$nama}%")
                    ->orderBy("or_comission.created_at", "DESC");
                } else {
                $data = OrComission::where("or_comission.to_id", auth('api')->user()->id)->where("or_comission.status", $request->status)
                    ->join('transaction','or_comission.trx_id','=','transaction.id')
                    ->join('orders','transaction.order_id','=','orders.id')
                    ->join('product','orders.product_id','=','product.id')
                    ->join('cart','transaction.cart_id','=','cart.id')
                    ->select(["trx_id","or_comission.comission as comission","or_comission.status as status","or_comission.created_at as created_at", "orders.id as order_id", 'product.name as name_product','cart.name as cart_name'])
                    ->orderBy("or_comission.created_at", "DESC");
                }
            }else {
                if (!empty($nama)) {
                    $data = OrComission::where("or_comission.to_id", auth('api')->user()->id)
                    ->join('transaction','or_comission.trx_id','=','transaction.id')
                    ->join('orders','transaction.order_id','=','orders.id')
                    ->join('product','orders.product_id','=','product.id')
                    ->join('cart','transaction.cart_id','=','cart.id')
                    ->select(["trx_id","or_comission.comission as comission","or_comission.status as status","or_comission.created_at as created_at", "orders.id as order_id", 'product.name as name_product','cart.name as cart_name'])
                    ->where('cart.name','LIKE',"%{$nama}%")
                    ->orderBy("or_comission.created_at", "DESC");
                } else {
                $data = OrComission::where("or_comission.to_id", auth('api')->user()->id)
                    ->join('transaction','or_comission.trx_id','=','transaction.id')
                    ->join('orders','transaction.order_id','=','orders.id')
                    ->join('product','orders.product_id','=','product.id')
                    ->join('cart','transaction.cart_id','=','cart.id')
                    ->select(["trx_id","or_comission.comission as comission","or_comission.status as status","or_comission.created_at as created_at", "orders.id as order_id", 'product.name as name_product','cart.name as cart_name'])
                    ->orderBy("or_comission.created_at", "DESC");
                }
            }
            
            switch ($request->filter) {
                case "today":
                    $data->where("created_at",'>=',date('Y-m-d'));
                    break;
                case "month":
                    $data->whereMonth("created_at",'=',date('m'))->whereYear("created_at",'=',date('Y'));
                    break;
                case "year":
                    $data->whereYear("created_at",'=',date('Y'));
                    break;
                case "all":
                    $data;
                    break;
                default:
                    $data;
            }
            $arr = [];
            $orcomissions = $data->get();
            $allOrComission = $data->get();
            if(count($orcomissions) == 0) {
                return response([
                    "status" => false,
                    "message" => "Data not found"
                ], 404);
            }
            foreach ($allOrComission as $key1 => $value) {
                    $getOrder = Order::find($value->order_id);
                    $getName = $getOrder->data[1]['data'] ?? '-';
                    $value['nama_tertanggung'] = $getName;
                    $pajak = $value->comission * auth('api')->user()->tax / 100;
                    $value['comission'] = floor((int) $value->comission - $pajak);

                    $arr[$key1] = $value;
            }
            
            foreach($orcomissions as $key => $orcomission) {
                $total = $orcomission->comission;
                $pajak = $total * auth('api')->user()->tax / 100;
                $orcomissions[$key]->comission = floor($total - $pajak);
            }

            return response([
                'status' => true,
                'data' => $arr,
                'total'=>(double)$orcomissions->sum("comission"),
            ], 200);
        }catch(Exception $e) {
            return response([
                "status" => false,
                "message" => $e->getMessage()." ".$e->getLine() 
            ], 500);
        }
    }

    public function duesoon(Request $request)
    {
        try {
            $nama = $request->name;
            $arr = [];
            if (!empty($nama)) {
                $data = Cart::where("cart.user_id", auth("api")->user()->id)
                    ->join('transaction','transaction.cart_id','=','cart.id')
                    ->join('orders','orders.cart_id','=','cart.id')
                    ->join('product','orders.product_id','=','product.id')
                    ->select(['cart.id as cart_id','cart.name as cart_name','cart.user_id','transaction.id as transaction_id','transaction.policy_end as policy_end','orders.id as order_id','product.name as product_name','transaction.renew as renew','product.id as product_id','product.flow as product_flow','product.category_id as product_categori_id'])
                    ->whereIn("cart.pg_status", [0, 2])
                    ->where('cart.name','LIKE',"%{$nama}%")
                    ->orderBy("transaction.policy_end", "DESC");
            } else {
                $data = Cart::where("cart.user_id", auth("api")->user()->id)
                    ->join('transaction','transaction.cart_id','=','cart.id')
                    ->join('orders','orders.cart_id','=','cart.id')
                    ->join('product','orders.product_id','=','product.id')
                    ->select('cart.id as cart_id','cart.name as cart_name','cart.user_id','transaction.id as transaction_id','transaction.policy_end as policy_end','orders.id as order_id','product.name as product_name','transaction.renew as renew','product.id as product_id','product.flow as product_flow','product.category_id as product_categori_id')
                    ->whereIn("cart.pg_status", [0, 2])
                    ->orderBy("transaction.policy_end", "DESC");
            }
            
            switch ($request->filter) {
                case "today":
                    $data->where("policy_end",'>=',date('Y-m-d'))->where("policy_end",'<=',date('Y-m-d'));
                    break;
                case "month":
                    $data->whereMonth("policy_end",'=',date('m'))->whereYear("policy_end",'=',date('Y'));
                    break;
                case "year":
                    $data->whereYear("policy_end",'=',date('Y'));
                    break;
                case "all":
                    $data;
                    break;
                default:
                    $data;
            }
            
            $dataDue = $data->get();
            // return $dataDue;
            foreach ($dataDue as $key => $value) {
                $getOrder = Order::find($value->order_id);
                $getProduct = Product::find($value->product_id);
                $value['logo'] = env('APP_URL').'/uploads/product/'.$getProduct->logo;
                $value['nama_tertanggung'] = $getOrder->data["1"]["data"] ?? '-';
                $arr[$key] = $value;
            }
            
            if(count($arr) == 0) {
                return response([
                    "status" => false,
                    "message" => "Data not found"
                ], 404);
            }

            return response([
                'status' => true,
                'data' => $arr,
            ], 200);
        } catch (\Exception $e) {
            return response([
                "status" => false,
                "message" => $e->getMessage()." ".$e->getLine() 
            ], 500);
        }

    }
}
