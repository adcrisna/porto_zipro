<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\OfferingTravelMail;
use GuzzleHttp\Client as Guzzle;
use Log, PDF, Str;
use Exception;

class OfferingTravelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $order;
    public $tries = 1;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order)
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
            $product = $order->product;
            $data = $order->additional_data[0];
            $email =  $order->offering_email;

            $start_date = date_format(date_create_from_format('d/m/Y',$data['start_date']), 'Y-m-d');
            $end_date = date_format(date_create_from_format('d/m/Y',$data['end_date']), 'Y-m-d');
            $birth = date_format(date_create_from_format('d M Y',$data['birth']), 'Y-m-d');

            $travel_product = null;
            $region_selected = null;
            $client = new Guzzle;
            $result = $client->get(env("TRAVEL_URL").'/products',[
                'query' => [
                    'APIKey' => env('TRAVEL_TOKEN'),
                    'RegionID' => $data['destinationArea'],
                    'DestinationID' => $data['destinationCountry'],
                    'PackageTypeID' => $data['package_type'],
                    'TravelStartDate' => $start_date,
                    'TravelEndDate' => $end_date,
                    'DateOfBirth' => $birth
                ]
            ]);

            $getProduct = json_decode($result->getBody(), true);
            $client = new Guzzle;
            $result = $client->get(env("TRAVEL_URL").'/regions?APIKey='.env('TRAVEL_TOKEN'));
            $client_type = new Guzzle;
            $res_type = $client_type->get(env("TRAVEL_URL").'/package-types?APIKey='.env('TRAVEL_TOKEN'));
            $client = new Guzzle;
            $res_travel = $client->get(env("TRAVEL_URL").'/travel-needs?APIKey='.env('TRAVEL_TOKEN'));

            $arr_types = json_decode($res_type->getBody(), true)['PackageTypes'];
            $regions = json_decode($result->getBody(), true);
            $destination = array_search($data['destinationArea'], array_column($regions['Regions'], 'ID'));
            $countries = $regions['Regions'][$destination]['Destinations'];
            $arr_needs = json_decode($res_travel->getBody(), true)['TravelNeeds'];

            $client = new Guzzle;
            $result = $client->get(env("TRAVEL_URL").'/coverages',[
                'query' => [
                    'APIKey' => env('TRAVEL_TOKEN'),
                    'ProductID' => $data['zurich_product_id'],
                    'TravelStartDate' => $start_date,
                    'TravelEndDate' => $end_date,
                ]
            ]);
            $coverages = json_decode($result->getBody(), true);
            $t = [];
            $region_selected = null;
            $tujuan = null;
            $types = [];
            $travelneeds = [];
            $price = 0;
            $sel_cover = [];
            // return $countries;
            // return $coverages;

            // foreach (json_decode($result_cov->getBody(), true) as $keyCover => $zcover) {
            //     if(!empty($data['coverages']) && in_array($zcover['ID'], $data['coverages'])) {
            //         $data['zurich_coverages'][] = $zcover;
            //     }
            // }
            foreach ($countries as $key => $country) {
                if($country['ID'] == $data['destinationCountry']) {
                    $region_selected = $country['Name'];
                }
                
            }
            foreach ($coverages as $keyCover => $zcover) {
                if(!empty($data['coverages']) && in_array($zcover['ID'], $data['coverages'])) {
                    $sel_cover[] = $zcover;
                }
            }

            foreach ($regions['Regions'] as $key => $reg) {
                unset($reg['Destinations']);
                $t[] = $reg;
            }

            foreach($t as $s) {
                if($s['ID'] == $data['destinationArea']) {
                    $tujuan = $s['Name'];
                }
            }
            foreach($getProduct as $key => $travel) {
                if($travel["ID"] == $data['zurich_product_id']) {
                    $travel_product = $travel;
                }
            }
            
            foreach ($arr_types as $key => $type) {
                if($type['ID'] == $data['package_type']) {
                    $types = $type;
                }
            }
            foreach ($arr_needs as $key => $need) {
                if($need['ID'] == $data['travel_need']) {
                    $travelneeds = $need;
                }
            }
            $path = asset('assets/img/zipro.png');
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

            // $namePDF = "offering_".md5($order->id).".pdf";
            $namePDF = "surat_penawaran_asuransi_".md5($order->id).".pdf";
            $savepath = storage_path('uploads/pdf/'.$namePDF);
            if (file_exists($savepath)) {
                unlink($savepath);
            }

            $pdf = PDF::loadView('pdf.offertravel', compact('order', 'base64', 'sel_cover', 'travel_product', 'region_selected', 'tujuan', 'types', 'travelneeds'))->setPaper('a4','potrait');
            $pdf->save(storage_path('uploads/pdf/'.$namePDF));
            if($pdf) {
                Mail::to($email)->send(new OfferingTravelMail($product, $namePDF));
            }
        } catch (\Exception $e) {
            throw $e;
        } 
    }

    public function failed(\Exception $e)
    {
        $text = "Error Zap2 Staging \n"
            . "Job : OfferingTravelJob \n"
            . "Order ID : " . $this->order?->id ?? null . " \n"
            . "Message : " . $e->getMessage();
        // TelegramBot::message($text);
        throw new Exception($e->getMessage(), 1);
    }
}
