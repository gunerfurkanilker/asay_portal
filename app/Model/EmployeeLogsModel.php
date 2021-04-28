<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class EmployeeLogsModel extends Model
{
    protected $table = "EmployeeLogs";

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];
    protected $appends = [];


    public static function setLog($EmployeeID, $logType, $newValue = null, $logText = null)
    {
        $newLog = new self();
        $newLog->EmployeeID = $EmployeeID;
        $newLog->LogType = $logType;
        $newLog->NewValue = $newValue;
        $newLog->LogText = $logText;

        $newLog->StartDate = date("Y-m-d H:i:s");

        $lastLogQ = self::where(["EmployeeID" => $EmployeeID, "LogType" => $logType])->whereNotNull("StartDate")->orderBy("LogDate", "DESC");
        if ($lastLogQ->count() > 0) {
            $lastLog = $lastLogQ->first();
            $EndDate = date("Y-m-d H:i:s");
            $lastLog->ResulationTime = ceil((strtotime($EndDate) - strtotime($lastLog->StartDate)) / 60);
            $newLog->OldValue = $lastLog->NewValue;
            $lastLog->EndDate = $EndDate;
            $lastLog->save();
        }

        $LogStatus = $newLog->save();
        return $LogStatus ? $newLog->id : false;

    }
}
