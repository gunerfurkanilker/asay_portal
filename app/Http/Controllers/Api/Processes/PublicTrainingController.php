<?php

namespace App\Http\Controllers\Api\Processes;

use Illuminate\Http\Request;
use App\Model\EmployeeTrainingModel;
use App\Model\EmployeeModel;
use App\Model\TrainingCompanyModel;
use App\Model\TrainingModel;
use App\Model\TrainingResultModel;
use App\Model\TrainingStatusModel;
use Illuminate\Support\Facades\DB;

class PublicTrainingController extends \App\Http\Controllers\Controller
{

    public function getTrainingByID($id){

             $employeeTrainings = EmployeeTrainingModel::Where(["EmployeeID" => $id])->get();

                  return response([
                         'status' => true,
                         'data' => $employeeTrainings
                     ],200);

    }
/*
        public static function sendMail($to,$title="")
        {
            Mail::send([], [], function ($email) use($to,$title) {
                if($title==""){
                    $title="My QR CODE";
                }
                $email->from('sender@asay.com.tr', $title);
                $email->to($to);

                $email->setBody(<img src="<?php echo $email->embed($pathToFile); ?>">, 'text/html');
            });
        } */
}
