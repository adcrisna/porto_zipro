<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use App\Models\User;
use App\Models\Orscheme;
use App\Models\Comission;
use App\Models\AdiraComission;
use App\Models\OrComission;
use App\Models\ProductReferral;
use App\Models\SchemaRefComission;
use App\Mail\PaymentMail;
use GuzzleHttp\Client as Guzzle;
use Log;
use Exception;

class ComissionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $order_id;
    public $tries = 1;

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
        try {
            $sf = null;
            $order_id = $this->order_id;
            $order = Order::find($order_id);
            $agent = Orscheme::select("user_id","upline_id")->where('user_id',$order->user_id)->first();
            $sf = ProductReferral::where('cart_id',$order->cart_id)->first();

            Log::warning('data or_comission '.$agent);
            Log::warning('data sf '. $sf);
            $json = $order->product->schemaComission->or_comission;
            // 394
            Log::warning("welcome to comission");
            // $discount = $order->deduct;
            if($order->product->flow == "mv" || $order->product->flow == "moto" ){

                $fix_dc = !empty($order->inquiry) ? $order->inquiry->discount : 0;
                //ambil discount di data inquiry
                $discount = $order->base_price * ($fix_dc / 100);
                $bagiComs = $order->base_price * ((40 - $fix_dc) / 100) ;
                if (!empty($sf)) {
                    $share_com = $bagiComs * 0.5;
                }else{
                    $share_com = $bagiComs;
                }
                //komisi yang dibagi2
                Log::warning("total 40% comission mv = ". $bagiComs);
                $fix_total = $order->base_price * (40/ 100);
                Log::warning("total share comission mv = ". $share_com);
                $adiraCommission = new AdiraComission;
        
                $formula =  $order->product->schemaComission->comission;
                if($formula['type'] == 'percent'){
                    $coms = $share_com * $formula['value']; 
                }else if($formula['type'] == 'decimal'){    
                    $coms = $formula['value'];
                }

                $sfComissionMv =  $order->product->schemaRefComission;
                Log::warning('data skema comission sf mv'. $sfComissionMv);
                $sfComs = $share_com * $sfComissionMv->comission['sf']['value']; 
                $sisaRefComs = 1 - $sfComissionMv->comission['ref']['value'];
                Log::warning('sisa percent comission sf ref 2 mv '. $sisaRefComs);
                $refComs1 = $share_com * $sfComissionMv->comission['ref']['value'];
                $refComs2 = $share_com * $sisaRefComs;

                Log::warning("total comission mv = ". $coms);

                $adiraCommission->transaction_id = $order->transaction_id;
                $adiraCommission->comission = $fix_total;
                $adiraCommission->discount = $discount;
                $adiraCommission->share_comission = $fix_total - $discount;
                // return $adiraCommission;
                $adiraCommission->save();
        
                $comission = new Comission;
                $comission->trx_id = $order->transaction_id;
                $comission->username = $order->user->name;
                $comission->useremail = $order->user->email;
                $comission->user_id = $order->user_id;
                $comission->formula = $formula;
                $comission->base_price = $order->base_price;
                $comission->comission = $coms;
                $comission->status = "unpaid";
                $comission->save();

                if (!empty($sf)) {
                    Log::warning("masuk sf mv");
                    $agentSf = User::find($sf->user_id);
                    $nextAgentSf = User::where('email',$agentSf->referrer_email)->first();
                    Log::warning("cek agenSF MV ".$agentSf);
                    Log::warning("cek nextAgentSf MV ".$nextAgentSf);

                    if (!empty($nextAgentSf) && $nextAgentSf->is_sf != 0) {
                        Log::warning("masuk 2 sf");
                        $newSfComission = new Comission;
                        $newSfComission->trx_id = $order->transaction_id;
                        $newSfComission->username = $order->user->name;
                        $newSfComission->useremail = $order->user->email;
                        $newSfComission->user_id = $sf->user_id;
                        $newSfComission->formula = $sfComissionMv->comission;
                        $newSfComission->base_price = $order->base_price;
                        $newSfComission->comission = $refComs1;
                        $newSfComission->status = "unpaid";
                        $newSfComission->save();

                        $newSfComission2 = new Comission;
                        $newSfComission2->trx_id = $order->transaction_id;
                        $newSfComission2->username = $order->user->name;
                        $newSfComission2->useremail = $order->user->email;
                        $newSfComission2->user_id = $nextAgentSf->id;
                        $newSfComission2->formula = $sfComissionMv->comission;
                        $newSfComission2->base_price = $order->base_price;
                        $newSfComission2->comission = $refComs2;
                        $newSfComission2->status = "unpaid";
                        $newSfComission2->save();

                        Log::warning("total comission Ref sf MV 1 = ". $refComs1);
                        Log::warning("total comission Ref sf MV 2 = ". $refComs2);
                    } else {
                        $newSfComission = new Comission;
                        $newSfComission->trx_id = $order->transaction_id;
                        $newSfComission->username = $order->user->name;
                        $newSfComission->useremail = $order->user->email;
                        $newSfComission->user_id = $sf->user_id;
                        $newSfComission->formula = $sfComissionMv->comission;
                        $newSfComission->base_price = $order->base_price;
                        $newSfComission->comission = $sfComs;
                        $newSfComission->status = "unpaid";
                        $newSfComission->save();

                        Log::warning("total comission sf MV = ". $sfComs);
                        Log::warning("masuk 1 sf MV");
                    }
                    

                    Log::warning("masuk comission sf MV");
                }

                Log::warning("masuk comission MV");
                if(isset($agent->upline_id) && $agent->upline_id != null){
                    if($json[0]['type'] == 'percent'){
                        $coms = $share_com * $json[0]['value']; 
                    }else if($json[0]['type'] == 'decimal'){    
                        $coms = $json[0]['value'];
                    }
                    $upline = $agent->upline_id;

                    Log::warning("total or comission MV = ". $coms);

                    $orcom = new OrComission;
                    $orcom->trx_id = $order->transaction_id;
                    $orcom->from_id = $order->user_id;
                    $orcom->to_id = $agent->upline_id;
                    $orcom->formula = $json[0];
                    $orcom->layer = 1;
                    $orcom->base_price = $order->base_price;
                    $orcom->comission = $coms;
                    $orcom->status= "unpaid";
                    $orcom->save();
                    Log::warning("masuk or comission mv ");
                    //$up[] = array("upline_id" => $upline, "commision" => $json[0]);
                    $i = 1;
        
                    while($upline !== null){
                    $anc =  Orscheme::select("user_id","upline_id")->where('user_id',$upline)->first();
        
                    if(isset($anc->upline_id) && $anc->upline_id != null){
                        $coms = 0;
                        if(isset($json[$i])){
                        if($json[$i]['type'] == 'percent'){
                            $coms = $share_com * $json[$i]['value']; 
                        }else if($json[$i]['type'] == 'decimal'){    
                            $coms = $json[$i]['value'];
                        }else{
        
                        }
                        }else{
                            $coms = 0;
                        }
                        $orcom = new OrComission;
                        $orcom->trx_id = $order->transaction_id;
                        $orcom->from_id = $anc->user_id;
                        $orcom->to_id = $anc->upline_id;
                        $orcom->formula = isset($json[$i])?$json[$i]:null ;
                        $orcom->layer = $i+1;
                        $orcom->base_price = $order->base_price;
                        $orcom->comission = $coms;
                        $orcom->status= "unpaid";
                        $orcom->save();

                        Log::warning("total or comission MV  = ". $coms);
                        Log::warning("masuk or comission MV 1/2");
                        //$up[] = array("upline_id" => $anc->upline_id, "commision" => isset($json[$i]) ? $json[$i]:"no layer");
                        $upline = $anc->upline_id;


                        $i++;
                    }else{
                        $upline = null;
                            }
                        }
                }
            }elseif ($order->product->adira_product_id == "ZTI") {
                $fix_dc = !empty($order->inquiry) ? $order->inquiry->discount : 0;
                $discount = $order->base_price * ($fix_dc / 100);
                //return (40 - $fix_dc / 100);
                $bagiComs = $order->base_price * ((30 - $fix_dc) / 100) ;
                if (!empty($sf)) {
                    $share_com = $bagiComs * 0.5;
                }else{
                    $share_com = $bagiComs;
                }
                //komisi yang dibagi2
                Log::warning("total 30% comission ZTI = ". $bagiComs);
                $fix_total = $order->base_price * (30/ 100) ;
        
                $adiraCommission = new AdiraComission;
                Log::warning("total disc = ". $fix_dc);
                Log::warning("total share comission ZTI = ". $share_com);

                $formula =  $order->product->schemaComission->comission;
                if($formula['type'] == 'percent'){
                    $coms = $share_com * $formula['value']; 
                }else if($formula['type'] == 'decimal'){    
                    $coms = $formula['value'];
                }

                $sfComissionZTI = $order->product->schemaRefComission;
                Log::warning('data skema comission sf ZTI'. $sfComissionZTI);
                $sfComs = $share_com * $sfComissionZTI->comission['sf']['value']; 
                $sisaRefComs = 1 - $sfComissionZTI->comission['ref']['value'];
                Log::warning('sisa percent comission sf ref 2 ZTI '. $sisaRefComs);
                $refComs1 = $share_com * $sfComissionZTI->comission['ref']['value'];
                $refComs2 = $share_com * $sisaRefComs;

                Log::warning("total comission ZTI = ". $coms);

                $adiraCommission->transaction_id = $order->transaction_id;
                $adiraCommission->comission = $fix_total;
                $adiraCommission->discount = $discount;
                $adiraCommission->share_comission = $fix_total - $discount;
                // return $adiraCommission;
                $adiraCommission->save();
        
                $comission = new Comission;
                $comission->trx_id = $order->transaction_id;
                $comission->username = $order->user->name;
                $comission->useremail = $order->user->email;
                $comission->user_id = $order->user_id;
                $comission->formula = $formula;
                $comission->base_price = $order->base_price;
                $comission->comission = $coms;
                $comission->status = "unpaid";
                $comission->save();

                if (!empty($sf)) {
                    Log::warning("masuk sf ZTI");
                    $agentSf = User::find($sf->user_id);
                    $nextAgentSf = User::where('email',$agentSf->referrer_email)->first();
                    Log::warning("cek agenSF ZTI ".$agentSf);
                    Log::warning("cek nextAgentSf ZTI ".$agentSf);

                    if (!empty($nextAgentSf) && $nextAgentSf->is_sf != 0) {
                        $newSfComission = new Comission;
                        $newSfComission->trx_id = $order->transaction_id;
                        $newSfComission->username = $order->user->name;
                        $newSfComission->useremail = $order->user->email;
                        $newSfComission->user_id = $sf->user_id;
                        $newSfComission->formula = $sfComissionZTI->comission;
                        $newSfComission->base_price = $order->base_price;
                        $newSfComission->comission = $refComs1;
                        $newSfComission->status = "unpaid";
                        $newSfComission->save();

                        $newSfComission2 = new Comission;
                        $newSfComission2->trx_id = $order->transaction_id;
                        $newSfComission2->username = $order->user->name;
                        $newSfComission2->useremail = $order->user->email;
                        $newSfComission2->user_id = $nextAgentSf->user_id;
                        $newSfComission2->formula = $sfComissionZTI->comission;
                        $newSfComission2->base_price = $order->base_price;
                        $newSfComission2->comission = $refComs2;
                        $newSfComission2->status = "unpaid";
                        $newSfComission2->save();

                        Log::warning("total comission Ref sf ZTI 1 = ". $refComs1);
                        Log::warning("total comission Ref sf ZTI 2 = ". $refComs2);
                        Log::warning("masuk 2 sf");
                    } else {
                        $newSfComission = new Comission;
                        $newSfComission->trx_id = $order->transaction_id;
                        $newSfComission->username = $order->user->name;
                        $newSfComission->useremail = $order->user->email;
                        $newSfComission->user_id = $sf->user_id;
                        $newSfComission->formula = $sfComissionZTI->comission;
                        $newSfComission->base_price = $order->base_price;
                        $newSfComission->comission = $sfComs;
                        $newSfComission->status = "unpaid";
                        $newSfComission->save();

                        Log::warning("total comission sf ZTI = ". $sfComs);
                        Log::warning("masuk 1 sf ZTI");
                    }
                    

                    Log::warning("masuk comission sf ZTI");
                }

                Log::warning("masuk comission ZTI");
                if(isset($agent->upline_id) && $agent->upline_id != null){
                    if($json[0]['type'] == 'percent'){
                        $coms = $share_com * $json[0]['value']; 
                    }else if($json[0]['type'] == 'decimal'){    
                        $coms = $json[0]['value'];
                    }
                    $upline = $agent->upline_id;

                    Log::warning("total or comission ZTI = ". $coms);

                    $orcom = new OrComission;
                    $orcom->trx_id = $order->transaction_id;
                    $orcom->from_id = $order->user_id;
                    $orcom->to_id = $agent->upline_id;
                    $orcom->formula = $json[0];
                    $orcom->layer = 1;
                    $orcom->base_price = $order->base_price;
                    $orcom->comission = $coms;
                    $orcom->status= "unpaid";
                    $orcom->save();
                    Log::warning("masuk or comission ZTI 2/1");
                    //$up[] = array("upline_id" => $upline, "commision" => $json[0]);
                    $i = 1;
        
                    while($upline !== null){
                    $anc =  Orscheme::select("user_id","upline_id")->where('user_id',$upline)->first();
        
                    if(isset($anc->upline_id) && $anc->upline_id != null){
                        $coms = 0;
                        if(isset($json[$i])){
                        if($json[$i]['type'] == 'percent'){
                            $coms = $share_com * $json[$i]['value']; 
                        }else if($json[$i]['type'] == 'decimal'){    
                            $coms = $json[$i]['value'];
                        }else{
        
                        }
                        }else{
                            $coms = 0;
                        }
                        $orcom = new OrComission;
                        $orcom->trx_id = $order->transaction_id;
                        $orcom->from_id = $anc->user_id;
                        $orcom->to_id = $anc->upline_id;
                        $orcom->formula = isset($json[$i])?$json[$i]:null ;
                        $orcom->layer = $i+1;
                        $orcom->base_price = $order->base_price;
                        $orcom->comission = $coms;
                        $orcom->status= "unpaid";
                        $orcom->save();

                        Log::warning("total or comission ZTI = ". $coms);
                        Log::warning("masuk or comission ZTI 2/2");
                        //$up[] = array("upline_id" => $anc->upline_id, "commision" => isset($json[$i]) ? $json[$i]:"no layer");
                        $upline = $anc->upline_id;


                        $i++;
                    }else{
                        $upline = null;
                            }
                        }
                }
            } else {

                $fix_dc =  0;
                $discount = $order->base_price * ($fix_dc / 100);
                //return (40 - $fix_dc / 100);
                $bagiComs = $order->base_price * ((25 - $fix_dc) / 100) ;
                if (!empty($sf)) {
                    $share_com = $bagiComs * 0.5;
                }else{
                    $share_com = $bagiComs;
                }
                //komisi yang dibagi2
                Log::warning("total 25% comission Micro = ". $bagiComs);
                $fix_total = $order->base_price * (25/ 100) ;

                $adiraCommission = new AdiraComission;
                
                Log::warning("total share comission Micro = ". $share_com);

                $formula =  $order->product->schemaComission->comission;
                if($formula['type'] == 'percent'){
                    $coms = $share_com * $formula['value']; 
                }else if($formula['type'] == 'decimal'){    
                    $coms = $formula['value'];
                }
                
                $sfComissionMicro = $order->product->schemaRefComission;
                Log::warning('data skema comission sf Micro'. $sfComissionMicro);
                $sfComs = $share_com * $sfComissionMicro->comission['sf']['value']; 
                $sisaRefComs = 1 - $sfComissionMicro->comission['ref']['value'];
                Log::warning('sisa percent comission sf ref 2 micro '. $sisaRefComs);
                $refComs1 = $share_com * $sfComissionMicro->comission['ref']['value'];
                $refComs2 = $share_com * $sisaRefComs;
                
                Log::warning("total comission Micro = ". $coms);

                $adiraCommission->transaction_id = $order->transaction_id;
                $adiraCommission->comission = $fix_total;
                $adiraCommission->discount = $discount;
                $adiraCommission->share_comission = $fix_total - $discount;
                // return $adiraCommission;
                $adiraCommission->save();
        
                $comission = new Comission;
                $comission->trx_id = $order->transaction_id;
                $comission->username = $order->user->name;
                $comission->useremail = $order->user->email;
                $comission->user_id = $order->user_id;
                $comission->formula = $formula;
                $comission->base_price = $order->base_price;
                $comission->comission = $coms;
                $comission->status = "unpaid";
                $comission->save();

                if (!empty($sf)) {
                    Log::warning("masuk sf Micro");
                    $agentSf = User::find($sf->user_id);
                    $nextAgentSf = User::where('email',$agentSf->referrer_email)->first();
                    Log::warning("cek agenSF MICRO ".$agentSf);
                    Log::warning("cek nextAgentSf MICRO ".$agentSf);

                    if (!empty($nextAgentSf) && $nextAgentSf->is_sf != 0) {
                        $newSfComission = new Comission;
                        $newSfComission->trx_id = $order->transaction_id;
                        $newSfComission->username = $order->user->name;
                        $newSfComission->useremail = $order->user->email;
                        $newSfComission->user_id = $sf->user_id;
                        $newSfComission->formula = $sfComissionMicro->comission;
                        $newSfComission->base_price = $order->base_price;
                        $newSfComission->comission = $refComs1;
                        $newSfComission->status = "unpaid";
                        $newSfComission->save();

                        $newSfComission2 = new Comission;
                        $newSfComission2->trx_id = $order->transaction_id;
                        $newSfComission2->username = $order->user->name;
                        $newSfComission2->useremail = $order->user->email;
                        $newSfComission2->user_id = $nextAgentSf->user_id;
                        $newSfComission2->formula = $sfComissionMicro->comission;
                        $newSfComission2->base_price = $order->base_price;
                        $newSfComission2->comission = $refComs2;
                        $newSfComission2->status = "unpaid";
                        $newSfComission2->save();

                        Log::warning("total comission Ref sf MICRO 1= ". $refComs1);
                        Log::warning("total comission Ref sf MICRO 2= ". $refComs2);
                        Log::warning("masuk 2 sf");
                    } else {
                        $newSfComission = new Comission;
                        $newSfComission->trx_id = $order->transaction_id;
                        $newSfComission->username = $order->user->name;
                        $newSfComission->useremail = $order->user->email;
                        $newSfComission->user_id = $sf->user_id;
                        $newSfComission->formula = $sfComissionMicro->comission;
                        $newSfComission->base_price = $order->base_price;
                        $newSfComission->comission = $sfComs;
                        $newSfComission->status = "unpaid";
                        $newSfComission->save();

                        Log::warning("total comission sf MICRO = ". $sfComs);
                        Log::warning("masuk 1 sf MICRO");
                    }
                    

                    Log::warning("masuk comission sf MICRO");
                }

                Log::warning("masuk comission Micro");
                if(isset($agent->upline_id) && $agent->upline_id != null){
                    if($json[0]['type'] == 'percent'){
                        $coms = $share_com * $json[0]['value']; 
                    }else if($json[0]['type'] == 'decimal'){    
                        $coms = $json[0]['value'];
                    }
                    $upline = $agent->upline_id;

                    Log::warning("total or comission Micro = ". $coms);

                    $orcom = new OrComission;
                    $orcom->trx_id = $order->transaction_id;
                    $orcom->from_id = $order->user_id;
                    $orcom->to_id = $agent->upline_id;
                    $orcom->formula = $json[0];
                    $orcom->layer = 1;
                    $orcom->base_price = $order->base_price;
                    $orcom->comission = $coms;
                    $orcom->status = "unpaid";
                    $orcom->save();

                    Log::warning("masuk or comission Micro 3/1");
                    //$up[] = array("upline_id" => $upline, "commision" => $json[0]);
                    $i = 1;
        
                    while($upline !== null){
                    $anc =  Orscheme::select("user_id","upline_id")->where('user_id',$upline)->first();
        
                    if(isset($anc->upline_id) && $anc->upline_id != null){
                        $coms = 0;
                        if(isset($json[$i])){
                            if($json[$i]['type'] == 'percent'){
                                $coms = $fix_total * $json[$i]['value']; 
                            }else if($json[$i]['type'] == 'decimal'){    
                                $coms = $json[$i]['value'];
                            }
                        }else{
                            $coms = 0;
                        }
                        $orcom = new OrComission;
                        $orcom->trx_id = $order->transaction_id;
                        $orcom->from_id = $anc->user_id;
                        $orcom->to_id = $anc->upline_id;
                        $orcom->formula = isset($json[$i])?$json[$i]:null ;
                        $orcom->layer = $i+1;
                        $orcom->base_price = $order->base_price;
                        $orcom->comission = $coms;
                        $orcom->status= "unpaid";
                        $orcom->save();

                        Log::warning("total or comission Micro 3/2 = ". $coms);
                        Log::warning("masuk or comission Micro 3/2");
                        //$up[] = array("upline_id" => $anc->upline_id, "commision" => isset($json[$i]) ? $json[$i]:"no layer");
                        $upline = $anc->upline_id;
                        $i++;
                    }else{
                        $upline = null;
                            }
                        }
                }
            }
        } catch (\Exception $e) {

            throw $e;
        }
    }

    public function failed(\Exception $e)
    {
        $text = "Error Zap3 \n"
            . "Job : ComissionJob \n"
            . "Order ID : ".$this->order_id." \n"
            . "Message : ".$e->getMessage();
        throw new Exception($e->getMessage(), 1);
    }
}
