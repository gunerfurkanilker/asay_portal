<?php


namespace App\Http\Controllers\Api\Common;


use App\Http\Controllers\Api\ApiController;
use App\Model\NotificationsModel;
use Illuminate\Http\Request;

class NotificationController extends  ApiController
{

    public function getAmountOfNotifications(){

    }

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

        $notificationsQ = NotificationsModel::where(['Active' => 1, 'EmployeeID' => $request->Employee]);

        $notifications['TotalCount'] = $notificationsQ->count();

        $notifications['Expense'] = $notificationsQ->where(['ObjectType' => 1])->get();
        $notifications['ExpenseCount'] = $notificationsQ->where(['ObjectType' => 1])->count();

        $notifications['AdvancePayment'] = $notificationsQ->where(['ObjectType' => 2])->get();
        $notifications['AdvancePaymentCount'] = $notificationsQ->where(['ObjectType' => 2])->count();

        $notifications['Permit'] = $notificationsQ->where(['ObjectType' => 3])->get();
        $notifications['PermitCount'] = $notificationsQ->where(['ObjectType' => 3])->count();

        $notifications['Overtime'] = $notificationsQ->where(['ObjectType' => 4])->get();
        $notifications['OvertimeCount'] = $notificationsQ->where(['ObjectType' => 4])->count();

        $notifications['ITSupport'] = $notificationsQ->where(['ObjectType' => 11])->get();
        $notifications['ITSupportCount'] = $notificationsQ->where(['ObjectType' => 11])->count();

        $notifications['CarNotify'] = $notificationsQ->where(['ObjectType' => 12])->get();
        $notifications['CarNotifyCount'] = $notificationsQ->where(['ObjectType' => 12])->count();


        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $notifications
        ],200);

    }


}
