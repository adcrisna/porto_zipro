<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class PolicyMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pg;
    public $data;
    public $pdf;
    public $policy_no;
    public $name;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data,$pg = 0,$pdf = [], $policy_no, $name)
    {
        $this->pg = $pg;
        $this->data = $data;
        $this->pdf = $pdf;
        $this->policy_no = $policy_no;
        $this->name = $name;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            from: new Address(env('MAIL_FROM_ADDRESS'),env('MAIL_FROM_NAME')),
            subject: 'Polis asuransi telah terbit',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'mails.polis',
            with: [
                "name" => $this->name,
                "no_polis" => $this->policy_no,
                "product" => $this->data['product'],
                "pg" => $this->pg
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        $files = [];
        foreach($this->pdf as $file) {
            $files[] = public_path('uploads/pdf/').$file;
        }

        return $files;
    }
}
