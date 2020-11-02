<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class NotificationsModel extends Model
{
    protected $table = "Notifications";


    public static function saveNotification($employeeID,$objectType,$objectId,$objectHeader,$content,$URL)
    {

        $notification = new NotificationsModel();
        if ($employeeID == null)
            return false;
        $notification->EmployeeID = $employeeID;
        $notification->ObjectType = $objectType;
        $notification->ObjectID = $objectId;
        $notification->ObjectHeader = $objectHeader;
        $notification->Content = $content;
        $notification->URL = $URL;


        if ($notification->save())
        {
            return true;
        }
        else
            return false;



    }

}
