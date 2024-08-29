<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;
use App\Models\Orders;
use App\Models\Transaction;
use App\Jobs\PaymentJob;

class CreatePolicyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $orders;
    protected $policy_no;
    protected $name;
    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->orders = $orders;
        $this->policy_no = $policy_no;
        $this->name = $name;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $orders = $this->orders;
        $policy_no = $this->policy_no;
        $name = $this->name;

        $client = new Guzzle();
        $transaction = Transaction::find($orders->transaction_id);


        $context = [
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,

        ];

        $namePDF = [];
        if($orders->product->flow == "mv" || $orders->product->flow == "moto" ){
            if (!empty($orders->product->additional_wording) || $orders->product->additional_wording != null) {
                $namePDF[] = $orders->product->additional_wording;
            }
            if(!empty($orders->adira_trx->document_policy['status']) && $orders->adira_trx->document_policy['status'] != 'fail'){

                $namePDF[] = $orders->adira_trx['adira_response']['data']['requestNumber']."-policy.zip";
            }else{

                $docPolicy = $client->post(env('ADIRA_ORDER_URL').'api/v1/documents-policy',[
                    'verify' => false,
                    'form_params' => [
                        "apiKey" => env('ADIRA_KEY'),
                        "requestNumber" => $orders->adira_trx['adira_response']['data']['requestNumber'],
                        ]
                    ]   
                );
                $json_policy = json_decode($docPolicy->getBody(),true);

                $options = array(
                    "ssl"=>array(
                        "verify_peer"=>false,
                        "verify_peer_name"=>false,
                    ),
                );

                if($json_policy['status'] != 'fail'){
                    $fileZip = $json_policy['data']['url'];
                    $policyFilename = $orders->adira_trx['adira_response']['data']['requestNumber']."-policy.zip";
                    $t = file_put_contents(public_path()."/uploads/pdf/".$policyFilename,file_get_contents($fileZip,false,stream_context_create($options)));
                }

                $orders->adira_trx->document_policy = $json_policy;

                $orders->adira_trx->save();

                $namePDF[] = $policyFilename;
            }
            if(!empty($orders->adira_trx->cover_note['status']) && $orders->adira_trx->cover_note['status'] != 'fail'){
                $namePDF[] = $orders->adira_trx['adira_response']['data']['requestNumber']."-covernote.pdf";
            }else{
                
                $cover = $client->post(env('ADIRA_ORDER_URL').'api/v1/cover-note',[
                    'verify' => false,
                    'form_params' => [
                        "apiKey" => env('ADIRA_KEY'),
                        "requestNumber" => $orders->adira_trx['adira_response']['data']['requestNumber'],
                        ]
                    ]
                );

                $options = array(
                    "ssl"=>array(
                        "verify_peer"=>false,
                        "verify_peer_name"=>false,
                    ),
                );
                $json_covernote = json_decode($cover->getBody(),true);

                $file = $json_covernote['data']['url'];
                $filename = $orders->adira_trx['adira_response']['data']['requestNumber']."-covernote.pdf";
                $t = file_put_contents(public_path()."/uploads/pdf/".$filename,file_get_contents($file,false,stream_context_create($options)));

                
                $orders->adira_trx->cover_note = $json_covernote;

                $orders->adira_trx->save();
            }
        }elseif($orders->product->flow == "nor"){
            Log::warning("masuk harusnya:");
            Log::warning( $this->orders);
            if(!empty($orders->adira_trx->document_policy)){
                $namePDF[] = $policy_no.".zip";
            }

        }else{

            $namePDF[] = date('YmdHis')."_ringkasan_polis_".$orders['data'][1]['data'].".pdf";
            $pdf = app()->make('dompdf.wrapper');
            $pdf->loadView('pdf.policy', compact('data', 'orders'))->setPaper('a3','potrait');
            $pdf->download()->getOriginalContent();
            $save = Storage::disk('public')->put('pdf/'.$namePDF,$pdf->output());
        }


    dispatch(new PaymentJob($orders->data[9]['data'],$transaction->order->product,$namePDF, $policy_no, $name));

    }
}
