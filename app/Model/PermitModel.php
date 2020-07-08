<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PermitModel extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'Permits';
    public $timestamps = false;

    public static function createPermit($req){

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

}
