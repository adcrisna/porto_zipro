<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;
use Log;
use Exception;

class CancelMV implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $data = $this->data;
            Log::warning("data ". $data);
            $client = new Client();
            $log_api = $data?->adira_trx?->log_api;
            Log::warning("log". json_encode($data->adira_trx));
            $reffno = array_values($log_api)[array_search('referenceInfo',array_column(array_values($log_api), 'name'))]['contents'];
            $can = $client->post(env('ADIRA_ORDER_URL').'api/v1/cancel-order',[
                'verify' => false,
                'form_params' => [
                    "apiKey" => env('ADIRA_KEY'),
                    "sendEmail" => 0,
                    // "referenceInfo" => $reffno ?? ""
                    "requestNumber" => !empty($data->adira_trx->request_number ) ? $data->adira_trx->request_number : '',
                    ]
                ]);
            $json_adira = json_decode($can->getBody(),true);
            if(!empty($json_adira['status']) && $json_adira['status'] !== 'fail' ) {
                error_log("ADA STATUS / SUCCESS cancel");
            }else {
                error_log("GAADA STATUS / FAILED cancel");
                $text = "Error Zap3 Prod \n"
                    . "Job : CancelMV \n"
                    . "Order ID : $data->id \n"
                    . "Message : ".json_encode($json_adira);
                throw new \Exception($text, 1);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function failed(\Exception $e)
    {
        $text = $e->getMessage();
        error_log("failed cancel mv : ".$e->getMessage());
        throw new \Exception($e->getMessage(), 1);
    }
}
