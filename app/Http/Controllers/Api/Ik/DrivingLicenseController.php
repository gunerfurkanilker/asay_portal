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
        $drivingLicense = DrivingLicenseModel::where(['EmployeeID' => $id, 'Active' => 1])->first();
        if ($drivingLicense)
        {
            if (!is_null($drivingLicense->DrivingLicenseClasses))
                $drivingLicense->DrivingLicenseClasses = array_map('intval', explode(",",$drivingLicense->DrivingLicenseClasses));
            if (!is_null($drivingLicense->SRCClasses))
                $drivingLicense->SRCClasses = array_map('strval', explode(",",$drivingLicense->SRCClasses));
        }



        return response([
            'status'    => true,
            'message'   => 'İşlem Başarılı',
            //'data' => DrivingLicenseModel::where(['EmployeeID' => $id, 'Active' => 1])->get()
            'data'      => $drivingLicense
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

    public function deleteDrivingLicenseInfo(Request $request)
    {
        $drivingLicenseId = $request->drivingLicenseId;

        $drivingLicense = DrivingLicenseModel::find($drivingLicenseId);
        $drivingLicense->Active = 0;

        if ($drivingLicense->save())
        {
            return response([
                'status' => true,
                'message' => "İşlem Başarılı.",
            ], 200);
        }
        else
            return response([
                'status' => false,
                'message' => "İşlem Başarısız.",
            ], 200);




    }


}
