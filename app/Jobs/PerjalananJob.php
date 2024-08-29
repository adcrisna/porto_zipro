<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use App\Models\AdiraTransaction;
use App\Jobs\PosMikroMail;
use GuzzleHttp\Client;
use App\Service\TelegramBot;
use Exception;

class PerjalananJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;
    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $order = $this->order;
            $client = new Client();
            $result = $client->post(
                env('ADIRA_URL') . '/valencia/v1/authenticate/authtoken',
                [
                    'verify' => false,
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Source' => 'ZAP',
                        'Businesssource' => 'PA_MUDIK',
                        'Authorization' => env('ADIRA_MUDIK_KEY')
                    ]
                ]
            );

            // ADIRA_TOKEN = "Basic U0bm6gGLpubF3WU2ELtdbZFXQvZfiFoHvgHffBZFVaQ=|U0bm6gGLpubF3WU2ELtdbZFXQvZfiFoHvgHffBZFVaQ="
            $author_token = $result->getBody();
            $get_order_id = Order::latest('created_at')->first()->id + 1;

            $dob_pasangan = null;
            $dob_firstchild = null;
            $dob_secondchild = null;
            if ($order->additional_data[0]['namaPasangan'] != null) {
                $dob_pasangan = date('m/d/Y', strtotime($order->additional_data[0]['tanggalLahirPasangan']));
            }
            if ($order->additional_data[0]['namaPasangan'] != null) {
                $dob_firstchild = date('m/d/Y', strtotime($order->additional_data[0]['tanggalLahirAnak1']));
            }
            if ($order->additional_data[0]['namaPasangan'] != null) {
                $dob_secondchild = date('m/d/Y', strtotime($order->additional_data[0]['tanggalLahirAnak2']));
            }

            $data['InsurancePeriod'] = "14 Day";
            $data['Asal'] = $order->additional_data[0]['asal'];
            $data['Tujuan'] = $order->additional_data[0]['tujuan'];
            $data['NamaPasangan'] = $order->additional_data[0]['namaPasangan'];
            $data['PassportPasangan'] = $order->additional_data[0]['passportPasangan'];
            $data['DOB_Pasangan'] = $dob_pasangan;
            $data['NamaAnak1'] = $order->additional_data[0]['namaAnak1'];
            $data['PassportAnak1'] = $order->additional_data[0]['passportAnak1'];
            $data['DOB_Anak1'] = $dob_firstchild;
            $data['NamaAnak2'] = $order->additional_data[0]['namaAnak2'];
            $data['PassportAnak2'] = $order->additional_data[0]['passportAnak2'];
            $data['DOB_Anak2'] = $dob_secondchild;
            $data['Passport'] = $order->additional_data[0]['passport'];
            $data['DOB'] = date('m/d/Y', strtotime($order->additional_data[0]['tanggalLahir']));
            $data['ReferenceNumber'] = "ZAP-" . (string)$get_order_id . (string)date("dmY");
            $data['ProductInsurance'] = $order->additional_data[0]['jenisKendaraan'];
            $data['InsuredName'] = $order->additional_data[0]['nama'];
            $data['InsuranceStartDate'] = date('m/d/Y', strtotime($order->additional_data[0]['startDate']));
            $data['InsuredAddress'] = $order->additional_data[0]['alamat'];

            // return $data;
            $client_ret = new Client();
            $retrieve = $client_ret->post(
                env('ADIRA_URL') . '/valencia/v1/datapool/submit_v2',
                [
                    'verify' => false,
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Source' => 'ZAP',
                        'Businesssource' => 'PA_MUDIK',
                        'Authorization' => env('ADIRA_MUDIK_KEY'),
                        'token' => json_decode($author_token, true)['ResponseData']['Table1'][0]['AuthToken'],
                        'Calculate' => 'False'
                    ],
                    'json' =>  $data
                ]
            );
            $res = json_decode($retrieve->getBody(), true);

            $transaction = new AdiraTransaction;
            $transaction->postmikro_response = $res;
            $transaction->order_id = $order->id;
            $transaction->log_api = $data;
            $transaction->status = $res['ResponseCode'] == "200" ? "success" : "fail";
            $transaction->save();

            if ($res['ResponseCode'] == 200) {
                error_log("SUCCESS");
                //$save->order_id = $order_data->id."".$referenceNumber;
                $transaction->status = "send";
                $transaction->save();
                dispatch(new PosMikroMail($transaction->id));
            } else if ($res['ResponseCode'] == "400") {
                $transaction->status = "fail";
                $transaction->save();
                $text = "Error Zap2 Production \n"
                    . "Job : PosMudikJob \n"
                    . "Order ID : " . $order->id . " \n"
                    . "Message : " . $res['ResponseDesc'];
                throw new Exception($text['ResponseDesc'], 1);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function failed(\Exception $e)
    {
        $text = "Error Zap3 Staging \n"
            . "Job : PerjalananJob \n"
            . "Order ID : " . $this->order->id . " \n"
            . "Message : " . $e->getMessage();
        // TelegramBot::message($text);
        throw new Exception($e->getMessage(), 1);
    }
}
