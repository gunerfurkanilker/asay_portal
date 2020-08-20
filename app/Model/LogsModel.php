<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class LogsModel extends Model
{
    protected $table = "logs";

    public $timestamps = false;
    protected $fillable = ['ObjectId','ObjectType','LogType'];

    protected $hidden = [];
    protected $casts = [];
    protected $appends = [];


    public static function setLog($EmployeeID,$objectId="",$objectType="",$logType="",$oldValue,$newValue,$logText="",$resulationTime="",$startDate="",$endDate="",$otherOldValue="",$otherNewValue="")
    {
        $newLog = new LogsModel();
        $newLog->EmployeeID         = $EmployeeID;
        $newLog->ObjectId           = $objectId;
        $newLog->LogType            = $logType;
        $newLog->OldValue           = $oldValue;
        $newLog->OtherOldValue      = $otherOldValue;
        $newLog->NewValue           = $newValue;
        $newLog->OtherNewValue      = $otherNewValue;

        if($logText!="") $newLog->LogText = $logText;
        if($resulationTime!="") $newLog->ResulationTime = $resulationTime;
        $newLog->StartDate = date("Y-m-d H:i:s");
        if($endDate!="") $newLog->EndDate = $endDate;
        if($objectType!="") $newLog->ObjectType = $objectType;

        /*if($logType=="ST" && $oldValue==$newValue)
            return false;
        if($logType=="GROUP" && $oldValue==$newValue)
            return false;
        if($logType=="ORDER_TYPE" && $oldValue==$newValue)
            return false;
        if($logType=="RT" && $oldValue==$newValue)
            return false;*/

        /*if($logType=="ST")
        {
            AkislarModel::where(["id"=>$newLog->akis_id])->update(["last_status_date"=>$newLog->start_date]);
        }*/

        self::LastLogUpdate($objectType,$objectId,$logType);
        $LogStatus = $newLog->save();
        return $LogStatus ? $newLog->id : false;

    }

    public function SaveLogToFile($objectType=1,$objectId="",$url="",$code,$requesst="",$response="")
    {
        try{
            $logFile = new LogFileModel();
            $logFile->ObjectType    = $objectType;
            $logFile->ObjectId      = $objectId;
            $logFile->RequestUrl    = $url;
            $logFile->Code          = $code;
            $logFile->Request       = $requesst;
            $logFile->Response      = $response;
            $logFile->save();
        }
        catch (Exception $e) {
            $dosya = fopen("response/" . $code."_".$objectId.".txt", "a");
            fwrite($dosya, $requesst."->".$response . "\n");
            fclose($dosya);
        }
    }

    public function LastLogUpdate($objectType,$objectId = "",$logType="")
    {
        $LogsQ = LogsModel::where(["ObjectType"=>$objectType,"ObjectId"=>$objectId,"LogType"=>$logType])->whereNotNull("start_date")->orderBy("date","DESC");
        if($LogsQ->count()>0)
        {
            $Logs = $LogsQ->first();
            $EndDate = date("Y-m-d H:i:s");
            $Logs->resulation_time = ceil((strtotime($EndDate)-strtotime($Logs->start_date))/60);
            $Logs->end_date = $EndDate;
            $Logs->save();
        }

    }
}
