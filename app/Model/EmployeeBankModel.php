<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
        $paymentAccount->BranchNo   = ((object)$request->PaymentAccount)->BranchNo;

        $personalAccount->BankName   = ((object)$request->PersonalAccount)->BankName;
        $personalAccount->AccountNo  = ((object)$request->PersonalAccount)->AccountNo;
        $personalAccount->IBAN       = ((object)$request->PersonalAccount)->IBAN;
        $personalAccount->BranchNo   = ((object)$request->PersonalAccount)->BranchNo;

        $jobAllowanceAccount->BankName   = ((object)$request->JobAllowanceAccount)->BankName;
        $jobAllowanceAccount->AccountNo  = ((object)$request->JobAllowanceAccount)->AccountNo;
        $jobAllowanceAccount->IBAN       = ((object)$request->JobAllowanceAccount)->IBAN;
        $jobAllowanceAccount->BranchNo   = ((object)$request->JobAllowanceAccount)->BranchNo;

        $loggedUser = DB::table("Employee")->find($request->Employee);
        $employee = DB::table("Employee")->find($request->EmployeeID);
        LogsModel::setLog($request->Employee,$paymentAccount->Id,15,54,"","",$loggedUser->UsageName . ' ' . $loggedUser->LastName . " adlı çalışan, " . $employee->UsageName . ' ' . $employee->LastName . " adındaki çalışanın, banka bilgilerini düzenledi","","","","","");
        LogsModel::setLog($request->Employee,$personalAccount->Id,15,54,"","",$loggedUser->UsageName . ' ' . $loggedUser->LastName . " adlı çalışan, " . $employee->UsageName . ' ' . $employee->LastName . " adındaki çalışanın, banka bilgilerini düzenledi","","","","","");
        LogsModel::setLog($request->Employee,$jobAllowanceAccount->Id,15,54,"","",$loggedUser->UsageName . ' ' . $loggedUser->LastName . " adlı çalışan, " . $employee->UsageName . ' ' . $employee->LastName . " adındaki çalışanın, banka bilgilerini düzenledi","","","","","");


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
