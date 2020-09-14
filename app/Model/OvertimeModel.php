<?php

namespace App\Model;

use App\Library\Asay;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use DateTime;
use Symfony\Component\Process\Process;

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
        'ObjectFile'
    ];
    protected $guarded = [];

    public static function columnNameToTurkish($columnName)
    {
        switch ($columnName) {
            case 'CreatedBy':
                return 'Oluşturan Yönetici';
            case 'ManagerID':
                return 'Onaylayacak Olan Yönetici';
            case 'AssignedID':
                return 'Atanan Kişinin ID Nosu';
            case 'KindID':
                return 'Fazla Çalışma Türü';
            case 'BeginDate':
                return 'Başlangıç Tarihi';
            case 'BeginTime':
                return 'Başlangıç Zamanı';
            case 'ProjectID':
                return 'Project ID Nosu';
            case 'JobOrderNo':
                return 'İş Emri No';
            case 'CityID':
                return 'Şehir ID Nosu';
            case 'FieldID':
                return 'Çalışma Saha ID Nosu';
            case 'FieldName':
                return 'Çalışma Saha Adı';
            case 'EndTime':
                return 'Çalışma Bitiş Zamanı';
            case 'UsingCar':
                return 'Araba Kullanıp Kullanılmayacağı (1:Evet, 0:Hayır)';
            case 'PlateNumber':
                return 'Araç Plaka Numarası';
            case 'Description':
                return 'Açıklama';


        }
    }

    public static function overtimeRemainingLimits($request)
    {

        $beginDate = Carbon::createFromFormat("Y-m-d", $request->BeginDate);

        $dailyTimes = OvertimeModel::selectRaw(' TIMEDIFF(EndTime,BeginTime) as timediff')->where(['Active' => 1, 'AssignedID' => $request->AssignedID])->where(function ($query) use ($beginDate) {
            $query->whereBetween('BeginDate', [$beginDate->year . '-' . $beginDate->month . '-' . $beginDate->day
                , $beginDate->year . '-' . $beginDate->month . '-' . $beginDate->day]);
        })->get(); // Günlük tanımlanmış saatleri çekiyoruz.

        $monthlyTimes = OvertimeModel::selectRaw(' TIMEDIFF(EndTime,BeginTime) as timediff')->where(['Active' => 1, 'AssignedID' => $request->AssignedID])->where(function ($query) use ($beginDate) {

            $query->whereBetween('BeginDate', [$beginDate->startOfMonth()->year . '-' . $beginDate->startOfMonth()->month . '-' . $beginDate->startOfMonth()->day
                , $beginDate->endOfMonth()->year . '-' . $beginDate->endOfMonth()->month . '-' . $beginDate->endOfMonth()->day]);
        })->get();

        $yearlyTimes = OvertimeModel::selectRaw(' TIMEDIFF(EndTime,BeginTime) as timediff')->where(['Active' => 1, 'AssignedID' => $request->AssignedID])->where(function ($query) use ($beginDate) {
            $query->whereBetween('BeginDate', [$beginDate->startOfYear()->year . '-' . $beginDate->startOfYear()->month . '-' . $beginDate->startOfYear()->day
                , $beginDate->endOfYear()->year . '-' . $beginDate->endOfYear()->month . '-' . $beginDate->endOfYear()->day]);
        })->get();


        $dailyMinutes = 0;
        $dailyHours = 0;
        $dailyMinutesLimit = 30;
        $dailyHoursLimit = 3;//3.5 Saat Günlük Limit

        foreach ($dailyTimes as $dailyTime) {
            $tempTime = Carbon::createFromFormat("H:i:s", $dailyTime->timediff);
            $dailyMinutes += $tempTime->minute;
            if ($dailyMinutes >= 60) {
                $dailyHours++;
                $dailyMinutes = $dailyMinutes % 60;
            }
            $dailyHours += $tempTime->hour;
        }

        $monthlyMinutes = 0;
        $monthlyHours = 0;
        $monthlyMinutesLimit = 30;
        $monthlyHoursLimit = 22;//22.5 Saat Aylık Limit

        foreach ($monthlyTimes as $monthlyTime) {
            $tempTime = Carbon::createFromFormat("H:i:s", $monthlyTime->timediff);
            $monthlyMinutes += $tempTime->minute;
            if ($monthlyMinutes >= 60) {
                $monthlyHours++;
                $monthlyMinutes = $monthlyMinutes % 60;
            }
            $monthlyHours += $tempTime->hour;
        }

        $yearlyMinutes = 0;
        $yearlyHours = 0;
        $yearlyMinutesLimit = 0;
        $yearlyHoursLimit = 270;//270 Saat Yıllık Limit

        foreach ($yearlyTimes as $yearlyTime) {
            $tempTime = Carbon::createFromFormat("H:i:s", $yearlyTime->timediff);
            $yearlyMinutes += $tempTime->minute;
            if ($yearlyMinutes >= 60) {
                $yearlyHours++;
                $yearlyMinutes = $yearlyMinutes % 60;
            }
            $yearlyHours += $tempTime->hour;
        }

        $remainingDailyMinutes = ($dailyHoursLimit * 60 + $dailyMinutesLimit) - (($dailyHours * 60) + $dailyMinutes);
        $remainingMonthlyMinutes = ($monthlyHoursLimit * 60 + $monthlyMinutesLimit) - (($monthlyHours * 60) + $monthlyMinutes);
        $remainingYearlyMinutes = ($yearlyHoursLimit * 60 + $yearlyMinutesLimit) - (($yearlyHours * 60) + $yearlyMinutes);

        $data[0] = (int)($remainingDailyMinutes / 60);
        $data[1] = ($remainingDailyMinutes % 60);

        $data[2] = (int)($remainingMonthlyMinutes / 60);
        $data[3] = ($remainingMonthlyMinutes % 60);

        $data[4] = (int)($remainingYearlyMinutes / 60);
        $data[5] = ($remainingYearlyMinutes % 60);

        return $data;

    }

    public static function overtimeLimitCheck($request, $neglectRecord = null)
    {

        $beginDate = Carbon::createFromFormat("Y-m-d", $request->BeginDate);
        $beginTime = Carbon::createFromFormat("H:i", $request->BeginTime);
        $endTime = Carbon::createFromFormat("H:i", $request->EndTime);


        $dailyTimesQ = OvertimeModel::selectRaw('id,TIMEDIFF(EndTime,BeginTime) as timediff')->where(['Active' => 1, 'AssignedID' => $request->AssignedID])->where(function ($query) use ($beginDate) {
            $query->whereBetween('BeginDate', [$beginDate->year . '-' . $beginDate->month . '-' . $beginDate->day
                , $beginDate->year . '-' . $beginDate->month . '-' . $beginDate->day]);
            $query->whereNotIn('StatusID',[3,5]);
        }); // Günlük tanımlanmış saatleri çekiyoruz.

        $dailyTimes = $dailyTimesQ->get();

        $monthlyTimesQ = OvertimeModel::selectRaw('id,TIMEDIFF(EndTime,BeginTime) as timediff')->where(['Active' => 1, 'AssignedID' => $request->AssignedID])->where(function ($query) use ($beginDate) {

            $query->whereBetween('BeginDate', [$beginDate->startOfMonth()->year . '-' . $beginDate->startOfMonth()->month . '-' . $beginDate->startOfMonth()->day
                , $beginDate->endOfMonth()->year . '-' . $beginDate->endOfMonth()->month . '-' . $beginDate->endOfMonth()->day]);
            $query->whereNotIn('StatusID',[3,5]);
        });



        $monthlyTimes = $monthlyTimesQ->get();

        $yearlyTimesQ = OvertimeModel::selectRaw('id,TIMEDIFF(EndTime,BeginTime) as timediff')->where(['Active' => 1, 'AssignedID' => $request->AssignedID])->where(function ($query) use ($beginDate) {
            $query->whereBetween('BeginDate', [$beginDate->startOfYear()->year . '-' . $beginDate->startOfYear()->month . '-' . $beginDate->startOfYear()->day
                , $beginDate->endOfYear()->year . '-' . $beginDate->endOfYear()->month . '-' . $beginDate->endOfYear()->day]);
            $query->whereNotIn('StatusID',[3,5]);
        });

        $yearlyTimes = $yearlyTimesQ->get();


        $dailyMinutes = 0;
        $dailyHours = 0;
        $dailyMinutesLimit = 30;
        $dailyHoursLimit = 3;//3.5 Saat Günlük Limit

        foreach ($dailyTimes as $dailyTime) {
            if (!is_null($neglectRecord))
                if ($dailyTime->id == $neglectRecord->id)
                    continue;
            $tempTime = Carbon::createFromFormat("H:i:s", $dailyTime->timediff);
            $dailyMinutes += $tempTime->minute;
            if ($dailyMinutes >= 60) {
                $dailyHours++;
                $dailyMinutes = $dailyMinutes % 60;
            }
            $dailyHours += $tempTime->hour;
        }

        if (($endTime->hour - $beginTime->hour) + $dailyHours > $dailyHoursLimit)
            return ['status' => false, 'message' => 'Atadığınız fazla çalışma, günlük fazla çalışma limitini aşıyor.'];
        elseif (($endTime->hour - $beginTime->hour) + $dailyHours == $dailyHoursLimit) {
            if (abs($endTime->minute - $beginTime->minute) + $dailyMinutes > 15) {
                return ['status' => false, 'message' => 'Atadığınız fazla çalışma, günlük fazla çalışma limitini aşıyor.'];
            }
        }


        $monthlyMinutes = 0;
        $monthlyHours = 0;
        $monthlyMinutesLimit = 30;
        $monthlyHoursLimit = 22;//22.5 Saat Aylık Limit

        foreach ($monthlyTimes as $monthlyTime) {
            if (!is_null($neglectRecord))
                if ($monthlyTime->id == $neglectRecord->id)
                    continue;
            $tempTime = Carbon::createFromFormat("H:i:s", $monthlyTime->timediff);
            $monthlyMinutes += $tempTime->minute;
            if ($monthlyMinutes >= 60) {
                $monthlyHours++;
                $monthlyMinutes = $monthlyMinutes % 60;
            }
            $monthlyHours += $tempTime->hour;
        }

        if (($endTime->hour - $beginTime->hour) + $monthlyHours > $monthlyHoursLimit)
            return ['status' => false, 'message' => 'Atadığınız fazla çalışma, aylık fazla çalışma limitini aşıyor.'];
        elseif (($endTime->hour - $beginTime->hour) + $dailyHours == $dailyHoursLimit) {
            if (abs($endTime->minute - $beginTime->minute) + $dailyMinutes > 15) {
                return ['status' => false, 'message' => 'Atadığınız fazla çalışma, günlük fazla çalışma limitini aşıyor.'];
            }
        }


        $yearlyMinutes = 0;
        $yearlyHours = 0;
        $yearlyMinutesLimit = 0;
        $yearlyHoursLimit = 270;//270 Saat Yıllık Limit

        foreach ($yearlyTimes as $yearlyTime) {
            if (!is_null($neglectRecord))
                if ($yearlyTime->id == $neglectRecord->id)
                    continue;
            $tempTime = Carbon::createFromFormat("H:i:s", $yearlyTime->timediff);
            $yearlyMinutes += $tempTime->minute;
            if ($yearlyMinutes >= 60) {
                $yearlyHours++;
                $yearlyMinutes = $yearlyMinutes % 60;
            }
            $yearlyHours += $tempTime->hour;
        }

        if (($endTime->hour - $beginTime->hour) + $yearlyHours > $yearlyHoursLimit)
            return ['status' => false, 'message' => 'Atadığınız fazla çalışma, yıllık fazla çalışma limitini aşıyor.'];


        return ['status' => true, 'message' => 'Atanan aşama herhangi bir limiti aşmıyor.'];

    }

    public static function getOvertimeByStatus($status,$EmployeeID)
    {
        $userEmployees = EmployeePositionModel::where(['Active' => 2, 'ManagerID' => $EmployeeID])->get();
        $userEmployeesIDs = [];
        foreach ($userEmployees as $userEmployee) {
            array_push($userEmployeesIDs, $userEmployee->EmployeeID);
        }
        return self::where(['Active' => 1, 'StatusID' => $status])->where(function ($query) use ($EmployeeID,$userEmployeesIDs) {
            $query->orWhere(['ManagerID' => $EmployeeID, 'CreatedBy' => $EmployeeID])->orWhereIn('CreatedBy', $userEmployeesIDs);
        })->orderBy('BeginDate', 'desc')->get();

    }

    public static function getEmployeesOvertimeByStatus($status, $EmployeeID)
    {
        return self::where(['Active' => 1, 'StatusID' => $status, 'AssignedID' => $EmployeeID])->orderBy('BeginDate', 'desc')->get();
    }

    public static function getOvertimeFields($managerId)
    {
        $fields = [];
        $fields['kinds'] = OvertimeKindModel::all();
        $fields['cities'] = CityModel::all();
        $fields['workingfields'] = WorkingFieldModel::all();

        return $fields;

    }

    public static function getManagersEmployees($managerId)
    {
        $employeePositions = EmployeePositionModel::where(['Active' => 2])->where(function ($query) use ($managerId) {
            $query->where('HRManagerID', $managerId)->orWhere(['ManagerID' => $managerId]);
        })->get();
        $employeeList = [];
        foreach ($employeePositions as $employeePosition) {
            $tempPositions = EmployeePositionModel::where('Active', 2)->where('ManagerID', $employeePosition->EmployeeID)->get();
            foreach ($tempPositions as $tempPosition) {
                $tempEmployee = EmployeeModel::select('Id', 'UsageName', 'LastName')->where('Id', $tempPosition->EmployeeID)->where('Active', 1)->first();
                $tempEmployee ? array_push($employeeList, $tempEmployee) : '';
            }
        }


        foreach ($employeePositions as $employeePosition) {
            $tempEmployee = EmployeeModel::select('Id', 'UsageName', 'LastName')->where('Id', $employeePosition->EmployeeID)->where('Active', 1)->first();
            $tempEmployee ? array_push($employeeList, $tempEmployee) : '';
        }
        return $employeeList;
    }

    public static function getEmployeesManagers($employeeID)
    {

        $employeeManagerPosition = EmployeePositionModel::where('Active', 2)->where('EmployeeID', $employeeID)->first();
        $projects = ProjectsModel::all();
        $projectManagers = [];
        foreach ($projects as $value) {
            if (!in_array($value->manager_id, $projectManagers, true)) {
                array_push($projectManagers, $value->manager_id);
            }
        }

        $managerIDList = [];
        array_push($managerIDList, $employeeManagerPosition->ManagerID);

        foreach ($projectManagers as $value) {
            if (!in_array($value, $managerIDList, true)) {
                array_push($managerIDList, $value);
            }
        }

        $managerList = [];
        foreach ($managerIDList as $managerID) {
            $temp = EmployeeModel::select('Id', 'UsageName', 'LastName')->where('Id', $managerID)->where('Active', 1)->first();
            $temp ? array_push($managerList, $temp) : '';
        }

        return $managerList;

    }

    public function getAssignedEmployeeAttribute()
    {

        $assignedEmployee = $this->hasOne(EmployeeModel::class, "Id", "AssignedID");
        if ($assignedEmployee) {
            return $assignedEmployee->where("Active", 1)->first();
        } else {
            return "";
        }
    }

    public static function saveOvertimeByProcessType($procestype, $request)
    {

        /*
         * Request Tipleri
         * Tip 0 : Fazla Çalışmayı kaydetme durumu
         * Tip 1 : Yöneticiden çalışana fazla çalışma atama durumu
         * Tip 2 : Çalışandan yöneticiye düzeltme talebi
         * Tip 3 : Çalışan tarafından reddedildi -> Yöneticiye düzeltme gidecek.
         * Tip 4 : Çalışan tarafından onaylandı
         * Tip 5 : Çalışan tarafından iptal edildi
         * Tip 6 : Çalışan tarafından çalışma tamamlandı -> Yönetici Onayı Bekleniyor.
         * Tip 7 : Yönetici tarafından fazla çalışmaya yönetici tarafından düzeltme talep edildi.
         * Tip 8 : Yönetici tarafından fazla çalışma onaylandı.
         * Tip 9 : IK tarfından fazla çalışmaya düzenleme talebi yapıldı.
         * Tip 10 : IK tarafından onaylandı
         *
         * */

        //$limitCheck = self::overtimeLimitCheck($request);


        switch ($procestype) {
            case 0:
                //Limit Kontrol
                $limitCheck = self::overtimeLimitCheck($request);
                if ($limitCheck['status'] == false)
                    return $limitCheck;
                return self::saveOvertimeRequest($request);
            case 1:
                if ($request->OvertimeId == null)
                {
                    $limitCheck = self::overtimeLimitCheck($request);
                    if ($limitCheck['status'] == false)
                        return $limitCheck;
                }
                else
                {
                    $overtimeRecord = OvertimeModel::find($request->OvertimeId);
                    $limitCheck = self::overtimeLimitCheck($request,$overtimeRecord);
                    if ($limitCheck['status'] == false)
                        return $limitCheck;
                }
                return self::sendOvertimeRequestToEmployee($request);
            case 2:
                $overtimeRecord = OvertimeModel::find($request->OvertimeId);
                $limitCheck = self::overtimeLimitCheck($request,$overtimeRecord);
                if ($limitCheck['status'] == false)
                    return $limitCheck;
                return self::overtimeCorrectionRequestFromEmployee($request);
            case 3:
                return self::overtimeRejectRequestFromEmployee($request);
            case 4:
                return self::overtimeApproveRequestFromEmployee($request);
            case 5:
                return self::overtimeCancelRequestFromEmployee($request);
            case 6:
                return self::overtimeCompleteRequestFromEmployee($request);
            case 7:
                $overtimeRecord = OvertimeModel::find($request->OvertimeId);
                $limitCheck = self::overtimeLimitCheck($request,$overtimeRecord);
                if ($limitCheck['status'] == false)
                    return $limitCheck;
                return self::overtimeCorrectionRequestFromManager($request);
            case 8:
                return self::overtimeApproveRequestFromManager($request);
            case 9:
                $overtimeRecord = OvertimeModel::find($request->OvertimeId);
                $limitCheck = self::overtimeLimitCheck($request,$overtimeRecord);
                if ($limitCheck['status'] == false)
                    return $limitCheck;
                return self::overtimeCorrectionRequestFromHR($request);
            case 10:
                return self::overtimeApproveRequestFromHR($request);

        }
    }

    public static function saveOvertimeRequest($overtimeRequest)
    {

        $overtimeRecord = !isset($overtimeRequest->OvertimeId) || $overtimeRequest->OvertimeId == null
            ? new OvertimeModel() :
            OvertimeModel::where(['id' => $overtimeRequest->OvertimeId, 'Active' => 1])->first();

        $overtimeRecord->CreatedBy = $overtimeRequest->CreatedBy;
        $overtimeRecord->ManagerID = $overtimeRequest->ManagerID;
        $overtimeRecord->AssignedID = $overtimeRequest->AssignedID;
        $overtimeRecord->KindID = $overtimeRequest->KindID;
        $overtimeRecord->BeginDate = $overtimeRequest->BeginDate;
        $overtimeRecord->BeginTime = $overtimeRequest->BeginTime;
        $overtimeRecord->ProjectID = $overtimeRequest->ProjectID;
        $overtimeRecord->JobOrderNo = $overtimeRequest->JobOrderNo;
        $overtimeRecord->CityID = $overtimeRequest->CityID;
        $overtimeRecord->FieldID = $overtimeRequest->FieldID;
        $overtimeRecord->FieldName = $overtimeRequest->FieldName;
        $overtimeRecord->EndTime = $overtimeRequest->EndTime;
        $overtimeRecord->UsingCar = $overtimeRequest->UsingCar;
        $overtimeRecord->PlateNumber = $overtimeRequest->PlateNumber;
        $overtimeRecord->Description = $overtimeRequest->Description;
        $overtimeRecord->StatusID = 0;


        if ($overtimeRecord->save()) {

            if (isset($overtimeRequest->OvertimeId))
            {
                $userEmployee = EmployeeModel::find($overtimeRequest->Employee);
                $logStatus = LogsModel::setLog($overtimeRequest->Employee,$overtimeRecord->id,3,21,'','',$overtimeRecord->BeginDate . ' ' . $overtimeRecord->BeginTime . ' tarihli fazla çalışma ' . $userEmployee->UsageName . '' . $userEmployee->LastName.' adlı personel tarafından düzenlendi.','','','','','');
            }

            else
            {
                $userEmployee = EmployeeModel::find($overtimeRequest->Employee);
                $logStatus = LogsModel::setLog($overtimeRequest->Employee,$overtimeRecord->id,3,22,'','',$overtimeRecord->BeginDate . ' ' . $overtimeRecord->BeginTime . ' tarihli fazla çalışma ' . $userEmployee->UsageName . '' . $userEmployee->LastName.' adlı yönetici tarafından oluşturuldu.','','','','','');
            }


            return ['status' => true, 'message' => 'İşlem Başarılı'];
        } else
            return ['status' => false, 'message' => 'Kayıt Sırasında Bir Hata Oluştu'];

    }

    public static function sendOvertimeRequestToEmployee($overtimeRequest)
    {

        $overtimeRecord = !isset($overtimeRequest->OvertimeId) || $overtimeRequest->OvertimeId == null
            ? new OvertimeModel() :
            OvertimeModel::where(['id' => $overtimeRequest->OvertimeId, 'Active' => 1])->first();

        $overtimeRecord->CreatedBy = $overtimeRequest->CreatedBy;
        $overtimeRecord->ManagerID = $overtimeRequest->ManagerID;
        $overtimeRecord->AssignedID = $overtimeRequest->AssignedID;
        $overtimeRecord->KindID = $overtimeRequest->KindID;
        $overtimeRecord->BeginDate = $overtimeRequest->BeginDate;
        $overtimeRecord->BeginTime = $overtimeRequest->BeginTime;
        $overtimeRecord->ProjectID = $overtimeRequest->ProjectID;
        $overtimeRecord->JobOrderNo = $overtimeRequest->JobOrderNo;
        $overtimeRecord->CityID = $overtimeRequest->CityID;
        $overtimeRecord->FieldID = $overtimeRequest->FieldID;
        $overtimeRecord->FieldName = $overtimeRequest->FieldName;
        $overtimeRecord->EndTime = $overtimeRequest->EndTime;
        $overtimeRecord->UsingCar = $overtimeRequest->UsingCar;
        $overtimeRecord->PlateNumber = $overtimeRequest->PlateNumber;
        $overtimeRecord->Description = $overtimeRequest->Description;
        $overtimeRecord->StatusID = 1;

        $employee = EmployeeModel::find($overtimeRequest->Employee);
        $assignedEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
        $assignedEmployeesManager = EmployeeModel::find(EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $overtimeRecord->AssignedID])->first()->ManagerID);

        $usingCar = $overtimeRecord->UsingCar == 0 ? 'Hayır' : 'Evet';

        if ($overtimeRecord->save()) {

            Asay::sendMail($assignedEmployee->JobEmail, "", "Fazla Çalışma Onayınızı Bekliyor",  'Sayın ' . $assignedEmployee->FirstName . ' ' . $assignedEmployee->LastName  .' fazla çalışma onayınızı beklemektedir.' . '
<html lang="en">
<head>
<title>Fazla Mesai Mail</title>
<style>
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
  padding: 5px;
  text-align: center;
  
}
</style>
</head>
<body>
<br><br>
<table width="800">
  <tr style="background-color: rgb(0,31,91);color:white" >
    <th colspan="2"  >İşlem Yapan Son Kullanıcı</th>
  </tr>
  <tr >
    <td colspan="2"  >
           ' . $employee->UsageName . ' ' . $employee->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>İş Emri No</b>
    </td>
    <td  >
        <b>Görevlendirilen Personel</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->JobOrderNo . '
    </td >
    <td  >
        ' . $assignedEmployee->UsageName . ' ' . $assignedEmployee->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Statü</b>
    </td>
    <td  >
        <b>Yönetici</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->Status->Name . '
    </td>
    <td  >
        ' . $assignedEmployeesManager->UsageName . ' ' . $assignedEmployeesManager->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Proje</b>
    </td>
    <td  >
        <b>Başlangıç Tarihi</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->Project->name . '
    </td>
    <td  >
        ' . date("d.m.Y", strtotime($overtimeRecord->BeginDate)) . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Başlangıç Saati</b>
    </td>
    <td  >
        <b>Bitiş Saati</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->BeginTime . '
    </td>
    <td  >
        ' . $overtimeRecord->EndTime . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Çalışma Yapılacak Saha ID</b>
    </td>
    <td  >
        <b>Çalışma Yapılacak Saha Adı</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $overtimeRecord->FieldID . '
    </td>
    <td>
        ' . $overtimeRecord->FieldName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Araç Kullanacak Mı</b>
    </td>
    <td  >
        <b>Araç Plakası</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $usingCar  . '
    </td>
    <td>
        ' . $overtimeRecord->PlateNumber . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  colspan="2">
        <b>Açıklama</b>
    </td>
  </tr>
  <tr>
    <td colspan="2">
        ' . $overtimeRecord->Description  . '
    </td>
  </tr>
</table>
</body>
</html>'
                , "Fazla Çalışma Onayınızı Bekliyor");

            return ['status' => true, 'message' => 'İşlem Başarılı'];
        } else
            return ['status' => false, 'message' => 'Kayıt Sırasında Bir Hata Oluştu'];

    }

    public static function overtimeCorrectionRequestFromEmployee($overtimeRequest)
    {

        $overtimeRecord = OvertimeModel::where(['id' => $overtimeRequest->OvertimeId, 'Active' => 1])->first();

        //$overtimeRecord->CreatedBy = $overtimeRequest['CreatedBy'];
        //$overtimeRecord->ManagerID = $overtimeRequest['ManagerID'];
        $overtimeRecord->AssignedID = $overtimeRequest->AssignedID;
        $overtimeRecord->KindID = $overtimeRequest->KindID;
        $overtimeRecord->BeginDate = $overtimeRequest->BeginDate;
        $overtimeRecord->BeginTime = $overtimeRequest->BeginTime . ":00";
        $overtimeRecord->ProjectID = $overtimeRequest->ProjectID;
        $overtimeRecord->JobOrderNo = $overtimeRequest->JobOrderNo;
        $overtimeRecord->CityID = $overtimeRequest->CityID;
        $overtimeRecord->FieldID = $overtimeRequest->FieldID;
        $overtimeRecord->FieldName = $overtimeRequest->FieldName;
        $overtimeRecord->EndTime = $overtimeRequest->EndTime . ":00";
        $overtimeRecord->UsingCar = $overtimeRequest->UsingCar;
        $overtimeRecord->PlateNumber = $overtimeRequest->PlateNumber;
        $overtimeRecord->Description = $overtimeRequest->Description;

        $dirtyFields = $overtimeRecord->getDirty();
        $dirtyFieldsString = "";
        foreach ($dirtyFields as $field => $newdata) {
            $olddata = $overtimeRecord->getOriginal($field);
            if ($olddata != $newdata) {
                $dirtyFieldsString .= '<tr><td>' . self::columnNameToTurkish($field) . ' (İlk Değer) :  ' . $olddata . '</td><td>' . self::columnNameToTurkish($field) . ' (Düzenlenen Değer) :  ' . $newdata . '</td></tr>';
            }
        }

        $overtimeRecord->StatusID = 2;

        $usingCar = $overtimeRecord->UsingCar == 0 ? 'Hayır' : 'Evet';

        $employee = EmployeeModel::find($overtimeRequest->Employee);
        $assignedEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
        $reason = $overtimeRequest->ProcessReason == "" || $overtimeRequest->ProcessReason != null ? $overtimeRequest->ProcessReason : "Açıklama Yapılmamış";
        $assignedEmployeesManager = EmployeeModel::find(EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $overtimeRecord->AssignedID])->first()->ManagerID);
        $mailTable = 'Sayın ' . $assignedEmployeesManager->UsageName . ' ' . $assignedEmployeesManager->LastName . ', fazla çalışma için ' . $employee->UsageName . ' ' . $employee->LastName . ' isimli personel tarafından düzenleme talep edildi.' . '
<html lang="en">
<head>
<title>Fazla Mesai Mail</title>
<style>
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
  padding: 5px;
  text-align: center;
  
}
</style>
</head>
<body>
<br><br>
<table width="800">
  <tr style="background-color: rgb(0,31,91);color:white" >
    <th colspan="2"  >İşlem Yapan Son Kullanıcı</th>
  </tr>
  <tr >
    <td colspan="2"  >
           ' . $employee->UsageName . ' ' . $employee->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>İş Emri No</b>
    </td>
    <td  >
        <b>Görevlendirilen Personel</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->JobOrderNo . '
    </td >
    <td  >
        ' . $assignedEmployee->UsageName . ' ' . $assignedEmployee->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Statü</b>
    </td>
    <td  >
        <b>Yönetici</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->Status->Name . '
    </td>
    <td  >
        ' . $assignedEmployeesManager->UsageName . ' ' . $assignedEmployeesManager->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Proje</b>
    </td>
    <td  >
        <b>Başlangıç Tarihi</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->Project->name . '
    </td>
    <td  >
        ' . date("d.m.Y", strtotime($overtimeRecord->BeginDate)) . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Başlangıç Saati</b>
    </td>
    <td  >
        <b>Bitiş Saati</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->BeginTime . '
    </td>
    <td  >
        ' . $overtimeRecord->EndTime . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Çalışma Yapılacak Saha ID</b>
    </td>
    <td  >
        <b>Çalışma Yapılacak Saha Adı</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $overtimeRecord->FieldID . '
    </td>
    <td>
        ' . $overtimeRecord->FieldName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Araç Kullanacak Mı</b>
    </td>
    <td  >
        <b>Araç Plakası</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $usingCar  . '
    </td>
    <td>
        ' . $overtimeRecord->PlateNumber . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td colspan="2" >
       <b>Açıklama</b>
    </td>
  </tr>
  <tr >
    <td  colspan="2" >
        ' . $reason . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
  	<td  colspan="2"  >
        <b>Düzenleme Yapılan Alanlar</b>
    </td>
  </tr>' . $dirtyFieldsString . '
  </table>
</body>
</html>
  ';

        Asay::sendMail($assignedEmployeesManager->JobEmail, "", "Fazla çalışma için düzenleme talep edildi.", $mailTable
            , "Fazla Çalışma İçin Düzenleme Talep Edildi");


        if ($overtimeRecord->save())
            return ['status' => true, 'message' => $mailTable];
        else
            return ['status' => false, 'message' => 'Kayıt Sırasında Bir Hata Oluştu'];

    }

    public static function overtimeRejectRequestFromEmployee($overtimeRequest)
    {
        $overtimeRecord = OvertimeModel::where(['id' => $overtimeRequest->OvertimeId, 'Active' => 1])->first();

        //$overtimeRecord->CreatedBy = $overtimeRequest['CreatedBy'];
        //$overtimeRecord->ManagerID = $overtimeRequest['ManagerID'];
        $overtimeRecord->AssignedID = $overtimeRequest->AssignedID;
        $overtimeRecord->KindID = $overtimeRequest->KindID;
        $overtimeRecord->BeginDate = $overtimeRequest->BeginDate;
        $overtimeRecord->BeginTime = $overtimeRequest->BeginTime . ":00";
        $overtimeRecord->ProjectID = $overtimeRequest->ProjectID;
        $overtimeRecord->JobOrderNo = $overtimeRequest->JobOrderNo;
        $overtimeRecord->CityID = $overtimeRequest->CityID;
        $overtimeRecord->FieldID = $overtimeRequest->FieldID;
        $overtimeRecord->FieldName = $overtimeRequest->FieldName;
        $overtimeRecord->EndTime = $overtimeRequest->EndTime . ":00";
        $overtimeRecord->UsingCar = $overtimeRequest->UsingCar;
        $overtimeRecord->PlateNumber = $overtimeRequest->PlateNumber;
        $overtimeRecord->Description = $overtimeRequest->Description;

        $dirtyFields = $overtimeRecord->getDirty();

        foreach ($dirtyFields as $field => $newdata) {
            $olddata = $overtimeRecord->getOriginal($field);
            if ($olddata != $newdata) {
                //TODO Loglama İşlemi burada yapılacak.
            }
        }

        $overtimeRecord->StatusID = 3;
        $usingCar = $overtimeRecord->UsingCar == 0 ? 'Hayır' : 'Evet';

        $employee = EmployeeModel::find($overtimeRequest->Employee);
        $assignedEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
        $reason = $overtimeRequest->ProcessReason == "" || $overtimeRequest->ProcessReason != null ? $overtimeRequest->ProcessReason : "Açıklama Yapılmamış";
        $assignedEmployeesManager = EmployeeModel::find(EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $overtimeRecord->AssignedID])->first()->ManagerID);
        Asay::sendMail($assignedEmployeesManager->JobEmail, "", "Fazla çalışma reddedildi", 'Sayın ' . $assignedEmployeesManager->UsageName . ' ' . $assignedEmployeesManager->LastName . ' fazla çalışma, ' . $employee->UsageName . ' ' . $employee->LastName . ' isimli personel tarafından reddedildi.' . '
<html lang="en">
<head>
<title>Fazla Mesai Mail</title>
<style>
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
  padding: 5px;
  text-align: center;
  
}
</style>
</head>
<body>
<br><br>
<table width="800">
  <tr style="background-color: rgb(0,31,91);color:white" >
    <th colspan="2"  >İşlem Yapan Son Kullanıcı</th>
  </tr>
  <tr >
    <td colspan="2"  >
           ' . $employee->UsageName . ' ' . $employee->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>İş Emri No</b>
    </td>
    <td  >
        <b>Görevlendirilen Personel</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->JobOrderNo . '
    </td >
    <td  >
        ' . $assignedEmployee->UsageName . ' ' . $assignedEmployee->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Statü</b>
    </td>
    <td  >
        <b>Yönetici</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->Status->Name . '
    </td>
    <td  >
        ' . $assignedEmployeesManager->UsageName . ' ' . $assignedEmployeesManager->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Proje</b>
    </td>
    <td  >
        <b>Başlangıç Tarihi</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->Project->name . '
    </td>
    <td  >
        ' . date("d.m.Y", strtotime($overtimeRecord->BeginDate)) . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Başlangıç Saati</b>
    </td>
    <td  >
        <b>Bitiş Saati</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->BeginTime . '
    </td>
    <td  >
        ' . $overtimeRecord->EndTime . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Çalışma Yapılacak Saha ID</b>
    </td>
    <td  >
        <b>Çalışma Yapılacak Saha Adı</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $overtimeRecord->FieldID . '
    </td>
    <td>
        ' . $overtimeRecord->FieldName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Araç Kullanacak Mı</b>
    </td>
    <td  >
        <b>Araç Plakası</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $usingCar  . '
    </td>
    <td>
        ' . $overtimeRecord->PlateNumber . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  colspan="2">
        <b>Açıklama</b>
    </td>
  </tr>
  <tr>
    <td colspan="2">
        ' . $reason  . '
    </td>
  </tr>
</table>
</body>
</html>'
            , "Fazla Çalışma Reddedildi");

        if ($overtimeRecord->save())
        {
            $userEmployee = EmployeeModel::find($overtimeRequest->Employee);
            $logStatus = LogsModel::setLog($overtimeRequest->Employee,$overtimeRecord->id,3,26,'','',$overtimeRecord->BeginDate . ' ' . $overtimeRecord->BeginTime . ' tarihli fazla çalışma ' . $userEmployee->UsageName . '' . $userEmployee->LastName.' adlı çalışan tarafından reddedildi.','','','','','');
            return ['status' => true, 'message' => 'İşlem Başarılı'];
        }

        else
            return ['status' => false, 'message' => 'Kayıt Sırasında Bir Hata Oluştu'];

    }

    public static function overtimeApproveRequestFromEmployee($overtimeRequest)
    {

        $overtimeRecord = OvertimeModel::where(['id' => $overtimeRequest->OvertimeId, 'Active' => 1])->first();
        $overtimeRecord->StatusID = 4;

        //TODO Loglama ve mail gönderimi yapılacak ISG EKİBİNE
        $employee = EmployeeModel::find($overtimeRequest->Employee);
        $assignedEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
        $assignedEmployeePosition = EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $overtimeRecord->AssignedID])->first();
        $assignedEmployeesManager = EmployeeModel::find($assignedEmployeePosition->ManagerID);
        $isgPositions = EmployeePositionModel::where(['Active' => 2,'RegionID' => $assignedEmployeePosition->RegionID])->get();
        $mailString = "";
        $isgGroupIDs = [];

        foreach ($isgPositions as $isgPosition){
            $userIsg = EmployeeModel::where(['EmployeeID' => $isgPosition->EmployeeID])->first();
            $hasGroup = EmployeeHasGroupModel::where(['EmployeeID' => $isgPosition->Id, 'group_id' => 24, 'active' => 1])->first();
            if ($hasGroup)
            {
                $mailString == "" ? $mailString = $mailString . $userIsg->JobEmail :  $mailString = $mailString .','.$userIsg->JobEmail;
            }

        }
        $usingCar = $overtimeRecord->UsingCar == 0 ? 'Hayır' : 'Evet';

        $objectFile = ObjectFileModel::where(['ObjectType' => 4, 'ObjectId' => $overtimeRecord->id, 'EmployeeID' => $overtimeRecord->AssignedID])->first();
        Asay::sendMail($mailString, "", "Fazla çalışma çalışan tarafından onaylandı.", 'Fazla çalışma, çalışan tarafından onaylandı.' . '
<html lang="en">
<head>
<title>Fazla Mesai Mail</title>
<style>
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
  padding: 5px;
  text-align: center;
  
}
</style>
</head>
<body>
<br><br>
<table width="800">
  <tr style="background-color: rgb(0,31,91);color:white" >
    <th colspan="2" >İşlem Yapan Son Kullanıcı</th>
  </tr>
  <tr >
    <td colspan="2"  >
           ' . $employee->UsageName . ' ' . $employee->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>İş Emri No</b>
    </td>
    <td  >
        <b>Görevlendirilen Personel</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->JobOrderNo . '
    </td >
    <td  >
        ' . $assignedEmployee->UsageName . ' ' . $assignedEmployee->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Statü</b>
    </td>
    <td  >
        <b>Yönetici</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->Status->Name . '
    </td>
    <td  >
        ' . $assignedEmployeesManager->UsageName . ' ' . $assignedEmployeesManager->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Proje</b>
    </td>
    <td  >
        <b>Başlangıç Tarihi</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->Project->name . '
    </td>
    <td  >
        ' . date("d.m.Y", strtotime($overtimeRecord->BeginDate)) . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Başlangıç Saati</b>
    </td>
    <td  >
        <b>Bitiş Saati</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->BeginTime . '
    </td>
    <td  >
        ' . $overtimeRecord->EndTime . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Çalışma Yapılacak Saha ID</b>
    </td>
    <td  >
        <b>Çalışma Yapılacak Saha Adı</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $overtimeRecord->FieldID . '
    </td>
    <td>
        ' . $overtimeRecord->FieldName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Araç Kullanacak Mı</b>
    </td>
    <td  >
        <b>Araç Plakası</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $usingCar  . '
    </td>
    <td>
        ' . $overtimeRecord->PlateNumber . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  colspan="2">
        <b>Açıklama</b>
    </td>
  </tr>
  <tr>
    <td colspan="2">
        ' . $overtimeRecord->Description  . '
    </td>
  </tr>
</table>
</body>
</html>'
            , "Fazla Çalışma Çalışan Tarafından Onaylandı");



        if ($overtimeRecord->save())
        {
            $userEmployee = EmployeeModel::find($overtimeRequest->Employee);
            $logStatus = LogsModel::setLog($overtimeRequest->Employee,$overtimeRecord->id,3,24,'','',$overtimeRecord->BeginDate . ' ' . $overtimeRecord->BeginTime . ' tarihli fazla çalışma ' . $userEmployee->UsageName . '' . $userEmployee->LastName.' adlı yönetici tarafından oluşturuldu.','','','','','');
            return ['status' => true, 'message' => 'İşlem Başarılı'];
        }
        else
            return ['status' => false, 'message' => 'Kayıt Sırasında Bir Hata Oluştu'];
    }//ISG'YE MAIL GIDICEK

    public static function overtimeCancelRequestFromEmployee($overtimeRequest)
    {

        $overtimeRecord = OvertimeModel::where(['id' => $overtimeRequest->OvertimeId, 'Active' => 1])->first();
        $overtimeRecord->StatusID = 5;

        //TODO Loglama ve mail göndeirmi yapılacak

        $usingCar = $overtimeRecord->UsingCar == 0 ? 'Hayır' : 'Evet';

        $employee = EmployeeModel::find($overtimeRequest->Employee);
        $assignedEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
        $reason = $overtimeRequest->ProcessReason == "" || $overtimeRequest->ProcessReason != null ? $overtimeRequest->ProcessReason : "Açıklama Yapılmamış";
        $assignedEmployeesManager = EmployeeModel::find(EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $overtimeRecord->AssignedID])->first()->ManagerID);
        Asay::sendMail($assignedEmployeesManager->JobEmail, "", $overtimeRecord->JobOrderNo . " iş emri kodlu fazla çalışma çalışan tarafından iptal edildi.", $overtimeRecord->JobOrderNo . ' iş emri nolu fazla çalışma, ' . $employee->UsageName . ' ' . $employee->LastName . ' isimli personel tarafından iptal edildi.' . '
<html lang="en">
<head>
<title>Fazla Mesai Mail</title>
<style>
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
  padding: 5px;
  text-align: center;
  
}
</style>
</head>
<body>
<br><br>
<table width="800">
  <tr style="background-color: rgb(0,31,91);color:white" >
    <th colspan="2" >İşlem Yapan Son Kullanıcı</th>
  </tr>
  <tr >
    <td colspan="2"  >
           ' . $employee->UsageName . ' ' . $employee->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>İş Emri No</b>
    </td>
    <td  >
        <b>Görevlendirilen Personel</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->JobOrderNo . '
    </td >
    <td  >
        ' . $assignedEmployee->UsageName . ' ' . $assignedEmployee->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Statü</b>
    </td>
    <td  >
        <b>Yönetici</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->Status->Name . '
    </td>
    <td  >
        ' . $assignedEmployeesManager->UsageName . ' ' . $assignedEmployeesManager->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Proje</b>
    </td>
    <td  >
        <b>Başlangıç Tarihi</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->Project->name . '
    </td>
    <td  >
        ' . date("d.m.Y", strtotime($overtimeRecord->BeginDate)) . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Başlangıç Saati</b>
    </td>
    <td  >
        <b>Bitiş Saati</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->BeginTime . '
    </td>
    <td  >
        ' . $overtimeRecord->EndTime . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Çalışma Yapılacak Saha ID</b>
    </td>
    <td  >
        <b>Çalışma Yapılacak Saha Adı</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $overtimeRecord->FieldID . '
    </td>
    <td>
        ' . $overtimeRecord->FieldName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Araç Kullanacak Mı</b>
    </td>
    <td  >
        <b>Araç Plakası</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $usingCar  . '
    </td>
    <td>
        ' . $overtimeRecord->PlateNumber . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  colspan="2">
        <b>Açıklama</b>
    </td>
  </tr>
  <tr>
    <td colspan="2">
        ' . $overtimeRecord->Description  . '
    </td>
  </tr>
</table>
</body>
</html>'
            , "Fazla Çalışma İptal Edildi");




        if ($overtimeRecord->save())
        {
            $userEmployee = EmployeeModel::find($overtimeRequest->Employee);
            $logStatus = LogsModel::setLog($overtimeRequest->Employee,$overtimeRecord->id,3,25,'','',$overtimeRecord->BeginDate . ' ' . $overtimeRecord->BeginTime . ' tarihli fazla çalışma ' . $userEmployee->UsageName . '' . $userEmployee->LastName.' adlı çalışan tarafından iptal edildi.','','','','','');
            return ['status' => true, 'message' => 'İşlem Başarılı'];
        }

        else
            return ['status' => false, 'message' => 'Kayıt Sırasında Bir Hata Oluştu'];

    }

    public static function overtimeCompleteRequestFromEmployee($overtimeRequest)
    {

        $overtimeRecord = OvertimeModel::where(['id' => $overtimeRequest->OvertimeId, 'Active' => 1])->first();
        $overtimeRecord->StatusID = 6;
        $result = $overtimeRecord->save();

        if ($result && $overtimeRequest->hasFile('WorkingReport')) {
            $file = file_get_contents($overtimeRequest->WorkingReport->path());
            $guzzleParams = [

                'multipart' => [
                    [
                        'name' => 'token',
                        'contents' => $overtimeRequest->token
                    ],
                    [
                        'name' => 'ObjectType',
                        'contents' => 4 // Harcama Masraf
                    ],
                    [
                        'name' => 'ObjectTypeName',
                        'contents' => 'Overtime'
                    ],
                    [
                        'name' => 'ObjectId',
                        'contents' => $overtimeRecord->id
                    ],
                    [
                        'name' => 'file',
                        'contents' => $file,
                        'filename' => 'fazla_calisma_' . $overtimeRecord->id . '.' . $overtimeRequest->WorkingReport->getClientOriginalExtension()
                    ],

                ],
            ];

            $client = new \GuzzleHttp\Client();
            $res = $client->request("POST", 'http://lifi.asay.com.tr/connectUpload', $guzzleParams);
            $responseBody = json_decode($res->getBody());

            if ($responseBody->status == false)
                return ['status' => false, 'message' => 'Belge yüklenirken bir hata oluştu'];
        }

        $usingCar = $overtimeRecord->UsingCar == 0 ? 'Hayır' : 'Evet';

        //TODO Loglama ve mail göndeirmi yapılacak
        $employee = EmployeeModel::find($overtimeRequest->Employee);
        $assignedEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
        $assignedEmployeesManager = EmployeeModel::find(EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $overtimeRecord->AssignedID])->first()->ManagerID);
        $objectFile = ObjectFileModel::where(['ObjectType' => 4, 'ObjectId' => $overtimeRecord->id, 'EmployeeID' => $overtimeRecord->AssignedID])->first();
        Asay::sendMail($assignedEmployeesManager->JobEmail, "", "Fazla çalışma tamamlandı.", 'Fazla çalışma, ' . $employee->UsageName . ' ' . $employee->LastName . ' tarafından tamamlandı. İlgili fazla çalışma onayınızı beklemektedir.' . '
<html lang="en">
<head>
<title>Fazla Mesai Mail</title>
<style>
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
  padding: 5px;
  text-align: center;
  
}
</style>
</head>
<body>
<br><br>
<table width="800">
  <tr style="background-color: rgb(0,31,91);color:white" >
    <th colspan="2"  >İşlem Yapan Son Kullanıcı</th>
  </tr>
  <tr >
    <td colspan="2"  >
           ' . $employee->UsageName . ' ' . $employee->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>İş Emri No</b>
    </td>
    <td  >
        <b>Görevlendirilen Personel</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->JobOrderNo . '
    </td >
    <td  >
        ' . $assignedEmployee->UsageName . ' ' . $assignedEmployee->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Statü</b>
    </td>
    <td  >
        <b>Yönetici</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->Status->Name . '
    </td>
    <td  >
        ' . $assignedEmployeesManager->UsageName . ' ' . $assignedEmployeesManager->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Proje</b>
    </td>
    <td  >
        <b>Başlangıç Tarihi</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->Project->name . '
    </td>
    <td  >
        ' . date("d.m.Y", strtotime($overtimeRecord->BeginDate)) . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Başlangıç Saati</b>
    </td>
    <td  >
        <b>Bitiş Saati</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->BeginTime . '
    </td>
    <td  >
        ' . $overtimeRecord->EndTime . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Çalışma Yapılacak Saha ID</b>
    </td>
    <td  >
        <b>Çalışma Yapılacak Saha Adı</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $overtimeRecord->FieldID . '
    </td>
    <td>
        ' . $overtimeRecord->FieldName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Araç Kullanacak Mı</b>
    </td>
    <td  >
        <b>Araç Plakası</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $usingCar  . '
    </td>
    <td>
        ' . $overtimeRecord->PlateNumber . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  colspan="2">
        <b>Açıklama</b>
    </td>
  </tr>
  <tr>
    <td colspan="2">
        ' . $overtimeRecord->Description  . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  colspan="2">
        <b>Çalışma Belgesi Linki</b>
    </td>
  </tr>
  <tr>
    <td colspan="2">
        ' . $objectFile->File  . '
    </td>
  </tr>
</table>
</body>
</html>'
            , "Fazla Çalışma Tamamlandı");

        if ($result)
        {
            $userEmployee = EmployeeModel::find($overtimeRequest->Employee);
            $logStatus = LogsModel::setLog($overtimeRequest->Employee,$overtimeRecord->id,3,27,'','',$overtimeRecord->BeginDate . ' ' . $overtimeRecord->BeginTime . ' tarihli fazla çalışma ' . $userEmployee->UsageName . '' . $userEmployee->LastName.' adlı çalışan tarafından tamamlandı.','','','','','');
            return ['status' => true, 'message' => 'İşlem Başarılı'];
        }
        else
            return ['status' => false, 'message' => 'Kayıt Sırasında Bir Hata Oluştu'];

    }

    public static function overtimeCorrectionRequestFromManager($overtimeRequest)
    {

        $overtimeRecord = OvertimeModel::where(['id' => $overtimeRequest->OvertimeId, 'Active' => 1])->first();

        //$overtimeRecord->CreatedBy = $overtimeRequest['CreatedBy'];
        $overtimeRecord->ManagerID = $overtimeRecord->CreatedBy;
        $overtimeRecord->AssignedID = $overtimeRequest->AssignedID;
        $overtimeRecord->KindID = $overtimeRequest->KindID;
        $overtimeRecord->BeginDate = $overtimeRequest->BeginDate;
        $overtimeRecord->BeginTime = $overtimeRequest->BeginTime.":00";
        $overtimeRecord->ProjectID = $overtimeRequest->ProjectID;
        $overtimeRecord->JobOrderNo = $overtimeRequest->JobOrderNo;
        $overtimeRecord->CityID = $overtimeRequest->CityID;
        $overtimeRecord->FieldID = $overtimeRequest->FieldID;
        $overtimeRecord->FieldName = $overtimeRequest->FieldName;
        $overtimeRecord->EndTime = $overtimeRequest->EndTime.":00";
        $overtimeRecord->UsingCar = $overtimeRequest->UsingCar;
        $overtimeRecord->PlateNumber = $overtimeRequest->PlateNumber;
        $overtimeRecord->Description = $overtimeRequest->Description;

        $dirtyFields = $overtimeRecord->getDirty();
        $dirtyFieldsString = "";
        foreach ($dirtyFields as $field => $newdata) {
            $olddata = $overtimeRecord->getOriginal($field);
            if ($olddata != $newdata) {
                //TODO Loglama İşlemi burada yapılacak.
                $dirtyFieldsString .= '<tr><td>' . self::columnNameToTurkish($field) . ' (İlk Değer) :  ' . $olddata . '</td><td>' . self::columnNameToTurkish($field) . ' (Düzenlenen Değer) :  ' . $newdata . '</td></tr>';
            }
        }

        $overtimeRecord->StatusID = 7;

        $usingCar = $overtimeRecord->UsingCar == 0 ? 'Hayır' : 'Evet';

        $employee = EmployeeModel::find($overtimeRequest->Employee);
        $assignedEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
        $reason = $overtimeRequest->ProcessReason == "" || $overtimeRequest->ProcessReason != null ? $overtimeRequest->ProcessReason : "Açıklama Yapılmamış";
        $assignedEmployeesManager = EmployeeModel::find($overtimeRecord->ManagerID);
        $mailTable = 'Fazla çalışmanız için, ' . $assignedEmployeesManager->UsageName . ' ' . $assignedEmployeesManager->LastName . ' isimli yöneticiniz tarafından düzenleme talep edildi.' . '
<html lang="en">
<head>
<title>Fazla Mesai Mail</title>
<style>
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
  padding: 5px;
  text-align: center;
  
}
</style>
</head>
<body>
<br><br>
<table width="800">
  <tr style="background-color: rgb(0,31,91);color:white" >
    <th colspan="2"  >İşlem Yapan Son Kullanıcı</th>
  </tr>
  <tr >
    <td colspan="2"  >
           ' . $employee->UsageName . ' ' . $employee->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>İş Emri No</b>
    </td>
    <td  >
        <b>Görevlendirilen Personel</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->JobOrderNo . '
    </td >
    <td  >
        ' . $assignedEmployee->UsageName . ' ' . $assignedEmployee->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Statü</b>
    </td>
    <td  >
        <b>Yönetici</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->Status->Name . '
    </td>
    <td  >
        ' . $assignedEmployeesManager->UsageName . ' ' . $assignedEmployeesManager->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Proje</b>
    </td>
    <td  >
        <b>Başlangıç Tarihi</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->Project->name . '
    </td>
    <td  >
        ' . date("d.m.Y", strtotime($overtimeRecord->BeginDate)) . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Başlangıç Saati</b>
    </td>
    <td  >
        <b>Bitiş Saati</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->BeginTime . '
    </td>
    <td  >
        ' . $overtimeRecord->EndTime . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Çalışma Yapılacak Saha ID</b>
    </td>
    <td  >
        <b>Çalışma Yapılacak Saha Adı</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $overtimeRecord->FieldID . '
    </td>
    <td>
        ' . $overtimeRecord->FieldName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Araç Kullanacak Mı (1:Evet , 2:Hayır)</b>
    </td>
    <td  >
        <b>Araç Plakası</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $usingCar  . '
    </td>
    <td>
        ' . $overtimeRecord->PlateNumber . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td colspan="2" >
       <b>Açıklama</b>
    </td>
  </tr>
  <tr >
    <td  colspan="2" >
        ' . $reason . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
  	<td  colspan="2"  >
        <b>Düzenleme Yapılan Alanlar</b>
    </td>
  </tr>' . $dirtyFieldsString . '
  </table>
</body>
</html>
  ';

        Asay::sendMail($assignedEmployee->JobEmail, "", "Fazla Çalışma İçin Düzenleme Talep Edildi", $mailTable
            , "Fazla Çalışma İçin Düzenleme Talep Edildi");



        if ($overtimeRecord->save())
            return ['status' => true, 'message' => 'İşlem Başarılı'];
        else
            return ['status' => false, 'message' => 'Kayıt Sırasında Bir Hata Oluştu'];

    }

    public static function overtimeApproveRequestFromManager($overtimeRequest)
    {

        $overtimeRecord = OvertimeModel::where(['id' => $overtimeRequest->OvertimeId, 'Active' => 1])->first();
        $managerSupervisor = EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $overtimeRecord->CreatedBy])->first();
        $project = ProjectsModel::find($overtimeRecord->ProjectID);

        //Mesai, proje yöneticisinin onayına gitmemiş ise
        /*if ($overtimeRecord->ManagerID != $project->manager_id)
        {
            $overtimeRecord->ManagerID = $project->manager_id;
            $overtimeRecord->StatusID = 6;
        }*/
        $usingCar = $overtimeRecord->UsingCar == 0 ?  'Hayır' : 'Evet';

        //Mesai, mesaiyi oluşturan kişinin bir üst yöneticisine gitmemiş ise
        if ($managerSupervisor == null) {
            $assignedEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
            $assignedEmployeePosition = EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $assignedEmployee->Id])->first();
            $overtimeRecord->ManagerID = $assignedEmployeePosition->HRManagerID;
            $overtimeRecord->StatusID = 8;

            $employee = EmployeeModel::find($overtimeRequest->Employee);
            $assignedEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
            $assignedEmployeePosition = EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $assignedEmployee->Id])->first();
            $assignedEmployeesManager = EmployeeModel::find(EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $overtimeRecord->AssignedID])->first()->ManagerID);
            $hrSpecialist = ProcessesSettingsModel::where(['object_type' => 4,'PropertyCode' => 'HRManager','RegionID' => $assignedEmployeePosition->RegionID])->first();
            $employeeOfHR = EmployeeModel::find($hrSpecialist->PropertyValue);
            $objectFile = ObjectFileModel::where(['ObjectType' => 4, 'ObjectId' => $overtimeRecord->id, 'EmployeeID' => $overtimeRecord->AssignedID])->first();
            Asay::sendMail($employeeOfHR->JobEmail, "", "Fazla çalışma onay bekliyor.", 'Sayın'. $employeeOfHR->UsageName . ' ' . $employeeOfHR->LastName . ' fazla çalışma, onayınızı bekliyor.' . '
<html lang="en">
<head>
<title>Fazla Mesai Mail</title>
<style>
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
  padding: 5px;
  text-align: center;
  
}
</style>
</head>
<body>
<br><br>
<table width="800">
  <tr style="background-color: rgb(0,31,91);color:white" >
    <th colspan="2"  >İşlem Yapan Son Kullanıcı</th>
  </tr>
  <tr >
    <td colspan="2"  >
           ' . $employee->UsageName . ' ' . $employee->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>İş Emri No</b>
    </td>
    <td  >
        <b>Görevlendirilen Personel</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->JobOrderNo . '
    </td >
    <td  >
        ' . $assignedEmployee->UsageName . ' ' . $assignedEmployee->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Statü</b>
    </td>
    <td  >
        <b>Yönetici</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->Status->Name . '
    </td>
    <td  >
        ' . $assignedEmployeesManager->UsageName . ' ' . $assignedEmployeesManager->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Proje</b>
    </td>
    <td  >
        <b>Başlangıç Tarihi</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->Project->name . '
    </td>
    <td  >
        ' . date("d.m.Y", strtotime($overtimeRecord->BeginDate)) . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Başlangıç Saati</b>
    </td>
    <td  >
        <b>Bitiş Saati</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->BeginTime . '
    </td>
    <td  >
        ' . $overtimeRecord->EndTime . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Çalışma Yapılacak Saha ID</b>
    </td>
    <td  >
        <b>Çalışma Yapılacak Saha Adı</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $overtimeRecord->FieldID . '
    </td>
    <td>
        ' . $overtimeRecord->FieldName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Araç Kullanacak Mı</b>
    </td>
    <td  >
        <b>Araç Plakası</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $usingCar  . '
    </td>
    <td>
        ' . $overtimeRecord->PlateNumber . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  colspan="2">
        <b>Açıklama</b>
    </td>
  </tr>
  <tr>
    <td colspan="2">
        ' . $overtimeRecord->Description  . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  colspan="2">
        <b>Çalışma Belgesi Linki</b>
    </td>
  </tr>
  <tr>
    <td colspan="2">
        ' . $objectFile->File  . '
    </td>
  </tr>
</table>
</body>
</html>'
                , "Fazla Çalışma Onay Bekliyor.");

            /*
             *
             * ÜST TARAF İK, ALT TARAF USER MAİLİ
             *
             * */
            $employee = EmployeeModel::find($overtimeRequest->Employee);
            $assignedEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
            $assignedEmployeesManager = EmployeeModel::find(EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $overtimeRecord->AssignedID])->first()->ManagerID);
            $objectFile = ObjectFileModel::where(['ObjectType' => 4, 'ObjectId' => $overtimeRecord->id, 'EmployeeID' => $overtimeRecord->AssignedID])->first();
            Asay::sendMail($assignedEmployee->JobEmail, "", "Fazla çalışma yöneticileriniz tarafından onaylandı.", 'Fazla çalışma, yöneticileriniz tarafından onaylandı.' . '
<html lang="en">
<head>
<title>Fazla Mesai Mail</title>
<style>
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
  padding: 5px;
  text-align: center;
  
}
</style>
</head>
<body>
<br><br>
<table width="800">
  <tr style="background-color: rgb(0,31,91);color:white" >
    <th colspan="2"  >İşlem Yapan Son Kullanıcı</th>
  </tr>
  <tr >
    <td colspan="2"  >
           ' . $employee->UsageName . ' ' . $employee->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>İş Emri No</b>
    </td>
    <td  >
        <b>Görevlendirilen Personel</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->JobOrderNo . '
    </td >
    <td  >
        ' . $assignedEmployee->UsageName . ' ' . $assignedEmployee->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Statü</b>
    </td>
    <td  >
        <b>Yönetici</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->Status->Name . '
    </td>
    <td  >
        ' . $assignedEmployeesManager->UsageName . ' ' . $assignedEmployeesManager->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Proje</b>
    </td>
    <td  >
        <b>Başlangıç Tarihi</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->Project->name . '
    </td>
    <td  >
        ' . date("d.m.Y", strtotime($overtimeRecord->BeginDate)) . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Başlangıç Saati</b>
    </td>
    <td  >
        <b>Bitiş Saati</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->BeginTime . '
    </td>
    <td  >
        ' . $overtimeRecord->EndTime . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Çalışma Yapılacak Saha ID</b>
    </td>
    <td  >
        <b>Çalışma Yapılacak Saha Adı</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $overtimeRecord->FieldID . '
    </td>
    <td>
        ' . $overtimeRecord->FieldName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Araç Kullanacak Mı</b>
    </td>
    <td  >
        <b>Araç Plakası</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $usingCar  . '
    </td>
    <td>
        ' . $overtimeRecord->PlateNumber . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  colspan="2">
        <b>Açıklama</b>
    </td>
  </tr>
  <tr>
    <td colspan="2">
        ' . $overtimeRecord->Description  . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  colspan="2">
        <b>Çalışma Belgesi Linki</b>
    </td>
  </tr>
  <tr>
    <td colspan="2">
        ' . $objectFile->File  . '
    </td>
  </tr>
</table>
</body>
</html>'
                , "Fazla Çalışma Onaylandı");



            $userEmployee = EmployeeModel::find($overtimeRequest->Employee);
            $logStatus = LogsModel::setLog($overtimeRequest->Employee,$overtimeRecord->id,3,28,'','',$overtimeRecord->BeginDate . ' ' . $overtimeRecord->BeginTime . ' tarihli fazla çalışma ' . $userEmployee->UsageName . '' . $userEmployee->LastName.' adlı yönetici tarafından onaylandı.','','','','','');



        }
        else if ($overtimeRecord->ManagerID != $managerSupervisor->ManagerID) {
            //TODO Loglama ve mail göndeirmi yapılacak
            $employee = EmployeeModel::find($overtimeRequest->Employee);
            $assignedEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
            $assignedEmployeesManager = EmployeeModel::find(EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $overtimeRecord->AssignedID])->first()->ManagerID);
            $assignedEmployeesManagerPosition = EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $overtimeRecord->AssignedID])->first();
            $supervisorEmployee = EmployeeModel::find($assignedEmployeesManagerPosition->ManagerID);
            $objectFile = ObjectFileModel::where(['ObjectType' => 4, 'ObjectId' => $overtimeRecord->id, 'EmployeeID' => $overtimeRecord->AssignedID])->first();
            Asay::sendMail($supervisorEmployee->JobEmail, "", "Fazla çalışma tamamlandı.", 'Sayın' . $supervisorEmployee->UsageName . ' ' . $supervisorEmployee->LastName . ' fazla çalışma tamamlandı, ' . $employee->UsageName . ' ' . $employee->LastName . ' tarafından onaylandı. İlgili fazla çalışma onayınızı beklemektedir.' . '
<html lang="en">
<head>
<title>Fazla Mesai Mail</title>
<style>
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
  padding: 5px;
  text-align: center;
  
}
</style>
</head>
<body>
<br><br>
<table width="800">
  <tr style="background-color: rgb(0,31,91);color:white" >
    <th colspan="2"  >İşlem Yapan Son Kullanıcı</th>
  </tr>
  <tr >
    <td colspan="2"  >
           ' . $employee->UsageName . ' ' . $employee->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>İş Emri No</b>
    </td>
    <td  >
        <b>Görevlendirilen Personel</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->JobOrderNo . '
    </td >
    <td  >
        ' . $assignedEmployee->UsageName . ' ' . $assignedEmployee->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Statü</b>
    </td>
    <td  >
        <b>Yönetici</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->Status->Name . '
    </td>
    <td  >
        ' . $assignedEmployeesManager->UsageName . ' ' . $assignedEmployeesManager->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Proje</b>
    </td>
    <td  >
        <b>Başlangıç Tarihi</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->Project->name . '
    </td>
    <td  >
        ' . date("d.m.Y", strtotime($overtimeRecord->BeginDate)) . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Başlangıç Saati</b>
    </td>
    <td  >
        <b>Bitiş Saati</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->BeginTime . '
    </td>
    <td  >
        ' . $overtimeRecord->EndTime . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Çalışma Yapılacak Saha ID</b>
    </td>
    <td  >
        <b>Çalışma Yapılacak Saha Adı</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $overtimeRecord->FieldID . '
    </td>
    <td>
        ' . $overtimeRecord->FieldName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Araç Kullanacak Mı</b>
    </td>
    <td  >
        <b>Araç Plakası</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $usingCar  . '
    </td>
    <td>
        ' . $overtimeRecord->PlateNumber . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  colspan="2">
        <b>Açıklama</b>
    </td>
  </tr>
  <tr>
    <td colspan="2">
        ' . $overtimeRecord->Description  . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  colspan="2">
        <b>Çalışma Belgesi Linki</b>
    </td>
  </tr>
  <tr>
    <td colspan="2">
        ' . $objectFile->File  . '
    </td>
  </tr>
</table>
</body>
</html>'
                , "Fazla Çalışma Onay Bekliyor");


            $overtimeRecord->ManagerID = $managerSupervisor->ManagerID;
            $overtimeRecord->StatusID = 6;


            $userEmployee = EmployeeModel::find($overtimeRequest->Employee);
            $logStatus = LogsModel::setLog($overtimeRequest->Employee,$overtimeRecord->id,3,28,'','',$overtimeRecord->BeginDate . ' ' . $overtimeRecord->BeginTime . ' tarihli fazla çalışma ' . $userEmployee->UsageName . '' . $userEmployee->LastName.' adlı yönetici tarafından onaylandı.','','','','','');

        }




        //Hem Proje Yöneticisi hem de mesaiyi oluşturan kişinin yöneticisi ise direkt olarak üst yönetici onayına gerek kalmaması için
        /*else if($overtimeRecord->ManagerID == $project->manager_id && $overtimeRecord->ManagerID != $managerSupervisor->ManagerID)
        {
            $overtimeRecord->StatusID = 8;
        }*/

        //Mesaiyi onaylayacak kimse kalmadı ise
        else {
            $assignedEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
            $assignedEmployeePosition = EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $assignedEmployee->Id])->first();
            $overtimeRecord->ManagerID = $assignedEmployeePosition->HRManagerID;
            $overtimeRecord->StatusID = 8;

            $usingCar = $overtimeRecord->UsingCar == 0 ? 'Hayır' : 'Evet';


            $employee = EmployeeModel::find($overtimeRequest->Employee);
            $assignedEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
            $assignedEmployeesManager = EmployeeModel::find(EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $overtimeRecord->AssignedID])->first()->ManagerID);
            $objectFile = ObjectFileModel::where(['ObjectType' => 4, 'ObjectId' => $overtimeRecord->id, 'EmployeeID' => $overtimeRecord->AssignedID])->first();
            Asay::sendMail($assignedEmployee->JobEmail, "", "Fazla çalışma yöneticileriniz tarafından onaylandı.", 'Fazla çalışma, yöneticileriniz tarafından onaylandı.' . '
<html lang="en">
<head>
<title>Fazla Mesai Mail</title>
<style>
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
  padding: 5px;
  text-align: center;
  
}
</style>
</head>
<body>
<br><br>
<table width="800">
  <tr style="background-color: rgb(0,31,91);color:white" >
    <th colspan="2"  >İşlem Yapan Son Kullanıcı</th>
  </tr>
  <tr >
    <td colspan="2"  >
           ' . $employee->UsageName . ' ' . $employee->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>İş Emri No</b>
    </td>
    <td  >
        <b>Görevlendirilen Personel</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->JobOrderNo . '
    </td >
    <td  >
        ' . $assignedEmployee->UsageName . ' ' . $assignedEmployee->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Statü</b>
    </td>
    <td  >
        <b>Yönetici</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->Status->Name . '
    </td>
    <td  >
        ' . $assignedEmployeesManager->UsageName . ' ' . $assignedEmployeesManager->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Proje</b>
    </td>
    <td  >
        <b>Başlangıç Tarihi</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->Project->name . '
    </td>
    <td  >
        ' . date("d.m.Y", strtotime($overtimeRecord->BeginDate)) . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Başlangıç Saati</b>
    </td>
    <td  >
        <b>Bitiş Saati</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->BeginTime . '
    </td>
    <td  >
        ' . $overtimeRecord->EndTime . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Çalışma Yapılacak Saha ID</b>
    </td>
    <td  >
        <b>Çalışma Yapılacak Saha Adı</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $overtimeRecord->FieldID . '
    </td>
    <td>
        ' . $overtimeRecord->FieldName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Araç Kullanacak Mı</b>
    </td>
    <td  >
        <b>Araç Plakası</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $usingCar  . '
    </td>
    <td>
        ' . $overtimeRecord->PlateNumber . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  colspan="2">
        <b>Açıklama</b>
    </td>
  </tr>
  <tr>
    <td colspan="2">
        ' . $overtimeRecord->Description  . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  colspan="2">
        <b>Çalışma Belgesi Linki</b>
    </td>
  </tr>
  <tr>
    <td colspan="2">
        ' . $objectFile->File  . '
    </td>
  </tr>
</table>
</body>
</html>'
                , "Fazla Çalışma Onaylandı");




            /*
             *
             *
             * ÜST TARAF ÇALIŞANA MAİL, ALT TARAF İK PERSONELİNE MAİL
             *
             *
             * */


            $employee = EmployeeModel::find($overtimeRequest->Employee);
            $assignedEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
            $assignedEmployeePosition = EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $assignedEmployee->Id])->first();
            $hrSpecialist = ProcessesSettingsModel::where(['object_type' => 4,'PropertyCode' => 'HRManager','RegionID' => $assignedEmployeePosition->RegionID])->first();
            $hrEmployee = EmployeeModel::find($hrSpecialist->PropertyValue);
            $objectFile = ObjectFileModel::where(['ObjectType' => 4, 'ObjectId' => $overtimeRecord->id, 'EmployeeID' => $overtimeRecord->AssignedID])->first();
            Asay::sendMail($hrEmployee->JobEmail, "", "Fazla çalışma onayınızı bekliyor.", 'Fazla çalışma, onayınızı bekliyor.' . '
<html lang="en">
<head>
<title>Fazla Mesai Mail</title>
<style>
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
  padding: 5px;
  text-align: center;
  
}
</style>
</head>
<body>
<br><br>
<table width="800">
  <tr style="background-color: rgb(0,31,91);color:white" >
    <th colspan="2"  >İşlem Yapan Son Kullanıcı</th>
  </tr>
  <tr >
    <td colspan="2"  >
           ' . $employee->UsageName . ' ' . $employee->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>İş Emri No</b>
    </td>
    <td  >
        <b>Görevlendirilen Personel</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->JobOrderNo . '
    </td >
    <td  >
        ' . $assignedEmployee->UsageName . ' ' . $assignedEmployee->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Statü</b>
    </td>
    <td  >
        <b>Yönetici</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->Status->Name . '
    </td>
    <td  >
        ' . $assignedEmployeesManager->UsageName . ' ' . $assignedEmployeesManager->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Proje</b>
    </td>
    <td  >
        <b>Başlangıç Tarihi</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->Project->name . '
    </td>
    <td  >
        ' . date("d.m.Y", strtotime($overtimeRecord->BeginDate)) . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Başlangıç Saati</b>
    </td>
    <td  >
        <b>Bitiş Saati</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->BeginTime . '
    </td>
    <td  >
        ' . $overtimeRecord->EndTime . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Çalışma Yapılacak Saha ID</b>
    </td>
    <td  >
        <b>Çalışma Yapılacak Saha Adı</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $overtimeRecord->FieldID . '
    </td>
    <td>
        ' . $overtimeRecord->FieldName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Araç Kullanacak Mı</b>
    </td>
    <td  >
        <b>Araç Plakası</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $usingCar  . '
    </td>
    <td>
        ' . $overtimeRecord->PlateNumber . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  colspan="2">
        <b>Açıklama</b>
    </td>
  </tr>
  <tr>
    <td colspan="2">
        ' . $overtimeRecord->Description  . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  colspan="2">
        <b>Çalışma Belgesi Linki</b>
    </td>
  </tr>
  <tr>
    <td colspan="2">
        ' . $objectFile->File  . '
    </td>
  </tr>
</table>
</body>
</html>'
                , "Fazla Çalışma Onay Bekliyor");


            $userEmployee = EmployeeModel::find($overtimeRequest->Employee);
            $logStatus = LogsModel::setLog($overtimeRequest->Employee,$overtimeRecord->id,3,28,'','',$overtimeRecord->BeginDate . ' ' . $overtimeRecord->BeginTime . ' tarihli fazla çalışma ' . $userEmployee->UsageName . '' . $userEmployee->LastName.' adlı yönetici tarafından onaylandı.','','','','','');

        }

        //$overtimeRecord->CreatedBy = $overtimeRequest['CreatedBy'];
        //$overtimeRecord->ManagerID = $overtimeRequest['ManagerID'];
        $overtimeRecord->AssignedID = $overtimeRequest->AssignedID;
        $overtimeRecord->KindID = $overtimeRequest->KindID;
        $overtimeRecord->BeginDate = $overtimeRequest->BeginDate;
        $overtimeRecord->BeginTime = $overtimeRequest->BeginTime;
        $overtimeRecord->ProjectID = $overtimeRequest->ProjectID;
        $overtimeRecord->JobOrderNo = $overtimeRequest->JobOrderNo;
        $overtimeRecord->CityID = $overtimeRequest->CityID;
        $overtimeRecord->FieldID = $overtimeRequest->FieldID;
        $overtimeRecord->FieldName = $overtimeRequest->FieldName;
        $overtimeRecord->EndTime = $overtimeRequest->EndTime;
        $overtimeRecord->UsingCar = $overtimeRequest->UsingCar;
        $overtimeRecord->PlateNumber = $overtimeRequest->PlateNumber;
        $overtimeRecord->Description = $overtimeRequest->Description;


        if ($overtimeRecord->save())
            return ['status' => true, 'message' => 'İşlem Başarılı'];
        else
            return ['status' => false, 'message' => 'Kayıt Sırasında Bir Hata Oluştu'];

    }//IK'ya mail gidecek

    public static function overtimeCorrectionRequestFromHR($overtimeRequest)
    {

        $overtimeRecord = OvertimeModel::where(['id' => $overtimeRequest->OvertimeId, 'Active' => 1])->first();

        //$overtimeRecord->CreatedBy = $overtimeRequest['CreatedBy'];
        $overtimeRecord->ManagerID = $overtimeRecord->CreatedBy;
        $overtimeRecord->AssignedID = $overtimeRequest->AssignedID;
        $overtimeRecord->KindID = $overtimeRequest->KindID;
        $overtimeRecord->BeginDate = $overtimeRequest->BeginDate;
        $overtimeRecord->BeginTime = $overtimeRequest->BeginTime.':00';
        $overtimeRecord->ProjectID = $overtimeRequest->ProjectID;
        $overtimeRecord->JobOrderNo = $overtimeRequest->JobOrderNo;
        $overtimeRecord->CityID = $overtimeRequest->CityID;
        $overtimeRecord->FieldID = $overtimeRequest->FieldID;
        $overtimeRecord->FieldName = $overtimeRequest->FieldName;
        $overtimeRecord->EndTime = $overtimeRequest->EndTime.':00';
        $overtimeRecord->UsingCar = $overtimeRequest->UsingCar;
        $overtimeRecord->PlateNumber = $overtimeRequest->PlateNumber;
        $overtimeRecord->Description = $overtimeRequest->Description;

        $dirtyFields = $overtimeRecord->getDirty();
        $dirtyFieldsString = "";

        foreach ($dirtyFields as $field => $newdata) {
            $olddata = $overtimeRecord->getOriginal($field);
            if (self::columnNameToTurkish($field) == 'Onaylayacak Olan Yönetici')
                continue;
            if ($olddata != $newdata) {
                //TODO Loglama İşlemi burada yapılacak.
                $dirtyFieldsString .= '<tr><td>' . self::columnNameToTurkish($field) . ' (İlk Değer) :  ' . $olddata . '</td><td>' . self::columnNameToTurkish($field) . ' (Düzenlenen Değer) :  ' . $newdata . '</td></tr>';
            }
        }

        $overtimeRecord->StatusID = 9;

        $usingCar = $overtimeRecord->UsingCar == 0 ? 'Hayır' : 'Evet';

        $employee = EmployeeModel::find($overtimeRequest->Employee);
        $assignedEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
        $reason = $overtimeRequest->ProcessReason == "" || $overtimeRequest->ProcessReason != null ? $overtimeRequest->ProcessReason : "Açıklama Yapılmamış";
        $assignedEmployeesManager = EmployeeModel::find($overtimeRecord->ManagerID);
        $mailTable = 'Fazla çalışma için İnsan Kaynakları biriminden düzenleme talep edildi.' . '
<html lang="en">
<head>
<title>Fazla Mesai Mail</title>
<style>
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
  padding: 5px;
  text-align: center;
  
}
</style>
</head>
<body>
<br><br>
<table width="800">
  <tr style="background-color: rgb(0,31,91);color:white" >
    <th colspan="2"  >İşlem Yapan Son Kullanıcı</th>
  </tr>
  <tr >
    <td colspan="2"  >
           ' . $employee->UsageName . ' ' . $employee->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>İş Emri No</b>
    </td>
    <td  >
        <b>Görevlendirilen Personel</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->JobOrderNo . '
    </td >
    <td  >
        ' . $assignedEmployee->UsageName . ' ' . $assignedEmployee->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Statü</b>
    </td>
    <td  >
        <b>Yönetici</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->Status->Name . '
    </td>
    <td  >
        ' . $assignedEmployeesManager->UsageName . ' ' . $assignedEmployeesManager->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Proje</b>
    </td>
    <td  >
        <b>Başlangıç Tarihi</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->Project->name . '
    </td>
    <td  >
        ' . date("d.m.Y", strtotime($overtimeRecord->BeginDate)) . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Başlangıç Saati</b>
    </td>
    <td  >
        <b>Bitiş Saati</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->BeginTime . '
    </td>
    <td  >
        ' . $overtimeRecord->EndTime . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Çalışma Yapılacak Saha ID</b>
    </td>
    <td  >
        <b>Çalışma Yapılacak Saha Adı</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $overtimeRecord->FieldID . '
    </td>
    <td>
        ' . $overtimeRecord->FieldName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Araç Kullanacak Mı</b>
    </td>
    <td  >
        <b>Araç Plakası</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $usingCar  . '
    </td>
    <td>
        ' . $overtimeRecord->PlateNumber . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  colspan="2">
        <b>Açıklama</b>
    </td>
  </tr>
  <tr>
    <td colspan="2">
        ' . $reason  . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
  	<td  colspan="2"  >
        <b>Düzenleme Yapılan Alanlar</b>
    </td>
  </tr>' . $dirtyFieldsString . '
  </table>
</body>
</html>
  ';

        Asay::sendMail($assignedEmployee->JobEmail, "", $overtimeRecord->JobOrderNo . " iş emri kodlu fazla çalışma için düzenleme talep edildi.", $mailTable
            , "Fazla Çalışma İçin Düzenleme Talep Edildi");


        if ($overtimeRecord->save())
            return ['status' => true, 'message' => 'İşlem Başarılı'];
        else
            return ['status' => false, 'message' => 'Kayıt Sırasında Bir Hata Oluştu'];

    }

    public static function overtimeApproveRequestFromHR($overtimeRequest)
    {

        $overtimeRecord = OvertimeModel::where(['id' => $overtimeRequest->OvertimeId, 'Active' => 1])->first();

        //$overtimeRecord->CreatedBy = $overtimeRequest['CreatedBy'];
        //$overtimeRecord->ManagerID = $overtimeRequest['ManagerID'];
        $overtimeRecord->AssignedID = $overtimeRequest->AssignedID;
        $overtimeRecord->KindID = $overtimeRequest->KindID;
        $overtimeRecord->BeginDate = $overtimeRequest->BeginDate;
        $overtimeRecord->BeginTime = $overtimeRequest->BeginTime;
        $overtimeRecord->ProjectID = $overtimeRequest->ProjectID;
        $overtimeRecord->JobOrderNo = $overtimeRequest->JobOrderNo;
        $overtimeRecord->CityID = $overtimeRequest->CityID;
        $overtimeRecord->FieldID = $overtimeRequest->FieldID;
        $overtimeRecord->FieldName = $overtimeRequest->FieldName;
        $overtimeRecord->EndTime = $overtimeRequest->EndTime;
        $overtimeRecord->UsingCar = $overtimeRequest->UsingCar;
        $overtimeRecord->PlateNumber = $overtimeRequest->PlateNumber;
        $overtimeRecord->Description = $overtimeRequest->Description;

        $dirtyFields = $overtimeRecord->getDirty();

        foreach ($dirtyFields as $field => $newdata) {
            $olddata = $overtimeRecord->getOriginal($field);
            if ($olddata != $newdata) {
                //TODO Loglama İşlemi burada yapılacak.
            }
        }

        $overtimeRecord->StatusID = 10;


        $employee = EmployeeModel::find($overtimeRequest->JobEmail);
        $assignedEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
        $assignedEmployeesManager = EmployeeModel::find(EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $overtimeRecord->AssignedID])->first()->ManagerID);
        $userAccountOfEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
        $objectFile = ObjectFileModel::where(['ObjectType' => 4, 'ObjectId' => $overtimeRecord->id, 'EmployeeID' => $overtimeRecord->AssignedID])->first();
        Asay::sendMail($userAccountOfEmployee->JobEmail, "", "Fazla çalışma insan kaynakları personeli tarafından onaylandı.", 'Fazla çalışma, insan kaynakları personeli tarafından onaylandı.' . '
<html lang="en">
<head>
<title>Fazla Mesai Mail</title>
<style>
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
  padding: 5px;
  text-align: center;
  
}
</style>
</head>
<body>
<br><br>
<table width="800">
  <tr style="background-color: rgb(0,31,91);color:white" >
    <th colspan="2"  >İşlem Yapan Son Kullanıcı</th>
  </tr>
  <tr >
    <td colspan="2"  >
           ' . $employee->UsageName . ' ' . $employee->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>İş Emri No</b>
    </td>
    <td  >
        <b>Görevlendirilen Personel</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->JobOrderNo . '
    </td >
    <td  >
        ' . $assignedEmployee->UsageName . ' ' . $assignedEmployee->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Statü</b>
    </td>
    <td  >
        <b>Yönetici</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->Status->Name . '
    </td>
    <td  >
        ' . $assignedEmployeesManager->UsageName . ' ' . $assignedEmployeesManager->LastName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Proje</b>
    </td>
    <td  >
        <b>Başlangıç Tarihi</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->Project->name . '
    </td>
    <td  >
        ' . date("d.m.Y", strtotime($overtimeRecord->BeginDate)) . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Başlangıç Saati</b>
    </td>
    <td  >
        <b>Bitiş Saati</b>
    </td>
  </tr>
  <tr>
    <td  >
        ' . $overtimeRecord->BeginTime . '
    </td>
    <td  >
        ' . $overtimeRecord->EndTime . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Çalışma Yapılacak Saha ID</b>
    </td>
    <td  >
        <b>Çalışma Yapılacak Saha Adı</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $overtimeRecord->FieldID . '
    </td>
    <td>
        ' . $overtimeRecord->FieldName . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  >
        <b>Araç Kullanacak Mı (1:Evet , 2:Hayır)</b>
    </td>
    <td  >
        <b>Araç Plakası</b>
    </td>
  </tr>
  <tr>
    <td>
        ' . $overtimeRecord->UsingCar  . '
    </td>
    <td>
        ' . $overtimeRecord->PlateNumber . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  colspan="2">
        <b>Açıklama</b>
    </td>
  </tr>
  <tr>
    <td colspan="2">
        ' . $overtimeRecord->Description  . '
    </td>
  </tr>
  <tr style="background-color: rgb(0,31,91);color:white">
    <td  colspan="2">
        <b>Çalışma Belgesi Linki</b>
    </td>
  </tr>
  <tr>
    <td colspan="2">
        ' . $objectFile->File  . '
    </td>
  </tr>
</table>
</body>
</html>'
            , "Fazla Çalışma Onaylandı");


        if ($overtimeRecord->save())
        {
            $userEmployee = EmployeeModel::find($overtimeRequest->Employee);
            $logStatus = LogsModel::setLog($overtimeRequest->Employee,$overtimeRecord->id,3,29,'','',$overtimeRecord->BeginDate . ' ' . $overtimeRecord->BeginTime . ' tarihli fazla çalışma ' . $userEmployee->UsageName . '' . $userEmployee->LastName.' adlı yönetici tarafından onaylandı.','','','','','');
            return ['status' => true, 'message' => 'İşlem Başarılı'];
        }
        else
            return ['status' => false, 'message' => 'Kayıt Sırasında Bir Hata Oluştu'];

    }

    public function getCityAttribute()
    {

        $cities = $this->hasOne(CityModel::class, "Id", "CityID");
        if ($cities) {
            return $cities->where("Active", 1)->first();
        } else {
            return "";
        }
    }

    public function getCreatedFromAttribute()
    {

        $createdFrom = $this->hasOne(EmployeeModel::class, "Id", "CreatedBy");
        if ($createdFrom) {
            return $createdFrom->where("Active", 1)->first();
        } else {
            return "";
        }

    }

    public function getApproveWhoAttribute()
    {

        $approveWho = $this->hasOne(EmployeeModel::class, "Id", "ManagerID");
        if ($approveWho) {
            return $approveWho->where("Active", 1)->first();
        } else {
            return "";
        }

    }

    public function getKindAttribute()
    {

        $kind = $this->hasOne(OvertimeKindModel::class, "id", "KindID");
        if ($kind) {
            return $kind->first();
        } else {
            return "";
        }

    }

    public function getProjectAttribute()
    {

        $project = $this->hasOne(ProjectsModel::class, "id", "ProjectID");
        if ($project) {
            return $project->where("Active", 1)->first();
        } else {
            return "";
        }

    }

    public function getStatusAttribute()
    {

        $status = $this->hasOne(OvertimeStatusModel::class, "id", "StatusID");
        if ($status) {
            return $status->first();
        } else {
            return "";
        }

    }

    public function getObjectFileAttribute()
    {

        $file = $this->hasOne(ObjectFileModel::class, "ObjectId", "id");
        if ($file) {
            return $file->where(['Active' => 1, 'ObjectType' => 4])->first();
        } else {
            return "";
        }

    }


}
