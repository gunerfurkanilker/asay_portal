<?php
/**
 * Created by IntelliJ IDEA.
 * User: serkan.erdinc
 * Date: 18.08.2020
 * Time: 16:20
 */

namespace App\Library;


use Illuminate\Support\Facades\Mail;

class Asay
{
    public static function sendMail($to,$cc="",$subject,$message,$title="",$attach="",$attachFileName="",$mimeType="")
    {
        Mail::send([], [], function ($email) use($to,$cc,$subject,$message,$title,$attach,$attachFileName,$mimeType) {
            if($title==""){
                $title="aSAY Group";
            }
            $email->from('sender@asay.com.tr', $title);
            $email->to($to);
            if($cc<>"")
                $email->cc($cc);
            $email->subject($subject);
            if($attach<>"")
            {
                $email->attach($attach,array(
                    'as' => $attachFileName,
                    'mime' => $mimeType
                ));
            }
            $email->setBody($message, 'text/html');
        });
    }

    public static function sendSMS($message,$messageTo){
        $messageHeader="ASAY";
        $username = "8503073830"; //
        $password = "N7LERJ4F"; //

        $url= "https://api.netgsm.com.tr/sms/send/get";

        $guzzleParams = [
            'query' => [
                'usercode'      => $username,
                'password'      => $password,
                'gsmno'         => $messageTo,
                'message'       => $message,
                'msgheader'     => $messageHeader
            ],
        ];

        $client = new \GuzzleHttp\Client();
        $res = $client->request("GET", $url,$guzzleParams);
        $responseBody = json_decode($res->getBody());

        $responseBodyArray = explode(" ",$responseBody); // 00 => hata kodu 123456 => SMS kontrol kodu

        if ($responseBodyArray[0] == "20")
            return ['status' => false,'message' => 'Mesaj karakter sınırını aşıyor.'];
        if ($responseBodyArray[0] == "30")
            return ['status' => false,'message' => 'API username veya password hatası'];
        if ($responseBodyArray[0] == "40")
            return ['status' => false,'message' => 'Gönderici adı sistemde kayıtlı değil'];
        if ($responseBodyArray[0] == "70")
            return ['status' => false,'message' => 'Hatalı parametre gönderdiniz, parametreleri kontrol ediniz'];

        return ['status' => true, 'message' => 'Mesaj Gönderimi Başarılı', 'apiResponse' => $responseBody];

    }



}
