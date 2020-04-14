<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PaymentModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = "Payment";
    protected $guarded = [];
    public $timestamps =false;
    protected $appends = [
        "AdditionalPayment",
        "Currency",
        "PayPeriod",
        "PayMethod"
    ];

    public static function addSalary($request,$id)
    {
        $employee = EmployeeModel::find($id);

            $salary = self::create([
                'Pay' => $request['pay'],
                'CurrencyID' => $request['currency'],
                'ExpireDate' => new Carbon($request['expiredate']),
                'PayPeriodID' => $request['payperiod'],
                'PayMethodID' => $request['paymethod'],
                'LowestPayID' => $request['lowestpay'],
            ]);

            $employee->PaymentID = $salary->Id;
            return $salary->fresh();
    }

    public static function editSalary($request,$salaryId)
    {
        $salary = PaymentModel::find($salaryId);

        $salary->Pay = $request['pay'];
        $salary->CurrencyID = $request['currency'];
        $salary->ExpireDate = new Carbon($request['expiredate']);
        $salary->PayPeriodID = $request['payperiod'];
        $salary->PayMethodID = $request['paymethod'];
        $salary->LowestPayID = $request['lowestpay'];

        $salary->save();

        return $salary->fresh();
    }

   public function getAdditionalPaymentAttribute()
    {
        $additionalPayments = $this->hasMany(AdditionalPaymentModel::class,"PaymentID","Id");
        return $additionalPayments->where("Active","1")->get();
    }

    public function getCurrencyAttribute()
   {
       $currency = $this->hasOne(CurrencyModel::class,"Id","CurrencyID");
       return $currency->where("Active",1)->first();
   }

   public function getPayPeriodAttribute()
   {
       $payPeriod = $this->hasOne(PayPeriodModel::class,"Id","PayPeriodID");
       return $payPeriod->where("Active",1)->first()->toArray();
   }

   public function getPayMethodAttribute()
   {
       $payMethod = $this->hasOne(PayMethodModel::class,"Id","PayMethodID");
       return $payMethod->where("Active",1)->first()->toArray();
   }

}
