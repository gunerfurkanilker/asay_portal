<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EmployeesChildModel extends Model
{
    protected $primaryKey = "id";
    public $timestamps = false;
    protected $table = "EmployeesChild";
    protected $appends = [
        'Gender',
        'RelationshipDegree',
        'EducationLevel'
    ];


    public static function saveEmployeesChild($request)
    {
        $record = $request->childId ? self::find($request->childId) : new EmployeesChildModel();



        if ($request->isEducationContinue == 1)
        {
            $record->EmployeeID = $request->EmployeeID;
            $record->TCKN = $request->TCKN;
            $record->name = $request->name;
            $record->birth_date = new Carbon($request->birth_date);
            $record->GenderID = $request->GenderID;
            $record->father_name = $request->father_name;
            $record->mother_name = $request->mother_name;
            $record->RelationshipID = $request->RelationshipID;
            $record->education_continue = 1;
            $record->school_register_date = $request->school_register_date;
            $record->school_name = $request->school_name;
            $record->EducationLevelID = $request->EducationLevelID;
            $record->description = $request->description;
        }
        else
        {
            $record->EmployeeID = $request->EmployeeID;
            $record->TCKN = $request->TCKN;
            $record->name = $request->name;
            $record->birth_date = new Carbon($request->birth_date);
            $record->GenderID = $request->GenderID;
            $record->father_name = $request->father_name;
            $record->mother_name = $request->mother_name;
            $record->RelationshipID = $request->RelationshipID;
            $record->education_continue = 0;
            $record->school_register_date = null;
            $record->school_name = null;
            $record->EducationLevelID = null;
            $record->description = null;
        }

        $loggedUser = DB::table("Employee")->find($request->Employee);
        $employee = DB::table("Employee")->find($request->EmployeeID);
        LogsModel::setLog($request->Employee,$record->id,15,48,"","",$loggedUser->UsageName . ' ' . $loggedUser->LastName . " adl?? ??al????an, " . $employee->UsageName . ' ' . $employee->LastName . " ad??ndaki ??al????an??n, ??ocuk bilgisini d??zenlendi","","","","","");

        return $record->save() ? true : false;


    }


    public function getGenderAttribute()
    {

        $gender = $this->hasOne(GenderModel::class,"Id","GenderID");
        if ($gender)
        {
            return $gender->where("Active",1)->first();
        }
        else
        {
            return "";
        }
    }

    public function getRelationshipDegreeAttribute()
    {

        $relationship = $this->hasOne(RelationshipDegreeModel::class,"id","RelationshipID");
        if ($relationship)
        {
            return $relationship->where("active",1)->first();
        }
        else
        {
            return "";
        }
    }

    public function getEducationLevelAttribute()
    {

        $educationLevel = $this->hasOne(EducationLevelModel::class,"Id","EducationLevelID");
        if ($educationLevel)
        {
            return $educationLevel->where("Active",1)->first();
        }
        else
        {
            return "";
        }
    }

}
