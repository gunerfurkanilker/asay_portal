<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PaymentModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = "Payment";
    protected $guarded = [];
    protected $appends = [

    ];

    public static function addSalary($request,$id)
    {

        $employee = EmployeeModel::find($id);

        if (!is_null($employee))
        {
            $salary = self::create([
                'Pay' => $request['pay'],
                'CurrencyID' => $request['currency'],
                'ExpireDate' => $request['expiredate'],
                'PayPeriodID' => $request['payperiod'],
                'PayMethodID' => $request['paymethod'],
                'LowestPayID' => $request['lowestpay']
            ]);

            $employee->PaymentID = $salary->Id;
            return $employee->fresh();
        }

        else{
            return false;
        }


    }

}
