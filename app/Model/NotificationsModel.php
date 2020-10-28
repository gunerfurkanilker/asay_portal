<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class NotificationsModel extends Model
{
    protected $table = "Notifications";


    public static function saveNotification($employeeID,$objectType,$objectId,$objectHeader,$content)
    {

        $notification = new NotificationsModel();

        $notification->EmployeeID = $employeeID;
        $notification->ObjectType = $objectType;
        $notification->ObjectID = $objectId;
        $notification->ObjectHeader = $objectHeader;
        $notification->Content = $content;

        switch ($objectType)
        {
            case 1:
                $notification->URL = "URL1";
                break;
            case 2:
                $notification->URL = "URL2";
                break;
            case 3:
                $notification->URL = "URL3";
                break;
            case 4:
                $notification->URL = "URL4";
                break;
        }


        if ($notification->save())
        {
            return true;
        }
        else
            return false;



    }

}
