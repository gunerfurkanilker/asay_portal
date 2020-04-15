<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class EmployeeBankModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = 'EmployeeBank';

    public static function saveEmployeeBank($request, $employeeBank)
    {
        $employeeBank = self::find($employeeBank);

        if ($employeeBank != null) {

            $employeeBank->BankName = $request['bankname'];
            $employeeBank->AccountNo = $request['accountno'];
            $employeeBank->IBAN = $request['iban'];


            $employeeBank->save();

            return $employeeBank->fresh();
        }
        else
            return false;
    }

    public static function addEmployeeBank($request,$employee)
    {
        $drivingLicense = self::create([
            'BankName' => $request['bankname'],
            'AccountNo' => $request['accountno'],
            'IBAN' => $request['iban']
        ]);

        if ($drivingLicense != null)
        {
            $employee->DrivingLicenceID = $drivingLicense->Id;
            $employee->save();
            return $drivingLicense;
        }

        else
            return false;
    }
}
