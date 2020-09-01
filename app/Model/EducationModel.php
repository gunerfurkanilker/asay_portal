<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EducationModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = "Education";
    protected $guarded = [];
    public $timestamps = false;
    protected $appends = [
        'EducationLevel',
        'EducationStatus',
        'ObjectFile'
    ];

    public static function saveEducation($request)
    {
        $education = EducationModel::find($request->EducationID);
        if ($education != null) {
            $education->EmployeeID = $request->EmployeeID;
            $education->StatusID = $request->EducationStatus;
            $education->Institution = $request->Institution;
            $education->LevelID = $request->EducationLevel;
            $result = $education->save();

            if ($result && $request->hasFile('education_file')) {


                $file = file_get_contents($request->education_file->path());
                $guzzleParams = [

                    'multipart' => [
                        [
                            'name' => 'token',
                            'contents' => $request->token
                        ],
                        [
                            'name' => 'ObjectType',
                            'contents' => 5 // Mezuniyet Belgesi
                        ],
                        [
                            'name' => 'ObjectTypeName',
                            'contents' => 'Education'
                        ],
                        [
                            'name' => 'ObjectId',
                            'contents' => $education->Id
                        ],
                        [
                            'name' => 'file',
                            'contents' => $file,
                            'filename' => 'mezuniyet_belgesi_' . $education->Id . '.' . $request->education_file->getClientOriginalExtension()
                        ],

                    ],
                ];

                $client = new \GuzzleHttp\Client();
                $res = $client->request("POST", 'http://lifi.asay.com.tr/connectUpload', $guzzleParams);
                $responseBody = json_decode($res->getBody());

                if ($responseBody->status == false)
                    return false;

            }

            return $education->fresh();
        } else
            return false;
    }

    public static function addEducation($request)
    {
        $education = new EducationModel();
        $education->EmployeeID = $request->EmployeeID;
        $education->StatusID = $request->EducationStatus;
        $education->Institution = $request->Institution;
        $education->LevelID = $request->EducationLevel;
        $result = $education->save();
        if ($result) {
            if ($request->hasFile('education_file')) {

                $file = file_get_contents($request->education_file->path());
                $guzzleParams = [

                    'multipart' => [
                        [
                            'name' => 'token',
                            'contents' => $request->token
                        ],
                        [
                            'name' => 'ObjectType',
                            'contents' => 5 // Mezuniyet Belgesi
                        ],
                        [
                            'name' => 'ObjectTypeName',
                            'contents' => 'Education'
                        ],
                        [
                            'name' => 'ObjectId',
                            'contents' => $education->Id
                        ],
                        [
                            'name' => 'file',
                            'contents' => $file,
                            'filename' => 'mezuniyet_belgesi_' . $education->Id . '.' . $request->education_file->getClientOriginalExtension()
                        ],

                    ],
                ];

                $client = new \GuzzleHttp\Client();
                $res = $client->request("POST", 'http://lifi.asay.com.tr/connectUpload', $guzzleParams);
                $responseBody = json_decode($res->getBody());

                if ($responseBody->status == false)
                    return $responseBody;
                else {
                    return $education;
                }


            }
            return $education;


        }
        else
            return false;
    }

    public static function getEducationFields()
    {
        $data = [];
        $data['EducationLevels'] = EducationLevelModel::all();
        $data['EducationStatus'] = EducationStatusModel::all();

        return $data;
    }

    public function getEducationLevelAttribute()
    {
        $educationLevel = $this->hasOne(EducationLevelModel::class, "Id", "LevelID");
        return $educationLevel->where("Active", 1)->first();
    }

    public function getEducationStatusAttribute()
    {
        $educationStatus = $this->hasOne(EducationStatusModel::class, "Id", "StatusID");
        return $educationStatus->where("Active", 1)->first();
    }

    public function getObjectFileAttribute()
    {
        $document = $this->hasOne(ObjectFileModel::class, "ObjectId", "Id");
        return $document->where(['Active' => 1,'ObjectType' => 5])->first();
    }

}
