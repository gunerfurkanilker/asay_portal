<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
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
    ];

    public static function saveEducation($request)
    {
        $education = EducationModel::find($request->EducationID);
        $employee = EmployeeModel::find($request->EmployeeID);
        $bool = true;
        if ($education == null)
        {
            $education = new EducationModel();
            $loggedUser = DB::table("Employee")->find($request->Employee);
            LogsModel::setLog($request->Employee,$education->Id,15,41,"","",$loggedUser->UsageName . ' ' . $loggedUser->LastName . " adlı çalışan, " . $employee->UsageName . ' ' . $employee->LastName . " adındaki çalışana eğitim bilgisi ekledi","","","","","");
            $bool = false;
        }

        $education->EmployeeID = $request->EmployeeID;
        $education->StatusID = $request->EducationStatus;
        $education->Institution = $request->Institution;
        $education->LevelID = $request->EducationLevel;

        if ($bool)
        {
            $loggedUser = DB::table("Employee")->find($request->Employee);
            $dirtyFields = $education->getDirty();
            foreach ($dirtyFields as $field => $newdata) {
                $olddata = $education->getOriginal($field);
                if ($olddata != $newdata) {
                    LogsModel::setLog($request->Employee,$education->Id,15,42,$olddata,$newdata,$loggedUser->UsageName . ' ' . $loggedUser->LastName . " adlı çalışan, " . $employee->UsageName . ' ' . $employee->LastName . " adındaki çalışanın eğitim bilgisini düzenledi","","","",$field,"");
                }
            }
        }

        $result = $education->save();

        if ($result && $request->hasFile('education_file')) {


            $file = file_get_contents($request->education_file->path());
            $guzzleParams = [

                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => $file,
                        'filename' => 'GraduationDoc_' . $employee->Id . '.' . $request->education_file->getClientOriginalExtension()
                    ],
                    [
                        'name' => 'moduleId',
                        'contents' => 'education'
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
                $education->EducationFile = $responseBody->data;
                $education->save();
            }

        }
        return $result;

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


}
