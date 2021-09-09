<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class EmployeeTrainingModel extends Model
{
    //
    protected $table = "EmployeeTrainings";
    protected $appends = [
        "Training",
        "Status",
        "Result"
    ];
    protected $guarded = [];

    public static function saveEmployeeTraining($request){

        $trainingInstance = EmployeeTrainingModel::firstOrNew([
            'id' => $request->id
        ]);

        $trainingInstance->TrainingID = $request->TrainingID;
        $trainingInstance->CreateDate = $trainingInstance->CreateDate ? "" : date("Y-m-d H:i:s");
        $trainingInstance->StartDate = $request->StartDate;
        $trainingInstance->ExpireDate = $request->ExpireDate;
        $trainingInstance->StatusID = $request->StatusID;
        $trainingInstance->ResultID = $request->ResultID;
        $trainingInstance->Grade = $request->Grade;
        $trainingInstance->EmployeeID = $request->EmployeeID;

        $result = $trainingInstance->save();

        return $result;

    }

    public static function getTrainings($filters,$employeeID){

        /*
         *
         * Front-endden filters değişkeni array şeklinde tablelardaki kolon isimleri ile uyumlu olmak zorunda
         *
         * */

        $trainingsQ = self::where("Active",1);

        $employeeID ? $trainingsQ->where('EmployeeID',$employeeID) : '';
        $arrayOfString = [];
        if ($filters && count($filters) > 0 )
            foreach ($filters as $key => $filter)
                $trainingsQ->where($key, $filter);


        return $trainingsQ->get();

    }

    public function getTrainingAttribute(){
        $training = $this->hasOne(TrainingModel::class,"id","TrainingID");
        if ($training)
        {
            $training = $training->where("Active",1)->first();
            if ($training)
                return $training;
            else
                return null;
        }
        else
            return null;
    }

    public function getStatusAttribute(){
        $status = $this->hasOne(TrainingStatusModel::class,"id","StatusID");
        if ($status)
        {
            $status = $status->where("Active",1)->first();
            if ($status)
                return $status;
            else
                return null;
        }
        else
            return null;
    }

    public function getResultAttribute(){
        $result = $this->hasOne(TrainingResultModel::class,"id","ResultID");
        if ($result)
        {
            $result = $result->where("Active",1)->first();
            if ($result)
                return $result;
            else
                return null;
        }
        else
            return null;
    }

//    public function employee()
//    {
//        return $this->belongsTo(EmployeeModel::class,'EmployeeID');
//    }
//
//    public function trainings()
//    {
//        return $this->belongsTo(Trai)
//    }



}
