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

    public static function saveEmergencyField($request, $emergencyFieldID)
    {
        $emergencyField = self::find($emergencyFieldID);

        if ($emergencyField != null) {

            $emergencyField->BloodTypeID = $request['bloodtype'];
            $emergencyField->EmergencyPerson = $request['emergencyperson'];
            $emergencyField->EPDegree = $request['emergencypersondegree'];
            $emergencyField->EPGsm = $request['emergencypersonno'];

            $emergencyField->save();

            return $emergencyField->fresh();
        }
        else
            return false;
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
