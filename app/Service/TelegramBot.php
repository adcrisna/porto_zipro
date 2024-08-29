<?php
namespace App\Service;
use GuzzleHttp\Client;
use Exception;

class TelegramBot {
    
    static public function message($data)
    {
        try{
        
        $data =[
            'chat_id' => env('TELEGRAM_GROUP_ID',"-4029102425"),
            'parse_mode' => 'HTML',
            'text' => $data
        ];
        error_log(json_encode($data));
        \Telegram::sendMessage($data);

    } catch (\Exception $th) {
        
    }

        try{

        $client = new Client();
        $response = $client->request('POST',env('WA_SALVUS_LINK'), [
            'form_params' => [
                'text' => $data
            ]
        ]);

    
    } catch (\Exception $th) {
        
    }
        
        
    }
}
