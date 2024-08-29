<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\OfferingMail;
use App\Models\Cart;
use GuzzleHttp\Client as Guzzle;
use Log, PDF, Str;
use App\Service\TelegramBot;
use Exception;

class OfferingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;
    protected $carts;
    protected $dataCart;
    protected $inquiry;
    public $tries = 1;
    public $timeout = 0;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($carts, $dataCart, $inquiry)
    {
        $this->carts = $carts;
        $this->dataCart = $dataCart;
        $this->inquiry = $inquiry;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $carts = $this->carts;
            $dataCart = $this->dataCart;
            $inquiry = $this->inquiry;
            $product = $inquiry->product;
            $email =  $carts->offering_email;

            $acc = [];
            for ($i = 1; $i <= 5; $i++) {
                $acc[] = !empty($inquiry->data['aksesoris']) ? $inquiry->data['aksesoris'][$i]['harga'] * (int) $inquiry->data['aksesoris'][$i]['qty'] : 0;
            }
            $path = asset('assets/zipro.png');
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            $total = array_sum($acc);
            // $namePDF = "offering_".md5($order->id).".pdf";
            $namePDF = "surat_penawaran_asuransi_" . md5($inquiry->id) . ".pdf";
            $savepath = storage_path('uploads/pdf/' . $namePDF);

            $updateCart = Cart::find($carts->id);

            $arrayData = $updateCart->pdf_link;
            $arrayData[$inquiry->id] = env('APP_URL') . "/uploads/pdf/" . $namePDF;

            $updateCart->pdf_link = $arrayData;
            $updateCart->save();

            if (file_exists($savepath)) {
                unlink($savepath);
            }
            $newPerluasan = $this->perluasan();
            $pdf = PDF::loadView('pdf.offer', compact('dataCart', 'carts', 'inquiry', 'total', 'base64', 'newPerluasan'))->setPaper('a4', 'potrait');
            $pdf->save(storage_path('uploads/pdf/' . $namePDF));
            if ($pdf) {
                Mail::to($email)->send(new OfferingMail($product, $namePDF));
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function perluasan()
    {
        $perluasan["TPL"] = "Tanggung Jawab Hukum terhadap Pihak Ketiga (Third Party Liability)";
        $perluasan["TFSHL"] = "Angin Topan, Banjir, Badai, Hujan Es & Tanah Longsor (Typhoon, Storm, Flood, Hail, Landslide)";
        $perluasan["EQVET"] = "Gempa Bumi, Tsunami & Letusan Gunung Berapi (Earthquake, Tsunami & Volcanic Eruption)";
        $perluasan["SRCC"] = "Huru-hara & Kerusuhan (Strike, Riot, & Civil Commotion)";
        $perluasan["TS"] = "Terorisme & Sabotase (Terorisme & Sabotage)";
        $perluasan["PA Pengemudi"] = "Kecelakaan Diri untuk Pengemudi (Personal Accident for Driver)";
        $perluasan["PA Penumpang (4 orang)"] = "Kecelakaan Diri untuk Penumpang (Personal Accident for Passenger)";
        return $perluasan;
    }
}
