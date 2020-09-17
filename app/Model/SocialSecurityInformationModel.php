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
        'DisabledDegree',
        'ObjectFile'
    ];

    public static function saveSocialSecurityInformation($request)
    {
        $employee = EmployeeModel::find($request->EmployeeID);
        $socialSecurityInformation = self::where("EmployeeID",$request->EmployeeID)->first();

        if ($socialSecurityInformation == null)
            $socialSecurityInformation = new SocialSecurityInformationModel();

            $socialSecurityInformation->SSICreateDate       = $request->SSICreateDate;
            $socialSecurityInformation->SSINo               = $request->SSINo;
            $socialSecurityInformation->SSIRecord           = $request->SSIRecord;
            $socialSecurityInformation->FirstLastName       = $request->FirstLastName;
            $socialSecurityInformation->DisabledDegreeID    = $request->DisabledDegreeID;
            $socialSecurityInformation->JobCodeID           = $request->JobCodeID;
            $socialSecurityInformation->JobDescription      = $request->JobDescription;
            $socialSecurityInformation->CriminalRecord      = $request->CriminalRecord;
            $socialSecurityInformation->ConvictRecord       = $request->ConvictRecord;
            $socialSecurityInformation->TerrorismComp       = $request->TerrorismComp;
            $result = $socialSecurityInformation->save();

            if ($result && $request->hasFile('disability_file')) {


                $file = file_get_contents($request->disability_file->path());
                $guzzleParams = [

                    'multipart' => [
                        [
                            'name' => 'file',
                            'contents' => $file,
                            'filename' => 'DisabilityDoc_' . $employee->Id . '.' . $request->disability_file->getClientOriginalExtension()
                        ],
                        [
                            'name' => 'moduleId',
                            'contents' => 'socialsecurity'
                        ],
                        [
                            'name' => 'token',
                            'contents' => $request->token
                        ]

                    ],
                ];

                $client = new \GuzzleHttp\Client();
                $res = $client->request("POST", 'http://portal.asay.com.tr/api/disk/addFile', $guzzleParams);
                $responseBody = json_decode($res->getBody());

                if ($responseBody->status == true)
                {
                    $socialSecurityInformation->DisabledReport = $responseBody->data;
                    $socialSecurityInformation->save();
                }


            }

            return $result;
    }

    public static function addSocialSecurityInformation($request,$employee)
    {

        $socialSecurityInformation = self::create([
            'SSICreateDate' => new Carbon($request->sgkcreatedate),
            'SSINo' => $request->sgkno,
            'SSIRecord' => $request->sgkrecord,
            'FirstLastName' => $request->firstlastname,
            'DisabledDegreeID' => $request->disableddegree,
            'DisabledReport' => $request->disabledreport,
            'JobCodeID' => $request->jobcode,
            'JobDescription' => $request->jobdescription,
            'CriminalRecord' => $request->criminalrecord,
            'ConvictRecord' => $request->convictrecord,
            'TerrorismComp' => $request->terrorismcomp

        ]);

        if ($socialSecurityInformation != null)
        {

            if ($request->hasFile('disability_file')) {


                $file = file_get_contents($request->disability_file->path());
                $guzzleParams = [

                    'multipart' => [
                        [
                            'name' => 'token',
                            'contents' => $request->token
                        ],
                        [
                            'name' => 'ObjectType',
                            'contents' => 6 // Engelli Raporu
                        ],
                        [
                            'name' => 'ObjectTypeName',
                            'contents' => 'Disability'
                        ],
                        [
                            'name' => 'ObjectId',
                            'contents' => $socialSecurityInformation->Id
                        ],
                        [
                            'name' => 'file',
                            'contents' => $file,
                            'filename' => 'mezuniyet_belgesi_' . $socialSecurityInformation->Id . '.' . $request->disability_file->getClientOriginalExtension()
                        ],

                    ],
                ];

                $client = new \GuzzleHttp\Client();
                $res = $client->request("POST", 'http://lifi.asay.com.tr/connectUpload', $guzzleParams);
                $responseBody = json_decode($res->getBody());

                if ($responseBody->status == false)
                    return false;
                else {
                    $employee->SocialSecurityInformationID = $socialSecurityInformation->Id;
                    $employee->save();
                    return $socialSecurityInformation;
                }


            }


            $employee->SocialSecurityInformationID = $socialSecurityInformation->Id;
            $employee->save();
            return $socialSecurityInformation;
        }

        else
            return false;
    }

    public static function getSSIFields()
    {
        $data = [];
        $data['DisabledDegrees'] = DisabledDegreeModel::all();
        $data['Jobs'] = JobModel::all();

        return $data;
    }

    public function getDisabledDegreeAttribute()
    {
        $disabledDegree = $this->hasOne(DisabledDegreeModel::class,"Id","DisabledDegreeID");
        return $disabledDegree->where("Active",1)->first();
    }

    public function getObjectFileAttribute()
    {
        $file = $this->hasOne(ObjectFileModel::class,"ObjectId","Id");
        return $file->where(['Active' => 1,'ObjectType' => 6])->first();
    }

}
