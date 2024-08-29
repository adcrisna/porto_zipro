<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use GuzzleHttp\Client;
use App\Models\AdiraProvince;

class GetProvinceCities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:province';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get data from adira to sync to our database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $client = new Client;
        $res = $client->get('https://portalmv.zurich.co.id/api/v1/prepare?apiKey=bf824ea230914973dbc9b5413a137a17', ['verify' => false]);
        $json_adira = json_decode($res->getBody(), true);
        foreach ($json_adira['data']['provinceCities'] as $province => $city) {
            $adiraProvince = AdiraProvince::firstorCreate(
                ['province' =>  $province],
                ['city' => $city]
            );
            $adiraProvince->cities = $city;
            $adiraProvince->update();
        }
    }
}
