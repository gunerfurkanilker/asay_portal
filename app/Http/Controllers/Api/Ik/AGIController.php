<?php


namespace App\Http\Controllers\Api\Ik;


use App\Http\Controllers\Api\ApiController;
use App\Model\AgiModel;
use App\Model\EmployeeModel;
use App\Model\LocationModel;
use Illuminate\Http\Request;

class AGIController extends ApiController
{

    public function saveAgi(Request $request)
    {
        $request_data = $request->all();
        $agi = AgiModel::where(['EmployeeID' => $request_data['employeeid']])->first();

        if ($agi)
            $agi = AgiModel::saveAgi($request_data, $agi->Id);
        else
            $agi = AgiModel::addAgi($request_data);

        if ($agi)
            return response([
                'status' => true,
                'message' => "İşlem Başarılı",
                'data' => $agi
            ], 200);
        else
            return response([
                'status' => false,
                'message' => "İşlem Başarısız",
            ], 200);

    }

    public function getAgiInformations($id)
    {
        $agi = AgiModel::where(['EmployeeID' => $id])->first();

        if ($agi)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => $agi
            ], 200);
        return response([
            'status' => false,
            'message' => 'Bilgi Yok',
        ], 200);

    }

    public function getAGIInformationFields()
    {
        $fields = AgiModel::getAGIFields();

        return response([
            'status' => true,
            'message' => "İşlem Başarılı.",
            'data' => $fields
        ], 200);

    }

}
