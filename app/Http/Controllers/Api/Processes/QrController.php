<?php

namespace App\Http\Controllers\Api\Processes;

use App\Http\Controllers\Api\ApiController;
use App\Model\EmployeePositionModel;
use App\Model\QrModel;
use Illuminate\Http\Request;

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




}
