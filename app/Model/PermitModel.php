<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PermitModel extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'Permits';
    public $timestamps = false;

    public static function createPermit($req)
    {

        $user = UserModel::find( UserTokensModel::where('user_token',$req['token']) -> first() -> user_id );

        $newPermit = new PermitModel();

        $newPermit->EmployeeID = $user->EmployeeID;
        $newPermit->kind = $req['kind'];
        $newPermit->description = $req['description'];
        $newPermit->start_date = $req['startDate'];
        $newPermit->end_date = $req['endDate'];
        $newPermit->total_day = $req['totalDay'];
        $newPermit->duty_transferee = $req['dutyTransferee'];
        $newPermit->transfer_date = $req['transferDate'];
        return $newPermit->save() ? true : false;
    }

    public static function getRemainingDaysYearlyPermit($req)
    {
        $user = UserModel::find($req->userId);
        //Yıllık İzin için bu kontrolü yapıyoruz ileride diğer izin tipleri için de bu tarz kontroller yapılabilir.
        $permitCounts = self::select(DB::raw('SUM(total_day) as total_days,SUM(total_hours) as total_hours'))
            ->where('EmployeeID',$user->EmployeeID)
            ->where('kind',12)->first();

        if ($permitCounts->total_hours%8 > 0)
        {
            $leftOverDays = floor($permitCounts->total_hours/8);
        }


        $data['hoursUsed'] = $permitCounts->total_hours % 8;
        $data['daysUsed'] = $permitCounts->total_days + $leftOverDays;
        $data['daysLeft'] = PermitKindModel::where('id',12)->first()
        ->dayLimitPerYear ?
            PermitKindModel::find(12)->dayLimitPerYear - $data['daysUsed'] :
            PermitKindModel::find(12)->dayLimitPerRequest - $data['daysUsed'] ;

        if ($data['daysLeft'] != 0)
            $data['hoursLeft'] = 8 - $data['hoursUsed'];
        else
            $data['hoursLeft'] = 0;
        if ($data['hoursLeft'] > 0 && $data['daysLeft'] == 1)
            $data['daysLeft'] = 0 ;

        return $data;








    }


}
