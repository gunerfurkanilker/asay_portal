<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SocialSecurityInformationModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = 'SocialSecurityInformation';

    public static function saveIDCard($request, $socialSecurityInformationID)
    {
        $socialSecurityInformation = self::find($socialSecurityInformationID);

        if ($socialSecurityInformation != null) {

            $socialSecurityInformation->SSICreateDate = new Carbon($request['sgkcreatedate']);
            $socialSecurityInformation->SSINo = $request['sgkno'];
            $socialSecurityInformation->SSIRecord = $request['sgkrecord'];
            $socialSecurityInformation->FirstLastName = $request['firstlastname'];
            $socialSecurityInformation->DisabledDegree = $request['disableddegree'];
            $socialSecurityInformation->DisabledReport = $request['disabledreport'];
            $socialSecurityInformation->JobCodeID = $request['jobcode'];
            $socialSecurityInformation->JobDescription = $request['jobdescription'];
            $socialSecurityInformation->CriminalRecord = $request['criminalrecord'];
            $socialSecurityInformation->ConvictRecord = $request['convictrecord'];
            $socialSecurityInformation->TerrorismComp = $request['terrorismrecord'];


            $socialSecurityInformation->save();

            return $socialSecurityInformation->fresh();
        }
        else
            return false;
    }

    public static function addIDCard($request,$employee)
    {

        $socialSecurityInformation = self::create([
            'SSICreateDate' => new Carbon($request['sgkcreatedate']),
            'SSINo' => $request['sgkno'],
            'SSIRecord' => $request['sgkrecord'],
            'FirstLastName' => $request['firstlastname'],
            'DisabledDegree' => $request['disableddegree'],
            'DisabledReport' => $request['disabledreport'],
            'JobCodeID' => $request['jobcode'],
            'JobDescription' => $request['jobdescription'],
            'CriminalRecord' => $request['criminalrecord'],
            'ConvictRecord' => $request['convictrecord'],
            'TerrorismComp' => $request['terrorismrecord']

        ]);

        if ($socialSecurityInformation != null)
        {
            $employee->SocialSecurityInformationID = $socialSecurityInformation->Id;
            $employee->save();
            return $socialSecurityInformation;
        }

        else
            return false;
    }
}
