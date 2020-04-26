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
        'DrivingLicenseType'
    ];

    public static function saveDrivingLicense($request, $drivingLicenseID)
    {
        $drivingLicenseID = self::find($drivingLicenseID);

        if ($drivingLicenseID != null) {

            $drivingLicenseID->DrivingLicenceType = $request['licensetype'];
            $drivingLicenseID->BirthDate = new Carbon($request['birthdate']);
            $drivingLicenseID->BirthPlace = $request['birthplace'];
            $drivingLicenseID->StartDate = new Carbon($request['licensebegindate']);
            $drivingLicenseID->EffectiveDate = new Carbon($request['licenseenddate']);
            $drivingLicenseID->PlaceOfIssue = $request['licenselocation'];
            $drivingLicenseID->DocumentNo = $request['licensedocumentno'];
            //$drivingLicenseID->DrivingLicenceClass = $request['licenseclass'];

            $drivingLicenseID->save();

            return $drivingLicenseID->fresh();
        }
        else
            return false;
    }

    public static function addDrivingLicense($request,$employee)
    {
        $drivingLicense = self::create([
            'DrivingLicenceType' => $request['licensetype'],
            'BirthDate' => new Carbon($request['birthdate']),
            'BirthPlace' => new Carbon($request['birthplace']),
            'StartDate' => new Carbon($request['licensebegindate']),
            'EffectiveDate' => new Carbon($request['licenseenddate']),
            'PlaceOfIssue' => $request['licenselocation'],
            'DocumentNo' => $request['licensedocumentno'],
            //'DrivingLicenceClass' => $request['licenseclass']
        ]);

        if ($drivingLicense != null)
        {
            $employee->DrivingLicenceID = $drivingLicense->Id;
            $employee->save();
            return $drivingLicense;
        }

        else
            return false;
    }

    public static function getDrivingLicenseFields()
    {
        $data = [];
        $data['DrivingLicenseTypes'] = DrivingLicenceType::all();

        return $data;
    }

    public function getDrivingLicenseTypeAttribute()
    {
        $drivingLicenseType = $this->hasOne(DrivingLicenceType::class,"Id","DrivingLicenceType");
        return $drivingLicenseType->where("Active",1)->first();
    }

}
