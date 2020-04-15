<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AgiModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = 'AGI';

    public static function saveAgi($request, $agiID)
    {
        $agiID = self::find($agiID);

        if ($agiID != null) {

            $agiID->MartialStatusID = $request['martialstatus'];
            $agiID->SpouseWorkingStatus = $request['spouseworkingstatus'];
            $agiID->TotalChildren = $request['totalchilden'];
            $agiID->PrePrimaryChildren = $request['preprimarychildren'];
            $agiID->PrimaryChild = $request['primarychilden'];
            $agiID->MiddleSchoolChild = $request['middleschoolchildren'];
            $agiID->HighSchoolChild = $request['highschoolchildren'];
            $agiID->UniversityChild = $request['universitychildren'];

            $agiID->save();

            return $agiID->fresh();
        }
        else
            return false;
    }

    public static function addAgi($request,$employee)
    {
        $agiID = self::create([
            'MartialStatusID' => $request['martialstatus'],
            'SpouseWorkingStatus' => $request['spouseworkingstatus'],
            'TotalChildren' => $request['totalchilden'],
            'PrePrimaryChildren' => $request['preprimarychildren'],
            'PrimaryChild' => $request['primarychilden'],
            'MiddleSchoolChild' => $request['middleschoolchildren'],
            'HighSchoolChild' => $request['highschoolchildren'],
            'UniversityChild' => $request['universitychildren']
        ]);

        if ($agiID != null)
        {
            $employee->DrivingLicenceID = $agiID->Id;
            $employee->save();
            return $agiID;
        }

        else
            return false;
    }
}
