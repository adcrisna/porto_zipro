<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Cart;
use App\Models\ProductReferral;
use Carbon\Carbon;
use Log;

class OrderRef extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:ref';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback Order Referensi';

    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $cartSf = Cart::where('is_checkout',0)->where('user_id','!=', null)->get();
        foreach ($cartSf as $key => $value) {
            $checkOrderRef = ProductReferral::where('cart_id',$value->id)->first();
            if (!empty($checkOrderRef)) {
                Log::warning("data order sf ada ". $value->id);
                $dateNow = Carbon::now();
                $startDate = Carbon::parse(date('Y-m-d H:i:s', strtotime($dateNow)));
                $endDate = Carbon::parse(date('Y-m-d H:i:s', strtotime($value->updated_at)));
                $timeDifference  = $endDate->diffInMinutes($startDate);
                $resultDate = $timeDifference / 60;
                Log::warning("total hours ". $resultDate);
                $getOrder = Order::where('cart_id',$value->id)->first();
                if (empty($getOrder)) {
                    if ($resultDate >= 72) {
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
        Log::warning("done rollback order sf");
    }
}
