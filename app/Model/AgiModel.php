<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AgiModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = 'AGI';
    protected $guarded = [];
    public $timestamps = false;
    protected $appends = [
        'MaritalStatus',
        'SpouseWorkingStatus'
    ];

    public static function saveAgi($request, $agiID)
    {
        $agiID = self::find($agiID);

        if ($agiID != null) {

            $agiID->MaritalStatusID = $request['maritalstatus'];
            $agiID->SpouseWorkingStatusID = $request['spouseworkingstatus'];
            $agiID->TotalChildren = $request['totalchildren'];
            $agiID->PrePrimaryChild = $request['preprimarychild'];
            $agiID->PrimaryChild = $request['primarychild'];
            $agiID->MiddleSchoolChild = $request['middleschoolchild'];
            $agiID->HighSchoolChild = $request['highschoolchild'];
            $agiID->UniversityChild = $request['universitychild'];

            $agiID->save();

            return $agiID->fresh();
        }
        else
            return false;
    }

    public static function addAgi($request,$employee)
    {
        $agiID = self::create([
            'MaritalStatusID' => $request['maritalstatus'],
            'SpouseWorkingStatusID' => $request['spouseworkingstatus'],
            'TotalChildren' => $request['totalchildren'],
            'PrePrimaryChild' => $request['preprimarychild'],
            'PrimaryChild' => $request['primarychild'],
            'MiddleSchoolChild' => $request['middleschoolchild'],
            'HighSchoolChild' => $request['highschoolchild'],
            'UniversityChild' => $request['universitychild']
        ]);

        if ($agiID != null)
        {
            $employee->AGIID = $agiID->Id;
            $employee->save();
            return $agiID;
        }

        else
            return false;
    }

    public static function getAGIFields()
    {
        $data = [];
        $data['MaritalStatus'] = CountryModel::all();
        $data['SpouseWorkingStatus'] = CityModel::all();

        return $data;
    }


    public function getMaritalStatusAttribute()
    {
        $maritalStatus = $this->hasOne(MartialStatusModel::class,"Id","MaritalStatusID");
        return $maritalStatus->where("Active",1)->first();
    }

    public function getSpouseWorkingStatusAttribute()
    {
        $spouseWorkingStatus = $this->hasOne(WorkingStatusModel::class,"Id","SpouseWorkingStatusID");
        return $spouseWorkingStatus->where("Active",1)->first();
    }

}
