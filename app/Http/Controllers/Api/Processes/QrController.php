<?php

namespace App\Http\Controllers\Api\Processes;

use App\Http\Controllers\Api\ApiController;
use App\Model\EmployeePositionModel;
use App\Model\QrModel;
use Illuminate\Http\Request;
use App\Library\Asay;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class QrController extends ApiController
{


    public function postQr(Request $request){

        $data=$request->except('token');


        QrModel::updateOrCreate([
            'EmployeeID'=>$request->EmployeeID,

        ],$request->except(['EmployeeID','token']));

        return response()->json([
            'success'=>true,
            'message'=>'Başarıyla Eklendi'
        ]);
    }

    public function sendMailQr(Request $request){

        $request->validate([
           'code'=>'required',
           'email'=>'required'
        ]);

        $code=$request->code;
        $email=$request->email;

        $mes="https://connect.ms.asay.com.tr/#/qr/qrDetay üzerinden verilen kod ile sorgulama yapabilirsiniz. Kodun geçerlilik süresi 60 dakikadır. Sorgulama kodu:".$code;

        Asay::sendMail($email,"","Qr CODE",$mes);

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı'
        ],200);
    }




}
