<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;
use App\Models\AdiraTransaction;
use App\Models\Order;
Use Illuminate\Support\Str;
use Exception, DateTime;
use App\Service\TelegramBot;

class TravelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $order;
    public $tries = 1;
    public $timeout = 0;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
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
            $request = $order->additional_data[0];

            $person = 0;
            $relations = null;
            $firstName = null;
            $lastName = null;
            $gender = null;
            $dateBirth = null;
            $birthplace = null;
            $idType = null;
            $id_number = null;
            $cities = null;
            $address = null;
            $coverages = null;

            $start_date = date_format(date_create_from_format('d/m/Y',$request['start_date']), 'Y-m-d');
            $end_date = date_format(date_create_from_format('d/m/Y',$request['end_date']), 'Y-m-d');
            $depature = null;

            $alreadyTrav = 0;
            if(!empty($request['alreadyTrav']) && $request['alreadyTrav'] == 'yes') {
                $alreadyTrav = 1;
            }
            if($alreadyTrav == 1) {
                if(!$this->validateDate($request['depature_date'])) {
                    $depature = date_format(date_create_from_format('d/m/Y',$request['depature_date']), 'Y-m-d');
                }else {
                    $depature = date_format(date_create_from_format('d M Y',$request['depature_date']), 'Y-m-d');
                }
            }
            // return $request['penutupan'];
            foreach ($request['penutupan'] as $key => $value) {
                $isAllNull = array_reduce($request['penutupan'][$key],function($result, $elem) {
                    return $result && ($elem === null);
                }, true);
                if(!$isAllNull) {
                    $person = $person + 1;
                    $dob = date_format(date_create_from_format('d M Y',$value['insured_dob']), 'Y-m-d');
                    $dateBirth = $dateBirth == null ?  $dob : $dateBirth.",".$dob;
                    
                    $relations = $relations == null ? "Primary" : $relations.",".$value['insured_relationship'];
                    $firstName = $firstName == null ? $value['insured_firstname'] : $firstName.",".$value['insured_firstname'];
                    $lastName = $lastName == null ? $value['insured_lastname'] : $lastName.",".$value['insured_lastname'];
                    $birthplace = $birthplace == null ? $value['insured_birthplace'] : $birthplace.",".$value['insured_birthplace'];
                    
                    if($value['insured_title'] == "tuan") {
                        $gender = $gender == null ? "Male" : $gender.",Male";
                    }else {
                        $gender = $gender == null ? "Female" : $gender.",Female";
                    }
                    
                    $idType = $idType == null ? $value['insured_identity'] : $idType.",".$value['insured_identity'];
                    $id_number = $id_number == null ? $value['insured_noindentity'] : $id_number.",".$value['insured_noindentity'];
                    $cities = $cities == null ? ucwords($value['insured_birthplace']) : $cities.",".ucwords($value['insured_birthplace']);
                    $resultAlamat = preg_replace('/[,]/', ' ', $value['insured_alamat']);
                    $address = $address == null ? $resultAlamat : $address.",".$resultAlamat;
                }                
            }

            foreach ($request['coverages'] as $key => $cover) {
                $coverages = $coverages == null ? $cover : $coverages.",".$cover;
            }
            $phone = null;
            if($request['penutupan'][0]['insured_phone'][0] == "0") {
                $phone = "62".substr($request['penutupan'][0]['insured_phone'], 1);
            }else {
                $phone = $request['penutupan'][0]['insured_phone'];
            }

            // return $relations;
            $client = new Client;
            $params = [
                'APIKey' => env('TRAVEL_TOKEN'),
                'OriginID' => $request['origins'],
                'RegionID' => $request['destinationArea'],
                'DestinationID' => $request['destinationCountry'],
                'ProductID' => $request['zurich_product_id'],
                'AlreadyTravelling' => $alreadyTrav,
                'DepartureDate' => $depature,
                'TravelStartDate' => $start_date,
                'TravelEndDate' => $end_date,
                'NumOfPersons' => $person,
                'CoverageIDs' => $coverages,
                'TravelNeedID' => $request['travel_need'],
                'Relationships' => $relations,
                'FirstNames' => $firstName,
                'LastNames' => $lastName,
                'Genders' => $gender,
                'DateOfBirths' => $dateBirth,
                'Emails' => $request['penutupan'][0]['insured_email'],
                'PersonalIDTypes' => $idType,
                'PersonalIDNos' => $id_number,
                'Addresses' => $address,
                'Cities' => $cities,
                'PlaceOfBirths' => $birthplace,
                'PhoneNumbers' => $phone,
                'ContactFullName' => $request['penutupan'][0]['insured_firstname']." ".$request['penutupan'][0]['insured_lastname'],
                'ContactPhoneNumber' => $phone,
                'ContactEmail' => $request['penutupan'][0]['insured_email'],
                'ContactPersonalIDNo' => $request['penutupan'][0]['insured_noindentity'],
                'SendEPolicy' => 1,
            ];
            $res = $client->post(env("TRAVEL_URL").'/order', [
                'form_params' => $params
            ]);

            $json = json_decode($res->getBody(), true);
            if(!empty($json['status'])) {
                if($json['status'] == "Failed" || $json['status'] == "fail") {
                    $getStatus = 'fail';
                    $text = "Error Zap2 Staging \n"
                    . "Job : TravelJob \n"
                    . "Order ID : ".$order->id." \n"
                    . "Message : ".json_encode($json);
                    throw new \Exception($text, 1);
                    
                }elseif($json['status'] == "success") {
                    $getStatus = "success";
                }
            }else {
                $getStatus = null;
            }
            $adira_trx = new AdiraTransaction;
            $adira_trx->order_id = $order->id;
            $adira_trx->postmikro_response = $json;
            $adira_trx->log_api = $params;
            $adira_trx->status = $getStatus;
            $adira_trx->save();

        } catch (\Exception $e) {
            throw $e;
        }

    }

    public function validateDate($date, $format = 'd M Y')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    public function failed(\Exception $e)
    {
        $text = $e->getMessage();
        TelegramBot::message($text);
        error_log("failed travel transaction : ".$e->getMessage());
        throw new \Exception($e->getMessage(), 1);
    }
}
