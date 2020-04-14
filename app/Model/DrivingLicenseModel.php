<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DrivingLicenseModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = "DrivingLicence";
    public $timestamps = false;
    protected $guarded = [];
    protected $appends = [

    ];

    public static function saveDrivingLicense($request, $drivingLicenseID)
    {
        $drivingLicenseID = self::find($drivingLicenseID);

        if ($drivingLicenseID != null) {

            $drivingLicenseID->DrivingLicenceType = $request['licensetype'];
            $drivingLicenseID->BirthDate = $request['birthdate'];
            $drivingLicenseID->BirthPlace = $request['birthplace'];
            $drivingLicenseID->StartDate = $request['licensebegindate'];
            $drivingLicenseID->EffectiveDate = $request['licenseenddate'];
            $drivingLicenseID->PlaceOfIssue = $request['licenselocation'];
            $drivingLicenseID->DocumentNo = $request['licensedocumentno'];
            $drivingLicenseID->DrivingLicenceClass = $request['licenseclass'];

            $drivingLicenseID->save();

            return $drivingLicenseID->fresh();
        }
        else
            return false;
    }

    public static function addDrivingLicense($request,$employee)
    {
        $drivingLicenseID = self::create([
            'DrivingLicenceType' => $request['licensetype'],
            'BirthDate' => $request['birthdate'],
            'BirthPlace' => $request['birthplace'],
            'StartDate' => $request['licensebegindate'],
            'EffectiveDate' => $request['licenseenddate'],
            'PlaceOfIssue' => $request['licenselocation'],
            'DocumentNo' => $request['licensedocumentno'],
            'DrivingLicenceClass' => $request['licenseclass']
        ]);

        if ($drivingLicenseID != null)
        {
            $employee->DrivingLicence = $drivingLicenseID->Id;
            $employee->save();
            return $drivingLicenseID;
        }

        else
            return false;
    }

}
