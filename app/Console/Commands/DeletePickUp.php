<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Cart;
use App\Models\ProductReferral;
use Carbon\Carbon;
use Log;

class DeletePickUp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:pickup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete Order Ref yang blm isi data penutupan';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::warning("this delete pickup");
        $cartSf = Cart::where('is_checkout',0)->get();
        foreach ($cartSf as $key => $value) {
            $checkOrderRef = ProductReferral::where('cart_id',$value->id)->first();
            if (!empty($checkOrderRef)) {
                Log::warning("data order sf ada untuk didelete ". $value->id);
                $dateNow = Carbon::now();
                $startDate = Carbon::parse(date('Y-m-d H:i:s', strtotime($dateNow)));
                $endDate = Carbon::parse(date('Y-m-d H:i:s', strtotime($value->created_at)));
                $timeDifference  = $endDate->diffInMinutes($startDate);
                $resultDate = $timeDifference / 60;
                Log::warning("total hours ". $resultDate);
                $getOrder = Order::where('cart_id',$value->id)->first();
                if (empty($getOrder)) {
                    if ($resultDate >= 336) {
                        $deleteRef = ProductReferral::where('cart_id',$value->id)->delete();
                        $deleteCartRef = Cart::where('id',$value->id)->delete();
                    }
                }
            }
        }
    }
}
