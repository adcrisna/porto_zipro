<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Order;
use App\Models\InquiryMv;
use App\Models\Renewal;
use App\Models\AdiraTransaction;
use App\Models\Chassist;
use App\Jobs\CancelMV;
use Validator;
use Str;

class CartController extends Controller
{
    public function list()
    {
        $carts = Cart::select(['id', 'name', 'total', 'data', 'is_checkout', 'is_ref'])
            ->where('user_id', auth('api')->user()->id)
            ->whereNull('pg_status')
            ->latest()
            ->get();

        if (count($carts) == 0) {
            return response([
                "status" => false,
                "message" => "Data not found"
            ], 404);
        }

        foreach ($carts as $key => $cart) {
            $carts[$key]['status_order'] = "Waiting";
            $carts[$key]['alr_penutupan'] = false;
            $carts[$key]['renewal'] = false;
            $carts[$key]['is_ref'] = $carts[$key]['is_ref'] == 0 ? false : true;
            $product = [];
            $cartData = $cart->data ?? [];
            $orders = Order::where('cart_id', $cart->id)->get()->toArray();
            foreach ($cartData as $keyBody => $cartbody) {
                unset($carts[$key]['data']);
                if (!empty($cartbody['inquiry_id'])) {
                    $getInquiry = InquiryMv::find($cartbody['inquiry_id']);
                    if (!empty($getInquiry) && !empty($getInquiry->data['policy_no'])) {
                        $re_data = Renewal::where('policy_no', $getInquiry->data['policy_no'])->first();
                        // return $getInquiry->data['policy_no'];
                        $body = $re_data->data;
                        $get_date = ($body[22] - (25567 + 2)) * 86400 * 1000;
                        $excelDate = $get_date / 1000;

                        $carts[$key]['renewal'] = true;
                        $carts[$key]['data_renewal'] = [
                            "insurer_name" => $body[20],
                            "start_date" => date("Y-m-d", $excelDate),
                            "vehicle_plat" => $body[39],
                            "vehicle_machine" => $body[41],
                            "policy_no" => $getInquiry->data['policy_no']
                        ];
                    }
                }
                if (!empty($cartbody['product_data'])) {
                    $product[] = $cartbody['product_data']['name'];
                }
            }
            $get_status = array_column($orders, 'status');
            if (in_array(0, $get_status)) {
                $carts[$key]['status_order'] = "Waiting";
            } elseif (in_array(1, $get_status)) {
                $carts[$key]['status_order'] = "Rejected";
            } elseif (in_array(2, $get_status)) {
                $carts[$key]['status_order'] = "Approved";
            }

            // return $orders;
            if (count($orders) > 0) {
                foreach ($orders as $kOrder => $order) {
                    $product_flow = Product::find($order['product_id'])->flow;
                    if ($order['data'] == null) {
                        $carts[$key]['alr_penutupan'] = false;
                    }elseif ($order['data'] !== null) {
                        $carts[$key]['alr_penutupan'] = true;
                    } elseif ($product_flow == "web") {
                        $carts[$key]['alr_penutupan'] = true;
                        $carts[$key]['status_order'] = "Approved";
                    }
                }
            }



            $carts[$key]["products"] = $product;
        }
        return response([
            "status" => true,
            "data" => $carts
        ], 200);
    }

    public function detail($id)
    {
        $carts = Cart::select(['id', 'name', 'total', 'data', 'pdf_link', 'is_offering','created_at'])->find($id);
        if (!$carts) {
            return response([
                "status" => false,
                "message" => "Cart not found"
            ], 404);
        }
        
        $orders = Order::select(['id', 'data', 'base_price', 'total', 'status','product_id'])->where('cart_id', $id)->get();
        $carts['orders'] = $orders;
        $data = $carts->data;
        $new_body = [];
        $status = "Waiting";
        // return $carts->created_at;
        if (count($orders) > 0) {
            foreach ($orders as $key => $order) {
                // return 'here';
                $adira_trx = AdiraTransaction::where('order_id', $order->id)->first();
                $cekOrder = Order::find($order->id);
                $status = $adira_trx->adira_status ?? "";

                if ($status == "fail" || $status == "") {
                    if ($cekOrder->product->flow == 'nor') {
                        $status = "Approved";
                    }else{
                        $status = "Waiting";
                    }
                }else {
                    $status = $adira_trx->adira_status;
                }
                $is_renewal = false;
                if (!empty($order->inquiry) && !empty($order->inquiry->data['policy_no'])) {
                        $dataRenewal = Renewal::where('policy_no', $order->inquiry->data['policy_no'])->first();
                        if (!empty($dataRenewal)) {
                            $is_renewal = true;
                        }
                }
                $data_pemesanan = [];
                if($order->product->flow == "web") {
                    $is_webview = 1;
                    $data_pemesanan['is_webview'] = $is_webview;
                    $dname = strtoupper($order->product->name);
                    if(Str::contains($dname, ['TRAVEL', 'TRAVELLING', 'TRAVELLIN', 'ZTI'])) {
                        $data_pemesanan['webview'] = [
                            "edit" => route('travel.editoffering', $order->id),
                            "detail" => route('travel.editoffering', $order->id).'?type=readonly',
                            "penutupan" => route('travel.penutupan', $order->id)
                        ];
                    }else{
                        $data_pemesanan = null;
                    }
                }else{
                    $data_pemesanan = null;
                }
                // return @$data[2]['inquiry_id'];
                $new_body[$key]['inquiry_id'] = @$data[$key]['inquiry_id'];
                $new_body[$key]['product_id'] = @$data[$key]['product_id'];
                $new_body[$key]['order_id'] = @$order->id;
                $new_body[$key]['nama_tertanggung'] = @$cekOrder->data[1]['data'] ?? null;
                $new_body[$key]['created_at'] = date('Y-m-d H:i:s', strtotime(@$cekOrder->updated_at));
                $new_body[$key]['vehicle_type'] = @$order->inquiry->data['modelstr'];
                $new_body[$key]['jenis_asuransi'] = @$order->inquiry->item[0];
                $new_body[$key]['total_premi'] = @$order->total;
                $new_body[$key]['order_status'] = $status;
                $new_body[$key]["note"] = $adira_trx?->polling_response['data']['policy']['backOrderRemark'] ?? null;
                $new_body[$key]['product_data'] = @$data[$key]['product_data'];
                $new_body[$key]['total'] = @$order->total ? @$order->total : @$order->inquiry->total;
                $new_body[$key]['is_renewal'] = $is_renewal;
                $new_body[$key]['data_pemesanan'] = $data_pemesanan;
                // return 'sini';
            }
        } else {
            // return "sini";
            foreach ($data as $keybody => $data_body) {
                $getInq = InquiryMv::find($data_body['inquiry_id']);
                $is_renewal = false;
                if (!empty($getInq)) {
                    $createdAt = date('Y-m-d H:i:s', strtotime(@$getInq['created_at']));
                    $total = @$getInq->total;
                    if (isset($getInq->data['policy_type'])) {
                        if (@$getInq->data['policy_type'] == 'hard') {
                            $feePolicyType = 50000;
                        } else {
                            $feePolicyType = 10000;
                        }
                    }else {
                        $feePolicyType = 0;
                    }

                    if (!empty($getInq->data['policy_no'])) {
                            $dataRenewal = Renewal::where('policy_no', $getInq->data['policy_no'])->first();
                            if (!empty($dataRenewal)) {
                                $is_renewal = true;
                            }
                    }
                }else{
                    $createdAt = date('Y-m-d H:i:s', strtotime($carts->created_at));
                    $total = @$data_body['total'];
                    $feePolicyType = 0;
                }

                
                // return $getInq;
                // return $getInq['item'][0];
                $new_body[$keybody]['inquiry_id'] = $data_body['inquiry_id'];
                $new_body[$keybody]['product_id'] = $data_body['product_id'];
                $new_body[$keybody]['order_id'] = null;
                $new_body[$keybody]['created_at'] = $createdAt;
                $new_body[$keybody]['vehicle_type'] = @$getInq['data']['modelstr'];
                $new_body[$keybody]['jenis_asuransi'] = @$getInq['item'][0];
                $new_body[$keybody]['total_premi'] = @$getInq['total'];
                $new_body[$keybody]['order_status'] = $status;
                $new_body[$keybody]["note"] = null;
                $new_body[$keybody]['product_data'] = $data_body['product_data'];
                $new_body[$keybody]['total'] = $total;
                $new_body[$keybody]['is_renewal'] = $is_renewal;
                $new_body[$keybody]['data_pemesanan'] = null;
                // return "sini";
            }
            // return $keybody;
        }
        $carts->data = $new_body;
        return response([
            "status" => true,
            "data" => $carts
        ], 200);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), ["name" => "required"]);
        if ($validator->fails()) {
            return response([
                "status" => false, "message" => "Validator errors", "data" => $validator->errors()
            ], 422);
        }
        try {
            $cart = new Cart;
            $cart->user_id = auth('api')->user()->id;
            $cart->name = $validator->validated()['name'];
            $cart->total = 0;
            $cart->save();
        } catch (\Exception $e) {
            return response([
                "status" => false, "message" => $e->getMessage()
            ], 500);
        }

        return response([
            "status" => true, "data" => $cart
        ], 200);
    }

    static public function insert($data, $cart = [], $user_id = null)
    {
        $user_id = !empty($user_id) ? $user_id : auth('api')->user()->id;
        $cartbody = [];
        try {
            if (!empty($cart['id'])) {
                $getCart = Cart::find($cart['id']);
                if (!empty($getCart->data)) {
                    foreach ($getCart->data as $key => $value) {
                        $getInqui = InquiryMv::find($value['inquiry_id']);
                        
                        if (!empty($getInqui) && !empty($getInqui->data['policy_no'])) {
                            $getRenew = Renewal::where('policy_no',$getInqui->data['policy_no'])->first();
                            if (!empty($getRenew)) {
                                return response([
                                    "status" => false,
                                    "message" => "Product Ini Tidak Dapat Dimasukan Ke Keranjang Renewal"
                                ], 500);
                            }
                        }
                        
                        $getProductCart = Product::find($value['product_id']);
                        if (!empty($getProductCart) && !empty($getProductCart->flow)) {
                                if ($getProductCart->flow == 'web') {
                                    return response([
                                        "status" => false,
                                        "message" => "Produk Ini Harus Menggunakan Keranjang Baru"
                                    ], 500);
                                }
                        }
                        $getProductData = Product::find($data->product_id);
                        if (!empty($getProductCart) && !empty($getProductCart->flow)) {
                            if ($getProductCart->flow == 'mv' && $getProductData->flow == 'moto') {
                                    return response([
                                        "status" => false,
                                        "message" => "Produk MV yang sama tidak bisa dalam 1 keranjang"
                                    ], 500);
                                }
                            if ($getProductCart->flow == 'mv' && $getProductData->flow == 'mv') {
                                    return response([
                                        "status" => false,
                                        "message" => "Produk MV yang sama tidak bisa dalam 1 keranjang"
                                    ], 500);
                                }
                            if ($getProductCart->flow == 'moto' && $getProductData->flow == 'mv') {
                                    return response([
                                        "status" => false,
                                        "message" => "Produk MV yang sama tidak bisa dalam 1 keranjang"
                                    ], 500);
                                }
                            if ($getProductCart->flow == 'moto' && $getProductData->flow == 'moto') {
                                    return response([
                                        "status" => false,
                                        "message" => "Produk MV yang sama tidak bisa dalam 1 keranjang"
                                    ], 500);
                                }
                        }
                    }
                }

                $getProduct = Product::find($data->product_id);
                if ($getCart->is_ref != 1) {
                    if ($getProduct->adira_product_id == 'ZTI' || $getProduct->flow == 'web') {
                        return response([
                            "status" => false,
                            "message" => "Produk Ini Harus Menggunakan Keranjang Baru"
                        ], 500);
                    }
                }
                if ($getCart->is_ref == 1) {
                    if ($getProduct->is_pg == 0) {
                        return response([
                            "status" => false,
                            "message" => "Produk ini tidak bisa menggunakan Keranjang Referral"
                        ], 500);
                    }
                }
                if ($getProduct->is_pg == 0) {
                    return response([
                        "status" => false,
                        "message" => "Produk Ini Harus Menggunakan Keranjang Baru"
                    ], 500);
                }
                
                if (empty($getCart->data)) {
                    $productDetail = Product::select([
                        'id', 'category_id', 'adira_product_id', 'binder_id', 'name',
                        'description', 'price', 'logo', 'comission', 'or_comission', 'validation', 'period_days',
                        'display_name', 'wording', 'point', 'is_pg', 'flow'
                    ])
                        ->where('id', $data->product_id)->first();

                    $cartbody[0]["inquiry_id"] = $data->id ?? null;
                    $cartbody[0]["product_id"] = $data->product_id;
                    $cartbody[0]["total"] = $data->total;
                    $cartbody[0]["product_data"] = $productDetail;
                } else {
                    $newkey = 0;
                    foreach ($getCart->data as $keyCart => $valueCart) {
                        $productDetail = Product::select([
                            'id', 'category_id', 'adira_product_id', 'binder_id', 'name',
                            'description', 'price', 'logo', 'comission', 'or_comission', 'validation', 'period_days',
                            'display_name', 'wording', 'point', 'is_pg', 'flow'
                        ])->where('id', $valueCart["product_id"])->first();

                        $cartbody[$keyCart]["inquiry_id"] = $valueCart["inquiry_id"] ?? null;
                        $cartbody[$keyCart]["product_id"] = $valueCart["product_id"];
                        $cartbody[$keyCart]["total"] = $valueCart["total"];
                        $cartbody[$keyCart]["product_data"] = $productDetail;
                        $newkey = $keyCart + 1;
                    }
                    $productDetail = Product::select([
                        'id', 'category_id', 'adira_product_id', 'binder_id', 'name',
                        'description', 'price', 'logo', 'comission', 'or_comission', 'validation', 'period_days',
                        'display_name', 'wording', 'point', 'is_pg', 'flow'
                    ])->where('id', $data->product_id)->first();

                    $cartbody[$newkey]["inquiry_id"] = $data->id ?? null;
                    $cartbody[$newkey]["product_id"] = $data->product_id;
                    $cartbody[$newkey]["total"] = $data->total;
                    $cartbody[$newkey]["product_data"] = $productDetail;
                }

                $total = array_sum(array_column($cartbody, 'total'));

                $getCart->data = $cartbody;
                $getCart->total = $total;
                $getCart->is_ref = 0;
                $getCart->save();
            } else {
                if (empty($cart['name'])) {
                    throw new \Exception("Nama Keranjang tidak boleh kosong.");
                }
                $productDetail = Product::select([
                    'id', 'category_id', 'adira_product_id', 'binder_id', 'name',
                    'description', 'price', 'logo', 'comission', 'or_comission', 'validation', 'period_days',
                    'display_name', 'wording', 'point', 'is_pg', 'flow'
                ])
                    ->where('id', $data->product_id)->first();
                
                $cartbody[0]["inquiry_id"] = $data->id ?? null;
                $cartbody[0]["product_id"] = $data->product_id;
                $cartbody[0]["total"] = $data->total;
                $cartbody[0]["product_data"] = $productDetail;

                $getCart = new Cart;
                $getCart->total = $data->total;
                $getCart->name = $cart['name'];
                $getCart->data = $cartbody;
                $getCart->user_id = $user_id;
                $getCart->is_ref = 0;
                $getCart->save();

            }

            // return $cartbody;
        } catch (\Exception $e) {
            return response([
                "status" => false,
                "message" => $e->getMessage() . ' ' . $e->getLine()
            ], 500);
        }

        return response([
            "status" => true,
            "cart" => [
                "id" => $getCart->id,
                "name" => $getCart->name
            ]
        ], 200);
    }

    public function offering(Request $request, $id)
    {
        try {
            $cart = Cart::find($id);
            $validator = Validator::make($request->all(), [
                'offering_name' => 'required',
                'offering_email' => 'required',
                'offering_telp' => 'required'
            ]);

            if ($validator->fails()) {
                return response([
                    "status" => false, "message" => "Validator errors", "data" => $validator->errors()
                ], 422);
            }

            $cart->is_offering = true;
            $cart->offering_name = $request->offering_name;
            $cart->offering_email = $request->offering_email;
            $cart->offering_telp = $request->offering_telp;
            $cart->save();

            return response([
                "status" => true,
                "message" => "Offering sended."
            ], 200);
        } catch (\Exception $e) {
            return response([
                "status" => false,
                "message" => $e->getMessage()
            ], 500);
        }
    }

    public function remove(Request $request)
    {
        try {
            $carts = Cart::find($request->cart_id);
            if (!$carts) {
                return response([
                    "status" => false,
                    "message" => "Cart not found"
                ], 404);
            }

            $cartbody = $carts->data;
            $newBody = [];
            $keys = $request->index;
            
            $orders = Order::select(['id', 'data', 'base_price', 'total', 'status'])->where('cart_id', $request->cart_id)->get();
            foreach ($keys as $key) {
                if (!empty($orders[$key])) {
                   $data = Order::find($orders[$key]['id']);
                    if (($data->product->flow == "mv" || $data->product->flow == "moto") && !empty($data->adira_trx['adira_response']['data']['requestNumber']) ) {
                        if(!empty($data->adira_trx) && $data->adira_trx['adira_status'] != null && $data->adira_trx['adira_status'] != 'Finished') {
                            dispatch(new CancelMV($data));

                            $rangka = $data->data['51']['data'] ?? null;
                            $chassist = Chassist::where('chassis', $rangka)->first();
                            $noPolicy = $data->additional_data[0]['policy_no'] ?? null;
                            $renew = Renewal::where('policy_no', $noPolicy)->first();
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
                        }
                    }
                    $data->delete();
                }
                unset($cartbody[$key]);
            }
            foreach ($cartbody as $keyCart => $cartbody) {
                $newBody[] = $cartbody;
            }

            $carts->data = $newBody;
            $carts->total = array_sum(array_column($newBody, 'total'));
            $carts->save();

            return response([
                'status' => true,
                'data' => $carts
            ], 200);
        } catch (\Exception $e) {
            return response([
                "status" => false,
                "message" => $e->getMessage()
            ], 500);
        }
    }

    function deleteCart($id)
    {
        try {
            $cart = Cart::find($id);
            $orders = Order::select(['id', 'data', 'base_price', 'total', 'status'])->where('cart_id', $cart->id)->get();
            foreach ($orders as $key) {
                   $data = Order::find($key->id);
                    if (($data->product->flow == "mv" || $data->product->flow == "moto") && !empty($data->adira_trx['adira_response']['data']['requestNumber']) ) {
                        if(!empty($data->adira_trx) && $data->adira_trx['adira_status'] != null && $data->adira_trx['adira_status'] != 'Finished') {
                            dispatch(new CancelMV($data));

                            $rangka = $data->data['51']['data'] ?? null;
                            $chassist = Chassist::where('chassis', $rangka)->first();
                            $noPolicy = $data->additional_data[0]['policy_no'] ?? null;
                            $renew = Renewal::where('policy_no', $noPolicy)->first();
                            if(!empty($renew)) {
                                $renew->new_order_id = null;
                                $renew->save();
                            }
                            if(!empty($chassist)) {
                                $chassist->delete();
                            }
                            $updateAdira = AdiraTransaction::where('order_id',$data->id)->orderBy('id','DESC')->first();
                            $updateAdira->adira_status = "Canceled";
                            $updateAdira->status = "Stop";
                            $updateAdira->save();
                        }
                    }
                    $data->delete();
                
            }
            $cart->delete();

            return response([
                'status' => true,
                'message' => 'cart successfuly deleted'
            ], 200);
        } catch (\Exception $e) {
            return response([
                "status" => false,
                "message" => $e->getMessage()
            ], 500);
        }
    }
}
