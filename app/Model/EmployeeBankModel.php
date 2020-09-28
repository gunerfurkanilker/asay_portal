<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class EmployeeBankModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = 'EmployeeBank';
    public $timestamps = false;
    protected $guarded = [
        'AccountType'
    ];

    public static function saveEmployeeBank($request)
    {

        $paymentAccount          = EmployeeBankModel::where(['EmployeeID' => $request->EmployeeID, 'AccountTypeID' => 1])->first();
        $personalAccount         = EmployeeBankModel::where(['EmployeeID' => $request->EmployeeID, 'AccountTypeID' => 2])->first();
        $jobAllowanceAccount     = EmployeeBankModel::where(['EmployeeID' => $request->EmployeeID, 'AccountTypeID' => 3])->first();

        if ($paymentAccount == null)
        {
            $paymentAccount = new EmployeeBankModel();
            $paymentAccount->AccountTypeID = 1;
            $paymentAccount->EmployeeID = $request->EmployeeID;
        }
        if ($personalAccount == null)
        {
            $personalAccount = new EmployeeBankModel();
            $personalAccount->AccountTypeID = 2;
            $personalAccount->EmployeeID = $request->EmployeeID;
        }
        if ($jobAllowanceAccount == null)
        {
            $jobAllowanceAccount = new EmployeeBankModel();
            $jobAllowanceAccount->AccountTypeID = 3;
            $jobAllowanceAccount->EmployeeID = $request->EmployeeID;
        }

        $paymentAccount->BankName   = ((object)$request->PaymentAccount)->BankName;
        $paymentAccount->AccountNo  = ((object)$request->PaymentAccount)->AccountNo;
        $paymentAccount->IBAN       = ((object)$request->PaymentAccount)->IBAN;

        $personalAccount->BankName   = ((object)$request->PersonalAccount)->BankName;
        $personalAccount->AccountNo  = ((object)$request->PersonalAccount)->AccountNo;
        $personalAccount->IBAN       = ((object)$request->PersonalAccount)->IBAN;

        $jobAllowanceAccount->BankName   = ((object)$request->JobAllowanceAccount)->BankName;
        $jobAllowanceAccount->AccountNo  = ((object)$request->JobAllowanceAccount)->AccountNo;
        $jobAllowanceAccount->IBAN       = ((object)$request->JobAllowanceAccount)->IBAN;

        return $paymentAccount->save() && $personalAccount->save() && $jobAllowanceAccount->save() ? true : false;

    }

    public function getAccountTypeAttribute(){
        $accountType = $this->hasOne(BankAccountTypeModel::class,'id','AccountTypeID');
        if ($accountType)
        {
            $accountType->first();
        }
        else
            return null;
    }

}
