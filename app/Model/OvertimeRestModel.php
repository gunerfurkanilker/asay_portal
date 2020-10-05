<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OvertimeRestModel extends Model
{
    protected $primaryKey = "id";
    protected $table = "OvertimeRest";

    public function getRemainingOvertimeRest($request)
    {
        OvertimeRestModel::where(['EmployeeID' => $request->EmployeeID, 'Active' => 1])->first();

    }

}
