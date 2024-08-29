<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

use App\Models\Orders;
use App\Models\Transaction;
use App\Mail\PolicyMail;
use GuzzleHttp\Client as Guzzle;
use Log;
use Exception;

class SendPolicyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $email;
    protected $product;
    protected $pdf;
    protected $policy_no;
    protected $name;
    public $tries = 1;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email, $product, $pdf, $policy_no, $name)
    {
        $this->email = $email;
        $this->product = $product->withoutRelations();
        $this->pdf = $pdf;
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
        try {
            $product = $this->product;
            $user = $this->email;
            $pdf = $this->pdf;
            $policy_no = $this->policy_no;
            $name = $this->name;
            $data = [
                "product" => $product,
                "email" => $user
            ];
            // error_log($pdf);
            $tidakada = [];
            foreach ($pdf as $key => $value) {
                if(!file_exists(public_path()."/uploads/pdf/$value")) {
                    $tidakada[] = $value;
                }
            }
    
            foreach($tidakada as $checkPDF) {
                $exs = explode("-",$checkPDF);
                $requestNumber = $exs[0]."-".$exs[1]."-".$exs[2];
                $newexs = explode(".", $exs[3]);
                if($newexs[0] == "covernote") {
    
                    $client = new Guzzle();
                    $cover = $client->post(env('ADIRA_ORDER_URL').'api/v1/cover-note',[
                        'verify' => false,
                        'form_params' => [
                            "apiKey" => env('ADIRA_KEY'),
                            "requestNumber" => $requestNumber,
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
                    $filename = $requestNumber."-covernote.pdf";
                    $t = file_put_contents(public_path()."/uploads/pdf/".$filename,file_get_contents($file,false,stream_context_create($options)));
    
                }else if($newexs[0] == "policy") {
                    $client = new Guzzle();
                    $docPolicy = $client->post(env('ADIRA_ORDER_URL').'api/v1/documents-policy',[
                        'verify' => false,
                        'form_params' => [
                            "apiKey" => env('ADIRA_KEY'),
                            "requestNumber" => $requestNumber,
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
    
                    $policyFilename = $requestNumber."-policy.zip";
                    if($json_policy['status'] != 'fail'){
                        $fileZip = $json_policy['data']['url'];
                        $t = file_put_contents(public_path()."/uploads/pdf/".$policyFilename,file_get_contents($fileZip,false,stream_context_create($options)));
                    }
                }
            }
    
            error_log("EMAIL USER :".$user);
            Mail::to($user)->send(new PolicyMail($data, $product->is_pg, $pdf, $policy_no, $name));
        } catch (\Exception $th) {
            throw $th;
        }
    }
}
