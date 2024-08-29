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
use App\Models\InquiryMv;
use App\Models\BasePrice;
use App\Models\User;
use App\Models\TestPost;
use App\Models\AdiraTransaction;
use App\Models\OrComission;
use App\Models\Comission;
use Log;

class SpectrumJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $order_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order_id)
    {
        $this->order_id = $order_id;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $order = Order::find($this->order_id);
        $oTransid = $order->transaction->id;
        $mikroCo = OrComission::where('trx_id', $oTransid)->get();
        $ceklayer = floatval($order->product->or_comission[0]['value']);
        $comor = Comission::where('trx_id', $oTransid)->first();
        
        if (!empty($order->transaction->contract_id)) {
            Log::info("REHIT FAILED CONTRACT ID ALREADY EXIST!".$order->transaction->contract_id);
            return 0;
            exit;
        }

        $cobDataFinal = [];
        
        // Check if $ceklayer is greater than 0
        if ($ceklayer > 0) {
            foreach ($mikroCo as $key => $value) {
                $cobDataFinal[] = [
                    "usersdata" => [
                        'name' => $value->user->name ?? "",
                        'phone' => "",
                        'email' => $value->user->email ?? "",
                    ],
                    'rate' => !empty($value->formula['value']) ? floatval($value->formula['value'] * 100) : 0,
                ];
            }
        }
        
        if ($comor) {
            $cobDataFinal[] = [
                "usersdata" => [
                    'name' => $comor->username ?? "",
                    'phone' => "",
                    'email' => $comor->useremail ?? "",
                ],
                'rate' => !empty($comor->formula['value']) ? floatval($comor->formula['value'] * 100) : 0,
            ];
        }
            $momentic_prod = $order->product->momentic_2_prod;
         
                if ($order->user->tax == 2.5 || $order->user->tax == 3)   {
                    $type = "Individual";
                }
                if ($order->user->tax == 2 || $order->user->tax == 4)   {
                    $type = "Company";
                }
    
                $status[0] = "Waiting Approval";
                $status[1] = "Rejected";
                $status[2] = "Approved";
                
                $data['periode'] =  $order->product->period_days;
                $data['inforce_date'] = $order->transaction->policy_start;
                $data['periode_start'] = $order->transaction->policy_start;
                $data['broker'] = 25 - $order->deduct;
                $data['policy_number'] = $order->transaction->id;
                $data['enginering_rate'] = 3.5;
    
                if ($momentic_prod == 55 || $momentic_prod == 54 || $momentic_prod == 56 || $momentic_prod == 509) {
                    $data['cashback_rate'] = null;
                }elseif ($momentic_prod == 510) {
                    $data['cashback_rate'] = 5;
                }
                else {
                    $data['cashback_rate'] = 15;
                }
                $data['additional_data']['usersdata']['name'] = $order->data[1]['data'];
                $data['additional_data']['usersdata']['phone'] = !empty($order->data[13]['data']) ? $order->data[13]['data'] : '';
                $data['additional_data']['usersdata']['email'] = $order->data[9]['data'];
                $data['additional_data']['usersdata']['type'] = $type;
                $data['additional_data']['usersdata']['address'] =  !empty($order->data[36]['data']) ? $order->data[36]['data'] : '';
  
                if ($momentic_prod == 54 || $momentic_prod == 56) {
                    # mikro
                    if ($momentic_prod == 54 ) {
                        $data['product_code'] =  "b5FR54";
                        $product_key =  "fWE7MejKOj54";
                    }
    
                    if ($momentic_prod == 56 ) {
                        $product_key =  "fWE7MejKOj56";
                        $data['product_code'] =  "b5FR56";
                    }
                    $data['additional_data']['application'][24] = "MOTOR VEHICLE"; // "CLASS OF BUSINESS"
                    $data['additional_data']['application'][26] = "MGDM"; // "BRANCH"
                    $data['additional_data']['application'][80] = "PARTNERSHIP"; // "DIVISION",
                    $data['additional_data']['application'][81] = "AFFINITY"; // "SEGMENT",
                    $data['additional_data']['application'][25] = "NEW"; // "CONTRACT TYPE",
                    $data['additional_data']['application'][117] = $order->deduct; // "DISCOUNT",
                    $data['additional_data']['application'][118] = 25 - $order->deduct; // "COMMISSION"
                    $data['additional_data']['application'][119] = $order->base_price; // "PREMIUM"
    
                    $data['additional_data']['bf'][0]['type'] = "Percent";
                    $data['additional_data']['bf'][0]['amount'] = 25 - $order->deduct; 
    
                    $data['additional_data']['object'][0]['data'][95]['values'] = $order->data[1]['data']; // "INSURED NAME",
                    $data['additional_data']['object'][0]['data'][100]['values'] = $order->transaction->id; // "TRANSACTION ID",
                    $data['additional_data']['object'][0]['data'][101]['values'] = $order->transaction->created_at; // "PAYMENT DATE",
                    $data['additional_data']['object'][0]['data'][102]['values'] = $status[$order->status]; // "PAYMENT STATUS",
                    $data['additional_data']['object'][0]['data'][103]['values'] = $order->data[5]['data']; // "DATE OF BIRTH",
                    $data['additional_data']['object'][0]['data'][110]['values'] = "-"; // "POLICY NUMBER",
                    $data['additional_data']['object'][0]['data'][113]['values'] = $order->data[13]['data']; // "PHONE NUMBER",
                    $data['additional_data']['object'][0]['data'][114]['values'] = $order->data[9]['data']; // "E-MAIL"
                    $data['additional_data']['object'][0]['data'][115]['values'] = !empty($order->data[54]) ? $order->data[54]['data'] : ""; // "PROVINCE"
                    $data['additional_data']['object'][0]['data'][116]['values'] = !empty($order->data[53]) ? $order->data[53]['data'] : ''; // "CITY"
                    $data['additional_data']['object'][0]['data'][120]['values'] = $order->product->name; // "PRODUCT NAME"
                    $data['additional_data']['object'][0]['insured'] = $order->base_price; // TODO:
                    $data['additional_data']['object'][0]['start_date'] = $order->transaction->policy_start;
                    $data['additional_data']['object'][0]['end_date'] = $order->transaction->policy_end;
                }
  
                if ($momentic_prod == 52 || $momentic_prod == 53 || $momentic_prod == 51 || $momentic_prod == 511) {
                    $keys = !empty($order->inquiry->item) ? array_column($order->inquiry->item, 'detail') : [];
                    if ($momentic_prod == 52 ) {
                        $data['product_code'] =  "eoZO52";
                        $product_key =  "yCW46grv4b52";
                    }
                    if ($momentic_prod == 53 ) {
                        $data['product_code'] =  "eoZO53";
                        $product_key =  "yCW46grv4b53";
                    }
                    if ($momentic_prod == 51 ) {
                        $data['product_code'] =  "eoZO51";
                        $product_key =  "yCW46grv4b51";
                    }
                    if ($momentic_prod == 511 ) {
                        $data['product_code'] =  "eoZO511";
                        $product_key =  "yCW46grv4b511";
                    }    
                    $data['additional_data']['application'][24] = "MOTOR VEHICLE"; // "CLASS OF BUSINESS"
                    $data['additional_data']['application'][26] = "MGDM"; // "BRANCH"
                    if ($momentic_prod == 51 || $momentic_prod == 511) {
                        $data['additional_data']['application'][80] = "PARTNERSHIP"; // "DIVISION", 
                    }else {
                        $data['additional_data']['application'][80] = "DIGITAL DISTRIBUTION"; // "DIVISION",
                    }
                    $data['additional_data']['application'][81] = "AFFINITY"; // "SEGMENT",
                    $data['additional_data']['application'][35] = "MOTOR VEHICLE"; // "OCCUPATION"
                    $data['additional_data']['application'][37] = "MGDM"; // "EQVET ZONE"
                    $data['additional_data']['application'][56] = "DIGITAL DISTRIBUTION"; // "POLICE NO",
                    $data['additional_data']['application'][57] = "AFFINITY"; // "CHASSIS NUMBER",
                    $data['additional_data']['application'][58] = "NEW"; // "ENGINE NUMBER",
                    $data['additional_data']['application'][59] = $order->deduct; // "YEAR",
                    $data['additional_data']['application'][60] = !empty(array_keys($keys, "SRCC")) ? $order->inquiry->item[array_keys($keys, "SRCC")[0]]['price'] : 0; // "RSCC (RIOT, STRIKE, AND CIVIL COMMOTION)"
                    $data['additional_data']['application'][61] =  !empty(array_keys($keys, "TS")) ? $order->inquiry->item[array_keys($keys, "TS")[0]]['price'] : 0; // "TS (TERRORISM AND SABOTAGE) MV"
                    $data['additional_data']['application'][63] = !empty(array_keys($keys, "TFSHL")) ? $order->inquiry->item[array_keys($keys, "TFSHL")[0]]['price'] : 0; // "TSHFL"
                    $data['additional_data']['application'][71] =  !empty(array_keys($keys, "PA Pengemudi")) ? $order->inquiry->item[array_keys($keys, "PA Pengemudi")[0]]['price'] : 0; // "PERSONAL ACCIDENT""
                    $data['additional_data']['application'][85] = $order->base_price; // "BRAND/TYPE"
                    $data['additional_data']['application'][111] = !empty(array_keys($keys, "AUTHORIZED WORKSHOP")) ? $order->inquiry->item[array_keys($keys, "AUTHORIZED WORKSHOP")[0]]['price'] : 0; // "AUTHORIZED WORKSHOP" ?//
                    $data['additional_data']['application'][112] = !empty(array_keys($keys, "TPL")) ? $order->inquiry->item[array_keys($keys, "TPL")[0]]['price'] : 0; // "THIRD PARTY LIABILITY"
                    $data['additional_data']['application'][117] = $order->deduct; // "DISCOUNT"
                    $data['additional_data']['application'][118] = 25 - $order->deduct; // "COMMISSION"
                    $data['additional_data']['application'][119] = $order->base_price; // "PREMIUM"
        
                    $data['additional_data']['bf'][0]['type'] = "Percent";
                    $data['additional_data']['bf'][0]['amount'] = 25 - $order->deduct; 

                    $data['additional_data']['object'][0]['data'][95]['values'] = $order->data[1]['data'] ?? ''; // "INSURED NAME",
                    $data['additional_data']['object'][0]['data'][100]['values'] = $order->transaction->id; // "TRANSACTION ID",
                    $data['additional_data']['object'][0]['data'][101]['values'] = $order->transaction->created_at; // "PAYMENT DATE",
                    $data['additional_data']['object'][0]['data'][102]['values'] = $status[$order->status] ?? ''; // "PAYMENT STATUS",
                    $data['additional_data']['object'][0]['data'][103]['values'] = $order->data[5]['data'] ?? ''; // "DATE OF BIRTH",
                    $data['additional_data']['object'][0]['data'][110]['values'] = "-"; // "POLICY NUMBER",
                    $data['additional_data']['object'][0]['data'][113]['values'] = $order->data[13]['data'] ?? ''; // "PHONE NUMBER",
                    $data['additional_data']['object'][0]['data'][114]['values'] = $order->data[9]['data'] ?? ''; // "E-MAIL"
                    $data['additional_data']['object'][0]['data'][115]['values'] = $order->data[54]['data'] ?? ''; // "PROVINCE"
                    $data['additional_data']['object'][0]['data'][116]['values'] = $order->data[53]['data'] ?? ''; // "CITY"
                    $data['additional_data']['object'][0]['data'][120]['values'] = $order->product->name; // "PRODUCT NAME"
            
                    $data['additional_data']['object'][0]['insured'] = $order->base_price; // TODO:
                    $data['additional_data']['object'][0]['start_date'] = $order->transaction->policy_start;
                    $data['additional_data']['object'][0]['end_date'] = $order->transaction->policy_end;
                }
    
                if ($momentic_prod == 50) {
                    $data['product_code'] =  "suJ519";
                    $product_key = "wu8PWYoVqw19";
                    $data['additional_data']['application'][24] = "MOTOR VEHICLE"; // "CLASS OF BUSINESS"
                    $data['additional_data']['application'][25] = "NEW"; // "CONTRACT TYPE"
                    $data['additional_data']['application'][27] = "IDR"; // "CURRENCY",
                    $data['additional_data']['application'][28] = "ANYWHERE IN INDONESIA"; // "RISK LOCATION",
                    $data['additional_data']['application'][41] = $order->transaction->created_at; // "PREMIUM PAYMENT",
                    $data['additional_data']['application'][80] = "PARTNERSHIP"; // "DIVISION",
                    $data['additional_data']['application'][81] = "AFFINITY"; // "SEGMENT"
        
                    $data['additional_data']['bf'][0]['type'] = "Percent";
                    $data['additional_data']['bf'][0]['amount'] = 25 - $order->deduct; 
        
                    $data['additional_data']['object'][0]['data'][57]['values'] = !empty($order->inquiry['data']['chassis']) ? $order->inquiry['data']['chassis'] : ''; // "CHASSIS NUMBER",
                    $data['additional_data']['object'][0]['data'][59]['values'] = !empty($order->inquiry['data']['tahun']) ? $order->inquiry['data']['tahun'] : ''; // "YEAR",
                    $data['additional_data']['object'][0]['data'][85]['values'] = !empty($order->inquiry['data']['modelstr']) ? $order->inquiry['data']['modelstr'] : ''; // "BRAND/TYPE",
                    $data['additional_data']['object'][0]['data'][95]['values'] = $order->data[1]['data']; // "INSURED NAME",
                    $data['additional_data']['object'][0]['data'][100]['values'] = $order->transaction->id; // "TRANSACTION ID",
                    $data['additional_data']['object'][0]['data'][101]['values'] = $order->transaction->created_at; // "PAYMENT DATE",
                    $data['additional_data']['object'][0]['data'][102]['values'] = $status[$order->status]; // "PAYMENT STATUS",
                    $data['additional_data']['object'][0]['data'][120]['values'] = $order->product->name; // "PRODUCT NAME"
            
                    $data['additional_data']['object'][0]['insured'] = $order->base_price; // TODO:
                    $data['additional_data']['object'][0]['start_date'] = $order->transaction->policy_start;
                    $data['additional_data']['object'][0]['end_date'] = $order->transaction->policy_end;
                }
    
                //MICRO HCP5D
                if ($momentic_prod == 55) {
                    $data['product_code'] =  "b5FR55";
                    $product_key = "fWE7MejKOj55";
    
                    $data['additional_data']['application'][24] =  "HOSPITAL CASH PLAN (HCP)"; // "CLASS OF BUSINESS"
                    $data['additional_data']['application'][25] =  "NEW"; // "CONTRACT TYPE"
                    $data['additional_data']['application'][26] =  "AFFINITY HEAD";
                    $data['additional_data']['application'][27] =  "IDR"; // "CURRENCY",
                    $data['additional_data']['application'][80] =  "PARTNERSHIP"; // "DIVISION",
                    $data['additional_data']['application'][81] =  "AFFINITY"; // "SEGMENT"
                    $data['additional_data']['application'][117] = $order->deduct; //DISCOUNT
                    $data['additional_data']['application'][118] = 25 - $order->deduct; // "COMMISSION"
                    $data['additional_data']['application'][119] = $order->base_price; // "PREMIUM"
                    $data['additional_data']['application'][295] = "MEGA DAMAIYANTI";
        
                    $data['additional_data']['bf'][0]['type'] = "Percent";
                    $data['additional_data']['bf'][0]['amount'] = 25 - $order->deduct; 
        
                    $data['additional_data']['object'][0]['data'][95]['values'] = !empty($order->data[1]['data']) ? $order->data[1]['data'] : '' ; // INSURED NAME
                    $data['additional_data']['object'][0]['data'][100]['values'] = $order->transaction_id ?? ''; // TRANSACTION ID
                    $data['additional_data']['object'][0]['data'][101]['values'] = date('Y-m-d', strtotime($order->transaction->created_at)) ?? ''; // "PAYMENT DATE",
                    $data['additional_data']['object'][0]['data'][102]['values'] = $status[$order->status]; // "PAYMENT STATUS",
                    $data['additional_data']['object'][0]['data'][103]['values'] = $order->data[5]['data'] ?? ''; // "DATE OF BIRTH",
                    $data['additional_data']['object'][0]['data'][110]['values'] = '-'; // POLICY NUMBER
                    $data['additional_data']['object'][0]['data'][113]['values'] = $order->data[13]['data'] ?? ''; // "PHONE NUMBER",
                    $data['additional_data']['object'][0]['data'][114]['values'] = $order->data[9]['data'] ?? ''; // "E-MAIL"
                    $data['additional_data']['object'][0]['data'][115]['values'] = $order->data[54]['data'] ?? ''; // "PROVINCE"
                    $data['additional_data']['object'][0]['data'][116]['values'] = $order->data[53]['data'] ?? ''; // "CITY"
                    $data['additional_data']['object'][0]['data'][120]['values'] = $order->product->name ?? ''; // "PRODUCT NAME"
                    $data['additional_data']['object'][0]['data'][275]['values'] = $order->product->name ?? ''; // CLAIMANT
                    $data['additional_data']['object'][0]['data'][278]['values'] = $order->transaction_id ?? ''; // UNIQUE IDENTIFICATION ID    
            
                    $data['additional_data']['object'][0]['insured'] = $order->base_price; // TODO:
                    $data['additional_data']['object'][0]['start_date'] = $order->transaction->policy_start;
                    $data['additional_data']['object'][0]['end_date'] = $order->transaction->policy_end;
                }
    
                //PA PERJALANAN
                if ($momentic_prod == 509) {
                    $data['product_code'] =  "i1m640";
                    $product_key = "mXKqI2qbcd40";
    
                    $data['additional_data']['application'][24] =  "PA"; // CLASS OF BUSINESS
                    $data['additional_data']['application'][25] =  "NEW"; // "CONTRACT TYPE"
                    $data['additional_data']['application'][26] =  "AFFINITY HEAD";
                    $data['additional_data']['application'][27] = "IDR"; // "CURRENCY",
                    $data['additional_data']['application'][28] =  $order->additional_data[0]['tujuan'] ?? ''; // RISK LOCATION
                    $data['additional_data']['application'][38] =  "NILL"; // DEDUCTIBLE
                    $data['additional_data']['application'][41] = $order->transaction->created_at; // "PREMIUM PAYMENT",
                    $data['additional_data']['application'][80] =  "PARTNERSHIP"; // "DIVISION",
                    $data['additional_data']['application'][81] =  "AFFINITY"; // "SEGMENT"
                    $data['additional_data']['application'][96] =  $order->product->description ?? ''; // DESCRIPTION
                    $data['additional_data']['application'][295] =  "MEGA DAMAIYANTI"; //SALES
        
                    $data['additional_data']['bf'][0]['type'] = "Percent";
                    $data['additional_data']['bf'][0]['amount'] = 25 - $order->deduct; 
                    
                    $data['additional_data']['object'][0]['data'][75]['values'] = $order->data[1]['data'] ?? ''; // MEMBER
                    $data['additional_data']['object'][0]['data'][95]['values'] = $order->data[1]['data'] ?? ''; // INSURED NAME
                    $data['additional_data']['object'][0]['data'][103]['values'] = $order->data[5]['data'] ?? ''; // "DATE OF BIRTH",
                    $data['additional_data']['object'][0]['data'][120]['values'] = $order->product->name ?? ''; // PRODUCT NAME
                    $data['additional_data']['object'][0]['data'][146]['values'] = ''; // OTHER INFO
                    $data['additional_data']['object'][0]['data'][226]['values'] = $order->data[33]['data'] ?? ''; // KTP/ NO IDENTIFICATION
                    $data['additional_data']['object'][0]['data'][263]['values'] = $order->additional_data[0]['tujuan'] ?? ''; // DESTINATION
                    $data['additional_data']['object'][0]['data'][264]['values'] = $order->transaction->policy_start ?? ''; // START DATE
                    $data['additional_data']['object'][0]['data'][265]['values'] = $order->transaction->policy_end ?? ''; // END DATE
                    $data['additional_data']['object'][0]['data'][275]['values'] = $order->product->name ?? ''; // CLAIMANT
                    $data['additional_data']['object'][0]['data'][278]['values'] = $order->transaction_id ?? ''; // UNIQUE IDENTIFICATION ID
    
            
                    $data['additional_data']['object'][0]['insured'] = $order->base_price; // TODO:
                    $data['additional_data']['object'][0]['start_date'] = $order->transaction->policy_start;
                    $data['additional_data']['object'][0]['end_date'] = $order->transaction->policy_end;
                }
    
                //TRAVEL
                if ($momentic_prod == 510) {
                    $data['product_code'] =  "yDpN41";
                    $product_key = "nJ9GPzG6B941";
    
                    $data['additional_data']['application'][24] =  "TRAVEL"; // CLASS OF BUSINESS
                    $data['additional_data']['application'][25] =  "NEW"; // "CONTRACT TYPE"
                    $data['additional_data']['application'][26] =  "AFFINITY HEAD";
                    $data['additional_data']['application'][27] = "IDR"; // "CURRENCY",
                    $data['additional_data']['application'][28] =  $order->additional_data[0]['zurich_origin_name'] ?? ''; // RISK LOCATION
                    $data['additional_data']['application'][41] =   date('Y-m-d', strtotime($order->transaction->created_at)) ?? ''; // "PREMIUM PAYMENT",
                    $data['additional_data']['application'][80] =  "PARTNERSHIP"; // "DIVISION",
                    $data['additional_data']['application'][81] =  "AFFINITY"; // "SEGMENT"
                    $data['additional_data']['application'][96] =  $order->product->description ?? ''; // DESCRIPTION
                    $data['additional_data']['application'][295] =  "MEGA DAMAIYANTI"; //SALES
        
                    $data['additional_data']['bf'][0]['type'] = "Percent";
                    $data['additional_data']['bf'][0]['amount'] = 25 - $order->deduct; 
        
                    $data['additional_data']['object'][0]['data'][75]['values'] = $order->data[1]['data'] ?? ''; // MEMBER
                    $data['additional_data']['object'][0]['data'][95]['values'] = $order->data[1]['data'] ?? ''; // INSURED NAME
                    $data['additional_data']['object'][0]['data'][100]['values'] = $order->transaction_id ?? '';// TRANSACTION ID
                    $data['additional_data']['object'][0]['data'][103]['values'] = !empty($order->data[5]['data'])  ? date('Y-m-d', strtotime($order->data[5]['data'])) : ''; // "DATE OF BIRTH",
                    // $data['additional_data']['object'][0]['data'][103]['values'] = ''; // DATE OF BIRTH
                    $data['additional_data']['object'][0]['data'][113]['values'] = $order->data[13]['data'] ?? ''; // PHONE NUMBER
                    $data['additional_data']['object'][0]['data'][116]['values'] = $order->data[44]['data'] ?? ''; // CITY
                    $data['additional_data']['object'][0]['data'][120]['values'] = $order->product->name; // PRODUCT NAME
                    $data['additional_data']['object'][0]['data'][175]['values'] = $order->data[9]['data'] ?? ''; // EMAIL
                    $data['additional_data']['object'][0]['data'][226]['values'] = $order->data[33]['data'] ?? ''; // KTP/ NO IDENTIFICATION
                    $data['additional_data']['object'][0]['data'][262]['values'] = date('Y-m-d', strtotime($order->transaction->created_at)) ?? ''; // DATE TRANSACTION
                    $data['additional_data']['object'][0]['data'][263]['values'] = $order->additional_data[0]['zurich_origin_name'] ?? ''; // DESTINATION
                    $data['additional_data']['object'][0]['data'][264]['values'] = $order->transaction->policy_start ?? ''; // START DATE
                    $data['additional_data']['object'][0]['data'][265]['values'] = $order->transaction->policy_end ?? ''; // END DATE
                    $data['additional_data']['object'][0]['data'][267]['values'] = $order->data[36]['data'] ?? ''; // ADDRESS
                    $data['additional_data']['object'][0]['data'][275]['values'] = $order->product->name ?? ''; // CLAIMANT
                    $data['additional_data']['object'][0]['data'][278]['values'] = $order->transaction_id ?? ''; // UNIQUE IDENTIFICATION ID
    
            
                    $data['additional_data']['object'][0]['insured'] = $order->base_price; // TODO:
                    $data['additional_data']['object'][0]['start_date'] = $order->transaction->policy_start;
                    $data['additional_data']['object'][0]['end_date'] = $order->transaction->policy_end;
                }
    
    
                $data['additional_data']['additional_cost'][0]['name'] = "0";
                $data['additional_data']['additional_cost'][0]['cost'] = "0";
                
    
                if ($momentic_prod == 55) {
                    $data['branch'] = "36";
                    $data['segment_prospect_id'] = 3;
                    $data['additional_data']['peril'][1]["id"] = "306";
                }elseif ($momentic_prod == 54) {
                    $data['branch'] = "36";
                    $data['segment_prospect_id'] = 3;
                    $data['additional_data']['peril'][1]["id"] = "305";
                }elseif ($momentic_prod == 56) {
                    $data['branch'] = "36";
                    $data['segment_prospect_id'] = 3;
                    $data['additional_data']['peril'][1]["id"] = "307";
                }elseif ($momentic_prod == 509) {
                    $data['branch'] = "36";
                    $data['segment_prospect_id'] = 3;
                    $data['additional_data']['peril'][1]["id"] = "1257";
                }elseif ($momentic_prod == 510) {
                    $data['branch'] = "36";
                    $data['segment_prospect_id'] = 3;
                    $data['additional_data']['peril'][1]["id"] = "294";
                }else {
                    $data['branch'] = "21";
                    $data['segment_prospect_id'] = 3;
                    $data['additional_data']['peril'][1]["id"] = "312";
                }
    
                $cash = $data['cashback_rate'] ?? 0;
                $data['additional_data']['peril'][1]["limit"] = "";
                $data['additional_data']['peril'][1]["value"] = $order->base_price;
                $data['additional_data']['peril'][1]["type"] = "Flat";
                $data['additional_data']['peril'][1]["discount"] =  $order->deduct;
                $data['additional_data']['peril'][1]["discount"] =  !empty($order->inquiry) ? $order->inquiry->discount : $order->deduct;
                $data['additional_data']['peril'][1]["type_brokerage"] ="Percent";
                if ($momentic_prod == 55 || $momentic_prod == 54 || $momentic_prod == 56 || $momentic_prod == 509 ||  $momentic_prod == 510) {
                    $data['additional_data']['peril'][1]["broker"] = 40-(!empty($order->inquiry) ? $order->inquiry->discount : $order->deduct); // broker untuk mikro 40 
                }else {
                    $data['additional_data']['peril'][1]["broker"] = (40-$cash)-(!empty($order->inquiry) ? $order->inquiry->discount : $order->deduct); // broker untuk mikro 40 
                }
                $data['additional_data']['co_insur'][0]["client_id"] = 159;
                $data['additional_data']['co_insur'][0]["rate"] = 100;
                $data['additional_data']['co_insur'][0]["type"] = "leader";
                
                if ($momentic_prod == 509|| $momentic_prod == 510 || $momentic_prod == 55 || $momentic_prod == 54 || $momentic_prod == 56) {
                    $data['additional_data']['co_broking'] = $cobDataFinal; // rate untuk mikro
    
                }else {
                    $data['additional_data']['co_broking'][0]['usersdata']['name'] = $order->user->name;
                    $data['additional_data']['co_broking'][0]['usersdata']['phone'] = "";
                    $data['additional_data']['co_broking'][0]['usersdata']['email'] = $order->user->email;
                    $data['additional_data']['co_broking'][0]['rate'] = 100; // rate untuk mv
                }

            $client = new Client();
            $res = $client->post(env('SPECTRUM_URL_API_PROD'),
            [
                'headers' => [
                            'Content-Type' => 'Application/Json',
                            'product-key' => $product_key,
                            'project-key' => 'f8OFNnv7XQ',
                            'authorization' => "xUlnlABcgZFo3XAbfsFF71POW"
                        
                    ],
                'body' => json_encode($data, true),
            ]);
            $order->transaction->contract_id = json_decode($res->getBody(), true)['contract_id'];
            $order->transaction->momentic_log = json_decode($res->getBody(), true);
            $order->transaction->save();
            Log::info("Spectrum Job Success!!! ".$order->transaction->contract_id);
            return 0;
    }
}
