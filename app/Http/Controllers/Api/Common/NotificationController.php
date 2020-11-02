<?php


namespace App\Http\Controllers\Api\Common;


use App\Http\Controllers\Api\ApiController;
use App\Model\NotificationsModel;
use Illuminate\Http\Request;

class NotificationController extends  ApiController
{

    public function notificationRead(Request $request){

        $notification = NotificationsModel::find($request->NotificationID);

        $notification->Seen = 1;

        $notification->save();

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı'
        ],200);

    }

    public function getNotifications(Request $request)
    {

        $notifications = NotificationsModel::where(['Active' => 1,'ObjectType' => $request->ObjectType,'EmployeeID' => $request->Employee])->orderBy("created_at","desc")->get();

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $notifications
        ],200);



    }

    public function getNotificationsCount(Request $request)
    {

        $notificationsCountOfProcesses = NotificationsModel::selectRaw("ObjectType,count(id) as amount")->where(['Active' => 1, 'EmployeeID' => $request->Employee,'Seen' => 0])->groupBy("ObjectType")->get();
        $notificationsTotalCount = NotificationsModel::where(['Active' => 1, 'EmployeeID' => $request->Employee,'Seen' => 0])->count();
        $notificationsTotalCountOfProcesses = NotificationsModel::where(['Active' => 1, 'EmployeeID' => $request->Employee,'Seen' => 0])->whereIn("ObjectType",[1,2,3,4,11,12])->count();


        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $notificationsCountOfProcesses,
            'totalCount' => $notificationsTotalCount,
            'countOfProcess' => $notificationsTotalCountOfProcesses
        ],200);

    }

    public function deleteNotification(Request $request)
    {

        $notification = NotificationsModel::find($request->NotificationID);

        $notification->Active = 0;

        if($notification->save())
        {
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı'
            ],200);
        }
        else
        {
            return response([
                'status' => false,
                'message' => 'İşlem Başarısız'
            ],200);
        }



    }


}
