<?php


namespace App\Http\Controllers\Api\Ik;


use App\Http\Controllers\Api\ApiController;
use App\Model\EmergencyFieldModel;
use App\Model\EmployeeModel;
use Illuminate\Http\Request;

class EmergencyFieldController extends ApiController
{
    public function saveEmergencyField(Request $request,$employeeId)
    {
        $employee = EmployeeModel::find($employeeId);
        if (!is_null($employee))
        {
            if ($employee->EmergencyFieldID != null)
                $agi = EmergencyFieldModel::saveAgi($request->all(),$employee->EmergencyFieldID);
            else
                $agi = EmergencyFieldModel::addAgi($request->all(),$employee);

            if ($agi)
                return response([
                    'status' => true,
                    'message' => $agi->Id . " ID No'lu Acil Durum Bilgisi Kaydedildi",
                    'data' =>$agi
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
