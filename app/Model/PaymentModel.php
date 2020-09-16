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

    public static function checkCurrentPayment($employeeID){

        $payments = self::where('EmployeeID',$employeeID)->get();

        foreach ($payments as $payment)
        {
            if ($payment->EndDate == null)
            {
                return $payment;
            }
        }

    }

    public static function getPaymentInformationFields()
    {

        $data = [];
        $data['Currencies'] = CurrencyModel::all();
        $data['PayPeriods'] = PayPeriodModel::all();
        $data['PayMethods'] = PayMethodModel::all();
        $data['AdditionalPayments'] = AdditionalPaymentTypeModel::all();

        return $data;
    }

    public static function savePayment($request)
    {
        $employee = EmployeeModel::find($request['EmployeeID']);
        $additionalPayments = $request['AdditionalPayments'];
        if ($request['PaymentID'] == null || !isset($request['PaymentID'])){
            $currentPayment = self::checkCurrentPayment($request['EmployeeID']);


            $salary = self::create([

                'EmployeeID' => $request['EmployeeID'],
                'Pay' => $request['Pay'],
                'CurrencyID' => $request['CurrencyID'],
                'StartDate' => $request['StartDate'],
                'PayPeriodID' => $request['PayPeriod'],
                'PayMethodID' => $request['PayMethod'] ? 2:1,
                'LowestPayID' => $request['LowestPay'] ? 1:0,
            ]);

            if ($currentPayment != null)
            {
                $currentPayment->EndDate = $salary->StartDate;
                $currentPayment->save();
            }

            $additionalPayments = $request['AdditionalPayments'];

            foreach ($additionalPayments as $additionalPayment)
            {
                AdditionalPaymentModel::create([
                    'Pay' => $additionalPayment['Pay'],
                    'PayPeriodID' => $additionalPayment['PayPeriodID'],
                    'PayMethodID' => $additionalPayment['PayMethodID'],
                    'AdditionalPaymentTypeID' => $additionalPayment['AdditionalPaymentTypeID'],
                    'PaymentID' => $salary->Id,
                    'AddPayroll' => $additionalPayment['AddPayroll'] ? 1 : 0,
                    'CurrencyID' => $additionalPayment['CurrencyID'],
                    'Description' => $additionalPayment['Description']
                ]);
            }


            $employee->PaymentID = $salary->Id;
            $employee->save();
        }
        else
        {

            $salary = PaymentModel::find($request['PaymentID']);

            $salary->Pay = $request['Pay'];
            $salary->CurrencyID = $request['CurrencyID'];
            $salary->StartDate = $request['StartDate'];
            $salary->PayPeriodID = $request['PayPeriod'];
            $salary->PayMethodID = $request['PayMethod'] ? 2 : 1;
            $salary->LowestPayID = $request['LowestPay'] ? 1 : 0 ;


            foreach ($additionalPayments as $additionalPayment)
            {
                $tempPayment = AdditionalPaymentModel::find($additionalPayment['Id']);

                $tempPayment->Pay           = $additionalPayment['Pay'];
                $tempPayment->CurrencyID    = $additionalPayment['CurrencyID'];
                $tempPayment->PayMethodID   = $additionalPayment['PayMethodID'];
                $tempPayment->AddPayroll    = $additionalPayment['AddPayroll'];
                $tempPayment->PayPeriodID   = $additionalPayment['PayPeriodID'];
                $tempPayment->Description   = $additionalPayment['Description'];

                $tempPayment->save();

            }

            $salary->save();

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

    public static function getAdditionalPayments($paymentID)
    {
        $additionalPaymentsOfPayment = AdditionalPaymentModel::where('PaymentID', $paymentID)->get();

        if ($additionalPaymentsOfPayment != null)
            return $additionalPaymentsOfPayment;
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
