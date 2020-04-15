<?php


namespace App\Http\Controllers\Api\Ik;


use App\Http\Controllers\Api\ApiController;
use App\Model\BodyMeasurementModel;
use App\Model\EmployeeModel;
use Illuminate\Http\Request;

class BodyMeasurementsController extends ApiController
{
    public function saveBodyMeasurements(Request $request,$employeeId)
    {
        $employee = EmployeeModel::find($employeeId);

        if (!is_null($employee))
        {
            if ($employee->BodyMeasurementID != null)
                $bodyMeasurements = BodyMeasurementModel::saveBodyMeasurements($request->all(),$employee->BodyMeasurementID);
            else
                $bodyMeasurements = BodyMeasurementModel::addBodyMeasurements($request->all(),$employee);

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
}
