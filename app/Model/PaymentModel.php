<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

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
        "PayMethod",
        "Pay",
        "StartDate",
        "EndDate",
        "Description",
    ];

    public static function checkCurrentPayment($employeeID)
    {

        $payments = self::where('EmployeeID', $employeeID)->get();

        foreach ($payments as $payment) {
            if ($payment->EndDate == null) {
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
        $employee = EmployeeModel::find($request->EmployeeID);
        $additionalPayments = $request->AdditionalPayments;
        if ($request->PaymentID == null || !isset($request->PaymentID)) {
            $currentPayment = self::checkCurrentPayment($request->EmployeeID);



            $salary = new PaymentModel();
            $salary->EmployeeID = $request->EmployeeID;
            $salary->Pay = $request->Pay;
            $salary->CurrencyID = $request->CurrencyID;
            $salary->StartDate = $request->StartDate;
            $salary->PayPeriodID = $request->PayPeriodID;
            $salary->PayMethodID = $request->PayMethodID;
            $salary->LowestPayID = $request->LowestPay ? 1 : 0;
            $salary->save();

            if ($currentPayment != null) {
                $currentPayment->EndDate = $salary->StartDate;
                $currentPayment->save();
            }

            $additionalPayments = $request->AdditionalPayments;

            $loggedUser = DB::table("Employee")->find($request->Employee);

            foreach ($additionalPayments as $additionalPayment) {

                $additionalPaymentNew = new AdditionalPaymentModel();
                $additionalPaymentNew->Pay = $additionalPayment['Pay'];
                $additionalPaymentNew->PayPeriodID = $additionalPayment['PayPeriodID'];
                $additionalPaymentNew->PayMethodID = $additionalPayment['PayMethodID'];
                $additionalPaymentNew->AdditionalPaymentTypeID = $additionalPayment['AdditionalPaymentTypeID'];
                $additionalPaymentNew->PaymentID = $salary->Id;
                $additionalPaymentNew->AddPayroll = $additionalPayment['AddPayroll'] ? 1 : 0;
                $additionalPaymentNew->CurrencyID = $additionalPayment['CurrencyID'];
                $additionalPaymentNew->Description = $additionalPayment['Description'];

                $additionalPaymentNew->save();

                LogsModel::setLog($request->Employee,$additionalPaymentNew->Id,15,43,"","",$loggedUser->UsageName . ' ' . $loggedUser->LastName . " adlı çalışan, " . $employee->UsageName . ' ' . $employee->LastName . " adındaki çalışana ek ödeme bilgisi ekledi","","","","","");
            }


            $employee->PaymentID = $salary->Id;
            $employee->save();


            LogsModel::setLog($request->Employee,$salary->Id,15,39,"","",$loggedUser->UsageName . ' ' . $loggedUser->LastName . " adlı çalışan, " . $employee->UsageName . ' ' . $employee->LastName . " adındaki çalışana maaş bilgisi ekledi","","","","","");

        } else {

            $salary = PaymentModel::find($request->PaymentID);

            $salary->Pay = $request->Pay;
            $salary->CurrencyID = $request->CurrencyID;
            $salary->StartDate = $request->StartDate;
            $salary->PayPeriodID = $request->PayPeriod;
            $salary->PayMethodID = $request->PayMethod ? 2 : 1;
            $salary->LowestPayID = $request->LowestPay ? 1 : 0;

            $allAdditionalPaymentTypeIDs = [1, 2, 3, 4, 5];
            $currentAdditionalPaymentIDs = [];
            foreach ($additionalPayments as $additionalPayment) {
                if (isset($additionalPayment['Id']))
                    $tempPayment = AdditionalPaymentModel::find($additionalPayment['Id']);
                else
                    $tempPayment = new AdditionalPaymentModel();

                $tempPayment->Pay = $additionalPayment['Pay'];
                $tempPayment->PaymentID = $salary->Id;
                $tempPayment->CurrencyID = $additionalPayment['CurrencyID'];
                $tempPayment->PayMethodID = $additionalPayment['PayMethodID'];
                $tempPayment->AdditionalPaymentTypeID = $additionalPayment['AdditionalPaymentTypeID'];
                $tempPayment->AddPayroll = $additionalPayment['AddPayroll'];
                $tempPayment->PayPeriodID = $additionalPayment['PayPeriodID'];
                $tempPayment->Description = $additionalPayment['Description'];

                array_push($currentAdditionalPaymentIDs, $additionalPayment['AdditionalPaymentTypeID']);

                $loggedUser = DB::table("Employee")->find($request->Employee);
                $dirtyFields = $tempPayment->getDirty();
                foreach ($dirtyFields as $field => $newdata) {
                    $olddata = $tempPayment->getOriginal($field);
                    if ($olddata != $newdata) {
                        LogsModel::setLog($request->Employee,$tempPayment->Id,15,40,$olddata,$newdata,$loggedUser->UsageName . ' ' . $loggedUser->LastName . " adlı çalışan, " . $employee->UsageName . ' ' . $employee->LastName . " adındaki çalışanın maaş ek ödeme bilgisini düzenledi","","","",$field,"");
                    }
                }

                $tempPayment->save();
            }

            $diffs = array_diff($allAdditionalPaymentTypeIDs, $currentAdditionalPaymentIDs);

            foreach ($diffs as $diff) {
                $tempAPayment = AdditionalPaymentModel::where(['PaymentID' => $salary->Id, 'AdditionalPaymentTypeID' => $diff])->first();
                if (!$tempAPayment)
                    continue;
                $tempAPayment->Active = 0;
                $tempAPayment->save();
            }


            $loggedUser = DB::table("Employee")->find($request->Employee);
            $dirtyFields = $salary->getDirty();
            foreach ($dirtyFields as $field => $newdata) {
                $olddata = $salary->getOriginal($field);
                if ($olddata != $newdata) {
                    LogsModel::setLog($request->Employee,$employee->Id,15,40,$olddata,$newdata,$loggedUser->UsageName . ' ' . $loggedUser->LastName . " adlı çalışan, " . $employee->UsageName . ' ' . $employee->LastName . " adındaki çalışanın maaş bilgisini düzenledi","","","",$field,"");
                }
            }

            $salary->save();




        }
        return $salary;
    }

    public static function editPayment($payment, $request)
    {
        $payment->EmployeeID = $request['employeeid'];
        $payment->Pay = $request['pay'];
        //$payment->Description = $request['description'];
        $payment->CurrencyID = $request['currencyid'];
        $payment->StartDate = new Carbon($request['startdate']);
        $payment->EndDate = new Carbon($request['enddate']);
        $payment->PayPeriodID = $request['payperiod'];
        $payment->PayMethodID = $request['paymethod'] ? 2 : 1;
        $payment->LowestPayID = $request['lowestpay'] ? 1 : 0;
        if ($payment->save())
            return $payment->fresh();
        else
            return false;
    }

    public static function deletePayment($id)
    {
        $payment = PaymentModel::find($id);

        $payment->Active = 0;

        $additionalPayments = AdditionalPaymentModel::where('PaymentID',$id)->get();

        foreach($additionalPayments as $additionalPayment)
        {
            $additionalPayment->Active = 0;
            $additionalPayment->save();
        }

        return $payment->save();


    }

    public static function getSalaries($employeeId)
    {
        $salariesOfEmployee = PaymentModel::where(['EmployeeID' => $employeeId ,'Active' => 1])->get();

        if ($salariesOfEmployee->count()>0)
            return $salariesOfEmployee;
        else
            return false;
    }

    public static function getAdditionalPayments($paymentID)
    {
        $additionalPaymentsOfPayment = AdditionalPaymentModel::where('PaymentID', $paymentID)->get();

        if ($additionalPaymentsOfPayment->count()>0)
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
        $data = $payPeriod->where("Active", 1)->first();
        if($data)
            return $data->toArray();
        else
            return null;
    }

    public function getPayMethodAttribute()
    {
        $payMethod = $this->hasOne(PayMethodModel::class, "Id", "PayMethodID");
        $data = $payMethod->where("Active", 1)->first();
        if($data)
            return $data->toArray();
        else
            return null;
    }

    public function setPayAttribute($value)
    {
        $this->attributes['Pay'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getPayAttribute($value)
    {
        try {
            return $this->attributes['Pay'] !== null || $this->attributes['Pay'] != '' ? Crypt::decryptString($this->attributes['Pay']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setStartDateAttribute($value)
    {
        $this->attributes['StartDate'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getStartDateAttribute($value)
    {
        try {
            return $this->attributes['StartDate'] !== null || $this->attributes['StartDate'] != '' ? Crypt::decryptString($this->attributes['StartDate']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setEndDateAttribute($value)
    {
        $this->attributes['EndDate'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getEndDateAttribute($value)
    {
        try {
            return $this->attributes['EndDate'] !== null || $this->attributes['EndDate'] != '' ? Crypt::decryptString($this->attributes['EndDate']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }

    public function setDescriptionAttribute($value)
    {
        $this->attributes['Description'] = $value !== null || $value != '' ? Crypt::encryptString($value) : null;
    }
    public function getDescriptionAttribute($value)
    {
        try {
            return $this->attributes['Description'] !== null || $this->attributes['Description'] != '' ? Crypt::decryptString($this->attributes['Description']) : null;
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }



}
