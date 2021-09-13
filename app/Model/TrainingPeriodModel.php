<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TrainingPeriodModel extends Model
{
    //
    protected $table = "TrainingPeriods";
    public $timestamps = false;

    public static function getPeriodOfTraining($request){

        $employeeSGKRegistryNumber = SocialSecurityInformationModel::where(['EmployeeID' => $request->EmployeeID])->first();
        $employeePosition = EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $request->EmployeeID])->first();

        if (!$employeeSGKRegistryNumber)
            return ['status' => false, 'message' => 'Çalışana ait SGK Sicil No Bilgisi Bulunamadı', 'data' => null];

        if (!$employeePosition || !$employeePosition->OrganizationID)
        {
            return ['status' => false, 'message' => 'Çalışanın organizasyon bilgisi bulunamadı', 'data' => null];
        }



        $trainingPeriod = TrainingPeriodModel::where(['TrainingCategoryID' =>  $request->CategoryID])
            ->where("SGKRegistryIDs","like","%$employeeSGKRegistryNumber%")
            ->where("OrganizationIDs","like","%$employeePosition->OrganizationID%")
            ->first();

        if(!$trainingPeriod)
            return ['status' => false, 'message' => 'Bu eğitime ait periyot bulunamadı, lütfen tamamlanma tarihini elle giriniz', 'data' => null];

        $trainingExpireDate = date("Y-m-d",strtotime("+$trainingPeriod->Period months", strtotime($request->TrainingStartDate)));


        return ['status' => true, 'message' => 'Bu eğitime ait periyot '.$trainingPeriod->Period . " aydır", 'data' => $trainingExpireDate];


    }

}
