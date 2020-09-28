<?php


namespace App\Http\Controllers\Api\Ik;


use App\Model\DrivingLicenceType;
use App\Model\DrivingLicenseModel;
use App\Model\EducationModel;
use App\Model\EmployeeModel;
use App\Model\LocationModel;
use Illuminate\Http\Request;

class DrivingLicenseController
{
    public function saveDrivingLicense(Request $request)
    {

        $status = DrivingLicenseModel::saveDrivingLicense($request);

        if ($status)
            return response([
                'status' => true,
                'message' => "İşlem Başarılı",
            ], 200);
        else
            return response([
                'status' => false,
                'message' => "İşlem Başarısız."
            ], 200);

    }


    public function getDrivingLicense($id)
    {

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => DrivingLicenseModel::where(['EmployeeID' => $id])->get()
        ], 200);
    }

    public function getDrivingLicenseFields()
    {
        $fields = DrivingLicenseModel::getDrivingLicenseFields();

        return response([
            'status' => true,
            'message' => "İşlem Başarılı.",
            'data' => $fields
        ], 200);

    }

    public function getDrivingLicenseClasses(Request $request)
    {
        $kind = $request->Kind == "true" ? 1 : 0;

        $drivingLicenseClasses = DrivingLicenceType::where(['Active' => 1, 'Kind' => $kind])->get();

        return response([
            'status' => true,
            'message' => "İşlem Başarılı.",
            'data' => $drivingLicenseClasses,
            'kind' => $kind
        ], 200);

    }


}
