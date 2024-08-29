<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use App\Models\InquiryMv;
use App\Models\BasePrice;
use App\Models\AdiraTransaction;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use GuzzleHttp\Client;
use App\Service\TelegramBot;
use Log;


class ResubmitJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $order;
    protected $data;
    protected $policyType;
    protected $start_date;
    protected $inquiry_id;
    public $tries = 1;
    public $timeout = 0;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order, $data, $policyType, $start_date, $inquiry_id)
    {
        $this->order = $order;
        $this->data = $data;
        $this->policyType = $policyType;
        $this->start_date = $start_date;
        $this->inquiry_id = $inquiry_id;
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
            $policyType = $this->policyType;
            $start_date = $this->start_date;
            $inquiry_id = $this->inquiry_id;

            $order_data = Order::find($order);
            $inquiry = InquiryMv::find($inquiry_id);

            $com_path = public_path("uploads/compressed");
            $min_size = 1000;
            $max_size = 20000;
            $base_rate = 196;
            
            if(!is_dir($com_path)) {
                mkdir($com_path,775,true);
            }

            $orr_img = [
                27 => "stnk_f",
                28 => "depan_f",
                29 => "belakang_f",
                30 => "kanan_f",
                31 => "kiri_f",
                55 => "dashboard_f",
                56 => "chassis_f",
                57 => "machine_f",
                61 => "polisExisting_f",
                59 => "PreExistingCondition_f",
                42 => "bastk_f",
                58 => "peneng_f",
                14 => "ktp_f"
            ];

            $fileopen = [];
            $refWidth = 1600;
            $refHeight = 1600;
            foreach($orr_img as $key => $imagename) {
                if (!empty($data[$key]['data']) && is_iterable($data[$key]['data'])) {
                    foreach ($data[$key]['data'] as $key => $newName) {
                        $getFile = public_path()."/uploads/file/".$newName; // ganti sesuai kebutuhan

                        $image = Image::make($getFile);
                        $sizeawal = $image->filesize() / 1000;

                        if($sizeawal > 1000) {
                            if($image->width() > $image->height()) {
                                $neoWidth = $refWidth;
                                $neoHeight = round(($image->height() * $refWidth) / $image->width());
                            }else {
                                $neoWidth = round(($image->width() * $refHeight) / $image->height());
                                $neoHeight = $refHeight;
                            }
                            $image->resize($neoWidth, $neoHeight);
                            if($sizeawal > 8000) {
                                $image->save($com_path."/".$newName, 70);
                            }else {
                                $image->save($com_path."/".$newName);
                            }
                        }else {
                            copy($getFile,$com_path."/".$newName);
                        }
                        $fileopen[$imagename] = fopen(public_path()."/uploads/compressed/".$newName, 'r');
                    }
                }else{
                    $fileopen[$imagename] = null;
                }
            }
            Log::critical("file open resubmit ". json_encode($fileopen));
            Log::warning("file open resubmit ". json_encode($fileopen));
            // return $data;
            
            if ($inquiry->product->comission['type'] == 'decimal') {
                $komisi = $inquiry->product->comission['value'] / $inquiry->total * 100; //-0.21621621621622
            }else{
                $komisi = $inquiry->product->comission['value'] * 100; //-0.21621621621622
            }       

            $discount = ($inquiry->total * ($inquiry->discount / 100)) ; 
            $date = date_create_from_format("Y", $inquiry->data['tahun']);
            $year = date_format($date,"Y");
            $product = $inquiry->product;  
            $price = BasePrice::where("brand",$inquiry->data['brand'])->where("name",$inquiry->data['modelstr'])->first();

            // return $inquiry->data['type'];
            if ($product->flow  == "mv" && $inquiry->data['type'] == 0 && $inquiry->data['newcar'] == 1) {
                //COMPRE NEW
                $product_adira_id = env('MV4_C_N');

            }elseif($product->flow  == "mv" &&  $inquiry->data['type'] == 0 && $inquiry->data['newcar'] == 0){
                //COMPRE OLD
                $product_adira_id = env('MV4_C_O');

            }elseif($product->flow  == "mv" && $inquiry->data['type'] == 1 && $inquiry->data['newcar'] == 1){
                //TLO NEW
                $product_adira_id = env('MV4_T_N');

            }elseif($product->flow  == "mv" && $inquiry->data['type'] == 1 && $inquiry->data['newcar'] == 0){
                //TLO OLD
                $product_adira_id = env('MV4_T_O');

            }elseif($product->flow  == "moto" && $inquiry->data['newcar'] == 0){
                //MOTO OLD
                $product_adira_id = env('MV2_T_O');

            }elseif($product->flow  == "moto" && $inquiry->data['newcar'] == 1){
                //MOTO NEW
                $product_adira_id = env('MV2_T_N');

            }

            switch ($inquiry->data['okupansi']) {
                case $inquiry->data['okupansi'] == 'KOMERSIAL':
                    $okupansi = 'OCC03';
                    break;
                
                case $inquiry->data['okupansi'] == 'PRIBADI':
                    $okupansi = 'OCC02';
                    break;
                
                case $inquiry->data['okupansi'] == 'DINAS':
                    $okupansi = 'OCC01';
                    break;
                
                default:
                    $okupansi = '';
                    break;
            }
            $adira_trx = AdiraTransaction::where("order_id",$order)->first();
            $client = new Client();
            $user = User::find($order_data->user_id);

            $start_date_fix = !empty($start_date) ? date("Y-m-d",strtotime($start_date)) : null;
            $end_date = !empty($start_date) ? date('Y-m-d',strtotime('+1 year', strtotime($start_date_fix))) : null;
            // Survey Type : 0 RIKO , 1 DIGITAL
            // if ( $data[60]['data'] == 0) {
            //     $type_survery = "On the Spot Survey";
            // }else{
            //     $type_survery = "Digital Survey";
            // }
            if ($product->flow == "mv") {
                $pa =6;
            }else{
                $pa = 12;
            }
            if(empty($data[67]['data'])) {
                $attentionAddress = $data[36]['data'];
                $sameAddressAttention = 1;
            }else {
                $attentionAddress = $data[67]['data'];
                $sameAddressAttention = 0;
            }
            $multipart = [
                [
                    'name'     => 'apiKey',
                    'contents' => env('ADIRA_KEY')
                ],
                [
                    'name'     => 'requestNumber',
                    'contents' => $order_data->adira_trx->adira_response['data']['requestNumber']
                ],
                [
                    'name'     => 'productId',
                    'contents' => $product_adira_id
                ],
                [
                    'name'     => 'startDate',
                    'contents' => $start_date_fix
                ],
                [
                    'name'     => 'endDate',
                    'contents' => $end_date
                ],
                [
                    'name'     => 'vehicleCode',
                    'contents' => $price->code
                ],
                [
                    'name'     => 'vehicleLocationCode',
                    'contents' => $inquiry->data['kode_plat']
                ],
                [
                    'name'     => 'vehicleFunctionCode',
                    'contents' => $okupansi
                ],
                [
                    'name'     => 'vehicleConditionCode',
                    'contents' => $inquiry->data['newcar'] == 1 ? "NEW" : 'USED'
                ],
                [
                    'name'     => 'vehicleLicenseNumber',
                    'contents' => !empty($data[23]['data']) ? $data[23]['data'] : ''
                ],
                [
                    'name'     => 'vehicleChassisNumber',
                    'contents' => !empty($inquiry->data['chassis']) ? $inquiry->data['chassis'] : ''
                ],
                [
                    'name'     => 'vehicleMachineNumber',
                    'contents' => !empty($data[52]['data']) ? $data[52]['data'] : ''
                ],
                [
                    'name'     => 'vehicleYear',
                    'contents' => $year
                ],
                [
                    'name'     => 'vehiclePrice',
                    'contents' => $inquiry->data['price'] //$inquiry->data['price'] - $inquiry->data['non_standard']
                ],
                [
                    'name'     => 'vehiclePreExistingCondition',
                    'contents' => ""
                ],
                [
                    'name'     => 'vehiclePreExistingCondition2',
                    'contents' => ""
                ],

                [
                    'name'     => 'additionalCoverageEnables[EQVET]',
                    'contents' => $inquiry->data['perluasan'][2] == true ? 1 : 0
                ],

                [
                    'name'     => 'additionalCoverageEnables[TSFHL]',
                    'contents' => $inquiry->data['perluasan'][1] == true ? 1 : 0
                ],

                [
                    'name'     => 'additionalCoverageEnables[SRCC]',
                    'contents' => $inquiry->data['perluasan'][3] == true ? 1 : 0
                ],

                [
                    'name'     => 'additionalCoverageEnables[TS]',
                    'contents' => $inquiry->data['perluasan'][4] == true ? 1 : 0
                ],

                [
                    'name'     => 'additionalCoverageEnables[ATPM]',
                    'contents' => !empty($inquiry->data['perluasan'][10]) && $inquiry->data['perluasan'][10] == true ? 1 : 0
                ],

                [
                    'name'     => 'additionalCoverageEnables[Autocillin Rescue]',
                    'contents' => !empty($inquiry->data['perluasan'][11]) && $inquiry->data['perluasan'][11] == true ? 1 : 0
                ],

                [
                    'name'     => 'additionalCoverageLimits[PA Driver]',
                    'contents' => !empty($inquiry->data['detail_perluasan'][5]['up']) ? $inquiry->data['detail_perluasan'][5]['up'] : 0
                ],

                [
                    'name'     => 'additionalCoverageLimits[PA Passenger]',
                    'contents' => !empty($inquiry->data['detail_perluasan'][$pa]['up']) ? $inquiry->data['detail_perluasan'][$pa]['up'] : 0
                ],

                [
                    'name'     => 'additionalCoverageQuantities[PA Passenger]',
                    'contents' => !empty($inquiry->data['detail_perluasan'][$pa]['passenger']) ? $inquiry->data['detail_perluasan'][$pa]['passenger'] : 0
                ],

                [
                    'name'     => 'additionalCoverageLimits[TPL]',
                    'contents' => !empty($inquiry->data['detail_perluasan'][7]['up']) ? $inquiry->data['detail_perluasan'][7]['up'] : 0
                ],

                [
                    'name'     => 'vehicleEquipmentNames[0]',
                    'contents' => !empty($inquiry->data['aksesoris'][1]) ? $inquiry->data['aksesoris'][1]['merk'] : null
                ],

                [
                    'name'     => 'vehicleEquipmentPrices[0]',
                    'contents' => !empty($inquiry->data['aksesoris'][1]) ? $inquiry->data['aksesoris'][1]['harga'] : null
                ],

                [
                    'name'     => 'vehicleEquipmentQuantities[0]',
                    'contents' => !empty($inquiry->data['aksesoris'][1]) ? $inquiry->data['aksesoris'][1]['qty'] : null
                ],

                [
                    'name'     => 'vehicleEquipmentNames[1]',
                    'contents' => !empty($inquiry->data['aksesoris'][2]) ? $inquiry->data['aksesoris'][2]['merk'] : null
                ],

                [
                    'name'     => 'vehicleEquipmentPrices[1]',
                    'contents' => !empty($inquiry->data['aksesoris'][2]) ? $inquiry->data['aksesoris'][2]['harga'] : null
                ],

                [
                    'name'     => 'vehicleEquipmentQuantities[1]',
                    'contents' => !empty($inquiry->data['aksesoris'][2]) ? $inquiry->data['aksesoris'][2]['qty'] : null
                ],
                [
                    'name'     => 'vehicleEquipmentNames[2]',
                    'contents' => !empty($inquiry->data['aksesoris'][3]) ? $inquiry->data['aksesoris'][3]['merk'] : null
                ],
                [
                    'name'     => 'vehicleEquipmentPrices[2]',
                    'contents' => !empty($inquiry->data['aksesoris'][3]) ? $inquiry->data['aksesoris'][3]['harga'] : null
                ],
                [
                    'name'     => 'vehicleEquipmentQuantities[2]',
                    'contents' => !empty($inquiry->data['aksesoris'][3]) ? $inquiry->data['aksesoris'][3]['qty'] : null
                ],
                [
                    'name'     => 'vehicleEquipmentNames[3]',
                    'contents' => !empty($inquiry->data['aksesoris'][4]) ? $inquiry->data['aksesoris'][4]['merk'] : null
                ],
                [
                    'name'     => 'vehicleEquipmentPrices[3]',
                    'contents' => !empty($inquiry->data['aksesoris'][4]) ? $inquiry->data['aksesoris'][4]['harga'] : null
                ],
                [
                    'name'     => 'vehicleEquipmentQuantities[3]',
                    'contents' => !empty($inquiry->data['aksesoris'][4]) ? $inquiry->data['aksesoris'][4]['qty'] : null
                ],
                [
                    'name'     => 'vehicleEquipmentNames[4]',
                    'contents' => !empty($inquiry->data['aksesoris'][5]) ? $inquiry->data['aksesoris'][5]['merk'] : null
                ],
                [
                    'name'     => 'vehicleEquipmentPrices[4]',
                    'contents' => !empty($inquiry->data['aksesoris'][5]) ? $inquiry->data['aksesoris'][5]['harga'] : null
                ],
                [
                    'name'     => 'vehicleEquipmentQuantities[4]',
                    'contents' => !empty($inquiry->data['aksesoris'][5]) ? $inquiry->data['aksesoris'][5]['qty'] : null
                ],
                [
                    'name'     => 'personalIdType',
                    'contents' => "KTP"
                ],
                [
                    'name'     => 'personalIdNumber',
                    'contents' => !empty($data[33]['data']) ? $data[33]['data'] : ''
                ],
                [
                    'name'     => 'name',
                    'contents' => !empty($data[1]['data']) ? $data[1]['data'] : ''
                ],
                [
                    'name'     => 'email',
                    'contents' =>  !empty($data[9]['data']) ? $data[9]['data'] : ''
                ],
                [
                    'name'     => 'mobileNumber',
                    'contents' => !empty($data[13]['data']) ? $data[13]['data'] : ''
                ],
                [
                    'name'     => 'address',
                    'contents' => !empty($data[36]['data']) ? $data[36]['data'] : ''
                ],
                [
                    'name'     => 'provinceCode',
                    'contents' => !empty($data[54]['data']) ? $data[54]['data'] : ''
                ],
                [
                    'name'     => 'cityCode',
                    'contents' => !empty($data[53]['data']) ? $data[53]['data'] : ''
                ],
                [
                    'name'     => 'sendHardCopyPolicy',
                    'contents' => !empty($inquiry->data['policy_type']) && $inquiry->data['policy_type'] == 'hard' ? 1 : 0
                ],
                [
                    'name'     => 'sameHardCopyDeliveryAddress',
                    'contents' => $sameAddressAttention
                ],
                [
                    'name'     => 'attentionName',
                    'contents' => !empty( $data[1]['data']) ? $data[1]['data'] : ''
                ],
                [
                    'name'     => 'attentionMobileNumber',
                    'contents' => !empty($data[13]['data']) ? $data[13]['data'] : ''
                ],
                [
                    'name'     => 'attentionEmail',
                    'contents' => !empty($data[9]['data']) ? $data[9]['data'] : ''
                ],
                [
                    'name'     => 'attentionAddress',
                    'contents' => $attentionAddress
                ],
                [
                    'name'     => 'attentionProvinceCode',
                    'contents' => !empty($data[54]['data']) ? $data[54]['data'] : ''
                ],
                [
                    'name'     => 'attentionCityCode',
                    'contents' => !empty($data[53]['data']) ? $data[53]['data'] : ''
                ],
                [
                    'name'     => 'sendEmail',
                    'contents' => !empty($inquiry->data['policy_type']) && $inquiry->data['policy_type'] == 'soft' ? 1 : 0
                ],
                [
                    'name'     => 'documentFiles[Personal ID]',
                    'contents' => $fileopen["ktp_f"]
                ],
                [
                    'name'     => 'documentFiles[STNK]',
                    'contents' => $fileopen["stnk_f"],
                ],
                [
                    'name'     => 'documentFiles[BASTK]',
                    'contents' => $fileopen["bastk_f"]
                ],
                [
                    'name'     => 'documentFiles[Policy Existing]',
                    'contents' => $fileopen["polisExisting_f"]
                ],
                [
                    'name'     => 'documentFiles[Other]',
                    'contents' => ""
                ],
                [
                    'name'     => 'documentFiles[Car Front]',
                    'contents' => $fileopen["depan_f"]
                ],
                [
                    'name'     => 'documentFiles[Car Back]',
                    'contents' => $fileopen["belakang_f"]
                ],
                [
                    'name'     => 'documentFiles[Car Left]',
                    'contents' => $fileopen["kiri_f"]
                ],
                [
                    'name'     => 'documentFiles[Car Right]',
                    'contents' => $fileopen["kanan_f"]
                ],
                [
                    'name'     => 'documentFiles[Car Dashboard]',
                    'contents' => $fileopen["dashboard_f"]
                ],
                [
                    'name'     => 'documentFiles[Car Chassis]',
                    'contents' => $fileopen["chassis_f"]
                ],
                [
                    'name'     => 'documentFiles[Car Machine]',
                    'contents' => $fileopen["machine_f"]
                ],
                [
                    'name'     => 'documentFiles[Car Peneng]',
                    'contents' => $fileopen["peneng_f"]
                ],
                [
                    'name'     => 'documentFiles[Car Pre Existing Condition]',
                    'contents' => $fileopen["PreExistingCondition_f"]
                ],
                [
                    'name'     => 'documentFiles[Car Other 1]',
                    'contents' => ""
                ],
                [
                    'name'     => 'documentFiles[Car Other 2]',
                    'contents' => ""
                ],
                [
                    'name'     => 'documentFiles[Car Other 3]',
                    'contents' => ""
                ],
                [
                    'name'     => 'discountType',
                    'contents' => "Percent"
                ],
                [
                    'name'     => 'discountPercentage',
                    'contents' => $inquiry->discount
                ],
                [
                    'name'     => 'discountFixed',
                    'contents' => floor($discount)
                ],
                [
                    'name'     => 'subjectToNoClaimDate',
                    'contents' => ""
                ],
                [
                    'name'     => 'referenceInfo',
                    'contents' => $adira_trx->ref_number ?? "ZAP-".(string)$order.(string)date("dmY")
                ],
                [
                    'name'     => 'surveyType',
                    'contents' => "Digital Survey"
                ],
                [
                    'name'     => 'remark1',
                    'contents' => !empty($order_data->additional_data[0]["rem1"]) ? !empty($order_data->additional_data[0]["rem1"]) : "REM1"
                ],
                [
                    'name'     => 'remark2',
                    'contents' => !empty($data[70]['data']) ? $data[70]['data'] : 'REM2'
                ],
                [
                    'name'     => 'remark3',
                    'contents' => !empty($user->partner) ? $user->partner->mo : ''
                ],
                [
                    'name'     => 'commissionPercentage',
                    'contents' => 25 - $inquiry->discount
                ],
            ];
            Log::warning("multi part  1 resubmit ". json_encode($multipart));
            Log::critical("multi part  1 resubmit ". json_encode($multipart));
            $result = $client->post(env('ADIRA_ORDER_URL').'api/v1/resubmit?apiKey='.env('ADIRA_KEY'), 
            [
                'verify' => false,
                'multipart' => $multipart
            ]);
            for ($i=57; $i <= 73; $i++) { 
                unset($multipart[$i]);
            }
            $json_adira = json_decode($result->getBody(),true);
            Log::warning("multi part  2 resubmit ". json_encode($multipart));
            Log::critical("multi part  2 resubmit ". json_encode($multipart));
            $adira_trx->log_api = $multipart;
            if($json_adira['status'] !== 'fail' && !empty($json_adira['status'])) {
                error_log("ADA STATUS / SUCCESS");
                $adira_trx->resubmit_response = $json_adira;
                $adira_trx->status = 'success';
                $adira_trx->save();
            }else {
                error_log("GAADA STATUS / FAILED");
                $adira_trx->resubmit_response = $json_adira;
                $adira_trx->status = 'success';
                $adira_trx->save();

                $textmo = $adira_trx?->order?->user?->partner?->mo ?? null;
                $text = "Error Zap3 Production \n"
                    . "Job : ResubmitJob \n"
                    . "Order ID : $adira_trx->order_id \n"
                    . "MO : $textmo \n"
                    . "Message : ".json_encode($json_adira) ?? '-';
                throw new \Exception($text, 1);

            }
            $adira_trx->save();

            $order_data->status = 0;
            $order_data->save();


            // $id_data = [27,28,29,30,31,55,56,57,61,59,42,58,14];
            // foreach ($id_data as $key => $value) {
            //     if (!empty($data[$value]['data'])) {
            //         foreach ($data[$value]['data'] as $key => $hapus) {
            //             $image_path = public_path("uploads/compressed/".$hapus);
            //             if (file_exists($image_path)) {
            //                 // File::delete($image_path);
            //                 unlink($image_path);
            //             }
            //         }
            //     }
            // }
        } catch (\Exception $th) {
            throw $th;
        }
    }

    public function failed(\Exception $e)
    {
        $text = $e->getMessage();
        TelegramBot::message($text);
        throw new \Exception($e->getMessage(), 1);
    }
}
