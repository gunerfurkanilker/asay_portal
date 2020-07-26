<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class EmployeesChildModel extends Model
{
    protected $primaryKey = "id";
    public $timestamps = false;
    protected $table = "EmployeesChild";


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

        return $record->save() ? true : false;


    }



}
