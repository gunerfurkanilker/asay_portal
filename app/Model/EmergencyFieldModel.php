<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class EmergencyFieldModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = 'EmergencyField';
    public $timestamps = false;
    protected $guarded = [];
    protected $appends = [
        'BloodType'
    ];

    public static function saveEmergencyField($request)
    {
        $emergencyPersonFirst   = EmergencyFieldModel::where(['EmployeeID' => $request->EmployeeID, 'Priority' => 1])->first();
        $emergencyPersonSecond  = EmergencyFieldModel::where(['EmployeeID' => $request->EmployeeID, 'Priority' => 0])->first();

        if ($emergencyPersonFirst == null)
        {
            $emergencyPersonFirst = new EmergencyFieldModel();
            $emergencyPersonFirst->EmployeeID = $request->EmployeeID;
            $emergencyPersonFirst->Priority = 1;
        }

        if ($emergencyPersonSecond == null)
        {
            $emergencyPersonSecond = new EmergencyFieldModel();
            $emergencyPersonSecond->EmployeeID = $request->EmployeeID;
            $emergencyPersonSecond->Priority = 0;
        }

        $emergencyPersonFirst->BloodTypeID          = $request->BloodTypeID;
        $emergencyPersonFirst->EmergencyPerson      = $request->EmergencyPersonFirst ? $request->EmergencyPersonFirst : "";
        $emergencyPersonFirst->EPDegree             = $request->EmergencyPersonRelationshipDegreeFirst ? $request->EmergencyPersonRelationshipDegreeFirst : "";
        $emergencyPersonFirst->EPGsm                = $request->EmergencyPersonPhoneNoFirst ? $request->EmergencyPersonPhoneNoFirst : "";

        $emergencyPersonSecond->BloodTypeID         = $request->BloodTypeID;
        $emergencyPersonSecond->EmergencyPerson     = $request->EmergencyPersonSecond ? $request->EmergencyPersonSecond : "";
        $emergencyPersonSecond->EPDegree            = $request->EmergencyPersonRelationshipDegreeSecond ? $request->EmergencyPersonRelationshipDegreeSecond : "";
        $emergencyPersonSecond->EPGsm               = $request->EmergencyPersonPhoneNoSecond ? $request->EmergencyPersonPhoneNoSecond : "";


        return $emergencyPersonFirst->save() && $emergencyPersonSecond->save() ? true : false;

    }

    public static function addEmergencyField($request,$employee)
    {
        $emergencyField = self::create([
            'BloodTypeID' => $request['bloodtype'],
            'EmergencyPerson' => $request['emergencyperson'],
            'EPDegree' => $request['emergencypersondegree'],
            'EPGsm' => $request['emergencypersonno']
        ]);

        if ($emergencyField != null)
        {
            $employee->EmergencyFieldID = $emergencyField->Id;
            $employee->save();
            return $emergencyField;
        }

        else
            return false;
    }

    public static function getEmergencyFields()
    {
        $data = [];
        $data['BloodTypes'] = BloodTypeModel::all();
        return $data;
    }

    public function getBloodTypeAttribute()
    {
        $bloodType = $this->hasOne(BloodTypeModel::class,"Id","BloodTypeID");

        return $bloodType->where("Active",1)->first();
    }
}
