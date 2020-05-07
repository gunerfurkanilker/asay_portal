<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PaymentModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = "Payment";
    protected $guarded = [];
    public $timestamps = false;
    protected $appends = [
        "AdditionalPayments",
        "Currency",
        "PayPeriod",
        "PayMethod"
    ];

    public static function getPaymentInformationFields()
    {

        $data = [];
        $data['Currencies'] = CurrencyModel::all();
        $data['PayPeriods'] = PayPeriodModel::all();
        $data['PayMethods'] = PayMethodModel::all();
        $data['AdditionalPayments'] = AdditionalPaymentModel::all();

        return $data;
    }

    public static function addPayment($request)
    {
        $employee = EmployeeModel::find($request['employeeid']);

        $salary = self::create([

            'EmployeeID' => $request['employeeid'],
            'Pay' => $request['pay'],
            //'Description' => $request['description'],
            'CurrencyID' => $request['currencyid'],
            'StartDate' => new Carbon($request['startdate']),
            'EndDate' => new Carbon($request['enddate']),
            'PayPeriodID' => $request['payperiod'],
            'PayMethodID' => $request['paymethod'] ? 2:1,
            'LowestPayID' => $request['lowestpay'] ? 1:0,
        ]);

        if ($request['iscurrent'])
        {
            $employee->PaymentID = $salary->Id;
            $employee->save();
        }
        return $salary->fresh();
    }

    public static function editPayment($payment,$request)
    {
        $payment->EmployeeID = $request['employeeid'];
        $payment->Pay = $request['pay'];
        //$payment->Description = $request['description'];
        $payment->CurrencyID = $request['currencyid'];
        $payment->StartDate = new Carbon($request['startdate']);
        $payment->EndDate = new Carbon($request['enddate']);
        $payment->PayPeriodID = $request['payperiod'];
        $payment->PayMethodID = $request['paymethod'] ? 2:1;
        $payment->LowestPayID = $request['lowestpay'] ? 1:0;
        if ($payment->save())
            return $payment->fresh();
        else
            return false;
    }

    public static function deletePayment($id)
    {
        $position = PaymentModel::find($id);
        try
        {
            $position->delete();
            return true;
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }

    }

    public static function getSalaries($employeeId)
    {
        $salariesOfEmployee = PaymentModel::where('EmployeeID', $employeeId)->get();

        if ($salariesOfEmployee != null)
            return $salariesOfEmployee;
        else
            return false;
    }

    public function getAdditionalPaymentsAttribute()
    {
        $additionalPayments = $this->hasMany(AdditionalPaymentModel::class, "PaymentID", "Id");
        return $additionalPayments->where("Active", "1")->get();
    }

    public function getCurrencyAttribute()
    {
        $currency = $this->hasOne(CurrencyModel::class, "Id", "CurrencyID");
        return $currency->where("Active", 1)->first();
    }

    public function getPayPeriodAttribute()
    {
        $payPeriod = $this->hasOne(PayPeriodModel::class, "Id", "PayPeriodID");
        return $payPeriod->where("Active", 1)->first()->toArray();
    }

    public function getPayMethodAttribute()
    {
        $payMethod = $this->hasOne(PayMethodModel::class, "Id", "PayMethodID");
        return $payMethod->where("Active", 1)->first()->toArray();
    }

}
