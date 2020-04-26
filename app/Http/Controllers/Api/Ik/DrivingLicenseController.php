<?php


namespace App\Http\Controllers\Api\Ik;


use App\Model\DrivingLicenseModel;
use App\Model\EducationModel;
use App\Model\EmployeeModel;
use App\Model\LocationModel;
use Illuminate\Http\Request;

class DrivingLicenseController
{
    public function saveDrivingLicense(Request $request)
    {
        $request_data = $request->all();
        $employee = EmployeeModel::find($request_data['employeeid']);
        if (!is_null($employee))
        {
            if ($employee->DrivingLicenceID != null)
                $drivingLicense = DrivingLicenseModel::saveDrivingLicense($request_data,$employee->DrivingLicenceID);
            else
                $drivingLicense = DrivingLicenseModel::addDrivingLicense($request_data,$employee);

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


    public function getDrivingLicense($id){
        $employee = EmployeeModel::find($id);

        if ($employee->DrivingLicenceID == null)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => null
            ],200);
        else
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => DrivingLicenseModel::find($employee->DrivingLicenceID)
            ],200);

    }

    public function getDrivingLicenseFields(){
        $fields = DrivingLicenseModel::getDrivingLicenseFields();

        return response([
            'status' => true,
            'message' => "İşlem Başarılı.",
            'data' => $fields
        ],200);

    }



}
