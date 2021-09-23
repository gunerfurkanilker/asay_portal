<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TrainingModel extends Model
{
    //
    protected $table = "Trainings";
    protected $appends = [
        "Category",
        "Company"
    ];
    protected $guarded = [];

    public static function saveISGCompany($request){

        $companyInstance = TrainingCompanyModel::firstOrNew([
            'id' => $request->id
        ]);

        $companyInstance->Name = $request->Name;
        $companyInstance->Description = $request-> Description;

        $result = $companyInstance->save();

        return $result;


    }

    public static function saveTraining($request){


        $trainingInstance = TrainingModel::firstOrNew([
            'CategoryID' => $request->CategoryID,
            'CompanyID' => $request->CompanyID
        ]);

        $trainingInstance->CategoryID = $request->CategoryID;
        $trainingInstance->StartDate = $request->StartDate;
        $trainingInstance->ExpireDate = $request->ExpireDate;
        $trainingInstance->CompanyID = $request->CompanyID;

        $result = $trainingInstance->save();

        return $result;

    }

    public function getCategoryAttribute(){
        $category = $this->hasOne(TrainingCategoryModel::class,"id","CategoryID");
        if ($category)
        {
            $category = $category->where("Active",1)->first();
            if ($category)
                return $category;
            else
                return null;
        }
        else
            return null;
    }

    public function getCompanyAttribute(){
        $company = $this->hasOne(TrainingCompanyModel::class,"id","CompanyID");
        if ($company)
        {
            $company = $company->where("Active",1)->first();
            if ($company)
                return $company;
            else
                return null;
        }
        else
            return null;
    }

}
