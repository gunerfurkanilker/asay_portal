<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SocialSecurityInformationModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = 'SocialSecurityInformation';
    protected $guarded = [];
    public $timestamps = false;
    protected $appends = [
        'DisabledDegree'
    ];

    public static function saveSocialSecurityInformation($request, $socialSecurityInformationID)
    {
        $socialSecurityInformation = self::find($socialSecurityInformationID);

        if ($socialSecurityInformation != null) {

            $socialSecurityInformation->SSICreateDate = new Carbon($request['sgkcreatedate']);
            $socialSecurityInformation->SSINo = $request['sgkno'];
            $socialSecurityInformation->SSIRecord = $request['sgkrecord'];
            $socialSecurityInformation->FirstLastName = $request['firstlastname'];
            $socialSecurityInformation->DisabledDegreeID = $request['disableddegree'];
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

    public static function addSocialSecurityInformation($request,$employee)
    {

        $socialSecurityInformation = self::create([
            'SSICreateDate' => new Carbon($request['sgkcreatedate']),
            'SSINo' => $request['sgkno'],
            'SSIRecord' => $request['sgkrecord'],
            'FirstLastName' => $request['firstlastname'],
            'DisabledDegreeID' => $request['disableddegree'],
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

    public function getDisabledDegreeAttribute()
    {
        $disabledDegree = $this->hasOne(DisabledDegreeModel::class,"Id","DisabledDegreeID");
        return $disabledDegree->where("Active",1)->first();
    }
}
