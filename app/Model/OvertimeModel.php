<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class OvertimeModel extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'Overtime';
    public $timestamps = false;

    protected $appends = [
        'AssignedEmployee',
        'City',
        'CreatedFrom',
        'ApproveWho',
        //'Field',
        'Kind',
        'Project',
        'Status',


    ];
    protected $guarded = [];

    public static function getOvertimeByStatus($status,$userId)
    {
        $user = UserModel::find($userId);
        $userEmployees = EmployeePositionModel::where(['Active' => 2,'ManagerID' => $user->EmployeeID])->get();
        $userEmployeesIDs=[];
        foreach ($userEmployees as $userEmployee){
            array_push($userEmployeesIDs,$userEmployee->EmployeeID);
        }
            return self::where(['StatusID' => $status])->where(function($query) use ($user,$userEmployeesIDs) {
             $query->orWhere(['ManagerID' => $user->EmployeeID, 'CreatedBy' => $user->EmployeeID])->orWhereIn('CreatedBy',$userEmployeesIDs);
         })->orderBy('BeginDate','desc')->get();

    }

    public static function getEmployeesOvertimeByStatus($status,$userId)
    {
        $user = UserModel::find($userId);
        return self::where(['StatusID' => $status, 'AssignedID' => $user->EmployeeID ])->orderBy('BeginDate','desc')->get();
    }

    public static function getOvertimeFields($managerId){
        $fields = [];
        $fields['kinds'] = OvertimeKindModel::all();
        $fields['cities'] = CityModel::all();
        $fields['workingfields'] = WorkingFieldModel::all();

        return $fields;

    }

    public static function getManagersEmployees($managerId){
        $employeePositions = EmployeePositionModel::where(['Active' => 2])->where(function ($query) use ($managerId) {
            $query->where('HRManagerID',$managerId)->orWhere(['ManagerID' => $managerId]);
        })->get();
        $employeeList = [];
        foreach($employeePositions as $employeePosition){
            $tempPositions = EmployeePositionModel::where('Active',2)->where('ManagerID',$employeePosition->EmployeeID)->get();
            foreach($tempPositions as $tempPosition)
            {
                $tempEmployee = EmployeeModel::select('Id','UsageName','LastName')->where('Id',$tempPosition->EmployeeID)->where('Active',1)->first();
                $tempEmployee ? array_push($employeeList,$tempEmployee) : '';
            }
        }


        foreach ($employeePositions as $employeePosition)
        {
            $tempEmployee = EmployeeModel::select('Id','UsageName','LastName')->where('Id',$employeePosition->EmployeeID)->where('Active',1)->first();
            $tempEmployee ? array_push($employeeList,$tempEmployee) : '';
        }
        return $employeeList;
    }

    public static function getEmployeesManagers($employeeID){

        $employeeManagerPosition = EmployeePositionModel::where('Active',2)->where('EmployeeID',$employeeID)->first();
        $projects = ProjectsModel::all();
        $projectManagers = [];
        foreach($projects as $value){
            if(!in_array($value->manager_id, $projectManagers, true)){
                array_push($projectManagers, $value->manager_id);
            }
        }

        $managerIDList = [];
        array_push($managerIDList,$employeeManagerPosition->ManagerID);

        foreach($projectManagers as $value){
            if(!in_array($value, $managerIDList, true)){
                array_push($managerIDList, $value);
            }
        }

        $managerList = [];
        foreach ($managerIDList as $managerID){
            $temp = EmployeeModel::select('Id','UsageName','LastName')->where('Id',$managerID)->where('Active',1)->first();
            $temp ? array_push($managerList,$temp) : '';
        }

        return $managerList;

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

        $overtimeRecord = !isset($overtimeRequest['OvertimeId']) || $overtimeRequest['OvertimeId'] == null
            ? new OvertimeModel() :
            OvertimeModel::where([ 'id' => $overtimeRequest['OvertimeId'], 'Active' => 1 ])->first();

        $overtimeRecord->CreatedBy = $overtimeRequest['CreatedBy'];
        $overtimeRecord->ManagerID = $overtimeRequest['ManagerID'];
        $overtimeRecord->AssignedID = $overtimeRequest['AssignedID'];
        $overtimeRecord->KindID = $overtimeRequest['KindID'];
        $overtimeRecord->BeginDate = $overtimeRequest['BeginDate'];
        $overtimeRecord->BeginTime = $overtimeRequest['BeginTime'];
        $overtimeRecord->ProjectID = $overtimeRequest['ProjectID'];
        $overtimeRecord->JobOrderNo = $overtimeRequest['JobOrderNo'];
        $overtimeRecord->CityID = $overtimeRequest['CityID'];
        $overtimeRecord->FieldID = $overtimeRequest['FieldID'];
        $overtimeRecord->FieldName = $overtimeRequest['FieldName'];
        $overtimeRecord->WorkHour = $overtimeRequest['WorkHour'];
        $overtimeRecord->UsingCar = $overtimeRequest['UsingCar'];
        $overtimeRecord->PlateNumber = $overtimeRequest['PlateNumber'];
        $overtimeRecord->Description = $overtimeRequest['Description'];
        $overtimeRecord->StatusID = 0;

        if ($overtimeRecord->save())
        {
            return true;
        }
        else
            return false;

    }

    public static function sendOvertimeRequestToEmployee($overtimeRequest){

        $overtimeRecord = !isset($overtimeRequest['OvertimeId']) || $overtimeRequest['OvertimeId'] == null
            ? new OvertimeModel() :
            OvertimeModel::where([ 'id' => $overtimeRequest['OvertimeId'], 'Active' => 1 ])->first();

        $overtimeRecord->CreatedBy = $overtimeRequest['CreatedBy'];
        $overtimeRecord->ManagerID = $overtimeRequest['ManagerID'];
        $overtimeRecord->AssignedID = $overtimeRequest['AssignedID'];
        $overtimeRecord->KindID = $overtimeRequest['KindID'];
        $overtimeRecord->BeginDate = $overtimeRequest['BeginDate'];
        $overtimeRecord->BeginTime = $overtimeRequest['BeginTime'];
        $overtimeRecord->ProjectID = $overtimeRequest['ProjectID'];
        $overtimeRecord->JobOrderNo = $overtimeRequest['JobOrderNo'];
        $overtimeRecord->CityID = $overtimeRequest['CityID'];
        $overtimeRecord->FieldID = $overtimeRequest['FieldID'];
        $overtimeRecord->FieldName = $overtimeRequest['FieldName'];
        $overtimeRecord->WorkHour = $overtimeRequest['WorkHour'];
        $overtimeRecord->UsingCar = $overtimeRequest['UsingCar'];
        $overtimeRecord->PlateNumber = $overtimeRequest['PlateNumber'];
        $overtimeRecord->Description = $overtimeRequest['Description'];
        $overtimeRecord->StatusID = 1;

        if ($overtimeRecord->save())
        {
            return true;
        }
        else
            return false;

    }

    public static function overtimeCorrectionRequestFromEmployee($overtimeRequest){

        $overtimeRecord = OvertimeModel::where([ 'id' => $overtimeRequest['OvertimeId'], 'Active' => 1 ])->first();

        //$overtimeRecord->CreatedBy = $overtimeRequest['CreatedBy'];
        //$overtimeRecord->ManagerID = $overtimeRequest['ManagerID'];
        $overtimeRecord->AssignedID = $overtimeRequest['AssignedID'];
        $overtimeRecord->KindID = $overtimeRequest['KindID'];
        $overtimeRecord->BeginDate = $overtimeRequest['BeginDate'];
        $overtimeRecord->BeginTime = $overtimeRequest['BeginTime'];
        $overtimeRecord->ProjectID = $overtimeRequest['ProjectID'];
        $overtimeRecord->JobOrderNo = $overtimeRequest['JobOrderNo'];
        $overtimeRecord->CityID = $overtimeRequest['CityID'];
        $overtimeRecord->FieldID = $overtimeRequest['FieldID'];
        $overtimeRecord->FieldName = $overtimeRequest['FieldName'];
        $overtimeRecord->WorkHour = $overtimeRequest['WorkHour'];
        $overtimeRecord->UsingCar = $overtimeRequest['UsingCar'];
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
        $overtimeRecord = OvertimeModel::where([ 'id' => $overtimeRequest['OvertimeId'], 'Active' => 1 ])->first();

        //$overtimeRecord->CreatedBy = $overtimeRequest['CreatedBy'];
        //$overtimeRecord->ManagerID = $overtimeRequest['ManagerID'];
        $overtimeRecord->AssignedID = $overtimeRequest['AssignedID'];
        $overtimeRecord->KindID = $overtimeRequest['KindID'];
        $overtimeRecord->BeginDate = $overtimeRequest['BeginDate'];
        $overtimeRecord->BeginTime = $overtimeRequest['BeginTime'];
        $overtimeRecord->ProjectID = $overtimeRequest['ProjectID'];
        $overtimeRecord->JobOrderNo = $overtimeRequest['JobOrderNo'];
        $overtimeRecord->CityID = $overtimeRequest['CityID'];
        $overtimeRecord->FieldID = $overtimeRequest['FieldID'];
        $overtimeRecord->FieldName = $overtimeRequest['FieldName'];
        $overtimeRecord->WorkHour = $overtimeRequest['WorkHour'];
        $overtimeRecord->UsingCar = $overtimeRequest['UsingCar'];
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

        $overtimeRecord->StatusID = 3;

        if ($overtimeRecord->save())
            return true;
        else
            return false;

    }

    public static function overtimeApproveRequestFromEmployee($overtimeRequest){

        $overtimeRecord = OvertimeModel::where([ 'id' => $overtimeRequest['OvertimeId'], 'Active' => 1 ])->first();
        $overtimeRecord->StatusID = 4;

        //TODO Loglama ve mail gönderimi yapılacak

        if ($overtimeRecord->save() )
            return true;
        else
            return false;
    }

    public static function overtimeCancelRequestFromEmployee($overtimeRequest){

        $overtimeRecord = OvertimeModel::where([ 'id' => $overtimeRequest['OvertimeId'], 'Active' => 1 ])->first();
        $overtimeRecord->StatusID = 5;

        //TODO Loglama ve mail göndeirmi yapılacak

        if ($overtimeRecord->save() )
            return true;
        else
            return false;

    }

    public static function overtimeCompleteRequestFromEmployee($overtimeRequest){

        $overtimeRecord = OvertimeModel::where([ 'id' => $overtimeRequest['OvertimeId'], 'Active' => 1 ])->first();
        $overtimeRecord->StatusID = 6;

        //TODO Loglama ve mail göndeirmi yapılacak

        if ($overtimeRecord->save() )
            return true;
        else
            return false;

    }

    public static function overtimeCorrectionRequestFromManager($overtimeRequest){

    $overtimeRecord = OvertimeModel::where([ 'id' => $overtimeRequest['OvertimeId'], 'Active' => 1 ])->first();

        //$overtimeRecord->CreatedBy = $overtimeRequest['CreatedBy'];
        $overtimeRecord->ManagerID = $overtimeRecord->CreatedBy;
        $overtimeRecord->AssignedID = $overtimeRequest['AssignedID'];
        $overtimeRecord->KindID = $overtimeRequest['KindID'];
        $overtimeRecord->BeginDate = $overtimeRequest['BeginDate'];
        $overtimeRecord->BeginTime = $overtimeRequest['BeginTime'];
        $overtimeRecord->ProjectID = $overtimeRequest['ProjectID'];
        $overtimeRecord->JobOrderNo = $overtimeRequest['JobOrderNo'];
        $overtimeRecord->CityID = $overtimeRequest['CityID'];
        $overtimeRecord->FieldID = $overtimeRequest['FieldID'];
        $overtimeRecord->FieldName = $overtimeRequest['FieldName'];
        $overtimeRecord->WorkHour = $overtimeRequest['WorkHour'];
        $overtimeRecord->UsingCar = $overtimeRequest['UsingCar'];
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

        $overtimeRecord = OvertimeModel::where([ 'id' => $overtimeRequest['OvertimeId'], 'Active' => 1 ])->first();
        $managerSupervisor = EmployeePositionModel::where(['Active' => 2,'EmployeeID' => $overtimeRecord->CreatedBy])->first();
        $project = ProjectsModel::find($overtimeRecord->ProjectID);

        //Mesai, proje yöneticisinin onayına gitmemiş ise
        /*if ($overtimeRecord->ManagerID != $project->manager_id)
        {
            $overtimeRecord->ManagerID = $project->manager_id;
            $overtimeRecord->StatusID = 6;
        }*/

        //Mesai, mesaiyi oluşturan kişinin bir üst yöneticisine gitmemiş ise
        if ($managerSupervisor == null)
        {
            $assignedEmployee =  EmployeeModel::find($overtimeRecord->AssignedID);
            $assignedEmployeePosition = EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $assignedEmployee->Id])->first();
            $overtimeRecord->ManagerID = $assignedEmployeePosition->HRManagerID;
            $overtimeRecord->StatusID = 8;
        }

        else if($overtimeRecord->ManagerID != $managerSupervisor->ManagerID)
        {
            $overtimeRecord->ManagerID = $managerSupervisor->ManagerID ;
            $overtimeRecord->StatusID = 6;
        }

        //Hem Proje Yöneticisi hem de mesaiyi oluşturan kişinin yöneticisi ise direkt olarak üst yönetici onayına gerek kalmaması için
        /*else if($overtimeRecord->ManagerID == $project->manager_id && $overtimeRecord->ManagerID != $managerSupervisor->ManagerID)
        {
            $overtimeRecord->StatusID = 8;
        }*/

        //Mesaiyi onaylayacak kimse kalmadı ise
        else
        {
            $assignedEmployee =  EmployeeModel::find($overtimeRecord->AssignedID);
            $assignedEmployeePosition = EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $assignedEmployee->Id])->first();
            $overtimeRecord->ManagerID = $assignedEmployeePosition->HRManagerID;
            $overtimeRecord->StatusID = 8;
        }

        //$overtimeRecord->CreatedBy = $overtimeRequest['CreatedBy'];
        //$overtimeRecord->ManagerID = $overtimeRequest['ManagerID'];
        $overtimeRecord->AssignedID = $overtimeRequest['AssignedID'];
        $overtimeRecord->KindID = $overtimeRequest['KindID'];
        $overtimeRecord->BeginDate = $overtimeRequest['BeginDate'];
        $overtimeRecord->BeginTime = $overtimeRequest['BeginTime'];
        $overtimeRecord->ProjectID = $overtimeRequest['ProjectID'];
        $overtimeRecord->JobOrderNo = $overtimeRequest['JobOrderNo'];
        $overtimeRecord->CityID = $overtimeRequest['CityID'];
        $overtimeRecord->FieldID = $overtimeRequest['FieldID'];
        $overtimeRecord->FieldName = $overtimeRequest['FieldName'];
        $overtimeRecord->WorkHour = $overtimeRequest['WorkHour'];
        $overtimeRecord->UsingCar = $overtimeRequest['UsingCar'];
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




        if ($overtimeRecord->save())
            return true;
        else
            return false;

    }

    public static function overtimeCorrectionRequestFromHR($overtimeRequest){

        $overtimeRecord = OvertimeModel::where([ 'id' => $overtimeRequest['OvertimeId'], 'Active' => 1 ])->first();

        //$overtimeRecord->CreatedBy = $overtimeRequest['CreatedBy'];
        $overtimeRecord->ManagerID = $overtimeRecord->CreatedBy;
        $overtimeRecord->AssignedID = $overtimeRequest['AssignedID'];
        $overtimeRecord->KindID = $overtimeRequest['KindID'];
        $overtimeRecord->BeginDate = $overtimeRequest['BeginDate'];
        $overtimeRecord->BeginTime = $overtimeRequest['BeginTime'];
        $overtimeRecord->ProjectID = $overtimeRequest['ProjectID'];
        $overtimeRecord->JobOrderNo = $overtimeRequest['JobOrderNo'];
        $overtimeRecord->CityID = $overtimeRequest['CityID'];
        $overtimeRecord->FieldID = $overtimeRequest['FieldID'];
        $overtimeRecord->FieldName = $overtimeRequest['FieldName'];
        $overtimeRecord->WorkHour = $overtimeRequest['WorkHour'];
        $overtimeRecord->UsingCar = $overtimeRequest['UsingCar'];
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

        $overtimeRecord = OvertimeModel::where([ 'id' => $overtimeRequest['OvertimeId'], 'Active' => 1 ])->first();

        //$overtimeRecord->CreatedBy = $overtimeRequest['CreatedBy'];
        //$overtimeRecord->ManagerID = $overtimeRequest['ManagerID'];
        $overtimeRecord->AssignedID = $overtimeRequest['AssignedID'];
        $overtimeRecord->KindID = $overtimeRequest['KindID'];
        $overtimeRecord->BeginDate = $overtimeRequest['BeginDate'];
        $overtimeRecord->BeginTime = $overtimeRequest['BeginTime'];
        $overtimeRecord->ProjectID = $overtimeRequest['ProjectID'];
        $overtimeRecord->JobOrderNo = $overtimeRequest['JobOrderNo'];
        $overtimeRecord->CityID = $overtimeRequest['CityID'];
        $overtimeRecord->FieldID = $overtimeRequest['FieldID'];
        $overtimeRecord->FieldName = $overtimeRequest['FieldName'];
        $overtimeRecord->WorkHour = $overtimeRequest['WorkHour'];
        $overtimeRecord->UsingCar = $overtimeRequest['UsingCar'];
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

    public function getCreatedFromAttribute()
    {

        $createdFrom = $this->hasOne(EmployeeModel::class,"Id","CreatedBy");
        if ($createdFrom)
        {
            return $createdFrom->where("Active",1)->first();
        }
        else
        {
            return "";
        }

    }

    public function getApproveWhoAttribute()
    {

        $approveWho = $this->hasOne(EmployeeModel::class,"Id","ManagerID");
        if ($approveWho)
        {
            return $approveWho->where("Active",1)->first();
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
