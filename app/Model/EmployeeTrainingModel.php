<?php

namespace App\Model;

use App\Library\Asay;
use Illuminate\Database\Eloquent\Model;

class EmployeeTrainingModel extends Model
{
    //
    protected $table = "EmployeeTrainings";
    protected $appends = [
        "Training",
        "Status",
        "Result",
        "Employee",
        "CreateTypeName"
    ];
    protected $guarded = [];


    public static function mailToIsgNewEmployee($request){

        $employee = EmployeeModel::find($request->EmployeeID);

        $mailData = [
            'employee' => $employee,
            'mailContext' => "Aşağıda bilgileri olan personel işe giriş yapmıştır, lütfen ilgili personelin eğitimlerini sisteme giriniz"
        ];
        $mailTable = view('mails.isg-new-employee', $mailData);
        //TODO İSG Gru

        //        Asay::sendMail("isg-ms@ms.asay.com.tr,ilker.guner@asay.com.tr","","Yeni Personel Kaydı","$mailTable","aSAY Group","","","");

        Asay::sendMail("isg-ms@ms.asay.com.tr","","Yeni Personel Kaydı","$mailTable","aSAY Group","","","");
    }

    public static function isTrainingExistAtEmployee($request){

        $employeesTrainings = self::where(['Active' => 1, 'EmployeeID' => $request->EmployeeID])->get();

        $trainingCategory = TrainingModel::find($request->TrainingID);
        $trainingCategory = $trainingCategory->CategoryID;
        $status = true;
        foreach ($employeesTrainings as $employeesTraining)
        {
            if($employeesTraining->Training->CategoryID == $trainingCategory)
                $status = false;
        }

        return $status;

    }

    public static function sendExpiredTrainingsMailToIsgEmployees(){
        $fifteenDaysLaterDate = date("Y-m-d",strtotime("+15 days"));
        $isgTrainingsExpireRecords = EmployeeTrainingModel::where("Active",1)
            ->whereBetween("ExpireDate",[date("Y-m-d"),$fifteenDaysLaterDate])
            ->get();
        if(count($isgTrainingsExpireRecords) < 1)
            return;
        $mailData = [
            'trainings' => $isgTrainingsExpireRecords ,
            'mailContext' => "Eğitim geçerlilik tarihi süresininin bitimine 15 günden az kalmış kayıtlar aşağıdaki gibidir"
        ];
        $mailTable = view('mails.isg-expire-trainings', $mailData);
        foreach ($isgTrainingsExpireRecords as $isgTrainingsExpireRecord)
        {
            $isgTrainingsExpireRecord->StatusID = 3;// Süresi Yaklaşıyor yapıldı;
            $isgTrainingsExpireRecord->save();
        }

        Asay::sendMail("isg-ms@ms.asay.com.tr","","Geçerlilik süresinin dolmasına 15 gün kalmış eğitimler","$mailTable","aSAY Group","","","");
    }

    public static function sendExpiredTrainingsMailToIsgEmployees2(){
        $isgTrainingsExpireRecords = EmployeeTrainingModel::where("Active",1)
            ->whereDate("ExpireDate","<=",date("Y-m-d"))
            ->get();
        if(count($isgTrainingsExpireRecords) < 1)
            return;
        $mailData = [
            'trainings' => $isgTrainingsExpireRecords,
            'mailContext' => "Eğitim geçerlilik tarihi süresi bitmiş kayıtlar aşağıdaki gibidir"
        ];
        $mailTable = view('mails.isg-expire-trainings', $mailData);
        foreach ($isgTrainingsExpireRecords as $isgTrainingsExpireRecord)
        {
            $isgTrainingsExpireRecord->StatusID = 2;// Süresi Dolmuş yapıldı;
            $isgTrainingsExpireRecord->save();
        }

        Asay::sendMail("isg-ms@ms.asay.com.tr","","Geçerlilik süresi bitmiş olan eğitimler","$mailTable","aSAY Group","","","");
    }

    public static function saveEmployeeTraining($request){


        $parent = 0;
        $root = 0;
        switch ($request->EditType){
            case 0:
                $trainingInstance = EmployeeTrainingModel::firstOrNew([
                    'id' => $request->id
                ]);

                break;
            case 1 || 2:
                $tempInstance = EmployeeTrainingModel::find($request->id);
                $parent = $tempInstance->id;
                $root = $tempInstance->Root + 1;
                $tempInstance->Active = 0;
                $tempInstance->save();
                $trainingInstance = new EmployeeTrainingModel();
                break;
            default:
                $trainingInstance = EmployeeTrainingModel::firstOrNew([
                    'id' => $request->id
                ]);
        }


        $trainingInstance->TrainingID = $request->TrainingID;
        $trainingInstance->CreateDate = $trainingInstance->CreateDate ? $trainingInstance->CreateDate : date("Y-m-d");
        $trainingInstance->Root = $trainingInstance->Root != 0 ? $trainingInstance->Root : $root;
        $trainingInstance->Parent = $trainingInstance->Parent != 0 ? $trainingInstance->Parent : $parent;
        $trainingInstance->TrainingDescription = $request->TrainingDescription;
        $trainingInstance->StartDate = $request->StartDate;
        $trainingInstance->ExpireDate = $request->ExpireDate;
        $trainingInstance->StatusID = $request->StatusID;
        $trainingInstance->ResultID = $request->ResultID;
        $trainingInstance->Grade = $request->Grade;
        $trainingInstance->EmployeeID = $request->EmployeeID;
        $trainingInstance->CreateType = $request->EditType;

        $result = $trainingInstance->save();

        return $result;

    }

    public static function deleteEmployeeTraining($request){
        $trainingInstance = EmployeeTrainingModel::firstOrNew([
            'id' => $request->id
        ]);
        $trainingInstance->TrainingID = $request->TrainingID;
    }

    public static function getTrainings($filters,$employeeID,$page = null,$rowPerPage = null,$active = 1){

        /*
         *
         * Front-endden filters değişkeni array şeklinde tablelardaki kolon isimleri ile uyumlu olmak zorunda
         *
         * */

        $trainingsQ = $active==1 ? self::where(['Active' => 1]) : self::whereIn("Active",[0,1]) ;

        if ($filters && count($filters) > 0 )
        {
            foreach ($filters as $key => $filter)
            {
                if (isset($filter['value']))
                {
                    switch ($filter['type'])
                    {
                        case 'where' :
                            $trainingsQ->where($filter['table'].".".$key, $filter['value']);
                            break;
                        case 'whereIn' :
                            $trainingsQ->whereIn($filter['table'].".".$key, $filter['value']);
                            break;
                        case 'whereMonth' && isset($filter['value']) :
                            $trainingsQ->whereMonth($filter['table'].".".$key, explode("-",$filter['value'])[1]);
                            $trainingsQ->whereYear($filter['table'].".".$key, explode("-",$filter['value'])[0]);
                            break;
                    }
                }

            }
        }
        $count = $trainingsQ->count();
        if (!is_null($page) && !is_null($rowPerPage)){
            $offset = ($page - 1)*$rowPerPage;
            $trainingsQ->offset($offset)->take($rowPerPage);
        }



        $data['trainings'] = $trainingsQ->get();
        $data['count'] = $count;

        return $data;

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

    public function getEmployeeAttribute(){
        $employee = $this->hasOne(EmployeeModel::class,"Id","EmployeeID");
        if ($employee)
        {
            $employee = $employee->where("Active",1)->first();
            if ($employee)
                return $employee;
            else
                return null;
        }
        else
            return null;
    }

    public function getCreateTypeNameAttribute(){
        switch ($this->attributes['CreateType'])
        {
            case 0:
                return "Yeni Kayıt / İlk Kayıt";
            case 1:
                return "Eğitim Yenileme";
            case 2:
                return "Eğitim Tekrarı";
            default:
                return "Bilinmiyor";
        }
    }


}
