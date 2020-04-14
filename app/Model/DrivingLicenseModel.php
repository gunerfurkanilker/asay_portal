<?php

namespace App\Model;

use Carbon\Carbon;
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
            $drivingLicenseID->BirthDate = new Carbon($request['birthdate']);
            $drivingLicenseID->BirthPlace = new Carbon($request['birthplace']);
            $drivingLicenseID->StartDate = new Carbon($request['licensebegindate']);
            $drivingLicenseID->EffectiveDate = new Carbon($request['licenseenddate']);
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
            'BirthDate' => new Carbon($request['birthdate']),
            'BirthPlace' => new Carbon($request['birthplace']),
            'StartDate' => new Carbon($request['licensebegindate']),
            'EffectiveDate' => new Carbon($request['licenseenddate']),
            'PlaceOfIssue' => $request['licenselocation'],
            'DocumentNo' => $request['licensedocumentno'],
            'DrivingLicenceClass' => $request['licenseclass']
        ]);

        if ($drivingLicenseID != null)
        {
            $employee->DrivingLicenceID = $drivingLicenseID->Id;
            $employee->save();
            return $drivingLicenseID;
        }

        else
            return false;
    }

}
