<?php


namespace App\Http\Controllers\Api\Ik;


use App\Http\Controllers\Api\ApiController;
use App\Model\BloodTypeModel;
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
                $emergencyField = EmergencyFieldModel::saveEmergencyField($request->all(),$employee->EmergencyFieldID);
            else
                $emergencyField = EmergencyFieldModel::addEmergencyField($request->all(),$employee);

            if ($emergencyField)
                return response([
                    'status' => true,
                    'message' => $emergencyField->Id . " ID No'lu Acil Durum Bilgisi Kaydedildi",
                    'data' =>$emergencyField
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

    public function getEmergencyInformations($id){
        $employee = EmployeeModel::find($id);

        if ($employee->EmergencyFieldID == null)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => null
            ],200);
        else
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => EmergencyFieldModel::find($employee->EmergencyFieldID)
            ],200);

    }

    public function getEmergencyInformationFields(){
        $fields = EmergencyFieldModel::getEmergencyFields();

        return response([
            'status' => true,
            'message' => "İşlem Başarılı.",
            'data' => $fields
        ],200);

    }
}
