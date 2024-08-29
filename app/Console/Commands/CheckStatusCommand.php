<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\AdiraTransaction;
use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Mail\PolicyMail;
use App\Jobs\CreatePolicy;
use Illuminate\Support\Facades\Mail;
use Log;

class CheckStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checking Transaction Status';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $status = AdiraTransaction::select('adira_response', 'order_id', 'adira_status', 'id', 'status', 'request_number')->where("status", "success")->orderBy('id', 'desc')->get();
        $start = microtime(true);
        foreach ($status as $key => $value) {
            Log::warning('kontrak '.$value->order_id);
            $client = new Client;
            $res = $client->get(env('ADIRA_ORDER_URL').'api/v1/status?apiKey=' . env('ADIRA_KEY') . "&requestNumber=" . $value->request_number);
            $json_adira = json_decode($res->getBody(), true);
            Log::warning('tes'.json_encode($json_adira));
            if (!empty($value->adira_status) && $value->adira_status == "Finished") {
                if ($value->status !== 'send') {
                    $client = new Client;
                    $docPolicy = $client->post(
                        env('ADIRA_ORDER_URL') . 'api/v1/documents-policy',
                        [
                            'verify' => false,
                            'form_params' => [
                                "apiKey" => env('ADIRA_KEY'),
                                "requestNumber" => $value->request_number,
                            ]
                        ]
                    );
                    $json_policy = json_decode($docPolicy->getBody(), true);
                    //$json_policy = json_decode('{"status":"success","data":{"url":"https:\/\/zandbox01.zurich.co.id:8084\/upload\/policies\/PA3056-PR2061-2403180182\/documents-0697d182f7db550fd38700d566a65d0d.zip"}}',true);
                    error_log(json_encode($json_policy));
                    
                    // error_log(json_encode($value));
                    $res = $client->get(env('ADIRA_ORDER_URL') . 'api/v1/status?apiKey=' . env('ADIRA_KEY') . '&requestNumber=' . $value->request_number, ['verify' => false]);
                    $polling_res = json_decode($res->getBody(), true);
                    
                    Log::warning('status'.$json_policy['status']);
                    Log::Warning('poling'.$polling_res['data']['policy']['carePolicyNo']);
                    if ($json_policy['status'] != 'fail' && $polling_res['data']['policy']['carePolicyNo']) {
                        $value->document_policy = $json_policy;
                        $policy_no = $polling_res['data']['policy']['carePolicyNo'];
                        $order = $value->order;
                        Log::warning('send');
                        dispatch(new CreatePolicy($order, $policy_no, $value->order['data'][1]['data']));
                        $value->status = "send";
                    }
                    $value->save();
                }
            } elseif (!empty($value->adira_status) && isset($json_adira['data']['policy']['status']) && ($json_adira['data']['policy']['status'] == 'Finished')) {
                if ($value->status !== "send") {
                    $value->adira_status = $json_adira['data']['policy']['status'];
                    $value->polling_response = $json_adira;
                    error_log('Finished');
                    $name = $json_adira['data']['policy']['name'];
                    $no_polis = $json_adira['data']['policy']['carePolicyNo'];

                    $order = Order::find($value->order_id);
                    if (!empty($order)) {
                        error_log('Finish');
                        $order->status = 2;
                        $order->save();
                    }

                    $docPolicy = $client->post(
                        env('ADIRA_ORDER_URL') . 'api/v1/documents-policy',
                        [
                            'verify' => false,
                            'form_params' => [
                                "apiKey" => env('ADIRA_KEY'),
                                "requestNumber" => $value->request_number,
                            ]
                        ]
                    );

                    $json_policy = json_decode($docPolicy->getBody(), true);
                    // $json_policy = json_decode('{"status":"success","data":{"url":"https:\/\/zandbox01.zurich.co.id:8084\/upload\/policies\/PA3056-PR2061-2403180182\/documents-0697d182f7db550fd38700d566a65d0d.zip"}}');
                    error_log("masuk");
                    error_log(json_encode($json_policy));
                    if ($json_policy['status'] != 'fail') {
                        $policy_no = $json_adira['data']['policy']['carePolicyNo'];

                        dispatch(new CreatePolicy($order, $policy_no, $value->order['data'][1]['data']));
                        $value->status = "send";
                    }
                    $value->save();
                }
            }
        }
        $end = microtime(true) - $start;
        Log::warning("poling time " . $end . "");
    }
}
