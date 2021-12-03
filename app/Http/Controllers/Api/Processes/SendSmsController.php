<?php

namespace App\Http\Controllers\Api\Processes;

use App\Http\Controllers\Api\ApiController;
use App\Library\Asay;
use Illuminate\Http\Request;

class SendSmsController extends ApiController
{


    public function smsSender(Request $request){

        $request->validate([
            'code'=>'required',
            'number'=>'required'
        ]);

        $code=$request->code;
        $number=$request->number;
        $mes="https://connect.ms.asay.com.tr/#/qr/qrDetay üzerinden verilen kod ile sorgulama yapabilirsiniz. Kodun geçerlilik süresi 60 dakikadır. Sorgulama kodu:".$code;

        Asay::sendSMS($mes,$number);

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı'
        ],200);
    }


}
