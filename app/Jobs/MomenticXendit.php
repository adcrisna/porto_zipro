<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;

class MomenticXendit implements ShouldQueue
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

        $contract_id = $this->data;
        $jsons["contract_momentic_2"] = $contract_id;
        $jsons["xendit_status"]="PAID";
        $jarr[]=$jsons;
        //error_log(json_encode($jarr));
        $client = new Client();
        $result = $client->post(
            env('MOMENTIC_FINANCE_URL') . '/api/zipro_api/create_xendit_statuspayment',
            [
                'verify' => false,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Auth-Key' => env('MOMENTIC_FINANCE_KEY'),
                    'Auth-Secret' =>  env('MOMENTIC_FINANCE_SECRET')
                ],
                'json' =>  $jarr
            ]
        );

        //error_log($result->getBody());
    }
}
