<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class SocialSecurityInformationModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = 'SocialSecurityInformation';
    protected $guarded = [];
    public $timestamps = false;
    protected $appends = [
        'DisabledDegree',
        'ObjectFile',
        'SSICreateDate',
        'SSINo',
        'SSIRecord',
        'SSIRecordObject',
        'FirstLastName',
        'DisabledDegreeID',
        'DisabledReport',
        'DisabledTaxDecrease',
        'JobCodeID',
        'JobDescription',
        'CriminalRecord',
        'ConvictRecord',
        'TerrorismComp'
    ];

    public static function saveSocialSecurityInformation($request)
    {
        $employee = EmployeeModel::find($request->EmployeeID);
        $socialSecurityInformation = self::where("EmployeeID",$request->EmployeeID)->first();

        if ($socialSecurityInformation == null)
            $socialSecurityInformation = new SocialSecurityInformationModel();

            $socialSecurityInformation->SSICreateDate       = $request->SSICreateDate ? $request->SSICreateDate : null ;
            $socialSecurityInformation->SSINo               = $request->SSINo;
            $socialSecurityInformation->SSIRecord           = $request->SSIRecord;
            $socialSecurityInformation->FirstLastName       = $request->FirstLastName ? $request->FirstLastName : '';
            $socialSecurityInformation->DisabledDegreeID    = $request->DisabledDegreeID;
            $socialSecurityInformation->JobCodeID           = $request->JobCodeID;
            $socialSecurityInformation->JobDescription      = $request->JobDescription ? $request->JobDescription :'';
            $socialSecurityInformation->CriminalRecord      = $request->CriminalRecord ? 1:0;
            $socialSecurityInformation->ConvictRecord       = $request->ConvictRecord ? 1:0;
            $socialSecurityInformation->TerrorismComp       = $request->TerrorismComp ? 1:0;
            $socialSecurityInformation->EmployeeID          = $request->EmployeeID;
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
                $res = $client->request("POST", 'http://'.\request()->getHttpHost().'/rest/api/disk/addFile', $guzzleParams);
                $responseBody = json_decode($res->getBody());

                if ($responseBody->status == true)
                {
                    $socialSecurityInformation->DisabledReport = $responseBody->data;
                    $socialSecurityInformation->save();
                }


            }

            if ($request->hasFile('DisabilityTaxDecrease'))
            {
                $socialSecurityInformation->DisabledTaxDecrease = self::saveDisabiltyTaxDecreaseFile($request,$socialSecurityInformation->Id);
                $result = $socialSecurityInformation->save();

            }

        $loggedUser = DB::table("Employee")->find($request->Employee);
        $employee = DB::table("Employee")->find($request->EmployeeID);
        LogsModel::setLog($request->Employee,$socialSecurityInformation->Id,15,53,"","",$loggedUser->UsageName . ' ' . $loggedUser->LastName . " adlı çalışan, " . $employee->UsageName . ' ' . $employee->LastName . " adındaki çalışanın, sosyal güvenlik bilglerini düzenledi","","","","","");

            return $result;
    }

    public static function saveDisabiltyTaxDecreaseFile($request,$id)
    {
        $file = file_get_contents($request->DisabilityTaxDecrease->path());
        $guzzleParams = [

            'multipart' => [
                [
                    'name' => 'file',
                    'contents' => $file,
                    'filename' => 'DisabilityTaxDecreaseDoc_' . $id . '.' . $request->DisabilityTaxDecrease->getClientOriginalExtension()
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
        $res = $client->request("POST", 'http://'.\request()->getHttpHost().'/rest/api/disk/addFile', $guzzleParams);
        $responseBody = json_decode($res->getBody());

        if ($responseBody->status == true)
        {
            return $responseBody->data;
        }
        else
            return false;
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
        $data['SGKRegistryNumbers'] = SGKRegistryNumbersModel::all();
        return $data;
    }

    public function getDisabledDegreeAttribute()
    {
        $disabledDegree = $this->hasOne(DisabledDegreeModel::class,"Id","DisabledDegreeID");
        return $disabledDegree->where("Active",1)->first();
    }

    public function getSSIRecordObjectAttribute()
    {
        $disabledDegree = $this->hasOne(SGKRegistryNumbersModel::class,"id","SSIRecord");
        return $disabledDegree->where("Active",1)->first();
    }

    public function getJobCodeAttribute()
    {
        $jobCode = $this->hasOne(JobModel::class,"Id","JobCodeID");
        return $jobCode->where("Active",1)->first();
    }

    public function getObjectFileAttribute()
    {
        $file = $this->hasOne(ObjectFileModel::class,"ObjectId","Id");
        return $file->where(['Active' => 1,'ObjectType' => 6])->first();
    }

    public function setSSICreateDateAttribute($value)
    {
        $this->attributes['SSICreateDate'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getSSICreateDateAttribute($value)
    {
        try {
            return $this->attributes['SSICreateDate'] !== null || $this->attributes['SSICreateDate'] != '' ? Crypt::decryptString($this->attributes['SSICreateDate']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setSSINoAttribute($value)
    {
        $this->attributes['SSINo'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getSSINoAttribute($value)
    {
        try {
            return $this->attributes['SSINo'] !== null || $this->attributes['SSINo'] != '' ? (int) Crypt::decryptString($this->attributes['SSINo']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setSSIRecordAttribute($value)
    {
        $this->attributes['SSIRecord'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getSSIRecordAttribute($value)
    {
        try {
            return $this->attributes['SSIRecord'] !== null || $this->attributes['SSIRecord'] != '' ? (int) Crypt::decryptString($this->attributes['SSIRecord']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setFirstLastNameAttribute($value)
    {
        $this->attributes['FirstLastName'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getFirstLastNameAttribute($value)
    {
        try {
            return $this->attributes['FirstLastName'] !== null || $this->attributes['FirstLastName'] != '' ? Crypt::decryptString($this->attributes['FirstLastName']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setDisabledDegreeIDAttribute($value)
    {
        $this->attributes['DisabledDegreeID'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getDisabledDegreeIDAttribute($value)
    {
        try {
            return $this->attributes['DisabledDegreeID'] !== null || $this->attributes['DisabledDegreeID'] != '' ? (int) Crypt::decryptString($this->attributes['DisabledDegreeID']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setDisabledReportAttribute($value)
    {
        $this->attributes['DisabledReport'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getDisabledReportAttribute($value)
    {
        try {
            return $this->attributes['DisabledReport'] !== null || $this->attributes['DisabledReport'] != '' ? (int) Crypt::decryptString($this->attributes['DisabledReport']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setDisabledTaxDecreaseAttribute($value)
    {
        $this->attributes['DisabledTaxDecrease'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getDisabledTaxDecreaseAttribute($value)
    {
        try {
            return $this->attributes['DisabledTaxDecrease'] !== null || $this->attributes['DisabledTaxDecrease'] != '' ? (int) Crypt::decryptString($this->attributes['DisabledTaxDecrease']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setJobCodeIDAttribute($value)
    {
        $this->attributes['JobCodeID'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getJobCodeIDAttribute($value)
    {
        try {
            return $this->attributes['JobCodeID'] !== null || $this->attributes['JobCodeID'] != '' ? (int) Crypt::decryptString($this->attributes['JobCodeID']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setJobDescriptionAttribute($value)
    {
        $this->attributes['JobDescription'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getJobDescriptionAttribute($value)
    {
        try {
            return $this->attributes['JobDescription'] !== null || $this->attributes['JobDescription'] != '' ? Crypt::decryptString($this->attributes['JobDescription']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setCriminalRecordAttribute($value)
    {
        $this->attributes['CriminalRecord'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getCriminalRecordAttribute($value)
    {
        try {
            return $this->attributes['CriminalRecord'] !== null || $this->attributes['CriminalRecord'] != '' ? (int) Crypt::decryptString($this->attributes['CriminalRecord']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setConvictRecordAttribute($value)
    {
        $this->attributes['ConvictRecord'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getConvictRecordAttribute($value)
    {
        try {
            return $this->attributes['ConvictRecord'] !== null || $this->attributes['ConvictRecord'] != '' ? (int) Crypt::decryptString($this->attributes['ConvictRecord']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setTerrorismCompAttribute($value)
    {
        $this->attributes['TerrorismComp'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getTerrorismCompAttribute($value)
    {
        try {
            return $this->attributes['TerrorismComp'] !== null || $this->attributes['TerrorismComp'] != '' ? (int) Crypt::decryptString($this->attributes['TerrorismComp']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }





}
