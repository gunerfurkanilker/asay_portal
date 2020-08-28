<?php


namespace App\Http\Controllers\Api\Ik;


use App\Http\Controllers\Api\ApiController;
use App\Model\BodyMeasurementModel;
use App\Model\EmployeeModel;
use App\Model\IdCardModel;
use Illuminate\Http\Request;

class IDCardController extends ApiController
{
    public function saveIDCard(Request $request)
    {
        $request_data = $request->all();
        $employee = EmployeeModel::find($request_data['employeeid']);

        if (!is_null($employee))
        {
            if ($employee->IDCardID != null)
                $idCard = IdCardModel::saveIDCard($request,$employee->IDCardID);
            else
                $idCard = IdCardModel::addIDCard($request,$employee);

            if ($idCard)
                return response([
                    'status' => true,
                    'message' => $idCard->Id . " ID No'lu Kimlik Bilgisi Kaydedildi",
                    'data' => $request_data
                ],200);
            else
                return response([
                    'status' => false,
                    'message' => "İşlem Başarısız."
                ],200);
        }
        else
        {
            return response([
                'status' => false,
                'message' => $request->EmployeeID. " ID No'lu Çalışan bulunamadı."
            ],200);
        }
    }

    public function getIDCard($id){
        $employee = EmployeeModel::find($id);

        if ($employee->IDCardID == null)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => null
            ],200);
        else
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => IdCardModel::find($employee->IDCardID)
            ],200);

    }

    public function getIDCardFields(){
        $fields = IdCardModel::getIDCardFields();

        return response([
            'status' => true,
            'message' => "İşlem Başarılı.",
            'data' => $fields
        ],200);

    }

}
