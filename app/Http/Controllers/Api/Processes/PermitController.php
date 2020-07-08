<?php


namespace App\Http\Controllers\Api\Processes;


use App\Http\Controllers\Api\ApiController;
use App\Model\PermitKindModel;
use App\Model\PermitModel;
use Illuminate\Http\Request;

class PermitController extends ApiController
{
    public function createPermit(Request $request) {

        $status = PermitModel::createPermit($request->all());
        if ($status)
            return response([
                'status' => true,
                'message' => "Kayıt Başarılı",
            ], 200);
        else
            return response([
                'status' => false,
                'message' => "Kayıt Başarısız",
            ], 200);

    }

    public function permitTypes(){
        return response([
            'status' => true,
            'message' => "İşlem Başarılı",
            'data' => PermitKindModel::getPermitKinds()
        ], 200);
    }

}
