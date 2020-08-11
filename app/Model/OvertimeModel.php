<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OvertimeModel extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'Overtime';
    public $timestamps = false;

    protected $appends = [
        'AssignedEmployee',
        'City',
        'CreatedBy',
        //'Field',
        'Kind',
        'Project',
        'Status',


    ];
    protected $guarded = [];

    public static function getOvertimeByStatus($status,$userId)
    {
        $user = UserModel::find($userId);
        return self::where(['StatusID' => $status, 'ManagerID' => $user->EmployeeID ])->orderBy('BeginDate','desc')->get();
    }

    public static function getOvertimeFields($managerId){
        $fields = [];
        $fields['kinds'] = OvertimeKindModel::all();
        $fields['cities'] = CityModel::all();
        $fields['workingfields'] = WorkingFieldModel::all();

        return $fields;

    }

    public static function getManagersEmployees($managerId){
        $employeePositions = EmployeePositionModel::where('Active',2)->where('ManagerID',$managerId)->get();
        $employeeList = [];
        foreach ($employeePositions as $employeePosition)
        {
            $tempEmployee = EmployeeModel::select('Id','UsageName','LastName')->where('Id',$employeePosition->EmployeeID)->first();
            array_push($employeeList,$tempEmployee);
        }
        return $employeeList;
    }

    public function getAssignedEmployeeAttribute()
    {

        $assignedEmployee = $this->hasOne(EmployeeModel::class,"Id","AssignedID");
        if ($assignedEmployee)
        {
            return $assignedEmployee->where("Active",1)->first();
        }
        else
        {
            return "";
        }
    }

    public static function saveOvertimeByProcessType($procestype,$overtimeRequest){
        switch ($procestype)
        {
            case 0:
                return self::saveOvertimeRequest($overtimeRequest);
            case 1:
                return self::sendOvertimeRequestToEmployee($overtimeRequest);
            case 2:
                return self::overtimeCorrectionRequestFromEmployee($overtimeRequest);
            case 3:
                return self::overtimeRejectRequestFromEmployee($overtimeRequest);
            case 4:
                return self::overtimeApproveRequestFromEmployee($overtimeRequest);
            case 5:
                return self::overtimeCancelRequestFromEmployee($overtimeRequest);
            case 6:
                return self::overtimeCompleteRequestFromEmployee($overtimeRequest);
            case 7:
                return self::overtimeCorrectionRequestFromManager($overtimeRequest);
            case 8:
                return self::overtimeApproveRequestFromManager($overtimeRequest);
            case 9:
                return self::overtimeCorrectionRequestFromHR($overtimeRequest);
            case 10:
                return self::overtimeApproveRequestFromHR($overtimeRequest);

        }
    }

    public static function saveOvertimeRequest($overtimeRequest){

        $overtimeRecord = !isset($overtimeRequest['overtimeId']) || $overtimeRequest['overtimeId'] == null
            ? new OvertimeModel() :
            OvertimeModel::where([ 'id' => $overtimeRequest['overtimeId'], 'Active' => 1 ])->first();

        $overtimeRecord->CreatedBy = $overtimeRequest['CreatedBy'];
        $overtimeRecord->ManagerID = $overtimeRequest['ManagerID'];
        $overtimeRecord->AssignedID = $overtimeRequest['AssignedID'];
        $overtimeRecord->KindID = $overtimeRequest['KindID'];
        $overtimeRecord->BeginDate = $overtimeRequest['BeginDate'];
        $overtimeRecord->ProjectID = $overtimeRequest['ProjectID'];
        $overtimeRecord->JobOrderNo = $overtimeRequest['JobOrderNo'];
        $overtimeRecord->CityID = $overtimeRequest['CityID'];
        $overtimeRecord->FieldID = $overtimeRequest['FieldID'];
        $overtimeRecord->FieldName = $overtimeRequest['FieldName'];
        $overtimeRecord->WorkHour = $overtimeRequest['WorkHour'];
        $overtimeRecord->PlateNumber = $overtimeRequest['PlateNumber'];
        $overtimeRecord->Description = $overtimeRequest['Description'];
        $overtimeRecord->StatusID = 0;

        if (!$overtimeRecord->save())
        {
            return true;
        }
        else
            return false;

    }

    public static function sendOvertimeRequestToEmployee($overtimeRequest){

        $overtimeRecord = !isset($overtimeRequest['overtimeId']) || $overtimeRequest['overtimeId'] == null
            ? new OvertimeModel() :
            OvertimeModel::where([ 'id' => $overtimeRequest['overtimeId'], 'Active' => 1 ])->first();

        $overtimeRecord->CreatedBy = $overtimeRequest['CreatedBy'];
        $overtimeRecord->ManagerID = $overtimeRequest['ManagerID'];
        $overtimeRecord->AssignedID = $overtimeRequest['AssignedID'];
        $overtimeRecord->KindID = $overtimeRequest['KindID'];
        $overtimeRecord->BeginDate = $overtimeRequest['BeginDate'];
        $overtimeRecord->ProjectID = $overtimeRequest['ProjectID'];
        $overtimeRecord->JobOrderNo = $overtimeRequest['JobOrderNo'];
        $overtimeRecord->CityID = $overtimeRequest['CityID'];
        $overtimeRecord->FieldID = $overtimeRequest['FieldID'];
        $overtimeRecord->FieldName = $overtimeRequest['FieldName'];
        $overtimeRecord->WorkHour = $overtimeRequest['WorkHour'];
        $overtimeRecord->PlateNumber = $overtimeRequest['PlateNumber'];
        $overtimeRecord->Description = $overtimeRequest['Description'];
        $overtimeRecord->StatusID = 1;

        if (!$overtimeRecord->save())
        {
            return true;
        }
        else
            return false;

    }

    public static function overtimeCorrectionRequestFromEmployee($overtimeRequest){

        $overtimeRecord = OvertimeModel::where([ 'id' => $overtimeRequest['overtimeId'], 'Active' => 1 ])->first();

        $overtimeRecord->CreatedBy = $overtimeRequest['CreatedBy'];
        $overtimeRecord->ManagerID = $overtimeRequest['ManagerID'];
        $overtimeRecord->AssignedID = $overtimeRequest['AssignedID'];
        $overtimeRecord->KindID = $overtimeRequest['KindID'];
        $overtimeRecord->BeginDate = $overtimeRequest['BeginDate'];
        $overtimeRecord->ProjectID = $overtimeRequest['ProjectID'];
        $overtimeRecord->JobOrderNo = $overtimeRequest['JobOrderNo'];
        $overtimeRecord->CityID = $overtimeRequest['CityID'];
        $overtimeRecord->FieldID = $overtimeRequest['FieldID'];
        $overtimeRecord->FieldName = $overtimeRequest['FieldName'];
        $overtimeRecord->WorkHour = $overtimeRequest['WorkHour'];
        $overtimeRecord->PlateNumber = $overtimeRequest['PlateNumber'];
        $overtimeRecord->Description = $overtimeRequest['Description'];

        $dirtyFields = $overtimeRecord->getDirty();

        foreach ($dirtyFields as $field => $newdata)
        {
            $olddata = $overtimeRecord->getOriginal($field);
            if ($olddata != $newdata)
            {
                //TODO Loglama İşlemi burada yapılacak.
            }
        }

        $overtimeRecord->StatusID = 2;



        if ($overtimeRecord->save() )
            return true;
        else
            return false;

    }

    public static function overtimeRejectRequestFromEmployee($overtimeRequest){
        $overtimeRecord = OvertimeModel::where([ 'id' => $overtimeRequest['overtimeId'], 'Active' => 1 ])->first();

        $overtimeRecord->CreatedBy = 2;
        $overtimeRecord->SupervisorManagerID = 2;
        $overtimeRecord->AssignedID = 2;
        $overtimeRecord->KindID = 2;
        $overtimeRecord->BeginDate = 2;
        $overtimeRecord->ProjectID = 2;
        $overtimeRecord->JobOrderNo = 2;
        $overtimeRecord->CityID = 2;
        $overtimeRecord->FieldID = 2;
        $overtimeRecord->WorkHour = 2;
        $overtimeRecord->PlateNumber = 2;
        $overtimeRecord->Description = 2;

        $dirtyFields = $overtimeRecord->getDirty();

        foreach ($dirtyFields as $field => $newdata)
        {
            $olddata = $overtimeRecord->getOriginal($field);
            if ($olddata != $newdata)
            {
                //TODO Loglama İşlemi burada yapılacak.
            }
        }

        $overtimeRecord->StatusID = 3;

        if ($overtimeRecord->save())
            return true;
        else
            return false;

    }

    public static function overtimeApproveRequestFromEmployee($overtimeRequest){

        $overtimeRecord = OvertimeModel::where([ 'id' => $overtimeRequest['overtimeId'], 'Active' => 1 ])->first();
        $overtimeRecord->StatusID = 4;

        //TODO Loglama ve mail gönderimi yapılacak

        if ($overtimeRecord->save() )
            return true;
        else
            return false;
    }

    public static function overtimeCancelRequestFromEmployee($overtimeRequest){

        $overtimeRecord = OvertimeModel::where([ 'id' => $overtimeRequest['overtimeId'], 'Active' => 1 ])->first();
        $overtimeRecord->StatusID = 5;

        //TODO Loglama ve mail göndeirmi yapılacak

        if ($overtimeRecord->save() )
            return true;
        else
            return false;

    }

    public static function overtimeCompleteRequestFromEmployee($overtimeRequest){

        $overtimeRecord = OvertimeModel::where([ 'id' => $overtimeRequest['overtimeId'], 'Active' => 1 ])->first();
        $overtimeRecord->StatusID = 6;

        //TODO Loglama ve mail göndeirmi yapılacak

        if ($overtimeRecord->save() )
            return true;
        else
            return false;

    }

    public static function overtimeCorrectionRequestFromManager($overtimeRequest){

    $overtimeRecord = OvertimeModel::where([ 'id' => $overtimeRequest['overtimeId'], 'Active' => 1 ])->first();

    $overtimeRecord->CreatedBy = $overtimeRequest['CreatedBy'];
    $overtimeRecord->ManagerID = $overtimeRequest['ManagerID'];
    $overtimeRecord->AssignedID = $overtimeRequest['AssignedID'];
    $overtimeRecord->KindID = $overtimeRequest['KindID'];
    $overtimeRecord->BeginDate = $overtimeRequest['BeginDate'];
    $overtimeRecord->ProjectID = $overtimeRequest['ProjectID'];
    $overtimeRecord->JobOrderNo = $overtimeRequest['JobOrderNo'];
    $overtimeRecord->CityID = $overtimeRequest['CityID'];
    $overtimeRecord->FieldID = $overtimeRequest['FieldID'];
    $overtimeRecord->FieldName = $overtimeRequest['FieldName'];
    $overtimeRecord->WorkHour = $overtimeRequest['WorkHour'];
    $overtimeRecord->PlateNumber = $overtimeRequest['PlateNumber'];
    $overtimeRecord->Description = $overtimeRequest['Description'];

    $dirtyFields = $overtimeRecord->getDirty();

    foreach ($dirtyFields as $field => $newdata)
    {
        $olddata = $overtimeRecord->getOriginal($field);
        if ($olddata != $newdata)
        {
            //TODO Loglama İşlemi burada yapılacak.
        }
    }

    $overtimeRecord->StatusID = 7;


    if ($overtimeRecord->save() )
        return true;
    else
        return false;

}

    public static function overtimeApproveRequestFromManager($overtimeRequest){

        $overtimeRecord = OvertimeModel::where([ 'id' => $overtimeRequest['overtimeId'], 'Active' => 1 ])->first();

        $overtimeRecord->CreatedBy = $overtimeRequest['CreatedBy'];
        $overtimeRecord->ManagerID = $overtimeRequest['ManagerID'];
        $overtimeRecord->AssignedID = $overtimeRequest['AssignedID'];
        $overtimeRecord->KindID = $overtimeRequest['KindID'];
        $overtimeRecord->BeginDate = $overtimeRequest['BeginDate'];
        $overtimeRecord->ProjectID = $overtimeRequest['ProjectID'];
        $overtimeRecord->JobOrderNo = $overtimeRequest['JobOrderNo'];
        $overtimeRecord->CityID = $overtimeRequest['CityID'];
        $overtimeRecord->FieldID = $overtimeRequest['FieldID'];
        $overtimeRecord->FieldName = $overtimeRequest['FieldName'];
        $overtimeRecord->WorkHour = $overtimeRequest['WorkHour'];
        $overtimeRecord->PlateNumber = $overtimeRequest['PlateNumber'];
        $overtimeRecord->Description = $overtimeRequest['Description'];

        $dirtyFields = $overtimeRecord->getDirty();

        foreach ($dirtyFields as $field => $newdata)
        {
            $olddata = $overtimeRecord->getOriginal($field);
            if ($olddata != $newdata)
            {
                //TODO Loglama İşlemi burada yapılacak.
            }
        }

        $overtimeRecord->StatusID = 8;


        if ($overtimeRecord->save() )
            return true;
        else
            return false;

    }

    public static function overtimeCorrectionRequestFromHR($overtimeRequest){

        $overtimeRecord = OvertimeModel::where([ 'id' => $overtimeRequest['overtimeId'], 'Active' => 1 ])->first();

        $overtimeRecord->CreatedBy = $overtimeRequest['CreatedBy'];
        $overtimeRecord->ManagerID = $overtimeRequest['ManagerID'];
        $overtimeRecord->AssignedID = $overtimeRequest['AssignedID'];
        $overtimeRecord->KindID = $overtimeRequest['KindID'];
        $overtimeRecord->BeginDate = $overtimeRequest['BeginDate'];
        $overtimeRecord->ProjectID = $overtimeRequest['ProjectID'];
        $overtimeRecord->JobOrderNo = $overtimeRequest['JobOrderNo'];
        $overtimeRecord->CityID = $overtimeRequest['CityID'];
        $overtimeRecord->FieldID = $overtimeRequest['FieldID'];
        $overtimeRecord->FieldName = $overtimeRequest['FieldName'];
        $overtimeRecord->WorkHour = $overtimeRequest['WorkHour'];
        $overtimeRecord->PlateNumber = $overtimeRequest['PlateNumber'];
        $overtimeRecord->Description = $overtimeRequest['Description'];

        $dirtyFields = $overtimeRecord->getDirty();

        foreach ($dirtyFields as $field => $newdata)
        {
            $olddata = $overtimeRecord->getOriginal($field);
            if ($olddata != $newdata)
            {
                //TODO Loglama İşlemi burada yapılacak.
            }
        }

        $overtimeRecord->StatusID = 9;


        if ($overtimeRecord->save() )
            return true;
        else
            return false;

    }

    public static function overtimeApproveRequestFromHR($overtimeRequest){

        $overtimeRecord = OvertimeModel::where([ 'id' => $overtimeRequest['overtimeId'], 'Active' => 1 ])->first();

        $overtimeRecord->CreatedBy = $overtimeRequest['CreatedBy'];
        $overtimeRecord->ManagerID = $overtimeRequest['ManagerID'];
        $overtimeRecord->AssignedID = $overtimeRequest['AssignedID'];
        $overtimeRecord->KindID = $overtimeRequest['KindID'];
        $overtimeRecord->BeginDate = $overtimeRequest['BeginDate'];
        $overtimeRecord->ProjectID = $overtimeRequest['ProjectID'];
        $overtimeRecord->JobOrderNo = $overtimeRequest['JobOrderNo'];
        $overtimeRecord->CityID = $overtimeRequest['CityID'];
        $overtimeRecord->FieldID = $overtimeRequest['FieldID'];
        $overtimeRecord->FieldName = $overtimeRequest['FieldName'];
        $overtimeRecord->WorkHour = $overtimeRequest['WorkHour'];
        $overtimeRecord->PlateNumber = $overtimeRequest['PlateNumber'];
        $overtimeRecord->Description = $overtimeRequest['Description'];

        $dirtyFields = $overtimeRecord->getDirty();

        foreach ($dirtyFields as $field => $newdata)
        {
            $olddata = $overtimeRecord->getOriginal($field);
            if ($olddata != $newdata)
            {
                //TODO Loglama İşlemi burada yapılacak.
            }
        }

        $overtimeRecord->StatusID = 10;


        if ($overtimeRecord->save() )
            return true;
        else
            return false;

    }

    public function getCityAttribute()
    {

        $cities = $this->hasOne(CityModel::class,"Id","CityID");
        if ($cities)
        {
            return $cities->where("Active",1)->first();
        }
        else
        {
            return "";
        }
    }

    public function getCreatedByAttribute()
    {

        $createdBy = $this->hasOne(EmployeeModel::class,"Id","CityID");
        if ($createdBy)
        {
            return $createdBy->where("Active",1)->first();
        }
        else
        {
            return "";
        }

    }

    public function getKindAttribute()
    {

        $kind = $this->hasOne(OvertimeKindModel::class,"id","KindID");
        if ($kind)
        {
            return $kind->first();
        }
        else
        {
            return "";
        }

    }

    public function getProjectAttribute()
    {

        $project = $this->hasOne(ProjectsModel::class,"id","ProjectID");
        if ($project)
        {
            return $project->where("Active",1)->first();
        }
        else
        {
            return "";
        }

    }

    public function getStatusAttribute()
    {

        $status = $this->hasOne(OvertimeStatusModel::class,"id","StatusID");
        if ($status)
        {
            return $status->first();
        }
        else
        {
            return "";
        }

    }


}
