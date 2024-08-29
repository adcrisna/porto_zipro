<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Orders;
use App\Models\Transaction;
use GuzzleHttp\Client;
use App\Models\Jobs\PaymentJob;
use App\Jobs\CreatePolicy;
use Illuminate\Support\Facades\Storage;
use App\Models\AdiraTransaction;
use Illuminate\Support\Str;
use Auth;
use Log;
use App\Service\TelegramBot;
use Exception;

class PosMikroMail implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $adira_tr_id;
    public $tries = 1;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($adira_tr_id)
    {
        $this->adira_tr_id = $adira_tr_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $trx_id = $this->adira_tr_id;
            $trx_adira = AdiraTransaction::find($trx_id);
            $nomor_polis = $trx_adira->postmikro_response['ResponseData']['Table'][0]['NOMOR POLIS'] ?? $trx_adira->postmikro_response['ResponseData']['Table'][0]['POLICY NUMBER'];
            $nama_tertanggung = $trx_adira->postmikro_response['ResponseData']['Table'][0]['NAMA TERTANGGUNG'] ?? $trx_adira->postmikro_response['ResponseData']['Table'][0]['INSURED NAME'];
            $mudik = false;
            $productname = $trx_adira->postmikro_response['ResponseData']['Table'][0]['PRODUCT INSURANCE'] ?? null;
            $dname = strtoupper($trx_adira->order->product->name);
            if (Str::contains($dname, ['PA MUDIK', 'MUDIK', "PERJALANAN", "PA PERJALANAN", "PERSONAL", "ACCIDENT","ASURANSI PERJALANAN"])) {
                $mudik = true;
            }
            if (!empty($trx_adira->postmikro_response) && $trx_adira->postmikro_response['ResponseCode'] == "200") {
                $send =  [
                    "PARAMNAME" => "POLICYNO",
                    "PARAMVALUE" => $nomor_polis,
                    "REPRINTF" => "1",
                ];
                $path = public_path() . "/uploads/pdf/" . $nomor_polis . ".zip";
                $file_path = fopen($path, 'w');
                // $stream = GuzzleHttp\Psr7\stream_for($file_path);

                $client_token = new Client();
                $result = $client_token->post(
                    env('ADIRA_URL') . 'valencia/v1/authenticate/authtoken',
                    [
                        'verify' => false,
                        'headers' => [
                            'Content-Type' => 'Application/Json',
                            'Authorization' => $mudik ? env('ADIRA_MUDIK_KEY') : env('ADIRA_TOKEN'),
                            'source' => $mudik ? "ZAP" : "GENERAL",
                            'businesssource' => $mudik ? "PA_MUDIK" : "MICRO_INSURANCE"
                        ]
                    ]
                );
                // ADIRA_TOKEN = "Basic U0bm6gGLpubF3WU2ELtdbZFXQvZfiFoHvgHffBZFVaQ=|U0bm6gGLpubF3WU2ELtdbZFXQvZfiFoHvgHffBZFVaQ="
                $author_token = $result->getBody();


                $client = new Client();
                $result = $client->post(
                    env('ADIRA_URL') . 'ValenciaDocument/api/APIFile/DownloadFile',
                    [
                        'verify' => false,
                        'headers' => [
                            "Content-Type" => "application/json",
                            "source" => $mudik ? "ZAP" : "GENERAL",
                            "BusinessSource" => $mudik ? "PA_MUDIK" : "MICRO_INSURANCE",
                            "Authorization" => $mudik ? env('ADIRA_MUDIK_KEY') : env('ADIRA_TOKEN'),
                            "Token" => json_decode($author_token, true)['ResponseData']['Table1'][0]['AuthToken'],
                            "Calculate" => "FALSE"
                        ],
                        'body' => json_encode($send),
                        'sink' => $file_path
                    ]
                );
                // ADIRA_TOKEN = "Basic U0bm6gGLpubF3WU2ELtdbZFXQvZfiFoHvgHffBZFVaQ=|U0bm6gGLpubF3WU2ELtdbZFXQvZfiFoHvgHffBZFVaQ="
                $trx_adira->document_policy = $nomor_polis . ".zip";
                $trx_adira->save();
                dispatch(new CreatePolicy($trx_adira->order, $nomor_polis, $nama_tertanggung));
            }
        } catch (\Exception $th) {
            throw $th;
        }
    }

    public function failed(\Exception $e)
    {
        throw new Exception($e->getMessage(), 1);
    }
}
