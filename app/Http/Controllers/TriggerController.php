<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Jobs\CreatePolicy;

class TriggerController extends Controller
{
    public function createPolicy(Request $request)
    {
        try {
            $order = Order::find($request->order_id);
            if(!isset($order)) {
                return response(['status' => false, 'message' => "Order not found"], 404);
            }
            dispatch(new CreatePolicy($order, $request->policy_no, $request->name));
            
            return response(['status' => true, 'message' => "Dispatched!"], 200);
        } catch (\Exception $e) {
            return response(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
