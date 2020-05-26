<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EducationModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = "Education";
    protected $guarded = [];
    public $timestamps =false;
    protected $appends = [
        'EducationLevel',
        'EducationStatus'
    ];

    public static function saveEducation($request, $educationID)
    {
        $education = self::find($educationID);



        if ($education != null) {

            $education->StatusID = $request['educationstatus'];
            $education->Institution = $request['institution'];
            $education->LevelID = $request['educationlevel'];

            $education->save();

            return $education->fresh();
        }
        else
            return false;
    }

    public static function addEducation($request,$employee)
    {
        $education = self::create([
            'StatusID' => $request['educationstatus'],
            'Institution' => $request['institution'],
            'LevelID' => $request['educationlevel'],
            'DocumentID' => $request['documentid']
        ]);

        if ($education != null)
        {
            $employee->EducationID = $education->Id;
            $employee->save();
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
        $educationLevel = $this->hasOne(EducationLevelModel::class,"Id","LevelID");
        return $educationLevel->where("Active",1)->first();
    }

    public function getEducationStatusAttribute()
    {
        $educationStatus = $this->hasOne(EducationStatusModel::class,"Id","StatusID");
        return $educationStatus->where("Active",1)->first();
    }

}
