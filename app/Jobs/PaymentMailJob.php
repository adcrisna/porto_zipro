<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentMail;

class PaymentMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $cart;
    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $cart = $this->cart;
        $order = Order::where('cart_id', $cart->id)->first();
        $email = $order->data[9]['data'];
        $trans_link = [
            "pg_link" => $cart->pg_link, 
            "email" => $email
        ];
        Mail::to($email)->send(new PaymentMail($trans_link));
    }
}
