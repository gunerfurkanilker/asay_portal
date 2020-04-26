<?php


namespace App\Http\Controllers\Api\Ik;


use App\Http\Controllers\Api\ApiController;
use App\Model\BodyMeasurementModel;
use App\Model\EmployeeModel;
use Illuminate\Http\Request;

class BodyMeasurementsController extends ApiController
{
    public function saveBodyMeasurements(Request $request)
    {
        $request_data = $request->all();
        $employee = EmployeeModel::find($request_data['employeeid']);

        if (!is_null($employee))
        {
            if ($employee->BodyMeasurementID != null)
                $bodyMeasurements = BodyMeasurementModel::saveBodyMeasurements($request_data,$employee->BodyMeasurementID);
            else
                $bodyMeasurements = BodyMeasurementModel::addBodyMeasurements($request_data,$employee);

            if ($bodyMeasurements)
                return response([
                    'status' => true,
                    'message' => $bodyMeasurements->Id . " ID No'lu Fiziksel Bilgi Kaydedildi",
                    'data' => $bodyMeasurements
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

    public function getBodyMeasurements($id){
        $employee = EmployeeModel::find($id);

        if ($employee->BodyMeasurementID == null)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => null
            ],200);
        else
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => BodyMeasurementModel::find($employee->BodyMeasurementID)
            ],200);

    }

    public function getBodyMeasurementsFields(){
        $fields = BodyMeasurementModel::getBodyMeasurementFields();

        return response([
            'status' => true,
            'message' => "İşlem Başarılı.",
            'data' => $fields
        ],200);

    }
}
