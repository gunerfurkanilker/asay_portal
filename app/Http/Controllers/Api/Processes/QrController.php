<?php

namespace App\Http\Controllers\Api\Processes;

use App\Http\Controllers\Api\ApiController;
use App\Model\EmployeeModel;
use App\Model\EmployeePositionModel;
use App\Model\QrModel;
use App\Model\UserTokensModel;
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
        $employee=EmployeeModel::find($request->Employee);
        $firstName=$employee->FirstName;
        $lastName = $employee->LastName;
        $name=$firstName." ".$lastName;
        $subject = "ÇALIŞAN ISG EĞİTİM Bilgi Formu -".$name;

        $mes="https://connect.ms.asay.com.tr/#/qr/qrDetay üzerinden verilen kod ile sorgulama yapabilirsiniz. Kodun geçerlilik süresi 60 dakikadır. Sorgulama kodu:".$code;
        $mes2="Sayın Yetkili,";
        $mes3="Aşağıda yer alan çalışanın ISG Eğitim Bilgi Formuna “https://connect.ms.asay.com.tr/#/qr/qrDetay”       linkine tıklayarak ulaşabilirsiniz. Doğrulama kodunun geçerlilik süresi 60 dakikadır.";
        $mes4="Doğrulama kodu:".$code;
        $mes5="Çalışan:".$name;

        $mes6="asaY Connect";
        $anamesaj= $mes2."<br />".$mes3."<br />".$mes4."<br />".$mes5."<br />".$mes6."<br />"."<span style='opacity: 0;'>+rand(10,100)</span>";

        Asay::sendMail($email,"",$subject,$anamesaj);

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı'
        ],200);
    }




}
