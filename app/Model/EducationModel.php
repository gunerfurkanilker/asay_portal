<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class EducationModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = "Education";

    public static function saveEducation($request, $educationID)
    {
        $education = self::find($educationID);

        if ($education != null) {

            $education->StatusID = $request['educationstatus'];
            $education->Institutions = $request['schoolname'];
            $education->LevelID = $request['educationlevel'];
            $education->DocumentID = $request['graduationdocument'];

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
            'Institutions' => $request['schoolname'],
            'LevelID' => $request['educationlevel'],
            'DocumentID' => $request['graduationdocument']
        ]);

        if ($education)
        {
            $employee->EducationID = $education->Id;
            $employee->save();
            return $education;
        }

        else
            return false;
    }

}
