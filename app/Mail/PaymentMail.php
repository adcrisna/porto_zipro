<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($trans_link)
    {
        $this->trans_link = $trans_link;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $trans_link = $this->trans_link;
        return $this->from(env('MAIL_FROM_ADDRESS'),env('MAIL_FROM_NAME'))
        ->view('mails.payment')
        ->subject('Lakukan pembayaran untuk pemesanan Anda')
        ->with(["trans_link" => $trans_link]);
    }
}
