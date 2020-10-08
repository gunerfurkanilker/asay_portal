<?php

namespace App\Model;

use App\Library\Asay;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use DateTime;
use Illuminate\Support\Facades\Storage;
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
            case 'WorkBeginDate':
                return 'Gerçekleşen Fazla Çalışma Tarihi';
            case 'WorkBeginTime':
                return 'Çalışma Başlangıç Saati';
            case 'WorkEndTime':
                return 'Çalışma Bitiş Saati';
            case 'WorkNo':
                return 'Çalışma No';


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

        $beginDate2 = isset($request->WorkBeginDate) && !is_null($request->BeginDate) ? Carbon::createFromFormat("Y-m-d", $request->WorkBeginDate) : null;
        $beginTime2 = isset($request->WorkBeginTime) && !is_null($request->WorkBeginTime) ? Carbon::createFromFormat("H:i", $request->WorkBeginTime) : null;
        $endTime2 = isset($request->WorkEndTime) && !is_null($request->WorkEndTime) ? Carbon::createFromFormat("H:i", $request->WorkEndTime) : null;


        $dailyTimesQ = OvertimeModel::selectRaw('id,TIMEDIFF(EndTime,BeginTime) as timediff,TIMEDIFF(WorkEndTime,WorkBeginTime) as timediff2,StatusID')->whereIn("StatusID", [0, 1, 2])->where(['Active' => 1, 'AssignedID' => $request->AssignedID])->where(function ($query) use ($beginDate, $beginDate2) {
            if ($beginDate2)
                $query->whereBetween('BeginDate', [$beginDate2->year . '-' . $beginDate2->month . '-' . $beginDate2->day
                    , $beginDate2->year . '-' . $beginDate2->month . '-' . $beginDate2->day]);
            else
                $query->whereBetween('BeginDate', [$beginDate->year . '-' . $beginDate->month . '-' . $beginDate->day
                    , $beginDate->year . '-' . $beginDate->month . '-' . $beginDate->day]);
            $query->whereNotIn('StatusID', [3, 5]);
        }); // Günlük tanımlanmış saatleri çekiyoruz.

        $dailyTimesQ2 = OvertimeModel::selectRaw('id,TIMEDIFF(EndTime,BeginTime) as timediff,TIMEDIFF(WorkEndTime,WorkBeginTime) as timediff2,StatusID')->whereIn("StatusID", [4, 6, 7, 8, 9, 10])->where(['Active' => 1, 'AssignedID' => $request->AssignedID])->where(function ($query) use ($beginDate, $beginDate2) {
            $query->whereNotIn('StatusID', [3, 5]);

            if ($beginDate2)
                $query->whereBetween('WorkBeginDate', [$beginDate2->year . '-' . $beginDate2->month . '-' . $beginDate2->day
                    , $beginDate2->year . '-' . $beginDate2->month . '-' . $beginDate2->day]);
            else
                $query->whereBetween('WorkBeginDate', [$beginDate->year . '-' . $beginDate->month . '-' . $beginDate->day
                    , $beginDate->year . '-' . $beginDate->month . '-' . $beginDate->day]);

        }); // Günlük tanımlanmış saatleri çekiyoruz.

        $dailyTimes = array();
        foreach ($dailyTimesQ->get() as $item)
            array_push($dailyTimes,$item);
        foreach ($dailyTimesQ2->get() as $item)
            array_push($dailyTimes, $item);



        $monthlyTimesQ = OvertimeModel::selectRaw('id,TIMEDIFF(EndTime,BeginTime) as timediff,TIMEDIFF(WorkEndTime,WorkBeginTime) as timediff2,StatusID')->whereIn("StatusID", [0, 1, 2])->where(['Active' => 1, 'AssignedID' => $request->AssignedID])->where(function ($query) use ($beginDate, $beginDate2) {
            if ($beginDate2)
                $query->whereBetween('BeginDate', [$beginDate2->year . '-' . $beginDate2->month . '-' . $beginDate2->startOfMonth()->day
                    , $beginDate2->year . '-' . $beginDate2->month . '-' . $beginDate2->endOfMonth()->day]);
            else
                $query->whereBetween('BeginDate', [$beginDate->year . '-' . $beginDate->month . '-' . $beginDate->startOfMonth()->day
                    , $beginDate->year . '-' . $beginDate->month . '-' . $beginDate->endOfMonth()->day]);
            $query->whereNotIn('StatusID', [3, 5]);
        });

        $monthlyTimesQ2 = OvertimeModel::selectRaw('id,TIMEDIFF(EndTime,BeginTime) as timediff,TIMEDIFF(WorkEndTime,WorkBeginTime) as timediff2,StatusID')->whereIn("StatusID", [4, 6, 7, 8, 9, 10])->where(['Active' => 1, 'AssignedID' => $request->AssignedID])->where(function ($query) use ($beginDate, $beginDate2) {
            $query->whereNotIn('StatusID', [3, 5]);
            if ($beginDate2)
                $query->whereBetween('WorkBeginDate', [$beginDate2->year . '-' . $beginDate2->month . '-' . $beginDate2->startOfMonth()->day
                    , $beginDate2->year . '-' . $beginDate2->month . '-' . $beginDate2->endOfMonth()->day]);
            else
                $query->whereBetween('WorkBeginDate', [$beginDate->year . '-' . $beginDate->month . '-' . $beginDate->startOfMonth()->day
                    , $beginDate->year . '-' . $beginDate->month . '-' . $beginDate->endOfMonth()->day]);

        });


        $monthlyTimes = array();
        foreach ($monthlyTimesQ->get() as $item)
            array_push($monthlyTimes,$item);
        foreach ($monthlyTimesQ2->get() as $item)
            array_push($monthlyTimes, $item);

        $yearlyTimesQ = OvertimeModel::selectRaw('id,TIMEDIFF(EndTime,BeginTime) as timediff,TIMEDIFF(WorkEndTime,WorkBeginTime) as timediff2,StatusID')->whereIn("StatusID", [0, 1, 2])->where(['Active' => 1, 'AssignedID' => $request->AssignedID])->where(function ($query) use ($beginDate, $beginDate2) {
            if ($beginDate2)
                $query->whereBetween('BeginDate', [$beginDate2->startOfYear()->year . '-' . $beginDate2->startOfYear()->month . '-' . $beginDate2->startOfYear()->day
                    , $beginDate2->endOfYear()->year . '-' . $beginDate2->endOfYear()->month . '-' . $beginDate2->endOfYear()->day]);
            else
                $query->whereBetween('BeginDate', [$beginDate->startOfYear()->year . '-' . $beginDate->startOfYear()->month . '-' . $beginDate->startOfYear()->day
                    , $beginDate->endOfYear()->year . '-' . $beginDate->endOfYear()->month . '-' . $beginDate->endOfYear()->day]);
        });

        $yearlyTimesQ2 = OvertimeModel::selectRaw('id,TIMEDIFF(EndTime,BeginTime) as timediff,TIMEDIFF(WorkEndTime,WorkBeginTime) as timediff2,StatusID')->whereIn("StatusID", [0, 1, 2])->where(['Active' => 1, 'AssignedID' => $request->AssignedID])->where(function ($query) use ($beginDate, $beginDate2) {

            if ($beginDate2)
                $query->whereBetween('WorkBeginDate', [$beginDate2->startOfYear()->year . '-' . $beginDate2->startOfYear()->month . '-' . $beginDate2->startOfYear()->day
                    , $beginDate2->endOfYear()->year . '-' . $beginDate2->endOfYear()->month . '-' . $beginDate2->endOfYear()->day]);
            else
                $query->whereBetween('WorkBeginDate', [$beginDate->startOfYear()->year . '-' . $beginDate->startOfYear()->month . '-' . $beginDate->startOfYear()->day
                    , $beginDate->endOfYear()->year . '-' . $beginDate->endOfYear()->month . '-' . $beginDate->endOfYear()->day]);
            $query->whereNotIn('StatusID', [3, 5]);
        });

        $yearlyTimes = array();
        foreach ($yearlyTimesQ->get() as $item)
            array_push($yearlyTimes,$item);
        foreach ($yearlyTimesQ2->get() as $item)
            array_push($yearlyTimes,$item);

        $dailyMinutes = 0;
        $dailyHours = 0;
        $dailyMinutesLimit = 30;
        $dailyHoursLimit = 3;//3.5 Saat Günlük Limit
        foreach ($dailyTimes as $dailyTime) {
            if (!is_null($neglectRecord))
                if ($dailyTime->id == $neglectRecord->id)
                    continue;

            if ($dailyTime->StatusID == 1 || $dailyTime->StatusID == 2 || $dailyTime->StatusID == 0)
                $tempTime = Carbon::createFromFormat("H:i:s", $dailyTime->timediff);
            else
                $tempTime = Carbon::createFromFormat("H:i:s", $dailyTime->timediff2);

            $dailyMinutes += $tempTime->minute;
            if ($dailyMinutes >= 60) {
                $dailyHours++;
                $dailyMinutes = $dailyMinutes % 60;
            }
            $dailyHours += $tempTime->hour;


        }

        if (isset($beginDate2) && !is_null($beginDate2)) {
            if (($endTime->hour - $beginTime->hour) + $dailyHours > $dailyHoursLimit || ($endTime2->hour - $beginTime2->hour) + $dailyHours > $dailyHoursLimit)
                return ['status' => false, 'message' => 'Girilen fazla çalışma süresi, günlük yasal fazla çalışma limitini aşıyor.'];
            elseif (($endTime->hour - $beginTime->hour) + $dailyHours == $dailyHoursLimit || ($endTime2->hour - $beginTime2->hour) + $dailyHours == $dailyHoursLimit) {
                if (abs($endTime->minute - $beginTime->minute) + $dailyMinutes > 15 || abs($endTime2->minute - $beginTime2->minute) + $dailyMinutes > 15) {
                    return ['status' => false, 'message' => 'Girilen fazla çalışma süresi, günlük yasal fazla çalışma limitini aşıyor.'];
                }
            }
        } else {
            if (($endTime->hour - $beginTime->hour) + $dailyHours > $dailyHoursLimit)
                return ['status' => false, 'message' => 'Girilen fazla çalışma süresi, günlük yasal fazla çalışma limitini aşıyor.'];
            elseif (($endTime->hour - $beginTime->hour) + $dailyHours == $dailyHoursLimit) {
                if (abs($endTime->minute - $beginTime->minute) + $dailyMinutes > 15) {
                    return ['status' => false, 'message' => 'Girilen fazla çalışma süresi, günlük yasal fazla çalışma limitini aşıyor.'];
                }
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
            if ($monthlyTime->StatusID == 1 || $monthlyTime->StatusID == 2 || $monthlyTime->StatusID == 0)
                $tempTime = Carbon::createFromFormat("H:i:s", $monthlyTime->timediff);
            else
                $tempTime = Carbon::createFromFormat("H:i:s", $monthlyTime->timediff2);

            $monthlyMinutes += $tempTime->minute;
            if ($monthlyMinutes >= 60) {
                $monthlyHours++;
                $monthlyMinutes = $monthlyMinutes % 60;
            }
            $monthlyHours += $tempTime->hour;
        }

        if ($beginDate2 && !is_null($beginDate2)) {
            if (($endTime->hour - $beginTime->hour) + $monthlyHours > $monthlyHoursLimit || ($endTime2->hour - $beginTime2->hour) + $monthlyHours > $monthlyHoursLimit)
                return ['status' => false, 'message' => 'Girilen fazla çalışma süresi, aylık yasal fazla çalışma limitini aşıyor.'];
            elseif (($endTime->hour - $beginTime->hour) + $dailyHours == $dailyHoursLimit || ($endTime2->hour - $beginTime2->hour) + $dailyHours == $dailyHoursLimit) {
                if (abs($endTime->minute - $beginTime->minute) + $dailyMinutes > 15 || abs($endTime2->minute - $beginTime2->minute) + $dailyMinutes > 15) {
                    return ['status' => false, 'message' => 'Girilen fazla çalışma süresi, aylık yasal fazla çalışma limitini aşıyor.'];
                }
            }
        } else {
            if (($endTime->hour - $beginTime->hour) + $monthlyHours > $monthlyHoursLimit)
                return ['status' => false, 'message' => 'Girilen fazla çalışma süresi, aylık yasal fazla çalışma limitini aşıyor.'];
            elseif (($endTime->hour - $beginTime->hour) + $dailyHours == $dailyHoursLimit) {
                if (abs($endTime->minute - $beginTime->minute) + $dailyMinutes > 15) {
                    return ['status' => false, 'message' => 'AGirilen fazla çalışma süresi, aylık yasal fazla çalışma limitini aşıyor.'];
                }
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
            if ($yearlyTime->StatusID == 1 || $yearlyTime->StatusID == 2 || $yearlyTime->StatusID == 0)
                $tempTime = Carbon::createFromFormat("H:i:s", $yearlyTime->timediff);
            else
                $tempTime = Carbon::createFromFormat("H:i:s", $yearlyTime->timediff2);

            $yearlyMinutes += $tempTime->minute;
            if ($yearlyMinutes >= 60) {
                $yearlyHours++;
                $yearlyMinutes = $yearlyMinutes % 60;
            }
            $yearlyHours += $tempTime->hour;
        }

        if (isset($beginDate2) && !is_null($beginDate2)) {
            if (($endTime->hour - $beginTime->hour) + $yearlyHours > $yearlyHoursLimit || ($endTime2->hour - $beginTime2->hour) + $yearlyHours > $yearlyHoursLimit)
                return ['status' => false, 'message' => 'Girilen fazla çalışma süresi, yıllık yasal fazla çalışma limitini aşıyor.'];
        } else {
            if (($endTime->hour - $beginTime->hour) + $yearlyHours > $yearlyHoursLimit)
                return ['status' => false, 'message' => 'Girilen fazla çalışma süresi, yıllık yasal fazla çalışma limitini aşıyor.'];
        }

        return ['status' => true, 'message' => 'Girilen fazla çalışma süresi herhangi bir limiti aşmıyor.', 'data' => $monthlyHours];

    }

    public static function getOvertimeByStatus($status, $EmployeeID)
    {
        $userEmployees = EmployeePositionModel::where(['Active' => 2])->orWhere(['UnitSupervisorID' => $EmployeeID, 'ManagerID' => $EmployeeID])->get();
        $userEmployeesIDs = [];
        foreach ($userEmployees as $userEmployee) {
            array_push($userEmployeesIDs, $userEmployee->EmployeeID);
        }
        $overtimeQ = OvertimeModel::where(['Active' => 1, 'StatusID' => $status]);

        return self::where(['Active' => 1, 'StatusID' => $status])->where(function ($query) use ($EmployeeID, $userEmployeesIDs, $status) {
            $query->orWhere(['ManagerID' => $EmployeeID, 'CreatedBy' => $EmployeeID]);
            if ($status == 8 || $status == 9 || $status == 10)
                $query->orWhereIn('AssignedID', $userEmployeesIDs);
        })->orderBy('BeginDate', 'asc')->get();

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
            $query->where('UnitSupervisorID', $managerId)->orWhere(['ManagerID' => $managerId]);
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

    public static function getHREmployees($request)
    {
        $hrEmployee = EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $request->Employee])->first();

        $employeePositions = EmployeePositionModel::where(['RegionID' => $hrEmployee->RegionID, 'Active' => 2])->get();

        $employeeList = [];

        foreach ($employeePositions as $employeePosition) {
            $tempEmployee = EmployeeModel::find($employeePosition->EmployeeID);
            array_push($employeeList, $tempEmployee);
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

        //TODO Kaydet, Kaydet ve Onaya Gönder Limit kontrolleri hatalı gibi duruyor.

        switch ($procestype) {
            case 0:
                //Limit Kontrol
                if ($request->OvertimeId == null) {
                    $limitCheck = self::overtimeLimitCheck($request);
                    if ($limitCheck['status'] == false)
                        return $limitCheck;
                } else {
                    $overtimeRecord = OvertimeModel::find($request->OvertimeId);
                    $limitCheck = self::overtimeLimitCheck($request, $overtimeRecord);
                    if ($limitCheck['status'] == false)
                        return $limitCheck;
                }
                return self::saveOvertimeRequest($request);
            case 1:
                if ($request->OvertimeId == null) {
                    $limitCheck = self::overtimeLimitCheck($request);
                    if ($limitCheck['status'] == false)
                        return $limitCheck;
                } else {
                    $overtimeRecord = OvertimeModel::find($request->OvertimeId);
                    $limitCheck = self::overtimeLimitCheck($request, $overtimeRecord);
                    if ($limitCheck['status'] == false)
                        return $limitCheck;
                }
                return self::sendOvertimeRequestToEmployee($request);
            case 2:
                $overtimeRecord = OvertimeModel::find($request->OvertimeId);
                $limitCheck = self::overtimeLimitCheck($request, $overtimeRecord);
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
                $overtimeRecord = OvertimeModel::find((int)$request->OvertimeId);
                $limitCheck = self::overtimeLimitCheck($request, $overtimeRecord);
                if ($limitCheck['status'] == false)
                    return $limitCheck;
                return self::overtimeCompleteRequestFromEmployee($request);
            case 7:
                $overtimeRecord = OvertimeModel::find($request->OvertimeId);
                $limitCheck = self::overtimeLimitCheck($request, $overtimeRecord);
                if ($limitCheck['status'] == false)
                    return $limitCheck;
                return self::overtimeCorrectionRequestFromManager($request);
            case 8:
                return self::overtimeApproveRequestFromManager($request);
            case 9:
                $overtimeRecord = OvertimeModel::find($request->OvertimeId);
                $limitCheck = self::overtimeLimitCheck($request, $overtimeRecord);
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

            if (isset($overtimeRequest->OvertimeId)) {
                $userEmployee = EmployeeModel::find($overtimeRequest->Employee);
                $logStatus = LogsModel::setLog($overtimeRequest->Employee, $overtimeRecord->id, 3, 21, '', '', $overtimeRecord->BeginDate . ' ' . $overtimeRecord->BeginTime . ' tarihli fazla çalışma ' . $userEmployee->UsageName . '' . $userEmployee->LastName . ' adlı personel tarafından düzenlendi.', '', '', '', '', '');
            } else {
                $userEmployee = EmployeeModel::find($overtimeRequest->Employee);
                $logStatus = LogsModel::setLog($overtimeRequest->Employee, $overtimeRecord->id, 3, 22, '', '', $overtimeRecord->BeginDate . ' ' . $overtimeRecord->BeginTime . ' tarihli fazla çalışma ' . $userEmployee->UsageName . '' . $userEmployee->LastName . ' adlı yönetici tarafından oluşturuldu.', '', '', '', '', '');
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

            $mailData = ['employee' => $employee, 'assignedEmployee' => $assignedEmployee, 'assignedEmployeesManager' => $assignedEmployeesManager,
                'usingCar' => $usingCar, 'overtime' => $overtimeRecord];
            $mailTable = view('mails.overtime', $mailData);

            Asay::sendMail($assignedEmployee->JobEmail, "", "Fazla Çalışma Onayınızı Bekliyor", $mailTable, "Fazla Çalışma Onayınızı Bekliyor");

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
        $dirtyFieldsArray = [];
        foreach ($dirtyFields as $field => $newdata) {
            $olddata = $overtimeRecord->getOriginal($field);
            if ($olddata != $newdata) {
                $dataObject = new \stdClass();
                $dataObject->changedFieldName = self::columnNameToTurkish($field);
                $dataObject->oldData = $olddata;
                $dataObject->newData = $newdata;
                array_push($dirtyFieldsArray, $dataObject);
            }
        }

        $overtimeRecord->StatusID = 2;

        $usingCar = $overtimeRecord->UsingCar == 0 ? 'Hayır' : 'Evet';

        $employee = EmployeeModel::find($overtimeRequest->Employee);
        $assignedEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
        $reason = $overtimeRequest->ProcessReason == "" || $overtimeRequest->ProcessReason != null ? $overtimeRequest->ProcessReason : "Açıklama Yapılmamış";
        $assignedEmployeesManager = EmployeeModel::find($overtimeRecord->CreatedBy);

        $mailData = ['employee' => $employee, 'assignedEmployee' => $assignedEmployee, 'assignedEmployeesManager' => $assignedEmployeesManager,
            'usingCar' => $usingCar, 'reason' => $reason, 'dirtyFields' => $dirtyFieldsArray, 'overtime' => $overtimeRecord];
        $mailTable = view('mails.overtime', $mailData);

        Asay::sendMail($assignedEmployeesManager->JobEmail, "", "Fazla çalışma için düzenleme talep edildi.", $mailTable
            , "Fazla Çalışma İçin Düzenleme Talep Edildi");


        if ($overtimeRecord->save())
            return ['status' => true, 'message' => 'İşlem Başarılı'];
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

        $mailData = ['employee' => $employee, 'assignedEmployee' => $assignedEmployee, 'assignedEmployeesManager' => $assignedEmployeesManager,
            'usingCar' => $usingCar, 'reason' => $reason, 'overtime' => $overtimeRecord];
        $mailTable = view('mails.overtime', $mailData);

        Asay::sendMail($assignedEmployeesManager->JobEmail, "", "Fazla çalışma reddedildi", $mailTable, "Fazla Çalışma Reddedildi");

        if ($overtimeRecord->save()) {
            $userEmployee = EmployeeModel::find($overtimeRequest->Employee);
            $logStatus = LogsModel::setLog($overtimeRequest->Employee, $overtimeRecord->id, 3, 26, '', '', $overtimeRecord->BeginDate . ' ' . $overtimeRecord->BeginTime . ' tarihli fazla çalışma ' . $userEmployee->UsageName . '' . $userEmployee->LastName . ' adlı çalışan tarafından reddedildi.', '', '', '', '', '');
            return ['status' => true, 'message' => 'İşlem Başarılı'];
        } else
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
        $assignedEmployeesManager = EmployeeModel::find($overtimeRecord->CreatedBy);
        $isgPositions = EmployeePositionModel::where(['Active' => 2, 'RegionID' => $assignedEmployeePosition->RegionID])->get();
        $mailToArray = [];
        $isgGroupIDs = [];

        foreach ($isgPositions as $isgPosition) {
            $userIsg = EmployeeModel::where(['Id' => $isgPosition->EmployeeID])->first();
            $hasGroup = EmployeeHasGroupModel::where(['EmployeeID' => $isgPosition->Id, 'group_id' => 24, 'active' => 1])->first();
            if ($hasGroup) {
                array_push($mailToArray, $isgPosition->JobEmail);
            }

        }
        $usingCar = $overtimeRecord->UsingCar == 0 ? 'Hayır' : 'Evet';

        $mailData = ['employee' => $employee, 'assignedEmployee' => $assignedEmployee, 'assignedEmployeesManager' => $assignedEmployeesManager,
            'usingCar' => $usingCar, 'overtime' => $overtimeRecord];
        $mailTable = view('mails.overtime', $mailData);

        Asay::sendMail($mailToArray, "", "Fazla çalışma çalışan tarafından onaylandı.", $mailTable, "Fazla Çalışma Çalışan Tarafından Onaylandı");


        if ($overtimeRecord->save()) {
            $userEmployee = EmployeeModel::find($overtimeRequest->Employee);
            $logStatus = LogsModel::setLog($overtimeRequest->Employee, $overtimeRecord->id, 3, 24, '', '', $overtimeRecord->BeginDate . ' ' . $overtimeRecord->BeginTime . ' tarihli fazla çalışma ' . $userEmployee->UsageName . '' . $userEmployee->LastName . ' adlı çalışan tarafından onaylandı.', '', '', '', '', '');
            return ['status' => true, 'message' => 'İşlem Başarılı'];
        } else
            return ['status' => false, 'message' => 'Kayıt Sırasında Bir Hata Oluştu'];
    }

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
        $mailData = ['employee' => $employee, 'assignedEmployee' => $assignedEmployee, 'assignedEmployeesManager' => $assignedEmployeesManager,
            'usingCar' => $usingCar, 'reason' => $reason, 'overtime' => $overtimeRecord];
        $mailTable = view('mails.overtime', $mailData);
        Asay::sendMail($assignedEmployeesManager->JobEmail, "", $overtimeRecord->JobOrderNo . " iş emri kodlu fazla çalışma çalışan tarafından iptal edildi.", $mailTable, "Fazla Çalışma İptal Edildi");


        if ($overtimeRecord->save()) {
            $userEmployee = EmployeeModel::find($overtimeRequest->Employee);
            $logStatus = LogsModel::setLog($overtimeRequest->Employee, $overtimeRecord->id, 3, 25, '', '', $overtimeRecord->BeginDate . ' ' . $overtimeRecord->BeginTime . ' tarihli fazla çalışma ' . $userEmployee->UsageName . '' . $userEmployee->LastName . ' adlı çalışan tarafından iptal edildi.', '', '', '', '', '');
            return ['status' => true, 'message' => 'İşlem Başarılı'];
        } else
            return ['status' => false, 'message' => 'Kayıt Sırasında Bir Hata Oluştu'];

    }

    public static function overtimeCompleteRequestFromEmployee($overtimeRequest)
    {

        $overtimeRecord = OvertimeModel::where(['id' => $overtimeRequest->OvertimeId, 'Active' => 1])->first();
        $overtimeRecord->WorkNo = $overtimeRequest->WorkNo;
        $overtimeRecord->WorkBeginDate = $overtimeRequest->WorkBeginDate;
        $overtimeRecord->WorkBeginTime = $overtimeRequest->WorkBeginTime;
        $overtimeRecord->WorkEndTime = $overtimeRequest->WorkEndTime;

        $overtimeRecord->StatusID = 6;
        $result = $overtimeRecord->save();

        $usingCar = $overtimeRecord->UsingCar == 0 ? 'Hayır' : 'Evet';

        //TODO Loglama ve mail göndeirmi yapılacak
        $employee = EmployeeModel::find($overtimeRequest->Employee);
        $assignedEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
        $assignedEmployeesManager = EmployeeModel::find($overtimeRecord->CreatedBy);
        $mailData = ['employee' => $employee, 'assignedEmployee' => $assignedEmployee,
            'assignedEmployeesManager' => $assignedEmployeesManager, 'overtime' => $overtimeRecord,
            'usingCar' => $usingCar, 'extraFields' => true];

        if ($result && $overtimeRequest->hasFile('WorkingReport')) {
            $file = file_get_contents($overtimeRequest->WorkingReport->path());
            $guzzleParams = [

                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => $file,
                        'filename' => 'OvertimeDoc_' . $overtimeRecord->id . '.' . $overtimeRequest->WorkingReport->getClientOriginalExtension()
                    ],
                    [
                        'name' => 'moduleId',
                        'contents' => 'overtime'
                    ],
                    [
                        'name' => 'token',
                        'contents' => $overtimeRequest->token
                    ]

                ],
            ];

            $client = new \GuzzleHttp\Client();
            $res = $client->request("POST", 'http://portal.asay.com.tr/api/disk/addFile', $guzzleParams);
            $responseBody = json_decode($res->getBody());

            if ($responseBody->status == true) {
                $overtimeRecord->File = $responseBody->data;
                $overtimeRecord->save();
            }
        }

        if ($overtimeRecord->File) {
            $returnVal = self::getFileOfOvertime($overtimeRequest);
            Asay::sendMail($assignedEmployeesManager->JobEmail, "", "Fazla çalışma tamamlandı.", view("mails.overtime", $mailData), "Fazla Çalışma Tamamlandı", $returnVal->FilePath, $returnVal->FileName, $returnVal->MimeType);
        } else
            Asay::sendMail($assignedEmployeesManager->JobEmail, "", "Fazla çalışma tamamlandı.", view("mails.overtime", $mailData), "Fazla Çalışma Tamamlandı", "", "", "");


        if ($result) {
            $userEmployee = EmployeeModel::find($overtimeRequest->Employee);
            $logStatus = LogsModel::setLog($overtimeRequest->Employee, $overtimeRecord->id, 3, 27, '', '', $overtimeRecord->BeginDate . ' ' . $overtimeRecord->BeginTime . ' tarihli fazla çalışma ' . $userEmployee->UsageName . '' . $userEmployee->LastName . ' adlı çalışan tarafından tamamlandı.', '', '', '', '', '');
            return ['status' => true, 'message' => 'İşlem Başarılı'];
        } else
            return ['status' => false, 'message' => 'Kayıt Sırasında Bir Hata Oluştu'];

    }

    public static function overtimeCorrectionRequestFromManager($overtimeRequest)
    {

        $overtimeRecord = OvertimeModel::where(['id' => $overtimeRequest->OvertimeId, 'Active' => 1])->first();

        //$overtimeRecord->CreatedBy = $overtimeRequest['CreatedBy'];
        $overtimeRecord->AssignedID = $overtimeRequest->AssignedID;
        $overtimeRecord->KindID = $overtimeRequest->KindID;
        $overtimeRecord->BeginDate = $overtimeRequest->BeginDate;
        $overtimeRecord->BeginTime = $overtimeRequest->BeginTime . ":00";
        $overtimeRecord->WorkBeginDate = $overtimeRequest->WorkBeginDate;
        $overtimeRecord->WorkBeginTime = $overtimeRequest->WorkBeginTime;
        $overtimeRecord->WorkEndTime = $overtimeRequest->WorkEndTime;
        $overtimeRecord->WorkNo = $overtimeRequest->WorkNo;
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
        $dirtyFieldsArray = [];
        foreach ($dirtyFields as $field => $newdata) {
            $olddata = $overtimeRecord->getOriginal($field);
            if ($olddata != $newdata) {
                //TODO Loglama İşlemi burada yapılacak.
                $dataObject = new \stdClass();
                $dataObject->changedFieldName = self::columnNameToTurkish($field);
                $dataObject->oldData = $olddata;
                $dataObject->newData = $newdata;
                array_push($dirtyFieldsArray, $dataObject);
            }
        }

        $overtimeRecord->StatusID = 7;
        $overtimeRecord->ManagerID = $overtimeRecord->CreatedBy;

        $usingCar = $overtimeRecord->UsingCar == 0 ? 'Hayır' : 'Evet';

        $employee = EmployeeModel::find($overtimeRequest->Employee);
        $assignedEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
        $reason = $overtimeRequest->ProcessReason == "" || $overtimeRequest->ProcessReason != null ? $overtimeRequest->ProcessReason : "Açıklama Yapılmamış";
        $assignedEmployeesManager = EmployeeModel::find($overtimeRecord->ManagerID);

        $mailData = ['employee' => $employee, 'assignedEmployee' => $assignedEmployee, 'assignedEmployeesManager' => $assignedEmployeesManager,
            'usingCar' => $usingCar, 'reason' => $reason, 'dirtyFields' => $dirtyFieldsArray, 'overtime' => $overtimeRecord, 'extraFields' => true];
        $mailTable = view('mails.overtime', $mailData);

        Asay::sendMail($assignedEmployee->JobEmail, "", "Fazla Çalışma İçin Düzenleme Talep Edildi", $mailTable
            , "Fazla Çalışma İçin Düzenleme Talep Edildi");


        if ($overtimeRecord->save())
            return ['status' => true, 'message' => 'İşlem Başarılı'];
        else
            return ['status' => false, 'message' => 'Kayıt Sırasında Bir Hata Oluştu'];

    }

    public static function overtimeApproveRequestFromManager($overtimeRequest)
    {
        //Bu fazla mesaiyi oluşturan kişinin, atadığı kişinin yöneticisi mi olduğu yoksa birim sorumlusu mu bu tespit etmeliyiz.
        $employee = EmployeeModel::find($overtimeRequest->Employee);
        $overtimeRecord = OvertimeModel::where(['id' => $overtimeRequest->OvertimeId, 'Active' => 1])->first();
        $assignedEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
        $assignedEmployeePosition = EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $assignedEmployee->Id])->first();
        $assignedEmployeesManager = EmployeeModel::find(EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $overtimeRecord->AssignedID])->first()->ManagerID);


        if ($assignedEmployeePosition->ManagerID == $overtimeRecord->ManagerID) {
            $usingCar = $overtimeRecord->UsingCar == 0 ? 'Hayır' : 'Evet';

            $mailData = ['employee' => $employee, 'assignedEmployee' => $assignedEmployee, 'assignedEmployeesManager' => $assignedEmployeesManager,
                'usingCar' => $usingCar, 'overtime' => $overtimeRecord, 'extraFields' => true];
            $mailTable = view('mails.overtime', $mailData);

            Asay::sendMail($assignedEmployee->JobEmail, "", "Fazla çalışma yöneticiniz tarafından onaylandı.", $mailTable, "Fazla Çalışma Onaylandı");
            $userEmployee = EmployeeModel::find($overtimeRequest->Employee);
            $logStatus = LogsModel::setLog($overtimeRequest->Employee, $overtimeRecord->id, 3, 28, '', '', $overtimeRecord->BeginDate . ' ' . $overtimeRecord->BeginTime . ' tarihli fazla çalışma ' . $userEmployee->UsageName . '' . $userEmployee->LastName . ' adlı yönetici tarafından onaylandı.', '', '', '', '', '');


            $hrSpecialist = ProcessesSettingsModel::where(['object_type' => 4, 'PropertyCode' => 'HRManager', 'RegionID' => $assignedEmployeePosition->RegionID])->first();
            $hrEmployee = EmployeeModel::find($hrSpecialist->PropertyValue);


            $mailData = ['employee' => $employee, 'assignedEmployee' => $assignedEmployee, 'assignedEmployeesManager' => $assignedEmployeesManager,
                'usingCar' => $usingCar, 'overtime' => $overtimeRecord, 'extraFields' => true];
            $mailTable = view('mails.overtime', $mailData);

            if ($overtimeRecord->File) {
                $returnVal = self::getFileOfOvertime($overtimeRequest);
                Asay::sendMail($hrEmployee->JobEmail, "", "Fazla çalışma çalışanın yöneticisi tarafından onaylandı", view("mails.overtime", $mailData), "Fazla Çalışma İçin Onayınız Bekleniyor", $returnVal->FilePath, $returnVal->FileName, $returnVal->MimeType);
            } else
                Asay::sendMail($hrEmployee->JobEmail, "", "Fazla çalışma çalışanın yöneticisi tarafından onaylandı", view("mails.overtime", $mailData), "Fazla Çalışma İçin Onayınız Bekleniyor", "", "", "");

            $overtimeRecord->ManagerID = $hrEmployee->Id;
            $overtimeRecord->StatusID = 8;
            $overtimeRecord->save();

        } else if ($assignedEmployeePosition->UnitSupervisorID == $overtimeRecord->ManagerID) {
            $usingCar = $overtimeRecord->UsingCar == 0 ? 'Hayır' : 'Evet';

            $mailData = ['employee' => $employee, 'assignedEmployee' => $assignedEmployee, 'assignedEmployeesManager' => $assignedEmployeesManager,
                'usingCar' => $usingCar, 'overtime' => $overtimeRecord, 'extraFields' => true, ''];
            $mailTable = view('mails.overtime', $mailData);

            //Yöneticiye mail
            if ($overtimeRecord->File) {
                $returnVal = self::getFileOfOvertime($overtimeRequest);
                Asay::sendMail($assignedEmployeesManager->JobEmail, "", "Fazla çalışma birim sorumlusu tarafından onaylandı", view("mails.overtime", $mailData), "Fazla Çalışma İçin Onay Bekleniyor", $returnVal->FilePath, $returnVal->FileName, $returnVal->MimeType);
            } else
                Asay::sendMail($assignedEmployeesManager->JobEmail, "", "Fazla çalışma birim sorumlusu tarafından onaylandı", view("mails.overtime", $mailData), "Fazla Çalışma İçin Onay Bekleniyor", "", "", "");

            //Çalışana Mail
            if ($overtimeRecord->File) {
                $returnVal = self::getFileOfOvertime($overtimeRequest);
                Asay::sendMail($assignedEmployee->JobEmail, "", "Fazla çalışma birim sorumlusu tarafından onaylandı", view("mails.overtime", $mailData), "Fazla Çalışma Onaylandı", $returnVal->FilePath, $returnVal->FileName, $returnVal->MimeType);
            } else
                Asay::sendMail($assignedEmployee->JobEmail, "", "Fazla çalışma birim sorumlusu tarafından onaylandı", view("mails.overtime", $mailData), "Fazla Çalışma Tamamlandı", "", "", "");

            $userEmployee = EmployeeModel::find($overtimeRequest->Employee);
            $logStatus = LogsModel::setLog($overtimeRequest->Employee, $overtimeRecord->id, 3, 28, '', '', $overtimeRecord->BeginDate . ' ' . $overtimeRecord->BeginTime . ' tarihli fazla çalışma ' . $userEmployee->UsageName . '' . $userEmployee->LastName . ' adlı birim sorumlusu tarafından onaylandı.', '', '', '', '', '');

            $overtimeRecord->ManagerID = $assignedEmployeesManager->Id;
            $overtimeRecord->save();

        }

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
        $overtimeRecord->BeginTime = $overtimeRequest->BeginTime . ':00';
        $overtimeRecord->WorkBeginDate = $overtimeRequest->WorkBeginDate;
        $overtimeRecord->WorkBeginTime = $overtimeRequest->WorkBeginTime;
        $overtimeRecord->WorkEndTime = $overtimeRequest->WorkEndTime;
        $overtimeRecord->WorkNo = $overtimeRequest->WorkNo;
        $overtimeRecord->ProjectID = $overtimeRequest->ProjectID;
        $overtimeRecord->JobOrderNo = $overtimeRequest->JobOrderNo;
        $overtimeRecord->CityID = $overtimeRequest->CityID;
        $overtimeRecord->FieldID = $overtimeRequest->FieldID;
        $overtimeRecord->FieldName = $overtimeRequest->FieldName;
        $overtimeRecord->EndTime = $overtimeRequest->EndTime . ':00';
        $overtimeRecord->UsingCar = $overtimeRequest->UsingCar;
        $overtimeRecord->PlateNumber = $overtimeRequest->PlateNumber;
        $overtimeRecord->Description = $overtimeRequest->Description;

        $dirtyFields = $overtimeRecord->getDirty();
        $dirtyFieldsArray = [];

        foreach ($dirtyFields as $field => $newdata) {
            $olddata = $overtimeRecord->getOriginal($field);
            if (self::columnNameToTurkish($field) == 'Onaylayacak Olan Yönetici')
                continue;
            if ($olddata != $newdata) {
                //TODO Loglama İşlemi burada yapılacak.
                $dataObject = new \stdClass();
                $dataObject->changedFieldName = self::columnNameToTurkish($field);
                $dataObject->oldData = $olddata;
                $dataObject->newData = $newdata;
                array_push($dirtyFieldsArray, $dataObject);
            }
        }

        $overtimeRecord->StatusID = 9;

        $usingCar = $overtimeRecord->UsingCar == 0 ? 'Hayır' : 'Evet';

        $employee = EmployeeModel::find($overtimeRequest->Employee);
        $assignedEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
        $reason = $overtimeRequest->ProcessReason == "" || $overtimeRequest->ProcessReason != null ? $overtimeRequest->ProcessReason : "Açıklama Yapılmamış";
        $assignedEmployeesManager = EmployeeModel::find($overtimeRecord->ManagerID);

        $mailData = ['employee' => $employee, 'assignedEmployee' => $assignedEmployee, 'assignedEmployeesManager' => $assignedEmployeesManager,
            'usingCar' => $usingCar, 'reason' => $reason, 'dirtyFields' => $dirtyFieldsArray, 'overtime' => $overtimeRecord, 'extraFields' => true];
        $mailTable = view('mails.overtime', $mailData);

        Asay::sendMail($assignedEmployee->JobEmail, "", "Fazla çalışma için İnsan Kaynakları birimi tarafından düzenleme talep edildi.", $mailTable
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
        $overtimeRecord->WorkBeginDate = $overtimeRequest->WorkBeginDate;
        $overtimeRecord->WorkBeginTime = $overtimeRequest->WorkBeginTime;
        $overtimeRecord->WorkEndTime = $overtimeRequest->WorkEndTime;
        $overtimeRecord->WorkNo = $overtimeRequest->WorkNo;
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

        $usingCar = $overtimeRecord->UsingCar == 0 ? 'Hayır' : 'Evet';

        $employee = EmployeeModel::find($overtimeRequest->Employee);
        $assignedEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
        $assignedEmployeesManager = EmployeeModel::find(EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $overtimeRecord->AssignedID])->first()->ManagerID);
        $userAccountOfEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
        $mailData = ['employee' => $employee, 'assignedEmployee' => $assignedEmployee, 'assignedEmployeesManager' => $assignedEmployeesManager,
            'usingCar' => $usingCar, 'overtime' => $overtimeRecord, 'extraFields' => true];
        $mailTable = view('mails.overtime', $mailData);

        if ($overtimeRecord->File) {
            $returnVal = self::getFileOfOvertime($overtimeRequest);
            Asay::sendMail($userAccountOfEmployee->JobEmail, "", "Fazla çalışma tamamlandı.", $mailTable, "Fazla Çalışma Tamamlandı", $returnVal->FilePath, $returnVal->FileName, $returnVal->MimeType);
        } else
            Asay::sendMail($userAccountOfEmployee->JobEmail, "", "Fazla çalışma tamamlandı.", $mailTable, "Fazla Çalışma Tamamlandı", "", "", "");


        if ($overtimeRecord->save()) {
            $userEmployee = EmployeeModel::find($overtimeRequest->Employee);
            $logStatus = LogsModel::setLog($overtimeRequest->Employee, $overtimeRecord->id, 3, 29, '', '', $overtimeRecord->BeginDate . ' ' . $overtimeRecord->BeginTime . ' tarihli fazla çalışma ' . $userEmployee->UsageName . '' . $userEmployee->LastName . ' adlı yönetici tarafından onaylandı.', '', '', '', '', '');

            //Fazla Çalışma tüm onay süreçlerinden geçerse kişi her saat başına yarım saat dinlenme izni kullanmak için hak kazanır. Bu izin YILDA 105 SAAT olarak sınırlandırılmıştır.
            self::addRestPermitToEmployee($overtimeRequest);

            return ['status' => true, 'message' => 'İşlem Başarılı'];
        } else
            return ['status' => false, 'message' => 'Kayıt Sırasında Bir Hata Oluştu'];

    }

    public static function getFileOfOvertime($overtimeRequest)
    {
        $overtimeRecord = OvertimeModel::where(['id' => $overtimeRequest->OvertimeId, 'Active' => 1])->first();

        if ($overtimeRecord->File) {
            $guzzleParams = [
                'query' => [
                    'token' => $overtimeRequest->token,
                    'fileId' => $overtimeRecord->File
                ],
            ];

            $client = new \GuzzleHttp\Client();
            $res = $client->request("GET", 'http://portal.asay.com.tr/api/disk/getFile', $guzzleParams);
            $responseBody = json_decode($res->getBody());

            if ($responseBody->status == true) {
                $data = new \stdClass();
                $filePath = Storage::disk("connect")->path($responseBody->file->subdir . '/' . $responseBody->file->filename);;
                $fileName = $responseBody->file->original_name;
                $mimeType = $responseBody->file->content_type;
                $data->FilePath = $filePath;
                $data->FileName = $fileName;
                $data->MimeType = $mimeType;
                return $data;
            } else
                return false;
        }
    }

    public static function addRestPermitToEmployee($request)
    {
        $workBeginDate = Carbon::createFromFormat("Y-m-d", $request->BeginDate);
        $workBeginTime = Carbon::createFromFormat("H:i", $request->WorkBeginTime);
        $workEndTime = Carbon::createFromFormat("H:i", $request->WorkEndTime);

        $workTotalTimeMinute = abs((int)($workEndTime->hour - $workBeginTime->hour)) * 60; //Dakikaya Çevrildi

        $earnedRestTime = ($workTotalTimeMinute / 2); //NetSaat hesabı

        $earnedRestMinute = 0;
        $earnedRestHour = 0;
        if ($earnedRestTime % 60 != 0) {
            $earnedRestMinute = abs($earnedRestTime - 60);
        }
        $earnedRestHour = (int)($workTotalTimeMinute / 60 + ($workTotalTimeMinute / 2) / 60);

        $overtimeRestHourLimit = 105;
        $overtimeRests = OvertimeRestModel::selectRaw(" SUM(Hour) as TotalHour, SUM(Minute) as TotalMinute")->where(['EmployeeID' => $request->AssignedID, 'Active' => 1])->whereYear('Date', "=", $workBeginDate->year)->first();

        if ($earnedRestHour + $overtimeRests->TotalHour > $overtimeRestHourLimit)
            return ['status' => false, 'message' => 'Yıllık Dinlenme İzni Limiti Dolmuştur'];
        if ($earnedRestHour + $overtimeRests->TotalHour == $overtimeRestHourLimit || $earnedRestMinute > 0)
            return ['status' => false, 'message' => 'Yıllık Dinlenme İzni Limiti Dolmuştur'];
        if ($earnedRestMinute + $overtimeRests->TotalMinute > 60) {
            if ($earnedRestHour + ($earnedRestMinute + $overtimeRests->TotalMinute / 60) > $overtimeRestHourLimit) {
                return ['status' => false, 'message' => 'Yıllık Dinlenme İzni Limiti Dolmuştur'];
            }
        }

        $newOvertimeRest = new OvertimeRestModel();
        $newOvertimeRest->OvertimeID = $request->OvertimeId;
        $newOvertimeRest->EmployeeID = $request->AssignedID;
        $newOvertimeRest->Date = $request->BeginDate;
        $newOvertimeRest->Hour = $earnedRestHour;
        $newOvertimeRest->Minute = $earnedRestMinute;

        if ($newOvertimeRest->save())
            return ['status' => true, 'message' => 'İşlem Başarılı', 'earnedTimeHour' => $earnedRestHour, 'earnedTimeMinute' => $earnedRestMinute];

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
