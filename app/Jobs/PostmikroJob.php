<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use App\Models\Transaction;
use GuzzleHttp\Client;
use App\Jobs\PaymentJob;
use App\Jobs\PosMikroMail;
use Illuminate\Support\Facades\Storage;
use App\Models\InquiryMv;
use App\Models\BasePrice;
use App\Models\User;
use App\Models\AdiraTransaction;
Use Illuminate\Support\Str;
use Auth;
use Log;
use App\Service\TelegramBot;
use Exception;

class PostmikroJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $order;
    protected $data;
    protected $start_date;
    protected $product;
    protected $gender;
    public $tries = 1;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order, $data, $start_date, $product, $gender)
    {
        $this->order = $order;
        $this->data = $data;
        $this->start_date = $start_date;
        $this->product = $product;
        $this->gender = $gender;
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
            $data = $this->data;
            $start_date = $this->start_date;
            $product = $this->product;
            $gender = $this->gender;
    
            $order_data = Order::find($order);
            $user = User::find($order_data->user_id);
            $start_date_fix = !empty($order_data->start_date) ? date("m/d/Y",strtotime($order_data->start_date)) : null;
            $referenceNumber = date("dmY",strtotime($order_data->created_at));
            
            if ($product->period_days > 360 && $product->period_days < 367) {
                $period = "1 TAHUN";
            }else if($product->period_days > 180 && $product->period_days < 185){
                $period = "6 BULAN";
            }else{
                $period = $product->period_days . ' HARI';
            }
            
            $originalDate = $data[5]['data'] ;
            $newDate = date("m/d/Y", strtotime($originalDate));
             
            $form_params = [
                "InsuredName" => !empty($data[1]['data']) ? $data[1]['data'] : '',
                "InsuredAddress" => !empty($data[36]['data']) ? $data[36]['data'] : '',
                "InsuredBirthdate" => $newDate,
                "InsuredEmail" => !empty($data[9]['data']) ? $data[9]['data'] : '',
                "InsuredGender" => !empty($data[37]['data']) ? $data[37]['data'] : '',
                "InsuredIDNumber" => !empty($data[33]['data']) ? $data[33]['data'] : '',
                "InsuredMobilePhone" => !empty($data[13]['data']) ? $data[13]['data'] : '',
                "ProductInsurance" => $product->adira_product_id,
                "InsurancePeriod" => $period,
                "ReferenceNumber" => $referenceNumber."".$order_data->id, //"SPRINT-2021090002", // order_id + created_at convertedto Ymd
                "InsuranceStartDate" => $start_date_fix,
                "VehicleBrand" => !empty($data[25]['data']) ? $data[25]['data'] : '',
                "VehiclePoliceNumber" => !empty($data[23]['data']) ? $data[23]['data'] : '',
                "VehicleChassisNumber" => !empty($data[51]['data']) ? $data[51]['data'] : '',
                "VehicleYOM" => !empty($data[24]['data']) ? $data[24]['data'] : '',
                "Branch" => "99",
                "MO" => !empty($user->partner) ? $user->partner->mo : '',
                "Segment" => "2C3003",
                "Partner" => "Sprint Salvus",
                //"BSID1" => "A/0309/191",
                "BSID1" => "M99SI00071",
                "BSType1" => "B",
                "BSFee1" => "25",
                "BSID2" => "",
                "BSType2" => "",
                "BSFee2" => "",
                "BSID3" => "",
                "BSType3" => "",
                "BSFee3" => "",
                "Discount" => "",
            ];

            $client_token = new Client();
            Log::warning(env('ADIRA_URL').'valencia/v1/authenticate/authtoken');
             $result = $client_token->post(env('ADIRA_URL').'valencia/v1/authenticate/authtoken', 
                [
                    'verify' => false,
                    'headers' => [
                        'Content-Type' => 'Application/Json',
                        'Authorization' => env('ADIRA_TOKEN'),
                        'source' => 'GENERAL',
                        'businesssource' => 'MICRO_INSURANCE'
                        ]
                ]);
                // ADIRA_TOKEN = "Basic U0bm6gGLpubF3WU2ELtdbZFXQvZfiFoHvgHffBZFVaQ=|U0bm6gGLpubF3WU2ELtdbZFXQvZfiFoHvgHffBZFVaQ="
            $author_token = $result->getBody();
    
            $client = new Client;
            Log::warning(env('ADIRA_URL').'valencia/v1/datapool/submit_v2');
            $res = $client->post(env('ADIRA_URL').'valencia/v1/datapool/submit_v2', 
            [
                'headers' => [
                    "Content-Type" => "application/json",
                    "source" => "GENERAL",
                    "BusinessSource" => "MICRO_INSURANCE",
                    "Authorization" => env('ADIRA_TOKEN'),
                    "Token" => json_decode($author_token, true)['ResponseData']['Table1'][0]['AuthToken'],//"qONBSIGqG8Z5B8lhltypSxoHF8M7M5Ae",
                    "Calculate" => "FALSE"
                ],
                'body' => json_encode($form_params)
            ]);
           
            Log::warning(json_encode($form_params));
            $json = json_decode($res->getBody(), true);
            $save = new AdiraTransaction;
            $save->log_api = $form_params;
            $save->order_id = $order_data->id;
            $save->postmikro_response = $json;
            $save->save();
            if($json['ResponseCode'] == 200) {
                error_log("SUCCESS");
                //$save->order_id = $order_data->id."".$referenceNumber;
                $save->status = "send";
                dispatch(new PosMikroMail($save->id));
            }else if($json['ResponseCode'] == 400){
                error_log("FAIL");
                $save->status = "fail";

                $textmo = $order_data?->user?->partner?->mo ?? null;
                $text = "Error Zap3 Prod \n"
                    . "Job : PosMikroJob \n"
                    . "Order ID : ".$order_data->id." \n"
                    . "MO : $textmo \n"
                    . "Message : ".json_encode($json);
                throw new Exception($text, 1);
            }
            $save->save();
        
        } catch (\Exception $th) {
            throw $th;
        }
    }

    public function failed(\Exception $e)
    {
        $text = $e->getMessage();
        // TelegramBot::message($text);
        throw new Exception($e->getMessage(), 1);
    }
}
