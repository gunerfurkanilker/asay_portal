<?php


namespace App\Http\Controllers\Api\Ik;


use App\Http\Controllers\Api\ApiController;
use App\Model\BodyMeasurementModel;
use App\Model\EmployeeModel;
use App\Model\IdCardModel;
use Illuminate\Http\Request;

class IDCardController extends ApiController
{
    public function saveIDCard(Request $request,$employeeId)
    {
        $employee = EmployeeModel::find($employeeId);

        if (!is_null($employee))
        {
            if ($employee->IDCardID != null)
                $idCard = IdCardModel::saveIDCard($request->all(),$employee->IDCardID);
            else
                $idCard = IdCardModel::addIDCard($request->all(),$employee);

            if ($idCard)
                return response([
                    'status' => true,
                    'message' => $idCard->Id . " ID No'lu Kimlik Bilgisi Kaydedildi",
                    'data' => $idCard
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
                'message' => $employeeId. " ID No'lu Çalışan bulunamadı."
            ],200);
        }
    }

}
