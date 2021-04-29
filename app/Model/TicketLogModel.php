<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TicketLogModel extends Model
{
    protected $table = "TicketLog";
    public $timestamps = false;

    public static function setLog($TicketID, $logType,$EmployeeID, $newValue = null, $logText = null)
    {
        $newLog = new self();
        $newLog->TicketID = $TicketID;
        $newLog->LogType = $logType;
        $newLog->NewValue = $newValue;
        $newLog->LogText = $logText;
        $newLog->UserID = $EmployeeID;

        $newLog->StartDate = date("Y-m-d H:i:s");

        $lastLogQ = self::where(["TicketID" => $TicketID, "LogType" => $logType])->whereNotNull("StartDate")->orderBy("LogDate", "DESC");
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
