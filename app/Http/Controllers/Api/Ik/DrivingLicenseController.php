<?php


namespace App\Http\Controllers\Api\Ik;


use App\Model\DrivingLicenseModel;
use App\Model\EducationModel;
use App\Model\EmployeeModel;
use Illuminate\Http\Request;

class DrivingLicenseController
{
    public function saveDrivingLicense(Request $request,$employeeId)
    {
        $employee = EmployeeModel::find($employeeId);
        if (!is_null($employee))
        {
            if ($employee->EducationID != null)
                $drivingLicense = DrivingLicenseModel::saveDrivingLicense($request->all(),$employee->DrivingLicenceID);
            else
                $drivingLicense = DrivingLicenseModel::addDrivingLicense($request->all(),$employee);

            if ($drivingLicense)
                return response([
                    'status' => true,
                    'message' => $drivingLicense->Id . " ID No'lu Sürücü Belgesi Bilgisi Kaydedildi",
                    'data' =>$drivingLicense
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
