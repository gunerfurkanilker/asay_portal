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

    public static function saveTraining($request){

        $trainingInstance = TrainingModel::firstOrNew([
            'id' => $request->TrainingID
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
