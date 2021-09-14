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
        else
            $employeeSGKRegistryNumber = $employeeSGKRegistryNumber->SSIRecord;

        if (!$employeePosition || !$employeePosition->OrganizationID)
            return ['status' => false, 'message' => 'Çalışanın organizasyon bilgisi bulunamadı', 'data' => null];


        $training = TrainingModel::find($request->TrainingID);

        $trainingPeriodQ = TrainingPeriodModel::where(['TrainingCategoryID' =>  $training->Category->id])
            ->where("SGKRegistryIDs","like","%$employeeSGKRegistryNumber%")
            ->where("OrganizationIDs","like","%$employeePosition->OrganizationID%");


         if($request->CompanyID != 1 && $request->CompanyID != 2 && $training->Category->id == 1)
         {
             $trainingPeriodQ->where("ExternalCompany",1);
         }

         $trainingPeriod = $trainingPeriodQ->first();

        if(!$trainingPeriod)
            return ['status' => false, 'message' => 'Bu eğitime ait periyot bulunamadı, lütfen tamamlanma tarihini elle giriniz', 'data' => null];

        $trainingExpireDate = date("Y-m-d",strtotime("+$trainingPeriod->Period months", strtotime($request->TrainingStartDate)));


        return ['status' => true, 'message' => 'Bu eğitime ait periyot '.$trainingPeriod->Period . " aydır", 'data' => $trainingExpireDate];


    }


}
