<?php

namespace App\Model;

use App\Library\Asay;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class OvertimeModel extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'Overtime';

    protected $appends = [
        'AssignedEmployee',
        'City',
        'CreatedFrom',
        'ApproveWho',
        //'Field',
        'Kind',
        'Project',
        'Status',
        'ObjectFile',
        'CreatedByEmployee'
    ];
    protected $guarded = [];


    public static function saveOvertimePermissions($request){

        if ($request->PermissionTypeID == 3)
            $permissionInstance = OvertimePermissionModel::where(["PermissionTypeID" => $request->PermissionTypeID, "YearControl" => $request->YearControl])->first();
        else
            $permissionInstance = OvertimePermissionModel::where("PermissionTypeID",$request->PermissionTypeID)->first();
        if (!$permissionInstance)
        {
            $permissionInstance = new OvertimePermissionModel();
            $permissionInstance->PermissionTypeID = $request->PermissionTypeID;
        }
        if ($request->PermissionTypeID == 3)
        {
            $permissionInstance->YearControl = $request->YearControl;
        }
        $permissionInstance->EmployeeIDs = implode(",",$request->EmployeeIDs);


        $result = $permissionInstance->save();

        return $result;

    }

    public static function getOvertimeEmployeePermissionList(){

        $list['weekendPublicHolidayOnly'] = OvertimePermissionModel::select("EmployeeIDs")->where("PermissionTypeID",1)->first()->EmployeeIDs;
        $list['blacklist'] = OvertimePermissionModel::select("EmployeeIDs")->where("PermissionTypeID",2)->first()->EmployeeIDs;

        $list['weekendPublicHolidayOnly'] = array_map('intval', explode(",",$list['weekendPublicHolidayOnly']));
        $list['blacklist'] = array_map('intval', explode(",",$list['blacklist']));


       return $list;

    }

    public static function dateCheck($request){

        if ($request->OvertimeId != null)
        {
            $overtimeRecord = OvertimeModel::find($request->OvertimeId);
            if (in_array($overtimeRecord->StatusID,[0,1,2,3,4,5]))
            {

                $permittedMonthCounts = OvertimePermissionModel::where("PermissionTypeID",3)
                    ->whereRaw('FIND_IN_SET(?,EmployeeIDs)', [$request->AssignedID])->get();
                if ($permittedMonthCounts){
                    foreach ($permittedMonthCounts as $permittedMonthCount){
                        $permittedYear = date("Y",strtotime($permittedMonthCount->YearControl));
                        $permittedMonth = date("m",strtotime($permittedMonthCount->YearControl));
                        $overtimeYear = date("Y",strtotime($request->BeginDate));
                        $overtimeMonth = date("m",strtotime($request->BeginDate));
                        if ($permittedYear == $overtimeYear && $permittedMonth == $overtimeMonth)
                            return true;
                    }
                }

                $today = date("d");
                $month      = date("m",strtotime($request->BeginDate));
                $todayMonth = date("m");

                if($today == '01' || $today == '02')
                {
                    $previousMonth = date('m',strtotime('first day of last month'));
                    //Ay??n 1 i veya 2 si ise gelen istekteki date'in ay?? ile bir ??nceki ay aras??ndaki farka bak??p,
                    // gelen iste??in o ay m?? yoksa bir ??nceki ay m?? oldu??una bak??yorum
                    if(($month - $previousMonth) == 1 || ($month - $previousMonth) == 0)
                        return true;
                    else
                        return false;
                }

                else if ($month != $todayMonth)
                    return false;
                else
                    return true;

            }
            else if (in_array($overtimeRecord->StatusID,[6,7]))
            {

                $permittedMonthCounts = OvertimePermissionModel::where("PermissionTypeID",3)
                    ->whereRaw('FIND_IN_SET(?,EmployeeIDs)', [$request->AssignedID])->get();
                if ($permittedMonthCounts){
                    foreach ($permittedMonthCounts as $permittedMonthCount){
                        $permittedYear = date("Y",strtotime($permittedMonthCount->YearControl));
                        $permittedMonth = date("m",strtotime($permittedMonthCount->YearControl));
                        $overtimeYear = date("Y",strtotime($request->WorkBeginDate));
                        $overtimeMonth = date("m",strtotime($request->WorkBeginDate));
                        if ($permittedYear == $overtimeYear && $permittedMonth == $overtimeMonth)
                            return true;
                    }
                }


                $today = date("d");
                $month      = date("m",strtotime($request->WorkBeginDate));
                $todayMonth = date("m");

                if($today == '01' || $today == '02')
                {
                    $previousMonth = date('m',strtotime('first day of last month'));
                    if(($month - $previousMonth) == 1 || ($month - $previousMonth) == 0)
                        return true;
                    else
                        return false;
                }

                else if ($month != $todayMonth)
                    return false;
                else
                    return true;
            }
            else
                return true;

        }
        else
        {

            $permittedMonthCounts = OvertimePermissionModel::where("PermissionTypeID",3)
                ->whereRaw('FIND_IN_SET(?,EmployeeIDs)', [$request->AssignedID])->get();
            if ($permittedMonthCounts){
                foreach ($permittedMonthCounts as $permittedMonthCount){
                    $permittedYear = date("Y",strtotime($permittedMonthCount->YearControl));
                    $permittedMonth = date("m",strtotime($permittedMonthCount->YearControl));
                    $overtimeYear = date("Y",strtotime($request->BeginDate));
                    $overtimeMonth = date("m",strtotime($request->BeginDate));
                    if ($permittedYear == $overtimeYear && $permittedMonth == $overtimeMonth)
                        return true;
                }
            }

            $today = date("d");
            $month      = date("m",strtotime($request->BeginDate));
            $todayMonth = date("m");

            if($today == '01' || $today == '02')
            {
                $previousMonth = date('m',strtotime('first day of last month'));
                if(($month - $previousMonth) == 1 || ($month - $previousMonth) == 0)
                    return true;
                else
                    return false;
            }

            else if ($month != $todayMonth)
                return false;
            else
                return true;
        }

    }

    public static function columnNameToTurkish($columnName)
    {
        switch ($columnName) {
            case 'CreatedBy':
                return 'Olu??turan Y??netici';
            case 'ManagerID':
                return 'Onaylayacak Olan Y??netici';
            case 'AssignedID':
                return 'Atanan Ki??inin ID Nosu';
            case 'KindID':
                return 'Fazla ??al????ma T??r??';
            case 'BeginDate':
                return 'Ba??lang???? Tarihi';
            case 'BeginTime':
                return 'Ba??lang???? Zaman??';
            case 'ProjectID':
                return 'Project ID Nosu';
            case 'JobOrderNo':
                return '???? Emri No';
            case 'CityID':
                return '??ehir ID Nosu';
            case 'FieldID':
                return '??al????ma Saha ID Nosu';
            case 'FieldName':
                return '??al????ma Saha Ad??';
            case 'EndTime':
                return '??al????ma Biti?? Zaman??';
            case 'UsingCar':
                return 'Araba Kullan??p Kullan??lmayaca???? (1:Evet, 0:Hay??r)';
            case 'PlateNumber':
                return 'Ara?? Plaka Numaras??';
            case 'Description':
                return 'A????klama';
            case 'WorkBeginDate':
                return 'Ger??ekle??en Fazla ??al????ma Tarihi';
            case 'WorkBeginTime':
                return '??al????ma Ba??lang???? Saati';
            case 'WorkEndTime':
                return '??al????ma Biti?? Saati';
            case 'WorkNo':
                return '??al????ma No';


        }
    }

    public static function overtimeRemainingLimits($request)
    {
        $beginDate = Carbon::createFromFormat("Y-m-d", $request->WorkBeginDate != null ? $request->WorkBeginDate : $request->BeginDate);

        $publicHolidays = PublicHolidayModel::whereDate('end_date',">",$beginDate)
            ->whereRaw('? >= DATE(start_date)', [$beginDate])->count();

        if ($publicHolidays > 0)
        {
            $data[0] = 'Limit Yok';
            $data[1] = 'Limit Yok';

            $data[2] = 'Limit Yok';
            $data[3] = 'Limit Yok';

            $data[4] = 'Limit Yok';
            $data[5] = 'Limit Yok';
            $data[6] = true;
            return $data;
        }
        $dailyTimes = [];
        $dailyTimesQ1 = OvertimeModel::selectRaw(' TIMEDIFF(EndTime,BeginTime) as timediff, BeginDate, WorkBeginDate')->where(['Active' => 1, 'AssignedID' => $request->AssignedID])->whereIn("StatusID",[0,1,2,3,4,5])->where(function ($query) use ($beginDate) {
            $query->whereBetween('BeginDate', [$beginDate->year . '-' . $beginDate->month . '-' . $beginDate->day
                , $beginDate->year . '-' . $beginDate->month . '-' . $beginDate->day]);
        }); // G??nl??k tan??mlanm???? saatleri ??ekiyoruz.
        $dailyTimesQ2 = OvertimeModel::selectRaw(' TIMEDIFF(EndTime,BeginTime) as timediff, BeginDate, WorkBeginDate')->where(['Active' => 1, 'AssignedID' => $request->AssignedID])->whereIn("StatusID",[6,7,8,9,10])->where(function ($query) use ($beginDate) {
            $query->whereBetween('WorkBeginDate', [$beginDate->year . '-' . $beginDate->month . '-' . $beginDate->day
                , $beginDate->year . '-' . $beginDate->month . '-' . $beginDate->day]);
        }); // G??nl??k tan??mlanm???? saatleri ??ekiyoruz.

        foreach ($dailyTimesQ1->get() as $item)
            array_push($dailyTimes,$item);
        foreach ($dailyTimesQ2->get() as $item)
            array_push($dailyTimes,$item);

        foreach ($dailyTimes as $key => $dailyTime)
        {
            $date = $dailyTime->WorkBeginDate != null ? $dailyTime->WorkBeginDate : $dailyTime->BeginDate;
            $publicHolidays = PublicHolidayModel::whereDate('end_date',">",$date)
                ->whereRaw('? >= DATE(start_date)', [$date])->count();
            if ($publicHolidays > 0)
            {
                unset($dailyTimes[$key]);
            }
        }
        $monthlyTimes=[];
        $monthlyTimesQ1 = OvertimeModel::selectRaw(' TIMEDIFF(EndTime,BeginTime) as timediff, BeginDate, WorkBeginDate')->where(['Active' => 1, 'AssignedID' => $request->AssignedID])->whereIn("StatusID",[0,1,2,3,4,5])->where(function ($query) use ($beginDate) {

            $query->whereBetween('BeginDate', [$beginDate->startOfMonth()->year . '-' . $beginDate->startOfMonth()->month . '-' . $beginDate->startOfMonth()->day
                , $beginDate->endOfMonth()->year . '-' . $beginDate->endOfMonth()->month . '-' . $beginDate->endOfMonth()->day]);
        });

        $monthlyTimesQ2 = OvertimeModel::selectRaw(' TIMEDIFF(EndTime,BeginTime) as timediff, BeginDate, WorkBeginDate')->where(['Active' => 1, 'AssignedID' => $request->AssignedID])->whereIn("StatusID",[6,7,8,9,10])->where(function ($query) use ($beginDate) {

            $query->whereBetween('WorkBeginDate', [$beginDate->startOfMonth()->year . '-' . $beginDate->startOfMonth()->month . '-' . $beginDate->startOfMonth()->day
                , $beginDate->endOfMonth()->year . '-' . $beginDate->endOfMonth()->month . '-' . $beginDate->endOfMonth()->day]);
        });

        foreach ($monthlyTimesQ1->get() as $item)
            array_push($monthlyTimes,$item);
        foreach ($monthlyTimesQ2->get() as $item)
            array_push($monthlyTimes,$item);

        foreach ($monthlyTimes as $key => $monthlyTime)
        {
            $date = $monthlyTime->WorkBeginDate != null ? $monthlyTime->WorkBeginDate : $monthlyTime->BeginDate;
            $publicHolidays = PublicHolidayModel::whereDate('end_date',">",$date)
                ->whereRaw('? >= DATE(start_date)', [$date])->count();
            if ($publicHolidays > 0)
            {
                unset($monthlyTimes[$key]);
            }
        }

        $yearlyTimes = [];
        $yearlyTimesQ1 = OvertimeModel::selectRaw(' TIMEDIFF(EndTime,BeginTime) as timediff, BeginDate, WorkBeginDate')->where(['Active' => 1, 'AssignedID' => $request->AssignedID])->whereIn("StatusID",[0,1,2,3,4,5])->where(function ($query) use ($beginDate) {
            $query->whereBetween('BeginDate', [$beginDate->startOfYear()->year . '-' . $beginDate->startOfYear()->month . '-' . $beginDate->startOfYear()->day
                , $beginDate->endOfYear()->year . '-' . $beginDate->endOfYear()->month . '-' . $beginDate->endOfYear()->day]);
        });

        $yearlyTimesQ2 = OvertimeModel::selectRaw(' TIMEDIFF(EndTime,BeginTime) as timediff, BeginDate, WorkBeginDate')->where(['Active' => 1, 'AssignedID' => $request->AssignedID])->whereIn("StatusID",[6,7,8,9,10])->where(function ($query) use ($beginDate) {
            $query->whereBetween('BeginDate', [$beginDate->startOfYear()->year . '-' . $beginDate->startOfYear()->month . '-' . $beginDate->startOfYear()->day
                , $beginDate->endOfYear()->year . '-' . $beginDate->endOfYear()->month . '-' . $beginDate->endOfYear()->day]);
        });

        foreach ($yearlyTimesQ1->get() as $item)
            array_push($yearlyTimes,$item);
        foreach ($yearlyTimesQ2->get() as $item)
            array_push($yearlyTimes,$item);

        foreach ($yearlyTimes as $key => $yearlyTime)
        {
            $date = $yearlyTime->WorkBeginDate != null ? $yearlyTime->WorkBeginDate : $yearlyTime->BeginDate;
            $publicHolidays = PublicHolidayModel::whereDate('end_date',">",$date)
                ->whereRaw('? >= DATE(start_date)', [$date])->count();
            if ($publicHolidays > 0)
            {
                unset($yearlyTimes[$key]);
            }
        }


        $dailyMinutes = 0;
        $dailyHours = 0;
        $dailyMinutesLimit = 30;
        $dailyHoursLimit = 3;//3.5 Saat G??nl??k Limit

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
        $monthlyHoursLimit = 22;//22.5 Saat Ayl??k Limit

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
        $yearlyHoursLimit = 270;//270 Saat Y??ll??k Limit

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
        $data[6] = false;


        return $data;

    }

    public static function overtimeAtHoliday($request){

        $beginDate = Carbon::createFromFormat("Y-m-d", $request->BeginDate);
        //$beginTime = Carbon::createFromFormat("H:i", $request->BeginTime);
        //$endTime = Carbon::createFromFormat("H:i", $request->EndTime);

        $beginDate2 = isset($request->WorkBeginDate) && !is_null($request->BeginDate) ? Carbon::createFromFormat("Y-m-d", $request->WorkBeginDate) : null;
        //$beginTime2 = isset($request->WorkBeginTime) && !is_null($request->WorkBeginTime) ? Carbon::createFromFormat("H:i", $request->WorkBeginTime) : null;
        //$endTime2 = isset($request->WorkEndTime) && !is_null($request->WorkEndTime) ? Carbon::createFromFormat("H:i", $request->WorkEndTime) : null;

        if (!is_null($beginDate2))
        {
            $publicHolidayRecCount = PublicHolidayModel::whereDate('end_date',">",$beginDate2->year . '-' . $beginDate2->month . '-' . $beginDate2->day)
                ->whereRaw('? >= DATE(start_date)', [$beginDate2->year . '-' . $beginDate2->month . '-' . $beginDate2->day])
                ->count();
            if ($publicHolidayRecCount > 0)
                return ['status' => true, 'message' => 'Resmi tatillerde limit kontrol?? yapm??yoruz'];
        }
        else
        {
            $publicHolidayRecCount = PublicHolidayModel::whereDate('end_date',">",$beginDate->year . '-' . $beginDate->month . '-' . $beginDate->day)
                ->whereRaw('? >= DATE(start_date)', [$beginDate->year . '-' . $beginDate->month . '-' . $beginDate->day])
                ->count();
            if ($publicHolidayRecCount > 0)
                return ['status' => true, 'message' => 'Resmi tatillerde limit kontrol?? yapm??yoruz'];
        }

        return ['status' => false, 'message' => 'Resmi Tatil De??il'];
    }

    public static function overtimeLimitCheck($request, $neglectRecord = null)
    {

        $beginDate = Carbon::createFromFormat("Y-m-d", $request->BeginDate);
        $beginTime = Carbon::createFromFormat("H:i", $request->BeginTime);
        $endTime = Carbon::createFromFormat("H:i", $request->EndTime);

        $beginDate2 = isset($request->WorkBeginDate) && !is_null($request->BeginDate) ? Carbon::createFromFormat("Y-m-d", $request->WorkBeginDate) : null;
        $beginTime2 = isset($request->WorkBeginTime) && !is_null($request->WorkBeginTime) ? Carbon::createFromFormat("H:i", $request->WorkBeginTime) : null;
        $endTime2 = isset($request->WorkEndTime) && !is_null($request->WorkEndTime) ? Carbon::createFromFormat("H:i", $request->WorkEndTime) : null;

        $publicHolidays     = PublicHolidayModel::whereYear("start_date","=",$beginDate->year)->get();
        $publicHolidays2    = !is_null($beginDate2) ? PublicHolidayModel::whereYear("start_date","=",$beginDate2->year)->get() : PublicHolidayModel::whereYear("start_date","=",$beginDate->year)->get();

        $publicHolidaysArray = [];
        $publicHolidaysArray2 = [];

        foreach ($publicHolidays as $publicHoliday)
        {
            $tempStartDate = Carbon::createFromFormat("Y-m-d", explode(" ",$publicHoliday->start_date)[0]);
            $tempEndDate = Carbon::createFromFormat("Y-m-d", explode(" ",$publicHoliday->end_date)[0]);
            $dayCount = $tempEndDate->diffInDays($tempStartDate);

            if ($dayCount > 1)
            {
                for ($i = 0; $i < $dayCount; $i++)
                {
                    if ($i==0)
                        $tempStartDate->addDays(0);
                    else
                        $tempStartDate->addDays(1);
                    $tempDate = Carbon::createFromFormat("Y-m-d", explode(" ",$tempStartDate)[0]);
                    array_push($publicHolidaysArray,$tempDate->year . '-' . $tempDate->month . '-' . $tempDate->day);
                }
            }
            else
            {
                $tempDate = Carbon::createFromFormat("Y-m-d", explode(" ",$publicHoliday->start_date)[0]);
                array_push($publicHolidaysArray,$tempDate->year . '-' . $tempStartDate->month . '-' . $tempStartDate->day);
            }


        }

        foreach ($publicHolidays2 as $publicHoliday)
        {

            $tempStartDate = Carbon::createFromFormat("Y-m-d", explode(" ",$publicHoliday->start_date)[0]);
            $tempEndDate = Carbon::createFromFormat("Y-m-d", explode(" ",$publicHoliday->end_date)[0]);
            $dayCount = $tempEndDate->diffInDays($tempStartDate);

            if ($dayCount > 1)
            {
                for ($i = 0; $i < $dayCount; $i++)
                {
                    if ($i==0)
                        $tempStartDate->addDays(0);
                    else
                        $tempStartDate->addDays(1);
                    $tempDate = Carbon::createFromFormat("Y-m-d", explode(" ",$tempStartDate)[0]);
                    array_push($publicHolidaysArray2,$tempDate->year . '-' . $tempDate->month . '-' . $tempDate->day);
                }
            }
            else
            {
                $tempDate = Carbon::createFromFormat("Y-m-d", explode(" ",$publicHoliday->start_date)[0]);
                array_push($publicHolidaysArray2,$tempDate->year . '-' . $tempDate->month . '-' . $tempDate->day);
            }

        }


        $dailyTimesQ = OvertimeModel::selectRaw('id,TIMEDIFF(EndTime,BeginTime) as timediff,TIMEDIFF(WorkEndTime,WorkBeginTime) as timediff2,StatusID')->whereIn("StatusID", [0, 1, 2, 4])->where(['Active' => 1, 'AssignedID' => $request->AssignedID])->where(function ($query) use ($beginDate, $beginDate2,$publicHolidaysArray) {
            if ($beginDate2)
            {
                $query->whereBetween('BeginDate', [$beginDate2->year . '-' . $beginDate2->month . '-' . $beginDate2->day
                    , $beginDate2->year . '-' . $beginDate2->month . '-' . $beginDate2->day]);
                $query->whereNotIn('BeginDate', $publicHolidaysArray);
            }

            else
            {
                $query->whereBetween('BeginDate', [$beginDate->year . '-' . $beginDate->month . '-' . $beginDate->day
                    , $beginDate->year . '-' . $beginDate->month . '-' . $beginDate->day]);
                $query->whereNotIn('BeginDate', $publicHolidaysArray);
            }

            $query->whereNotIn('StatusID', [3, 5]);
        }); // G??nl??k tan??mlanm???? saatleri ??ekiyoruz.

        $dailyTimesQ2 = OvertimeModel::selectRaw('id,TIMEDIFF(EndTime,BeginTime) as timediff,TIMEDIFF(WorkEndTime,WorkBeginTime) as timediff2,StatusID')->whereIn("StatusID", [6, 7, 8, 9, 10])->where(['Active' => 1, 'AssignedID' => $request->AssignedID])->where(function ($query) use ($beginDate, $beginDate2,$publicHolidaysArray2) {
            $query->whereNotIn('StatusID', [3, 5]);

            if ($beginDate2)
            {
                $query->whereBetween('WorkBeginDate', [$beginDate2->year . '-' . $beginDate2->month . '-' . $beginDate2->day
                    , $beginDate2->year . '-' . $beginDate2->month . '-' . $beginDate2->day]);
                $query->whereNotIn('WorkBeginDate', $publicHolidaysArray2);
            }

            else
            {
                $query->whereBetween('WorkBeginDate', [$beginDate->year . '-' . $beginDate->month . '-' . $beginDate->day
                    , $beginDate->year . '-' . $beginDate->month . '-' . $beginDate->day]);
                $query->whereNotIn('WorkBeginDate', $publicHolidaysArray2);
            }


        }); // G??nl??k tan??mlanm???? saatleri ??ekiyoruz.

        $dailyTimes = array();
        foreach ($dailyTimesQ->get() as $item)
            array_push($dailyTimes, $item);
        foreach ($dailyTimesQ2->get() as $item)
            array_push($dailyTimes, $item);

        $dailyMinutes = 0;
        $dailyHours = 0;
        $dailyMinutesLimit = 30;
        $dailyHoursLimit = 3;//3.5 Saat G??nl??k Limit
        foreach ($dailyTimes as $dailyTime) {
            if (!is_null($neglectRecord))
                if ($dailyTime->id == $neglectRecord->id)
                    continue;

            if ($dailyTime->StatusID == 1 || $dailyTime->StatusID == 2 || $dailyTime->StatusID == 0 || $dailyTime->StatusID == 4)
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


            $timeDiff2 = $endTime2->diff($beginTime2)->format('%H:%I:%S');
            $timeDiff2_Hour = explode(":",$timeDiff2)[0];
            $timeDiff2_Minute = explode(":",$timeDiff2)[1];

            if (!is_null($neglectRecord) && in_array($neglectRecord->StatusID,[6, 7, 8, 9, 10]))
            {
                $timeDiff1 = $timeDiff2;
                $timeDiff1_Hour = explode(":",$timeDiff1)[0];
                $timeDiff1_Minute = explode(":",$timeDiff1)[1];
            }
            else
            {
                $timeDiff1 = $endTime->diff($beginTime)->format('%H:%I:%S');
                $timeDiff1_Hour = explode(":",$timeDiff1)[0];
                $timeDiff1_Minute = explode(":",$timeDiff1)[1];
            }




            $totalMinutes1 = (($timeDiff1_Hour)*60) + ($dailyHours *60) + abs($timeDiff1_Minute) + $dailyMinutes;
            $totalMinutes2 = (($timeDiff2_Hour)*60) + ($dailyHours *60) + abs($timeDiff2_Minute) + $dailyMinutes;
            $dailyLimitMinute = ($dailyHoursLimit*60) + $dailyMinutesLimit;
            if ($totalMinutes1 > $dailyLimitMinute || $totalMinutes2 > $dailyLimitMinute)
                return ['status' => false, 'message' => 'Girilen fazla ??al????ma s??resi, g??nl??k yasal fazla ??al????ma limitini a????yor.'];

        }
        else {

            $timeDiff1 = $endTime->diff($beginTime)->format('%H:%I:%S');
            $timeDiff1_Hour = explode(":",$timeDiff1)[0];
            $timeDiff1_Minute = explode(":",$timeDiff1)[1];

            $totalMinutes1 = (($timeDiff1_Hour)*60) + ($dailyHours *60) + abs($timeDiff1_Minute) + $dailyMinutes;
            $dailyLimitMinute = ($dailyHoursLimit*60) + $dailyMinutesLimit;
            if ($totalMinutes1 > $dailyLimitMinute )
                return ['status' => false, 'message' => 'Girilen fazla ??al????ma s??resi, g??nl??k yasal fazla ??al????ma limitini a????yor.'];
        }

        $monthlyTimesQ = OvertimeModel::selectRaw('id,TIMEDIFF(EndTime,BeginTime) as timediff,TIMEDIFF(WorkEndTime,WorkBeginTime) as timediff2,StatusID')->whereIn("StatusID", [0, 1, 2, 4])->where(['Active' => 1, 'AssignedID' => $request->AssignedID])->where(function ($query) use ($beginDate, $beginDate2,$publicHolidaysArray) {
            if ($beginDate2)
            {
                $query->whereBetween('BeginDate', [$beginDate2->year . '-' . $beginDate2->month . '-' . $beginDate2->startOfMonth()->day
                    , $beginDate2->year . '-' . $beginDate2->month . '-' . $beginDate2->endOfMonth()->day]);
                $query->whereNotIn('BeginDate', $publicHolidaysArray);
            }

            else
            {
                $query->whereBetween('BeginDate', [$beginDate->year . '-' . $beginDate->month . '-' . $beginDate->startOfMonth()->day
                    , $beginDate->year . '-' . $beginDate->month . '-' . $beginDate->endOfMonth()->day]);
                $query->whereNotIn('BeginDate', $publicHolidaysArray);
            }

            $query->whereNotIn('StatusID', [3, 5]);
        });

        $monthlyTimesQ2 = OvertimeModel::selectRaw('id,TIMEDIFF(EndTime,BeginTime) as timediff,TIMEDIFF(WorkEndTime,WorkBeginTime) as timediff2,StatusID')->whereIn("StatusID", [6, 7, 8, 9, 10])->where(['Active' => 1, 'AssignedID' => $request->AssignedID])->where(function ($query) use ($beginDate, $beginDate2,$publicHolidaysArray2) {
            $query->whereNotIn('StatusID', [3, 5]);
            if ($beginDate2)
            {
                $query->whereBetween('WorkBeginDate', [$beginDate2->year . '-' . $beginDate2->month . '-' . $beginDate2->startOfMonth()->day
                    , $beginDate2->year . '-' . $beginDate2->month . '-' . $beginDate2->endOfMonth()->day]);
                $query->whereNotIn('WorkBeginDate', $publicHolidaysArray2);
            }

            else
            {
                $query->whereBetween('WorkBeginDate', [$beginDate->year . '-' . $beginDate->month . '-' . $beginDate->startOfMonth()->day
                    , $beginDate->year . '-' . $beginDate->month . '-' . $beginDate->endOfMonth()->day]);
                $query->whereNotIn('WorkBeginDate', $publicHolidaysArray2);
            }


        });


        $monthlyTimes = array();
        foreach ($monthlyTimesQ->get() as $item)
            array_push($monthlyTimes, $item);
        foreach ($monthlyTimesQ2->get() as $item)
            array_push($monthlyTimes, $item);

        $monthlyMinutes = 0;
        $monthlyHours = 0;
        $monthlyMinutesLimit = 30;
        $monthlyHoursLimit = 22;//22.5 Saat Ayl??k Limit
        foreach ($monthlyTimes as $monthlyTime) {
            if (!is_null($neglectRecord))
                if ($monthlyTime->id == $neglectRecord->id)
                    continue;
            if ($monthlyTime->StatusID == 1 || $monthlyTime->StatusID == 2 || $monthlyTime->StatusID == 0 || $monthlyTime->StatusID == 4)
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


            $timeDiff2 = $endTime2->diff($beginTime2)->format('%H:%I:%S');
            $timeDiff2_Hour = explode(":",$timeDiff2)[0];
            $timeDiff2_Minute = explode(":",$timeDiff2)[1];

            if (!is_null($neglectRecord) && in_array($neglectRecord->StatusID,[6, 7, 8, 9, 10]))
            {
                $timeDiff1 = $timeDiff2;
                $timeDiff1_Hour = explode(":",$timeDiff1)[0];
                $timeDiff1_Minute = explode(":",$timeDiff1)[1];
            }
            else
            {
                $timeDiff1 = $endTime->diff($beginTime)->format('%H:%I:%S');
                $timeDiff1_Hour = explode(":",$timeDiff1)[0];
                $timeDiff1_Minute = explode(":",$timeDiff1)[1];
            }

            $totalMinutes1 = (($timeDiff1_Hour)*60) + ($monthlyHours *60) + $monthlyMinutes + abs($timeDiff1_Minute);
            $totalMinutes2 = (($timeDiff2_Hour)*60) + ($monthlyHours*60) + $monthlyMinutes + abs($timeDiff2_Minute);
            $monthlyLimitMinute = ($monthlyHoursLimit*60) + $monthlyMinutesLimit;
            if (($totalMinutes1 > $monthlyLimitMinute || $totalMinutes2 > $monthlyLimitMinute))
                return ['status' => false, 'message' => 'Girilen fazla ??al????ma s??resi, ayl??k yasal fazla ??al????ma limitini a????yor.'];
        } else {

            $timeDiff1 = $endTime->diff($beginTime)->format('%H:%I:%S');
            $timeDiff1_Hour = explode(":",$timeDiff1)[0];
            $timeDiff1_Minute = explode(":",$timeDiff1)[1];

            $totalMinutes1 = (($timeDiff1_Hour)*60) + ($monthlyHours *60) + abs($timeDiff1_Minute) +$monthlyMinutes;
            $monthlyLimitMinute = ($monthlyHoursLimit*60) + $monthlyMinutesLimit;
            if (($totalMinutes1 > $monthlyLimitMinute))
                return ['status' => false, 'message' => 'Girilen fazla ??al????ma s??resi, ayl??k yasal fazla ??al????ma limitini a????yor.'];
        }


        $yearlyTimesQ = OvertimeModel::selectRaw('id,TIMEDIFF(EndTime,BeginTime) as timediff,TIMEDIFF(WorkEndTime,WorkBeginTime) as timediff2,StatusID')->whereIn("StatusID", [0, 1, 2, 4])->where(['Active' => 1, 'AssignedID' => $request->AssignedID])->where(function ($query) use ($beginDate, $beginDate2,$publicHolidaysArray) {
            if ($beginDate2)
            {
                $query->whereBetween('BeginDate', [$beginDate2->startOfYear()->year . '-' . $beginDate2->startOfYear()->month . '-' . $beginDate2->startOfYear()->day
                    , $beginDate2->endOfYear()->year . '-' . $beginDate2->endOfYear()->month . '-' . $beginDate2->endOfYear()->day]);
                $query->whereNotIn('BeginDate', $publicHolidaysArray);
            }

            else
            {
                $query->whereBetween('BeginDate', [$beginDate->startOfYear()->year . '-' . $beginDate->startOfYear()->month . '-' . $beginDate->startOfYear()->day
                    , $beginDate->endOfYear()->year . '-' . $beginDate->endOfYear()->month . '-' . $beginDate->endOfYear()->day]);
                $query->whereNotIn('BeginDate', $publicHolidaysArray);
            }


        });

        $yearlyTimesQ2 = OvertimeModel::selectRaw('id,TIMEDIFF(EndTime,BeginTime) as timediff,TIMEDIFF(WorkEndTime,WorkBeginTime) as timediff2,StatusID')->whereIn("StatusID", [6, 7, 8, 9, 10])->where(['Active' => 1, 'AssignedID' => $request->AssignedID])->where(function ($query) use ($beginDate, $beginDate2,$publicHolidaysArray2) {

            if ($beginDate2)
            {
                $query->whereBetween('WorkBeginDate', [$beginDate2->startOfYear()->year . '-' . $beginDate2->startOfYear()->month . '-' . $beginDate2->startOfYear()->day
                    , $beginDate2->endOfYear()->year . '-' . $beginDate2->endOfYear()->month . '-' . $beginDate2->endOfYear()->day]);
                $query->whereNotIn('WorkBeginDate', $publicHolidaysArray2);
            }

            else
            {
                $query->whereBetween('WorkBeginDate', [$beginDate->startOfYear()->year . '-' . $beginDate->startOfYear()->month . '-' . $beginDate->startOfYear()->day
                    , $beginDate->endOfYear()->year . '-' . $beginDate->endOfYear()->month . '-' . $beginDate->endOfYear()->day]);
                $query->whereNotIn('WorkBeginDate', $publicHolidaysArray2);
            }


            $query->whereNotIn('StatusID', [3, 5]);
        });

        $yearlyTimes = array();
        foreach ($yearlyTimesQ->get() as $item)
            array_push($yearlyTimes, $item);
        foreach ($yearlyTimesQ2->get() as $item)
            array_push($yearlyTimes, $item);


        $yearlyMinutes = 0;
        $yearlyHours = 0;
        $yearlyMinutesLimit = 0;
        $yearlyHoursLimit = 270;//270 Saat Y??ll??k Limit

        foreach ($yearlyTimes as $yearlyTime) {
            if (!is_null($neglectRecord))
                if ($yearlyTime->id == $neglectRecord->id)
                    continue;
            if ($yearlyTime->StatusID == 1 || $yearlyTime->StatusID == 2 || $yearlyTime->StatusID == 0 || $yearlyTime->StatusID == 4)
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

            $timeDiff2 = $endTime2->diff($beginTime2)->format('%H:%I:%S');
            $timeDiff2_Hour = explode(":",$timeDiff2)[0];
            $timeDiff2_Minute = explode(":",$timeDiff2)[1];

            if (!is_null($neglectRecord) && in_array($neglectRecord->StatusID,[6, 7, 8, 9, 10]))
            {
                $timeDiff1 = $timeDiff2;
                $timeDiff1_Hour = explode(":",$timeDiff1)[0];
                $timeDiff1_Minute = explode(":",$timeDiff1)[1];
            }
            else
            {
                $timeDiff1 = $endTime->diff($beginTime)->format('%H:%I:%S');
                $timeDiff1_Hour = explode(":",$timeDiff1)[0];
                $timeDiff1_Minute = explode(":",$timeDiff1)[1];
            }

            $totalMinutes1 = (($timeDiff1_Hour)*60) + ($yearlyHours *60) + abs($timeDiff1_Minute) + $yearlyMinutes;
            $totalMinutes2 = (($timeDiff2_Hour)*60) + ($yearlyHours*60) + abs($timeDiff2_Minute) + $yearlyMinutes;
            $yearlyLimitMinute = ($yearlyHoursLimit*60) + $yearlyMinutesLimit;
            if ($totalMinutes1 > $yearlyLimitMinute || $totalMinutes2 > $yearlyLimitMinute)
                return ['status' => false, 'message' => 'Girilen fazla ??al????ma s??resi, y??ll??k yasal fazla ??al????ma limitini a????yor.'];
        } else {

            $timeDiff1 = $endTime->diff($beginTime)->format('%H:%I:%S');
            $timeDiff1_Hour = explode(":",$timeDiff1)[0];
            $timeDiff1_Minute = explode(":",$timeDiff1)[1];

            $totalMinutes1 = (($timeDiff1_Hour)*60) + ($yearlyHours *60) + abs($timeDiff1_Minute) + $yearlyMinutes;
            $yearlyLimitMinute = ($yearlyHoursLimit*60) + $yearlyMinutesLimit;
            if ($totalMinutes1 > $yearlyLimitMinute )
                return ['status' => false, 'message' => 'Girilen fazla ??al????ma s??resi, y??ll??k yasal fazla ??al????ma limitini a????yor.'];
        }

        return ['status' => true, 'message' => 'Girilen fazla ??al????ma s??resi herhangi bir limiti a??m??yor.', 'data' => $monthlyHours];

    }

    public static function checkOvertimeExists($request,$neglectRecord = null)
    {
        $recordsCount = 999;
        if ($neglectRecord == null)
        {
            $recordsQ = OvertimeModel::where(['Active' => 1]);
            $recordsQ->where(['BeginDate' => $request->BeginDate,'AssignedID' => $request->AssignedID]);
            $recordsQ->whereIn('StatusID',[0,1,2,4]);
            $recordsQ->whereBetween('BeginTime',[$request->BeginTime,$request->EndTime]);
            $recordsCount = $recordsQ->count();

            $recordsQ2 = OvertimeModel::where(['Active' => 1]);
            $recordsQ2->where(['WorkBeginDate' => $request->BeginDate,'AssignedID' => $request->AssignedID]);
            $recordsQ2->whereIn('StatusID',[6,7,8,9]);
            $recordsQ2->whereBetween('WorkBeginDate',[$request->BeginTime,$request->EndTime]);
            $recordsCount+= $recordsQ2->count();

            if ($recordsCount > 0)
                return ['status' => false,'message' => '??al????may?? olu??turdu??unuz tarih ve saatlerde ilgili ??al????an??n ba??ka bir ??al????mas?? bulunmaktad??r ' .$recordsCount];

        }

        return ['status' => false,'message' => $recordsCount];


    }


    public static function getOvertimeByStatus($year=null,$month = null,$employee=null,$status, $EmployeeID, $paginationPage, $recordPerPage)
    {
        $userEmployees = EmployeePositionModel::where(['Active' => 2])->where(['UnitSupervisorID' => $EmployeeID])->get();
        $userEmployees2 = EmployeePositionModel::where(['Active' => 2])->where(['ManagerID' => $EmployeeID])->get();
        $userEmployeesIDs = [];
        foreach ($userEmployees as $userEmployee) {
            array_push($userEmployeesIDs, $userEmployee->EmployeeID);
        }
        foreach ($userEmployees2 as $userEmployee2) {
            array_push($userEmployeesIDs, $userEmployee2->EmployeeID);
        }
        $overtimeQ = self::where(['Active' => 1, 'StatusID' => $status])->where(function ($query) use ($EmployeeID, $userEmployeesIDs, $status) {
            if ($status == 8 || $status == 9 || $status == 10){
                //??ste??in ??K yetkilisinden mi yoksa normal y??neticiden mi geldi??ini anl??yoruz buradan
                $hrRegion = ProcessesSettingsModel::where(["object_type" => 4, "PropertyCode" => "HRManager", "PropertyValue" => $EmployeeID])->groupBy("RegionID")->pluck("RegionID");
                if (count($hrRegion) > 0)
                {
                    $usersApprove = EmployeePositionModel::where(["Active" => 2])->whereIn("RegionID", $hrRegion)->groupBy("EmployeeID")->pluck("EmployeeID");
                    $query->whereIn('AssignedID', $usersApprove);
                }
                else{
                    $query->whereIn('AssignedID', $userEmployeesIDs);
                    $query->orWhere(['ManagerID' => $EmployeeID, 'CreatedBy' => $EmployeeID]);
                }



            }
            else{
                $query->whereIn('AssignedID', $userEmployeesIDs);
                $query->where("ManagerID", $EmployeeID);
            }

        });


        if(in_array($status,[0,1,2,3,4]))
        {
            if ($year != null)
                $overtimeQ->whereYear("BeginDate",$year);
            if ($month != null)
                $overtimeQ->whereMonth("BeginDate",$month);

        }
        else{
            if ($year != null)
                $overtimeQ->whereYear("WorkBeginDate",$year);
            if ($month != null)
                $overtimeQ->whereMonth("WorkBeginDate",$month);
        }

        $overtimeQ->orderBy('BeginDate', 'desc');

        $overtimeCountQ = $overtimeQ;


        $data = [
            'singleStatusCount' => $overtimeCountQ->count(),
            'overtimes' => $overtimeQ->offset($paginationPage)->take($recordPerPage)->get(),

        ];

        return $data;

    }

    public static function getEmployeesOvertimeByStatus($status, $EmployeeID,$paginationPage, $recordPerPage)
    {
        $overtimeQ = self::where(['Active' => 1, 'StatusID' => $status, 'AssignedID' => $EmployeeID])->orderBy('BeginDate', 'desc');

        $overtimeCountQ = $overtimeQ;

        $data = [
            'singleStatusCount' => $overtimeCountQ->count(),
            'overtimes' => $overtimeQ->offset($paginationPage)->take($recordPerPage)->get()
        ];

        return $data;
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
                $tempEmployee = EmployeeModel::where('Id', $tempPosition->EmployeeID)->where('Active', 1)->first();
                $tempEmployee ? array_push($employeeList, $tempEmployee) : '';
            }
        }


        foreach ($employeePositions as $employeePosition) {
            $tempEmployee = EmployeeModel::where('Id', $employeePosition->EmployeeID)->where('Active', 1)->first();
            $tempEmployee ? array_push($employeeList, $tempEmployee) : '';
        }
        return $employeeList;
    }

    public static function getHREmployees($request)
    {
        $hrSpecialistRegions = ProcessesSettingsModel::where(['object_type' => 4, 'PropertyCode' => 'HRManager', 'PropertyValue' => $request->Employee])->pluck("RegionID");

        $employeePositionIDs = EmployeePositionModel::where(['Active' => 2])->where("EmployeeID",">",999)->whereIn("RegionID",$hrSpecialistRegions)->pluck("EmployeeID");

        $employeeList = [];

        $employeeList = DB::table("Employee")->where("Id",">","999")->where("Active",1)->whereIn("Id",$employeePositionIDs)->get();

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
            $temp = DB::table("Employee")->where('Id', $managerID)->where('Active', 1)->first();
            $temp ? array_push($managerList, $temp) : '';
        }

        return $managerList;

    }

    public function getAssignedEmployeeAttribute()
    {
        if (isset($this->attributes['AssignedID'])) {
            return EmployeeModel::where(['Id' => $this->attributes['AssignedID']])->first();
        } else {
            return "";
        }
    }

    public static function saveOvertimeByProcessType($procestype, $request)
    {

        /*
         * Request Tipleri
         * Tip 0 : Fazla ??al????may?? kaydetme durumu
         * Tip 1 : Y??neticiden ??al????ana fazla ??al????ma atama durumu
         * Tip 2 : ??al????andan y??neticiye d??zeltme talebi
         * Tip 3 : ??al????an taraf??ndan reddedildi -> Y??neticiye d??zeltme gidecek.
         * Tip 4 : ??al????an taraf??ndan onayland??
         * Tip 5 : ??al????an taraf??ndan iptal edildi
         * Tip 6 : ??al????an taraf??ndan ??al????ma tamamland?? -> Y??netici Onay?? Bekleniyor.
         * Tip 7 : Y??netici taraf??ndan fazla ??al????maya y??netici taraf??ndan d??zeltme talep edildi.
         * Tip 8 : Y??netici taraf??ndan fazla ??al????ma onayland??.
         * Tip 9 : IK tarf??ndan fazla ??al????maya d??zenleme talebi yap??ld??.
         * Tip 10 : IK taraf??ndan onayland??
         *
         * */

        //$limitCheck = self::overtimeLimitCheck($request);

        //TODO Kaydet, Kaydet ve Onaya G??nder Limit kontrolleri hatal?? gibi duruyor.

        switch ($procestype) {
            case 0:
                //Limit Kontrol
                if ($request->OvertimeId == null) {
                    $atHoliday = self::overtimeAtHoliday($request);
                    if ($atHoliday['status'] == false) {
                        $limitCheck = self::overtimeLimitCheck($request);
                        if ($limitCheck['status'] == false)
                            return $limitCheck;
                    }
                } else {
                    $atHoliday = self::overtimeAtHoliday($request);
                    if ($atHoliday['status'] == false)
                    {
                        $overtimeRecord = OvertimeModel::find($request->OvertimeId);
                        $limitCheck = self::overtimeLimitCheck($request, $overtimeRecord);
                        if ($limitCheck['status'] == false)
                            return $limitCheck;
                    }
                }
                return self::saveOvertimeRequest($request);
            case 1:
                if ($request->OvertimeId == null) {
                    $atHoliday = self::overtimeAtHoliday($request);
                    if ($atHoliday['status'] == false) {
                        $limitCheck = self::overtimeLimitCheck($request);
                        if ($limitCheck['status'] == false)
                            return $limitCheck;
                    }
                } else {
                    $atHoliday = self::overtimeAtHoliday($request);
                    if ($atHoliday['status'] == false) {
                        $overtimeRecord = OvertimeModel::find($request->OvertimeId);
                        $limitCheck = self::overtimeLimitCheck($request, $overtimeRecord);
                        if ($limitCheck['status'] == false)
                            return $limitCheck;
                    }
                }

                return self::sendOvertimeRequestToEmployee($request);
            case 2:
                $atHoliday = self::overtimeAtHoliday($request);
                if ($atHoliday['status'] == false) {
                    $overtimeRecord = OvertimeModel::find($request->OvertimeId);
                    $limitCheck = self::overtimeLimitCheck($request, $overtimeRecord);
                    if ($limitCheck['status'] == false)
                        return $limitCheck;
                }
                return self::overtimeCorrectionRequestFromEmployee($request);
            case 3:
                return self::overtimeRejectRequestFromEmployee($request);
            case 4:
                return self::overtimeApproveRequestFromEmployee($request);
            case 5:
                return self::overtimeCancelRequestFromManager($request);
            case 6:
                $atHoliday = self::overtimeAtHoliday($request);
                if ($atHoliday['status'] == false) {
                    $overtimeRecord = OvertimeModel::find($request->OvertimeId);
                    $limitCheck = self::overtimeLimitCheck($request, $overtimeRecord);
                    $limitCheck['testMessage'] = $atHoliday['message'];
                    if ($limitCheck['status'] == false)
                        return $limitCheck;
                }
                return self::overtimeCompleteRequestFromEmployee($request);
            case 7:
                $atHoliday = self::overtimeAtHoliday($request);
                if ($atHoliday['status'] == false) {
                    $overtimeRecord = OvertimeModel::find($request->OvertimeId);
                    $limitCheck = self::overtimeLimitCheck($request, $overtimeRecord);
                    if ($limitCheck['status'] == false)
                        return $limitCheck;
                }
                return self::overtimeCorrectionRequestFromManager($request);
            case 8:
                return self::overtimeApproveRequestFromManager($request);
            case 9:
                $atHoliday = self::overtimeAtHoliday($request);
                if ($atHoliday['status'] == false) {
                    $overtimeRecord = OvertimeModel::find($request->OvertimeId);
                    $limitCheck = self::overtimeLimitCheck($request, $overtimeRecord);
                    if ($limitCheck['status'] == false)
                        return $limitCheck;
                }
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
                $logStatus = LogsModel::setLog($overtimeRequest->Employee, $overtimeRecord->id, 4, 21, '', '', $overtimeRecord->BeginDate . ' ' . $overtimeRecord->BeginTime . ' tarihli fazla ??al????ma ' . $userEmployee->UsageName . '' . $userEmployee->LastName . ' adl?? personel taraf??ndan d??zenlendi.', '', '', '', '', '');
            } else {
                $userEmployee = EmployeeModel::find($overtimeRequest->Employee);
                $logStatus = LogsModel::setLog($overtimeRequest->Employee, $overtimeRecord->id, 4, 22, '', '', $overtimeRecord->BeginDate . ' ' . $overtimeRecord->BeginTime . ' tarihli fazla ??al????ma ' . $userEmployee->UsageName . '' . $userEmployee->LastName . ' adl?? y??netici taraf??ndan olu??turuldu.', '', '', '', '', '');
            }


            return ['status' => true, 'message' => '????lem Ba??ar??l??'];
        } else
            return ['status' => false, 'message' => 'Kay??t S??ras??nda Bir Hata Olu??tu'];

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

        $usingCar = $overtimeRecord->UsingCar == 0 ? 'Hay??r' : 'Evet';

        if ($overtimeRecord->save()) {
            $overtimeLink = $assignedEmployee->EmployeePosition->OrganizationID == 4 ? "http://connect.ms.asay.com.tr/#/overtime/".$overtimeRecord->id : 'http://portal.asay.com.tr/#/overtime/'.$overtimeRecord->id ;
            $mailData = ['employee' => $employee, 'assignedEmployee' => $assignedEmployee, 'assignedEmployeesManager' => $assignedEmployeesManager,
                'usingCar' => $usingCar, 'overtime' => $overtimeRecord,'overtimeLink' => $overtimeLink];
            $mailTable = view('mails.overtime', $mailData);

            Asay::sendMail($assignedEmployee->JobEmail, "", "Fazla ??al????ma Onay??n??z?? Bekliyor", $mailTable, "aSAY Group");
            NotificationsModel::saveNotification($overtimeRecord->AssignedID,4,$overtimeRecord->id,"Fazla ??al????ma",date("d.m.Y H:i:s",strtotime($overtimeRecord->BeginDate . ' ' .$overtimeRecord->BeginTime))." - ".date("d.m.Y H:i:s",strtotime($overtimeRecord->BeginDate . ' ' .$overtimeRecord->EndTime))." tarihleri aras??ndaki fazla ??al????ma i??in onay??n??z bekleniyor","overtime/".$overtimeRecord->id);
            return ['status' => true, 'message' => '????lem Ba??ar??l??'];
        } else
            return ['status' => false, 'message' => 'Kay??t S??ras??nda Bir Hata Olu??tu'];

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
        $overtimeRecord->ProcessDescription = $overtimeRequest->ProcessReason;

        $dirtyFields = $overtimeRecord->getDirty();
        $dirtyFieldsString = "";
        $dirtyFieldsArray = [];
        foreach ($dirtyFields as $field => $newdata) {
            $olddata = $overtimeRecord->getOriginal($field);
            if (self::columnNameToTurkish($field) == 'Onaylayacak Olan Y??netici' || $field == 'ProcessDescription')
                continue;
            if ($olddata != $newdata) {
                $dataObject = new \stdClass();
                $dataObject->changedFieldName = self::columnNameToTurkish($field);
                $dataObject->oldData = $olddata;
                $dataObject->newData = $newdata;
                array_push($dirtyFieldsArray, $dataObject);
                $overtimeRecord->ProcessDescription .= "\n\n" . $dataObject->changedFieldName ." (??lk De??er) : " . $dataObject->oldData . "\n" .
                    $dataObject->changedFieldName ." (D??zenlenen De??er) : " . $dataObject->newData;
            }
        }

        $overtimeRecord->StatusID = 2;

        $usingCar = $overtimeRecord->UsingCar == 0 ? 'Hay??r' : 'Evet';

        $employee = EmployeeModel::find($overtimeRequest->Employee);
        $assignedEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
        $reason = $overtimeRequest->ProcessReason == "" || $overtimeRequest->ProcessReason != null ? $overtimeRequest->ProcessReason : "A????klama Yap??lmam????";
        $assignedEmployeesManager = EmployeeModel::find($overtimeRecord->CreatedBy);

        $overtimeLink = $assignedEmployee->EmployeePosition->OrganizationID == 4 ? "http://connect.ms.asay.com.tr/#/overtime-manager/".$overtimeRecord->id : 'http://portal.asay.com.tr/#/overtime-manager/'.$overtimeRecord->id ;
        $mailData = ['employee' => $employee, 'assignedEmployee' => $assignedEmployee, 'assignedEmployeesManager' => $assignedEmployeesManager,
            'usingCar' => $usingCar, 'reason' => $reason, 'dirtyFields' => $dirtyFieldsArray, 'overtime' => $overtimeRecord,'overtimeLink' => $overtimeLink];
        $mailTable = view('mails.overtime', $mailData);

        Asay::sendMail($assignedEmployeesManager->JobEmail, "", "Fazla ??al????ma i??in d??zenleme talep edildi.", $mailTable
            , "aSAY Group");


        if ($overtimeRecord->save())
        {
            NotificationsModel::saveNotification($overtimeRecord->ManagerID,4,$overtimeRecord->id,"Fazla ??al????ma",date("d.m.Y H:i:s",strtotime($overtimeRecord->BeginDate . ' ' .$overtimeRecord->BeginTime))." - ".date("d.m.Y H:i:s",strtotime($overtimeRecord->BeginDate . ' ' .$overtimeRecord->EndTime))." tarihleri aras??ndaki fazla ??al????ma i??in ??al????an taraf??ndan d??zenleme talep edildi","overtime-manager/".$overtimeRecord->id);
            return ['status' => true, 'message' => '????lem Ba??ar??l??'];
        }

        else
            return ['status' => false, 'message' => 'Kay??t S??ras??nda Bir Hata Olu??tu'];

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
        $overtimeRecord->ProcessDescription = $overtimeRequest->ProcessReason;

        $dirtyFields = $overtimeRecord->getDirty();

        foreach ($dirtyFields as $field => $newdata) {
            $olddata = $overtimeRecord->getOriginal($field);
            if ($olddata != $newdata) {
                //TODO Loglama ????lemi burada yap??lacak.
            }
        }

        $overtimeRecord->StatusID = 3;
        $usingCar = $overtimeRecord->UsingCar == 0 ? 'Hay??r' : 'Evet';

        $employee = EmployeeModel::find($overtimeRequest->Employee);
        $assignedEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
        $reason = $overtimeRequest->ProcessReason == "" || $overtimeRequest->ProcessReason != null ? $overtimeRequest->ProcessReason : "";
        $assignedEmployeesManager = EmployeeModel::find($overtimeRecord->CreatedBy);


        $overtimeLink = $assignedEmployee->EmployeePosition->OrganizationID == 4 ? "http://connect.ms.asay.com.tr/#/overtime-manager/".$overtimeRecord->id : 'http://portal.asay.com.tr/#/overtime-manager/'.$overtimeRecord->id ;
        $mailData = ['employee' => $employee, 'assignedEmployee' => $assignedEmployee, 'assignedEmployeesManager' => $assignedEmployeesManager,
            'usingCar' => $usingCar, 'reason' => $reason, 'overtime' => $overtimeRecord,'overtimeLink' => $overtimeLink];
        $mailTable = view('mails.overtime', $mailData);

        Asay::sendMail($assignedEmployeesManager->JobEmail, "", "Fazla ??al????ma reddedildi", $mailTable, "aSAY Group");

        if ($overtimeRecord->save()) {
            $userEmployee = EmployeeModel::find($overtimeRequest->Employee);
            $logStatus = LogsModel::setLog($overtimeRequest->Employee, $overtimeRecord->id, 4, 26, '', '', $overtimeRecord->BeginDate . ' ' . $overtimeRecord->BeginTime . ' tarihli fazla ??al????ma ' . $userEmployee->UsageName . '' . $userEmployee->LastName . ' adl?? ??al????an taraf??ndan reddedildi.', '', '', '', '', '');
            NotificationsModel::saveNotification($overtimeRecord->ManagerID,4,$overtimeRecord->id,"Fazla ??al????ma",date("d.m.Y H:i:s",strtotime($overtimeRecord->BeginDate . ' ' .$overtimeRecord->BeginTime))." - ".date("d.m.Y H:i:s",strtotime($overtimeRecord->BeginDate . ' ' .$overtimeRecord->EndTime))." tarihleri aras??ndaki fazla ??al????ma ??al????an taraf??ndan reddedildi","overtime-manager/".$overtimeRecord->id);
            return ['status' => true, 'message' => '????lem Ba??ar??l??'];
        } else
            return ['status' => false, 'message' => 'Kay??t S??ras??nda Bir Hata Olu??tu'];

    }

    public static function overtimeApproveRequestFromEmployee($overtimeRequest)
    {

        $overtimeRecord = OvertimeModel::where(['id' => $overtimeRequest->OvertimeId, 'Active' => 1])->first();
        $overtimeRecord->StatusID = 4;

        //TODO Loglama ve mail g??nderimi yap??lacak ISG EK??B??NE
        $employee = EmployeeModel::find($overtimeRequest->Employee);
        $assignedEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
        $assignedEmployeePosition = EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $overtimeRecord->AssignedID])->first();
        $assignedEmployeesManager = EmployeeModel::find($overtimeRecord->CreatedBy);
        $isgPositions = EmployeePositionModel::where(['Active' => 2, 'RegionID' => $assignedEmployeePosition->RegionID])->get();
        $mailToArray = [];
        $isgGroupIDs = [];

        foreach ($isgPositions as $isgPosition) {
            $userIsg = EmployeeModel::where(['Id' => $isgPosition->EmployeeID,'Active' => 1])->first();
            $hasGroup = EmployeePositionModel::where(['EmployeeID' => $isgPosition->EmployeeID])->whereIn("TitleID",[101,102,103])->first();
            if ($hasGroup) {
                array_push($mailToArray, $userIsg->JobEmail);
            }

        }
        $usingCar = $overtimeRecord->UsingCar == 0 ? 'Hay??r' : 'Evet';


        $overtimeLink = '';
        $mailData = ['employee' => $employee, 'assignedEmployee' => $assignedEmployee, 'assignedEmployeesManager' => $assignedEmployeesManager,
            'usingCar' => $usingCar, 'overtime' => $overtimeRecord,'overtimeLink' => $overtimeLink];
        $mailTable = view('mails.overtime', $mailData);

        //Asay::sendMail($mailToArray, "", "Yap??lmas?? planlanan fazla ??al????ma", $mailTable, "aSAY Group");


        if ($overtimeRecord->save()) {
            $userEmployee = EmployeeModel::find($overtimeRequest->Employee);
            $logStatus = LogsModel::setLog($overtimeRequest->Employee, $overtimeRecord->id, 4, 24, '', '', $overtimeRecord->BeginDate . ' ' . $overtimeRecord->BeginTime . ' tarihli fazla ??al????ma ' . $userEmployee->UsageName . '' . $userEmployee->LastName . ' adl?? ??al????an taraf??ndan onayland??.', '', '', '', '', '');
            NotificationsModel::saveNotification($overtimeRecord->ManagerID,4,$overtimeRecord->id,"Fazla ??al????ma",date("d.m.Y H:i:s",strtotime($overtimeRecord->BeginDate . ' ' .$overtimeRecord->BeginTime))." - ".date("d.m.Y H:i:s",strtotime($overtimeRecord->BeginDate . ' ' .$overtimeRecord->EndTime))." tarihleri aras??ndaki fazla ??al????ma ??al????an taraf??ndan onayland??","overtime-manager/".$overtimeRecord->id);
            return ['status' => true, 'message' => '????lem Ba??ar??l??'];
        } else
            return ['status' => false, 'message' => 'Kay??t S??ras??nda Bir Hata Olu??tu'];
    }

    public static function overtimeCancelRequestFromManager($overtimeRequest)
    {
        $overtimeRecord = OvertimeModel::where(['id' => $overtimeRequest->OvertimeId, 'Active' => 1])->first();

        if($overtimeRecord->StatusID == 0)
        {
            $overtimeRecord->Active = 0;
            $overtimeRecord->save();
            return ['status' => true, 'message' => '????lem Ba??ar??l??'];
        }

        $overtimeRecord->StatusID = 5;
        $overtimeRecord->ProcessDescription = $overtimeRequest->ProcessReason;

        //TODO Loglama ve mail g??ndeirmi yap??lacak

        $usingCar = $overtimeRecord->UsingCar == 0 ? 'Hay??r' : 'Evet';

        $employee = EmployeeModel::find($overtimeRequest->Employee);
        $assignedEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
        $reason = $overtimeRequest->ProcessReason == "" || $overtimeRequest->ProcessReason != null ? $overtimeRequest->ProcessReason : "A????klama Yap??lmam????";
        $assignedEmployeesManager = EmployeeModel::find(EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $overtimeRecord->AssignedID])->first()->ManagerID);
        $overtimeLink = $assignedEmployee->EmployeePosition->OrganizationID == 4 ? "http://connect.ms.asay.com.tr/#/overtime/".$overtimeRecord->id : 'http://portal.asay.com.tr/#/overtime/'.$overtimeRecord->id ;
        $mailData = ['employee' => $employee, 'assignedEmployee' => $assignedEmployee, 'assignedEmployeesManager' => $assignedEmployeesManager,
            'usingCar' => $usingCar, 'reason' => $reason, 'overtime' => $overtimeRecord,'overtimeLink' => $overtimeLink];
        $mailTable = view('mails.overtime', $mailData);
        Asay::sendMail($assignedEmployee->JobEmail, "", "Fazla ??al????man??z y??neticiniz taraf??ndan iptal edildi", $mailTable, "aSAY Group");


        if ($overtimeRecord->save()) {
            $userEmployee = EmployeeModel::find($overtimeRequest->Employee);
            $logStatus = LogsModel::setLog($overtimeRequest->Employee, $overtimeRecord->id, 4, 25, '', '', $overtimeRecord->BeginDate . ' ' . $overtimeRecord->BeginTime . ' tarihli fazla ??al????ma ' . $userEmployee->UsageName . '' . $userEmployee->LastName . ' adl?? ??al????an taraf??ndan iptal edildi.', '', '', '', '', '');
            NotificationsModel::saveNotification($overtimeRecord->AssignedID,4,$overtimeRecord->id,"Fazla ??al????ma",date("d.m.Y H:i:s",strtotime($overtimeRecord->BeginDate . ' ' .$overtimeRecord->BeginTime))." - ".date("d.m.Y H:i:s",strtotime($overtimeRecord->BeginDate . ' ' .$overtimeRecord->EndTime))." tarihleri aras??ndaki fazla ??al????ma y??neticiniz taraf??ndan iptal edildi","overtime/".$overtimeRecord->id);
            return ['status' => true, 'message' => '????lem Ba??ar??l??'];
        } else
            return ['status' => false, 'message' => 'Kay??t S??ras??nda Bir Hata Olu??tu'];

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

        $usingCar = $overtimeRecord->UsingCar == 0 ? 'Hay??r' : 'Evet';

        //TODO Loglama ve mail g??ndeirmi yap??lacak
        $employee = EmployeeModel::find($overtimeRequest->Employee);
        $assignedEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
        $assignedEmployeesManager = EmployeeModel::find($overtimeRecord->CreatedBy);
        $overtimeLink = $assignedEmployee->EmployeePosition->OrganizationID == 4 ? "http://connect.ms.asay.com.tr/#/overtime-manager/".$overtimeRecord->id : 'http://portal.asay.com.tr/#/overtime-manager/'.$overtimeRecord->id ;
        $mailData = ['employee' => $employee, 'assignedEmployee' => $assignedEmployee,
            'assignedEmployeesManager' => $assignedEmployeesManager, 'overtime' => $overtimeRecord,
            'usingCar' => $usingCar, 'extraFields' => true,'overtimeLink' => $overtimeLink];

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
            $res = $client->request("POST", 'http://'.\request()->getHttpHost().'/rest/api/disk/addFile', $guzzleParams);
            $responseBody = json_decode($res->getBody());

            if ($responseBody->status == true) {
                $overtimeRecord->File = $responseBody->data;
                $overtimeRecord->save();
            }
        }

        if ($overtimeRecord->File) {
            $returnVal = self::getFileOfOvertime($overtimeRequest);
            Asay::sendMail($assignedEmployeesManager->JobEmail, "", "Fazla ??al????ma tamamland??.", view("mails.overtime", $mailData), "aSAY Group", $returnVal->FilePath, $returnVal->FileName, $returnVal->MimeType);
        } else
            Asay::sendMail($assignedEmployeesManager->JobEmail, "", "Fazla ??al????ma tamamland??.", view("mails.overtime", $mailData), "aSAY Group", "", "", "");


        if ($result) {
            $userEmployee = EmployeeModel::find($overtimeRequest->Employee);

            $logStatus = LogsModel::setLog($overtimeRequest->Employee, $overtimeRecord->id, 4, 27, '', '', $overtimeRecord->BeginDate . ' ' . $overtimeRecord->BeginTime . ' tarihli fazla ??al????ma ' . $userEmployee->UsageName . '' . $userEmployee->LastName . ' adl?? ??al????an taraf??ndan tamamland??.', '', '', '', '', '');
            NotificationsModel::saveNotification($overtimeRecord->ManagerID,4,$overtimeRecord->id,"Fazla ??al????ma",date("d.m.Y H:i:s",strtotime($overtimeRecord->BeginDate . ' ' .$overtimeRecord->BeginTime))." - ".date("d.m.Y H:i:s",strtotime($overtimeRecord->BeginDate . ' ' .$overtimeRecord->EndTime))." tarihleri aras??ndaki fazla ??al????ma ??al????an taraf??ndan tamamland??","overtime-manager/".$overtimeRecord->id);
            return ['status' => true, 'message' => '????lem Ba??ar??l??'];
        } else
            return ['status' => false, 'message' => 'Kay??t S??ras??nda Bir Hata Olu??tu'];

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
        $overtimeRecord->ProcessDescription = $overtimeRequest->ProcessReason;

        $dirtyFields = $overtimeRecord->getDirty();
        $dirtyFieldsString = "";
        $dirtyFieldsArray = [];
        foreach ($dirtyFields as $field => $newdata) {
            $olddata = $overtimeRecord->getOriginal($field);
            if (self::columnNameToTurkish($field) == 'Onaylayacak Olan Y??netici' || $field == 'ProcessDescription')
                continue;
            if ($olddata != $newdata) {
                //TODO Loglama ????lemi burada yap??lacak.
                $dataObject = new \stdClass();
                $dataObject->changedFieldName = self::columnNameToTurkish($field);
                $dataObject->oldData = $olddata;
                $dataObject->newData = $newdata;
                array_push($dirtyFieldsArray, $dataObject);
                $overtimeRecord->ProcessDescription .= "\n\n" . $dataObject->changedFieldName ." (??lk De??er) : " . $dataObject->oldData . "\n" .
                    $dataObject->changedFieldName ." (D??zenlenen De??er) : " . $dataObject->newData;
            }
        }

        $overtimeRecord->StatusID = 7;
        $overtimeRecord->ManagerID = $overtimeRecord->CreatedBy;

        $usingCar = $overtimeRecord->UsingCar == 0 ? 'Hay??r' : 'Evet';

        $employee = EmployeeModel::find($overtimeRequest->Employee);
        $assignedEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
        $reason = $overtimeRequest->ProcessReason == "" || $overtimeRequest->ProcessReason != null ? $overtimeRequest->ProcessReason : "A????klama Yap??lmam????";
        $assignedEmployeesManager = EmployeeModel::find($overtimeRecord->ManagerID);
        $overtimeLink = $assignedEmployee->EmployeePosition->OrganizationID == 4 ? "http://connect.ms.asay.com.tr/#/overtime-manager/".$overtimeRecord->id : 'http://portal.asay.com.tr/#/overtime-manager/'.$overtimeRecord->id ;
        $mailData = ['employee' => $employee, 'assignedEmployee' => $assignedEmployee, 'assignedEmployeesManager' => $assignedEmployeesManager,
            'usingCar' => $usingCar, 'reason' => $reason, 'dirtyFields' => $dirtyFieldsArray, 'overtime' => $overtimeRecord, 'extraFields' => true,'overtimeLink' => $overtimeLink];
        $mailTable = view('mails.overtime', $mailData);


        Asay::sendMail($assignedEmployee->JobEmail, "", "Fazla ??al????ma ????in D??zenleme Talep Edildi", $mailTable
            , "aSAY Group");


        if ($overtimeRecord->save())
        {
            NotificationsModel::saveNotification($overtimeRecord->AssignedID,4,$overtimeRecord->id,"Fazla ??al????ma",date("d.m.Y H:i:s",strtotime($overtimeRecord->BeginDate . ' ' .$overtimeRecord->BeginTime))." - ".date("d.m.Y H:i:s",strtotime($overtimeRecord->BeginDate . ' ' .$overtimeRecord->EndTime))." tarihleri aras??ndaki fazla ??al????ma i??in y??neticiniz taraf??ndan d??zenleme talep edildi","overtime/".$overtimeRecord->id);
            return ['status' => true, 'message' => '????lem Ba??ar??l??'];
        }

        else
            return ['status' => false, 'message' => 'Kay??t S??ras??nda Bir Hata Olu??tu'];

    }

    public static function overtimeApproveRequestFromManager($overtimeRequest)
    {
        //Bu fazla mesaiyi olu??turan ki??inin, atad?????? ki??inin y??neticisi mi oldu??u yoksa birim sorumlusu mu bu tespit etmeliyiz.
        $employee = EmployeeModel::find($overtimeRequest->Employee);
        $overtimeRecord = OvertimeModel::where(['id' => $overtimeRequest->OvertimeId, 'Active' => 1])->first();
        $assignedEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
        $assignedEmployeePosition = EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $assignedEmployee->Id])->first();
        $assignedEmployeesManager = EmployeeModel::find($assignedEmployeePosition->ManagerID);


        if ($assignedEmployeePosition->ManagerID != null && ($assignedEmployeePosition->ManagerID == $employee->Id)) {
            $usingCar = $overtimeRecord->UsingCar == 0 ? 'Hay??r' : 'Evet';

            $overtimeLink = $assignedEmployee->EmployeePosition->OrganizationID == 4 ? "http://connect.ms.asay.com.tr/#/overtime/".$overtimeRecord->id : 'http://portal.asay.com.tr/#/overtime/'.$overtimeRecord->id ;
            $mailData = ['employee' => $employee, 'assignedEmployee' => $assignedEmployee, 'assignedEmployeesManager' => $assignedEmployeesManager,
                'usingCar' => $usingCar, 'overtime' => $overtimeRecord, 'extraFields' => true,'overtimeLink' => $overtimeLink];
            $mailTable = view('mails.overtime', $mailData);

            Asay::sendMail($assignedEmployee->JobEmail, "", "Fazla ??al????ma y??neticiniz taraf??ndan onayland??.", $mailTable, "aSAY Group");
            $userEmployee = EmployeeModel::find($overtimeRequest->Employee);
            $logStatus = LogsModel::setLog($overtimeRequest->Employee, $overtimeRecord->id, 4, 28, '', '', $overtimeRecord->BeginDate . ' ' . $overtimeRecord->BeginTime . ' tarihli fazla ??al????ma ' . $userEmployee->UsageName . '' . $userEmployee->LastName . ' adl?? y??netici taraf??ndan onayland??.', '', '', '', '', '');


            $hrSpecialists = ProcessesSettingsModel::where(['object_type' => 4, 'PropertyCode' => 'HRManager', 'RegionID' => $assignedEmployeePosition->RegionID])->where("PropertyValue","!=","1639")->get();


            $overtimeLink = $assignedEmployee->EmployeePosition->OrganizationID == 4 ? "http://connect.ms.asay.com.tr/#/overtime-hr/".$overtimeRecord->id : 'http://portal.asay.com.tr/#/overtime-hr/'.$overtimeRecord->id ;
            $mailData = ['employee' => $employee, 'assignedEmployee' => $assignedEmployee, 'assignedEmployeesManager' => $assignedEmployeesManager,
                'usingCar' => $usingCar, 'overtime' => $overtimeRecord, 'extraFields' => true,'overtimeLink' => $overtimeLink];
            $mailTable = view('mails.overtime', $mailData);

            foreach ($hrSpecialists as $hrSpecialist){
                $hrEmployee = EmployeeModel::find($hrSpecialist->PropertyValue);
                if ($overtimeRecord->File) {
                    $returnVal = self::getFileOfOvertime($overtimeRequest);
                    Asay::sendMail($hrEmployee->JobEmail, "", "Fazla ??al????ma ??al????an??n y??neticisi taraf??ndan onayland??", view("mails.overtime", $mailData), "aSAY Group", $returnVal->FilePath, $returnVal->FileName, $returnVal->MimeType);
                } else
                    Asay::sendMail($hrEmployee->JobEmail, "", "Fazla ??al????ma ??al????an??n y??neticisi taraf??ndan onayland??", view("mails.overtime", $mailData), "aSAY Group", "", "", "");
                $overtimeRecord->ManagerID = $hrEmployee->Id;
            }

            $overtimeRecord->StatusID = 8;
            $overtimeRecord->save();

            NotificationsModel::saveNotification($overtimeRecord->AssignedID,4,$overtimeRecord->id,"Fazla ??al????ma",date("d.m.Y H:i:s",strtotime($overtimeRecord->BeginDate . ' ' .$overtimeRecord->BeginTime))." - ".date("d.m.Y H:i:s",strtotime($overtimeRecord->BeginDate . ' ' .$overtimeRecord->EndTime))." tarihleri aras??ndaki fazla ??al????ma, y??neticiniz taraf??ndan onayland??","overtime/".$overtimeRecord->id);
            NotificationsModel::saveNotification($hrEmployee->Id,4,$overtimeRecord->id,"Fazla ??al????ma",date("d.m.Y H:i:s",strtotime($overtimeRecord->BeginDate . ' ' .$overtimeRecord->BeginTime))." - ".date("d.m.Y H:i:s",strtotime($overtimeRecord->BeginDate . ' ' .$overtimeRecord->EndTime))." tarihleri aras??ndaki fazla ??al????ma i??in onay??n??z bekleniyor","overtime-hr/".$overtimeRecord->id);

        }
        else if ($assignedEmployeePosition->UnitSupervisorID == $employee->Id) {

            //Varsa-Y??neticiye mail
            if ($assignedEmployeesManager != null)
            {
                $usingCar = $overtimeRecord->UsingCar == 0 ? 'Hay??r' : 'Evet';
                $overtimeLink1 = $assignedEmployee->EmployeePosition->OrganizationID == 4 ? "http://connect.ms.asay.com.tr/#/overtime-manager/".$overtimeRecord->id : 'http://portal.asay.com.tr/#/overtime-manager/'.$overtimeRecord->id ;

                $mailData1 = ['employee' => $employee, 'assignedEmployee' => $assignedEmployee, 'assignedEmployeesManager' => $assignedEmployeesManager,
                    'usingCar' => $usingCar, 'overtime' => $overtimeRecord, 'extraFields' => true, 'overtimeLink' => $overtimeLink1];
                $mailTable1 = view('mails.overtime', $mailData1);


                if ($overtimeRecord->File) {
                    $returnVal = self::getFileOfOvertime($overtimeRequest);
                    Asay::sendMail($assignedEmployeesManager->JobEmail, "", "Fazla ??al????ma birim sorumlusu taraf??ndan onayland??", $mailTable1, "aSAY Group", $returnVal->FilePath, $returnVal->FileName, $returnVal->MimeType);
                } else
                    Asay::sendMail($assignedEmployeesManager->JobEmail, "", "Fazla ??al????ma birim sorumlusu taraf??ndan onayland??", $mailTable1, "aSAY Group", "", "", "");
                $overtimeRecord->ManagerID = $assignedEmployeesManager->Id;
            }
            //Y??netici Yoksa ??K'ya mail ve ik onay??na ge??i??
            else
            {
                $usingCar = $overtimeRecord->UsingCar == 0 ? 'Hay??r' : 'Evet';
                $overtimeLink = $assignedEmployee->EmployeePosition->OrganizationID == 4 ? "http://connect.ms.asay.com.tr/#/overtime-hr/".$overtimeRecord->id : 'http://portal.asay.com.tr/#/overtime-hr/'.$overtimeRecord->id ;
                $mailData = ['employee' => $employee, 'assignedEmployee' => $assignedEmployee, 'assignedEmployeesManager' => $assignedEmployeesManager,
                    'usingCar' => $usingCar, 'overtime' => $overtimeRecord, 'extraFields' => true,'overtimeLink' => $overtimeLink];
                $mailTable = view('mails.overtime', $mailData);

                $hrSpecialists = ProcessesSettingsModel::where(['object_type' => 4, 'PropertyCode' => 'HRManager', 'RegionID' => $assignedEmployeePosition->RegionID])->get();
                //T??m B??lge ??K Sorumlular??na
                foreach ($hrSpecialists as $hrSpecialist)
                {
                    $hrEmployee = EmployeeModel::find($hrSpecialist->PropertyValue);
                    if ($overtimeRecord->File) {
                        $returnVal = self::getFileOfOvertime($overtimeRequest);
                        Asay::sendMail($hrEmployee->JobEmail, "", "Fazla ??al????ma ??al????an??n birim sorumlusu taraf??ndan onayland??", view("mails.overtime", $mailData), "aSAY Group", $returnVal->FilePath, $returnVal->FileName, $returnVal->MimeType);
                    } else
                        Asay::sendMail($hrEmployee->JobEmail, "", "Fazla ??al????ma ??al????an??n birim sorumlusu taraf??ndan onayland??", view("mails.overtime", $mailData), "aSAY Group", "", "", "");

                    $overtimeRecord->ManagerID = $hrEmployee->Id;
                }

                $overtimeRecord->StatusID = 8;
            }

            //??al????ana Mail
            if ($overtimeRecord->File) {
                $overtimeLink2 = $assignedEmployee->EmployeePosition->OrganizationID == 4 ? "http://connect.ms.asay.com.tr/#/overtime/".$overtimeRecord->id : 'http://portal.asay.com.tr/#/overtime/'.$overtimeRecord->id ;
                $mailData2 = ['employee' => $employee, 'assignedEmployee' => $assignedEmployee, 'assignedEmployeesManager' => $assignedEmployeesManager,
                    'usingCar' => $usingCar, 'overtime' => $overtimeRecord, 'extraFields' => true, 'overtimeLink' => $overtimeLink2];
                $mailTable2 = view('mails.overtime', $mailData2);
                $returnVal = self::getFileOfOvertime($overtimeRequest);
                Asay::sendMail($assignedEmployee->JobEmail, "", "Fazla ??al????ma birim sorumlusu taraf??ndan onayland??", $mailTable2, "aSAY Group", $returnVal->FilePath, $returnVal->FileName, $returnVal->MimeType);

            } else
            {
                $overtimeLink2 = $assignedEmployee->EmployeePosition->OrganizationID == 4 ? "http://connect.ms.asay.com.tr/#/overtime/".$overtimeRecord->id : 'http://portal.asay.com.tr/#/overtime/'.$overtimeRecord->id ;
                $mailData2 = ['employee' => $employee, 'assignedEmployee' => $assignedEmployee, 'assignedEmployeesManager' => $assignedEmployeesManager,
                    'usingCar' => $usingCar, 'overtime' => $overtimeRecord, 'extraFields' => true, 'overtimeLink' => $overtimeLink2];
                $mailTable2 = view('mails.overtime', $mailData2);
                Asay::sendMail($assignedEmployee->JobEmail, "", "Fazla ??al????ma birim sorumlusu taraf??ndan onayland??", $mailTable2, "aSAY Group", "", "", "");
            }


            $userEmployee = EmployeeModel::find($overtimeRequest->Employee);
            $logStatus = LogsModel::setLog($overtimeRequest->Employee, $overtimeRecord->id, 4, 28, '', '', $overtimeRecord->BeginDate . ' ' . $overtimeRecord->BeginTime . ' tarihli fazla ??al????ma ' . $userEmployee->UsageName . '' . $userEmployee->LastName . ' adl?? birim sorumlusu taraf??ndan onayland??.', '', '', '', '', '');

            NotificationsModel::saveNotification($overtimeRecord->AssignedID,4,$overtimeRecord->id,"Fazla ??al????ma",date("d.m.Y H:i:s",strtotime($overtimeRecord->BeginDate . ' ' .$overtimeRecord->BeginTime))." - ".date("d.m.Y H:i:s",strtotime($overtimeRecord->BeginDate . ' ' .$overtimeRecord->EndTime))." tarihleri aras??ndaki fazla ??al????ma, birim sorumlusu taraf??ndan onayland??","overtime/".$overtimeRecord->id);
            $overtimeRecord->save();


        }

        //$overtimeRecord->AssignedID = $overtimeRequest->AssignedID;
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
        {
            return ['status' => true, 'message' => '????lem Ba??ar??l??'];
        }
        else
            return ['status' => false, 'message' => 'Kay??t S??ras??nda Bir Hata Olu??tu'];

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
        $overtimeRecord->WorkBeginTime = $overtimeRequest->WorkBeginTime.':00';
        $overtimeRecord->WorkEndTime = $overtimeRequest->WorkEndTime.':00';
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
        $overtimeRecord->ProcessDescription = $overtimeRequest->ProcessReason;

        $dirtyFields = $overtimeRecord->getDirty();
        $dirtyFieldsArray = [];

        foreach ($dirtyFields as $field => $newdata) {
            $olddata = $overtimeRecord->getOriginal($field);
            if (self::columnNameToTurkish($field) == 'Onaylayacak Olan Y??netici' || $field == 'ProcessDescription')
                continue;
            if ($olddata != $newdata) {
                //TODO Loglama ????lemi burada yap??lacak.
                $dataObject = new \stdClass();
                $dataObject->changedFieldName = self::columnNameToTurkish($field);
                $dataObject->oldData = $olddata;
                $dataObject->newData = $newdata;
                array_push($dirtyFieldsArray, $dataObject);
                $overtimeRecord->ProcessDescription .= "\n\n" . $dataObject->changedFieldName ." (??lk De??er) : " . $dataObject->oldData . "\n" .
                    $dataObject->changedFieldName ." (D??zenlenen De??er) : " . $dataObject->newData;
            }
        }

        $overtimeRecord->StatusID = 9;

        $usingCar = $overtimeRecord->UsingCar == 0 ? 'Hay??r' : 'Evet';

        $employee = EmployeeModel::find($overtimeRequest->Employee);
        $assignedEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
        $reason = $overtimeRequest->ProcessReason == "" || $overtimeRequest->ProcessReason != null ? $overtimeRequest->ProcessReason : "A????klama Yap??lmam????";
        $assignedEmployeesManager = EmployeeModel::find($overtimeRecord->ManagerID);

        $overtimeLink = $assignedEmployee->EmployeePosition->OrganizationID == 4 ? "http://connect.ms.asay.com.tr/#/overtime-hr/".$overtimeRecord->id : 'http://portal.asay.com.tr/#/overtime-hr/'.$overtimeRecord->id ;
        $mailData = ['employee' => $employee, 'assignedEmployee' => $assignedEmployee, 'assignedEmployeesManager' => $assignedEmployeesManager,
            'usingCar' => $usingCar, 'reason' => $reason, 'dirtyFields' => $dirtyFieldsArray, 'overtime' => $overtimeRecord, 'extraFields' => true,'overtimeLink' => $overtimeLink];
        $mailTable = view('mails.overtime', $mailData);

        Asay::sendMail($assignedEmployee->JobEmail, "", "Fazla ??al????ma i??in ??nsan Kaynaklar?? birimi taraf??ndan d??zenleme talep edildi.", $mailTable
            , "aSAY Group");


        if ($overtimeRecord->save())
        {
            NotificationsModel::saveNotification($overtimeRecord->AssignedID,4,$overtimeRecord->id,"Fazla ??al????ma",date("d.m.Y H:i:s",strtotime($overtimeRecord->BeginDate . ' ' .$overtimeRecord->BeginTime))." - ".date("d.m.Y H:i:s",strtotime($overtimeRecord->BeginDate . ' ' .$overtimeRecord->EndTime))." tarihleri aras??ndaki fazla ??al????ma i??in insan kaynaklar?? birimi taraf??ndan d??zenleme talep edildi","overtime/".$overtimeRecord->id);
            return ['status' => true, 'message' => '????lem Ba??ar??l??'];
        }

        else
            return ['status' => false, 'message' => 'Kay??t S??ras??nda Bir Hata Olu??tu'];

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
                //TODO Loglama ????lemi burada yap??lacak.
            }
        }

        $overtimeRecord->StatusID = 10;

        $usingCar = $overtimeRecord->UsingCar == 0 ? 'Hay??r' : 'Evet';

        $employee = EmployeeModel::find($overtimeRequest->Employee);
        $assignedEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
        $assignedEmployeesManager = EmployeeModel::find(EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $overtimeRecord->AssignedID])->first()->ManagerID);
        $userAccountOfEmployee = EmployeeModel::find($overtimeRecord->AssignedID);
        $overtimeLink = $assignedEmployee->EmployeePosition->OrganizationID == 4 ? "http://connect.ms.asay.com.tr/#/overtime/".$overtimeRecord->id : 'http://portal.asay.com.tr/#/overtime/'.$overtimeRecord->id ;
        $mailData = ['employee' => $employee, 'assignedEmployee' => $assignedEmployee, 'assignedEmployeesManager' => $assignedEmployeesManager,
            'usingCar' => $usingCar, 'overtime' => $overtimeRecord, 'extraFields' => true,'overtimeLink' => $overtimeLink];
        $mailTable = view('mails.overtime', $mailData);

        if ($overtimeRecord->File) {
            $returnVal = self::getFileOfOvertime($overtimeRequest);
            Asay::sendMail($userAccountOfEmployee->JobEmail, "", "Fazla ??al????man??z, insan kaynaklar?? birimi taraf??ndan onayland??", $mailTable, "aSAY Group", $returnVal->FilePath, $returnVal->FileName, $returnVal->MimeType);
        } else
            Asay::sendMail($userAccountOfEmployee->JobEmail, "", "Fazla ??al????man??z, insan kaynaklar?? birimi taraf??ndan onayland??", $mailTable, "aSAY Group", "", "", "");


        if ($overtimeRecord->save()) {
            $userEmployee = EmployeeModel::find($overtimeRequest->Employee);
            $logStatus = LogsModel::setLog($overtimeRequest->Employee, $overtimeRecord->id, 4, 29, '', '', $overtimeRecord->BeginDate . ' ' . $overtimeRecord->BeginTime . ' tarihli fazla ??al????ma ' . $userEmployee->UsageName . '' . $userEmployee->LastName . ' adl?? y??netici taraf??ndan onayland??.', '', '', '', '', '');
            NotificationsModel::saveNotification($overtimeRecord->AssignedID,4,$overtimeRecord->id,"Fazla ??al????ma",date("d.m.Y H:i:s",strtotime($overtimeRecord->BeginDate . ' ' .$overtimeRecord->BeginTime))." - ".date("d.m.Y H:i:s",strtotime($overtimeRecord->BeginDate . ' ' .$overtimeRecord->EndTime))." tarihleri aras??ndaki fazla ??al????ma, insan kaynaklar?? birimi taraf??ndan onayland??","overtime/".$overtimeRecord->id);
            //Fazla ??al????ma t??m onay s??re??lerinden ge??erse ki??i her saat ba????na yar??m saat dinlenme izni kullanmak i??in hak kazan??r. Bu izin YILDA 105 SAAT olarak s??n??rland??r??lm????t??r.

            $resp = self::addRestPermitToEmployee($overtimeRequest);

            return ['status' => true, 'message' => '????lem Ba??ar??l??'];
        } else
            return ['status' => false, 'message' => 'Kay??t S??ras??nda Bir Hata Olu??tu'];

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
            $res = $client->request("GET", 'http://'.\request()->getHttpHost().'/rest/api/disk/getFile', $guzzleParams);
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

        $workTotalTimeMinute = abs((int)($workEndTime->hour - $workBeginTime->hour)) * 60 + (abs($workBeginTime->minute - $workEndTime->minute)); //Dakikaya ??evrildi



        $earnedRestMinute = (($workTotalTimeMinute * 1.5)) % 60;
        $earnedRestHour = (int)(($workTotalTimeMinute * 1.5) / 60);


        $overtimeRestHourLimit = 105;
        $overtimeRests = OvertimeRestModel::selectRaw(" SUM(Hour) as TotalHour, SUM(Minute) as TotalMinute")->where(['EmployeeID' => $request->AssignedID, 'Active' => 1])->whereYear('Date', "=", $workBeginDate->year)->first();

        if ($earnedRestHour + $overtimeRests->TotalHour > $overtimeRestHourLimit)
            return ['status' => false, 'message' => 'Y??ll??k Dinlenme ??zni Limiti Dolmu??tur'];

        else if ($earnedRestHour + $overtimeRests->TotalHour == $overtimeRestHourLimit)
            if ($earnedRestMinute > 0)
                return ['status' => false, 'message' => 'Y??ll??k Dinlenme ??zni Limiti Dolmu??tur. Toplam hakedilmi?? izin : ' . $overtimeRestHourLimit];

        if ($earnedRestMinute + $overtimeRests->TotalMinute > 60) {
            if ($earnedRestHour + ((int)($earnedRestMinute + $overtimeRests->TotalMinute / 60)) > $overtimeRestHourLimit) {
                return ['status' => false, 'message' => 'Y??ll??k Dinlenme ??zni Limiti Dolmu??tur'];
            }
        }



        $newOvertimeRest = OvertimeRestModel::where(['Active' => 1, 'EmployeeID' => $request->AssignedID, 'OvertimeID' => $request->OvertimeId])->first();
        if (!$newOvertimeRest)
            $newOvertimeRest = new OvertimeRestModel();
        $newOvertimeRest->OvertimeID = $request->OvertimeId;
        $newOvertimeRest->EmployeeID = $request->AssignedID;
        $newOvertimeRest->Date = $request->BeginDate;
        $newOvertimeRest->Hour = $earnedRestHour;
        $newOvertimeRest->Minute = $earnedRestMinute;

        if ($newOvertimeRest->save())
            return ['status' => true, 'message' => '????lem Ba??ar??l??', 'earnedTimeHour' => $earnedRestHour, 'earnedTimeMinute' => $earnedRestMinute];

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
        if (isset($this->attributes['CreatedBy'])) {
            return DB::table("Employee")->where(['Id' => $this->attributes['CreatedBy']])->first();
        } else {
            return "";
        }

    }

    public function getApproveWhoAttribute()
    {
        if (isset($this->attributes['ManagerID'])) {
            return DB::table("Employee")->where(['Id' => $this->attributes['ManagerID']])->first();
        } else {
            return "";
        }

    }
    public function getCreatedByEmployeeAttribute()
    {
        if (isset($this->attributes['CreatedBy'])) {
            return DB::table("Employee")->where(['Id' => $this->attributes['CreatedBy']])->first();
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
