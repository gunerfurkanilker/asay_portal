<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DocumentFileModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = 'DocumentFile';
    public $timestamps = false;
    protected $guarded = [];

    public static function uploadEducationDocument($file,$employeeID){

        $employee = EmployeeModel::find($employeeID);
        $path = $file->store('docs');

        $documentRecord = self::create([
            'URL' => $path,
            'Type' => 1, // EducationDocument
            'Active' => 1
        ]);

        $education = EducationModel::find($employee->EducationID);

        $education->DocumentID = $documentRecord->Id;
        $education->save();

        return $path;

    }

}
