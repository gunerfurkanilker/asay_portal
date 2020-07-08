<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PermitModel extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'Permits';
    public $timestamps = false;

    public static function createPermit($req){

        $insertData = [];
        $insertData['kind']             = $req['kind'];
        $insertData['description']      = $req['description'];
        $insertData['start_date']       = $req['startDate'];
        $insertData['end_date']         = $req['endDate'];
        $insertData['total_day']        = $req['totalDay'];
        $insertData['duty_transferee']  = $req['dutyTransferee'];
        $insertData['transfer_date']    = $req['transferDate'];

        $newPermit = new PermitModel();

        $newPermit->EmployeeID = $req['employeeId'];
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
