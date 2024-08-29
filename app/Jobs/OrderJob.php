<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\InquiryMv;
use App\Models\Order;
use App\Models\User;
use App\Models\AdiraTransaction;
use App\Models\BasePrice;
use App\Models\Product;
use GuzzleHttp\Client;
use App\Models\Renewal;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Models\SchemaComission;
use Intervention\Image\ImageManagerStatic as Image;
use Log;
use Exception;
use App\Service\TelegramBot;


class OrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;
    protected $inquiry;
    protected $product;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order, InquiryMv $inquiry, $product)
    {
        $this->order = $order;
        $this->inquiry = $inquiry;
        $this->product = $product;
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
            $inquiry = $this->inquiry;
            $product = $this->product;
            $body = [];
            
            $data = $order->data;
            $renew = Renewal::where('order_id', $order->id)->orWhere('new_order_id', $order->id)->first();
            $body['renew'] = $renew;
            $com_path = public_path("uploads/compressed");
            $min_size = 1000;
            $max_size = 20000;
            $base_rate = 196;

            if (!is_dir($com_path)) {
                mkdir($com_path, 775, true);
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
            foreach ($orr_img as $key => $imagename) {
                if (!empty($data[$key]['data']) && $data[$key]['data'] !== "null") {
                    // foreach ($data[$key]['data'] as $key => $newName) {
                    $newName = $data[$key]['data'];
                    $getFile = public_path() . "/uploads/file/" . $newName;
                    
                    $image = Image::make($getFile);
                    $sizeawal = $image->filesize() / 1000;
                    
                    if ($sizeawal > 1000) {
                        if ($image->width() > $image->height()) {
                            $neoWidth = $refWidth;
                            $neoHeight = round(($image->height() * $refWidth) / $image->width());
                        } else {
                            $neoWidth = round(($image->width() * $refHeight) / $image->height());
                            $neoHeight = $refHeight;
                        }
                        $image->resize($neoWidth, $neoHeight);
                        if ($sizeawal > 8000) {
                            $image->save($com_path . "/" . $newName, 70);
                        } else {
                            $image->save($com_path . "/" . $newName);
                        }
                    } else {
                        copy($getFile, $com_path . "/" . $newName);
                    }
                    $fileopen[$imagename] = fopen(public_path() . "/uploads/compressed/" . $newName, 'r');
                    // }
                } else {
                    $fileopen[$imagename] = null;
                }
            }
            
            
            $product = Product::find($order['product_id']);
            $schema = SchemaComission::find($product->schema_id);

            if ($schema['comission']['type'] == 'decimal') {
                $komisi = $schema['comission']['value'] / $inquiry->total * 100;
            } else {
                $komisi = $schema['comission']['value'] * 100;
            }

            $body['car_code'] = BasePrice::where("brand", $inquiry->data['brand'])
                ->where("name", $inquiry->data['modelstr'])
                ->first()->code ?? null;

            // [
            //     "text" => "DINAS",
            //     "value" => "OFFICIAL"
            // ],
            // [
            //     "text" => "PRIBADI",
            //     "value" => "PERSONAL"
            // ],
            // [
            //     "text" => "KOMERSIAL",
            //     "value" => "COMMERCIAL"
            // ],

            switch ($inquiry->data['okupansi']) {
                case $inquiry->data['okupansi'] == 'KOMERSIAL':
                    $body['okupansi'] = 'OCC03';
                    break;

                case $inquiry->data['okupansi'] == 'PRIBADI':
                    $body['okupansi'] = 'OCC02';
                    break;

                case $inquiry->data['okupansi'] == 'DINAS':
                    $body['okupansi'] = 'OCC01';
                    break;

                case $inquiry->data['okupansi'] == 'COMMERCIAL':
                    $body['okupansi'] = 'OCC03';
                    break;

                case $inquiry->data['okupansi'] == 'PERSONAL':
                    $body['okupansi'] = 'OCC02';
                    break;

                case $inquiry->data['okupansi'] == 'OFFICIAL':
                    $body['okupansi'] = 'OCC01';
                    break;
                    
                default:
                    $body['okupansi'] = '';
                    break;
            }
            // Log::warning("sini dek");
            $user = User::find($order->user_id);
            $body['start_date'] = !empty($order->start_date) ? date("Y-m-d", strtotime($order->start_date)) : null;
            $body['end_date'] = !empty($order->start_date) ? date('Y-m-d', strtotime('+1 year', strtotime($body['start_date']))) : null;

            if ($product['flow'] == "mv") {
                $pa = 6;
            } else {
                $pa = 12;
            }

            if (empty($data[67]['data'])) {
                $body['attention_address'] = $data[36]['data'] ?? null;
                $body['same_address'] = 1;
            } else {
                $body['attention_address'] = $data[67]['data'] ?? null;
                $body['same_address'] = 0;
            }

            $body['renewal_pdf'] = null;
            if (!empty($renew)) {
                $body['renewal_pdf'] = fopen(public_path() . "/uploads/renewal/" . $renew->data[148], 'r');
            }
            
            $body['discount'] = ($inquiry->total * ($inquiry->discount / 100));
            $date = date_create_from_format("Y", $inquiry->data['tahun']);
            $body['year'] = date_format($date, "Y");

            if ($product['flow']  == "mv" && $inquiry->data['type'] == 0 && $inquiry->data['newcar'] == 1) {
                //COMPRE NEW
                $body['product_adira_id'] = env('MV4_C_N');
            } elseif ($product['flow']  == "mv" &&  $inquiry->data['type'] == 0 && $inquiry->data['newcar'] == 0) {
                //COMPRE OLD
                $body['product_adira_id'] = env('MV4_C_O');
            } elseif ($product['flow']  == "mv" && $inquiry->data['type'] == 1 && $inquiry->data['newcar'] == 1) {
                //TLO NEW
                $body['product_adira_id'] = env('MV4_T_N');
            } elseif ($product['flow']  == "mv" && $inquiry->data['type'] == 1 && $inquiry->data['newcar'] == 0) {
                //TLO OLD
                $body['product_adira_id'] = env('MV4_T_O');
            } elseif ($product['flow']  == "moto" && $inquiry->data['newcar'] == 0) {
                //MOTO OLD
                $body['product_adira_id'] = env('MV2_T_O');
            } elseif ($product['flow']  == "moto" && $inquiry->data['newcar'] == 1) {
                //MOTO NEW
                $body['product_adira_id'] = env('MV2_T_N');
            }
            $body['policy_type'] = $order->additional_data[0]['copy'] ?? 0;
            $body['reffinfo'] = "ZAP-" . (string)$order->id . (string)date("dmY");

            $body['remark3'] = !empty($user->partner) ? $user->partner->mo : '';
            
            $res = $this->multipartData($body, $inquiry, $order->data, $fileopen, $pa);
            
            $mul = $res['log'];
            for ($i = 57; $i <= 73; $i++) {
                unset($mul[$i]);
            }
            
            $adira_trx = new AdiraTransaction;
            $adira_trx->log_api = $mul;
            $adira_trx->order_id = $order->id;
            $adira_trx->ref_number = $body['reffinfo'];

            if (!empty($res['result']['status']) && $res['result']['status'] !== 'fail') {
                error_log("ADA STATUS / SUCCESS");
                $adira_trx->adira_response = $res['result'];
                $adira_trx->request_number = !empty($res['result']['data']) ? $res['result']['data']['requestNumber'] : null;
                $adira_trx->status = 'success';
                $adira_trx->save();
            } else {
                error_log("GAADA STATUS / FAILED");
                $adira_trx->adira_response = $res['result'];
                $adira_trx->status = 'fail';
                $adira_trx->save();

                $textmo = $adira_trx?->order?->user?->partner?->mo ?? null;
                $text = "Error Zap3 Prod \n"
                    . "Job : OrderJob \n"
                    . "Order ID : $adira_trx->order_id \n"
                    . "MO : $textmo \n"
                    . "Message : ".json_encode($res['result']);
                throw new \Exception($text, 1);
            }

            $adira_trx->save();
        } catch (\Exception $th) {
            throw $th;
        }
    }

    private function multipartData($body, $inquiry, $data, $fileopen, $pa)
    {
        $client = new Client();
        $multipart = [
            [
                'name'     => 'apiKey',
                'contents' => env('ADIRA_KEY')
            ],
            [
                'name'     => 'productId',
                'contents' => $body['product_adira_id']
            ],
            [
                'name'     => 'startDate',
                'contents' => $body['start_date']
            ],
            [
                'name'     => 'endDate',
                'contents' => $body['end_date']
            ],
            [
                'name'     => 'vehicleCode',
                'contents' => $body['car_code']
            ],
            [
                'name'     => 'vehicleLocationCode',
                'contents' => $inquiry->data['kode_plat']
            ],
            [
                'name'     => 'vehicleFunctionCode',
                'contents' => $body['okupansi']
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
                'contents' => $body['year']
            ],
            [
                'name'     => 'vehiclePrice',
                'contents' => $inquiry->data['price']
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
                'contents' => $body['same_address']
            ],
            [
                'name'     => 'attentionName',
                'contents' => !empty($data[1]['data']) ? $data[1]['data'] : ''
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
                'contents' => !empty($data[36]['data']) ? $data[36]['data'] : ''
            ],
            [
                'name'     => 'attentionProvinceCode',
                'contents' => !empty($data[69]['data']) ? $data[69]['data'] : ''
            ],
            [
                'name'     => 'attentionCityCode',
                'contents' => !empty($data[68]['data']) ? $data[68]['data'] : ''
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
                'contents' => $body['renewal_pdf']
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
                'contents' => floor($body['discount'])
            ],
            [
                'name'     => 'subjectToNoClaimDate',
                'contents' => ""
            ],
            [
                'name'     => 'referenceInfo',
                'contents' => $body['reffinfo']
            ],
            [
                'name'     => 'surveyType',
                'contents' => "Digital Survey"
            ],
            [
                'name'     => 'remark1',
                'contents' => !empty($body['renew']) ? "RENEWAL" : "REM1"
            ],
            [
                'name'     => 'remark2',
                'contents' => !empty($data[70]['data']) ? $data[70]['data'] : 'REM2'
            ],
            [
                'name'     => 'remark3',
                'contents' => $body['remark3']
            ],
            [
                'name'     => 'commissionPercentage',
                'contents' => (25 - $inquiry->discount)
            ],
        ];
        
        $result = $client->post(
            env('ADIRA_ORDER_URL') . 'api/v1/order?apiKey=' . env('ADIRA_KEY'),
            [
                'verify' => false,
                'multipart' => $multipart
            ]
        );
        return [
            "log" => $multipart,
            "result" => json_decode($result->getBody(), true)
        ];
    }

    public function failed(\Exception $e)
    {
        $text = $e->getMessage();
        TelegramBot::message($text);
        error_log("failed adira transaction : ".$e->getMessage());
        throw new \Exception($e->getMessage(), 1);
    }
}
