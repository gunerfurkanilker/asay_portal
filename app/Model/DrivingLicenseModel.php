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
        'DrivingLicenseType',
    ];

    public static function saveDrivingLicense($request)
    {

        if ($request->DrivingLicenseID != null)
            $drivingLicense = self::find($request->DrivingLicenseID);
        else
            $drivingLicense = new DrivingLicenseModel();

        $drivingLicense->EmployeeID             = $request->EmployeeID;
        $drivingLicense->HasDrivingLicense      = $request->HasDrivingLicense;
        $drivingLicense->DrivingLicenseKind     = $request->DrivingLicenseKind;
        //$drivingLicense->DrivingLicenceClass    = $request->DrivingLicenceClass;
        $drivingLicense->DrivingLicenseClasses  = implode(",",$request->DrivingLicenseClass);
        $drivingLicense->BirthDate              = $request->BirthDate;
        $drivingLicense->BirthPlace             = $request->BirthPlace;
        $drivingLicense->StartDate              = $request->StartDate;
        $drivingLicense->EffectiveDate          = $request->EffectiveDate;
        $drivingLicense->PlaceOfIssue           = $request->PlaceOfIssue;
        $drivingLicense->DocumentNo             = $request->DocumentNo;
        $drivingLicense->EditPerson             = $request->EditPerson;
        $drivingLicense->BackSerialNo           = $request->BackSerialNo;
            //$drivingLicenseID->DrivingLicenceClass = $request['licenseclass'];

        return $drivingLicense->save() ? true : false;

    }

    public static function getDrivingLicenseFields()
    {
        $data = [];
        $data['DrivingLicenseTypes'] = DrivingLicenceType::all();

        return $data;
    }

    public function getDrivingLicenseTypeAttribute()
    {
        $drivingLicenseType = $this->hasOne(DrivingLicenceType::class,"Id","DrivingLicenceClass");
        return $drivingLicenseType->where("Active",1)->first();
    }


}
