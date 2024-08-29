<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\AdiraTransaction;
use App\Service\TelegramBot;
use GuzzleHttp\Client;

class FinishOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $cart;
    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $cart = $this->cart;
            $orders = Order::where('cart_id', $cart->id)->get();
            foreach($orders as $order) {
                $client = new Client;
                $form_param = [
                    "apiKey" => env('ADIRA_KEY'),
                    "requestNumber" =>  !empty($order->adira_trx->adira_response['data']['requestNumber'] ) ? $order->adira_trx->adira_response['data']['requestNumber'] : '',
                    "vehicleEquipmentNames[0]"=> !empty($order->inquiry->data['aksesoris']) ? $order->inquiry->data['aksesoris'][1]['merk'] : null,
                    "vehicleEquipmentPrices[0]"=> !empty($order->inquiry->data['aksesoris']) ? $order->inquiry->data['aksesoris'][1]['harga'] : null,
                    "vehicleEquipmentQuantities[0]"=> !empty($order->inquiry->data['aksesoris']) ? $order->inquiry->data['aksesoris'][1]['qty'] : null,
                    "vehicleEquipmentNames[1]"=> !empty($order->inquiry->data['aksesoris']) ? $order->inquiry->data['aksesoris'][2]['merk'] : null,
                    "vehicleEquipmentPrices[1]"=> !empty($order->inquiry->data['aksesoris']) ?$order->inquiry->data['aksesoris'][2]['harga'] : null,
                    "vehicleEquipmentQuantities[1]"=> !empty($order->inquiry->data['aksesoris']) ? $order->inquiry->data['aksesoris'][2]['qty'] : null,
                    "vehicleEquipmentNames[2]"=> !empty($order->inquiry->data['aksesoris']) ? $order->inquiry->data['aksesoris'][3]['merk'] : null,
                    "vehicleEquipmentPrices[2]"=> !empty($order->inquiry->data['aksesoris']) ? $order->inquiry->data['aksesoris'][3]['harga'] : null, 
                    "vehicleEquipmentQuantities[2]"=> !empty($order->inquiry->data['aksesoris']) ? $order->inquiry->data['aksesoris'][3]['qty'] : null,
                    "vehicleEquipmentNames[3]"=> !empty($order->inquiry->data['aksesoris']) ? $order->inquiry->data['aksesoris'][4]['merk'] : null,
                    "vehicleEquipmentPrices[3]"=> !empty($order->inquiry->data['aksesoris']) ? $order->inquiry->data['aksesoris'][4]['harga'] : null,
                    "vehicleEquipmentQuantities[3]"=> !empty($order->inquiry->data['aksesoris']) ?$order->inquiry->data['aksesoris'][4]['qty'] : null,
                    "vehicleEquipmentNames[4]"=> !empty($order->inquiry->data['aksesoris']) ? $order->inquiry->data['aksesoris'][5]['merk'] : null,
                    "vehicleEquipmentPrices[4]"=> !empty($order->inquiry->data['aksesoris']) ? $order->inquiry->data['aksesoris'][5]['harga'] : null,
                    "vehicleEquipmentQuantities[4]"=> !empty($order->inquiry->data['aksesoris']) ? $order->inquiry->data['aksesoris'][5]['qty'] : null,
                    "name"=> !empty($order->data['1']['data']) ? $order->data['1']['data'] : '',
                    "email"=> !empty($order->data['9']['data']) ? $order->data['9']['data'] : '',
                    "mobileNumber"=> !empty($order->data['13']['data']) ? $order->data['13']['data'] : '',
                    "address"=> !empty($order->data['36']['data']) ? $order->data['36']['data'] : '',
                    "provinceCode"=> !empty($order->data['54']['data']) ? $order->data['54']['data'] : '',
                    "cityCode"=> !empty($order->data['53']['data']) ? $order->data['53']['data'] : '',
                    "sendHardCopyPolicy"=> $order->additional_data[0]['copy'] == "soft" ? false : true,
                    "attentionName"=> !empty($order->data['1']['data']) ? $order->data['1']['data'] : '',
                    "attentionMobileNumber"=> !empty($order->data['13']['data']) ? $order->data['13']['data'] : '',
                    "attentionEmail"=> !empty($order->data['9']['data']) ? $order->data['9']['data'] : '', //9 
                    "attentionAddress"=> !empty($order->data['67']['data']) ? $order->data['67']['data'] : '',
                    "attentionProvinceCode"=> !empty($order->data['69']['data']) ? $order->data['69']['data'] : '',
                    "attentionCityCode"=> !empty($order->data['68']['data']) ? $order->data['68']['data'] : '',
                    "sendEmail"=>  $order->additional_data[0]['copy'] == "soft" ? true : false,
                    "documentFiles[Payment Receipt]"=> "",
                ];

                $res = $client->post(env('ADIRA_ORDER_URL').'api/v1/finish?apiKey='.env('ADIRA_KEY'),[
                    'verify' => false,
                    'form_params' => $form_param
                ]);
                $json_adira = json_decode($res->getBody(),true);
                $docPolicy = $client->post(env('ADIRA_ORDER_URL').'api/v1/documents-policy',[
                    'verify' => false,
                    'form_params' => [
                        "apiKey" => env('ADIRA_KEY'),
                        "requestNumber" => !empty($order->adira_trx->adira_response['data']['requestNumber'] ) ? $order->adira_trx->adira_response['data']['requestNumber'] : '',
                        ]
                    ]   
                );
                $json_policy = json_decode($docPolicy->getBody(),true);
            
                $cover = $client->post(env('ADIRA_ORDER_URL').'api/v1/cover-note',[
                    'verify' => false,
                    'form_params' => [
                        "apiKey" => env('ADIRA_KEY'),
                        "requestNumber" => !empty($order->adira_trx->adira_response['data']['requestNumber'] ) ? $order->adira_trx->adira_response['data']['requestNumber'] : '',
                        ]
                    ]
                );
                $json_covernote = json_decode($cover->getBody(),true);
                    $options = array(
                    "ssl"=>array(
                        "verify_peer"=>false,
                        "verify_peer_name"=>false,
                    ),
                );
                if(!empty($json_policy['status']) && $json_policy['status'] != 'fail'){
                    $fileZip = $json_policy['data']['url'];
                    $policyFilename = $order->adira_trx['adira_response']['data']['requestNumber']."-policy.zip";
                    $t = file_put_contents(public_path()."/uploads/pdf/".$policyFilename,file_get_contents($fileZip,false,stream_context_create($options)));
                    
                    $file = $json_covernote['data']['url'];
                    $filename = $order->adira_trx['adira_response']['data']['requestNumber']."-covernote.pdf";
                    $t = file_put_contents(public_path()."/uploads/pdf/".$filename,file_get_contents($file,false,stream_context_create($options)));
                }

                $adira_trx = AdiraTransaction::where('order_id',$order->id)->first();
                if(!empty($adira_trx)) {
                    $adira_trx->adira_status = !empty($json_adira['status']) ? $json_adira['status'] : "fail";
                    $adira_trx->post_finish = $json_adira;
                    $adira_trx->document_policy = $json_policy;
                    $adira_trx->cover_note = $json_covernote;
        
                    if($json_adira['status'] !== 'fail' && !empty($json_adira['status'])) {
                        $adira_trx->status = 'success';
                        $adira_trx->save();
                    }else {
                        $adira_trx->status = 'fail';
                        $adira_trx->save();

                        $textmo = $adira_trx?->order?->user?->partner?->mo ?? null;
                        $text = "Error Zap3 Prod \n"
                            . "Job : FinishOrderJob \n"
                            . "Order ID : $adira_trx->order_id \n"
                            . "MO : $textmo \n"
                            . "Message : ".json_encode($json_adira);
                        throw new \Exception($text,1);
                    }
                    
                    $adira_trx->save();
                }
            }
        } catch (\Exception $th) {
            throw $th;
        }
    }

    public function failed(\Exception $e)
    {
        $text = $e->getMessage();
        TelegramBot::message($text);
        throw new Exception($e->getMessage(), 1);
    }
}
