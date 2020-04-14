<?php


namespace App\Http\Controllers\Api\Ik;


use App\Http\Controllers\Api\ApiController;
use App\Model\EducationModel;
use App\Model\EmployeeModel;
use App\Model\LocationModel;
use http\Client\Request;

class EducationController extends ApiController
{

    public function saveEducation(Request $request,$employeeId)
    {
        $employee = EmployeeModel::find($employeeId);
        if (!is_null($employee))
        {
            if ($employee->LocationID != null)
                $education = EducationModel::saveLocation($request->all(),$employee->EducationID);
            else
                $education = EducationModel::addLocation($request->all(),$employee);

            if ($education)
                return response([
                    'status' => true,
                    'message' => $education->Id . " ID No'lu Lokasyon Kaydedildi",
                    'data' =>$education
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
