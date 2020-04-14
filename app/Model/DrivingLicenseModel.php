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

            $drivingLicenseID->StatusID = $request['educationstatus'];
            $drivingLicenseID->Institution = $request['schoolname'];
            $drivingLicenseID->LevelID = $request['educationlevel'];
            $drivingLicenseID->DocumentID = $request['graduationdocument'];

            $drivingLicenseID->save();

            return $drivingLicenseID->fresh();
        }
        else
            return false;
    }

    public static function addDrivingLicense($request,$employee)
    {
        $drivingLicenseID = self::create([
            'StatusID' => $request['educationstatus'],
            'Institution' => $request['schoolname'],
            'LevelID' => $request['educationlevel'],
            'DocumentID' => $request['graduationdocument']
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
