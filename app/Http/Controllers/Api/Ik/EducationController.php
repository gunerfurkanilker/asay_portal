<?php


namespace App\Http\Controllers\Api\Ik;


use App\Http\Controllers\Api\ApiController;
use App\Model\EducationModel;
use App\Model\EmployeeModel;
use App\Model\LocationModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EducationController extends ApiController
{

    public function saveEducation(Request $request)
    {
        $request_data = $request->all();
        $employee = EmployeeModel::find($request_data['employeeid']);
        if (!is_null($employee))
        {
            if ($employee->EducationID != null)
                $education = EducationModel::saveEducation($request_data,$employee->EducationID);
            else
                $education = EducationModel::addEducation($request_data,$employee);

            if ($education)
                return response([
                    'status' => true,
                    'message' => $education->Id . " ID No'lu Eğitim Bilgisi Kaydedildi",
                    'data' =>$education
                ],200);
            else
                return response([
                    'status' => false,
                    'message' => "İşlem Başarısız."
                ],200);
        }
        else
        {
            return response([
                'status' => false,
                'message' => $employeeId. " ID No'lu Çalışan bulunamadı."
            ],200);
        }
    }

    public function saveEducationDocument(Request $request)
    {
        return response([
            'status' => false,
            'message' => "İşlem Başarısız.",
            'data' => $request->all()
        ],200);
    }

    public function getEducationInformations($employeeid)
    {
        $employee = EmployeeModel::find($employeeid);

        if ($employee->EducationID == null)
            return response([
                'status' => false,
                'message' => 'Eğitim Bilgisi Bulunamadı!',
                'data' => null
            ],200);
        else
        {
            $education = EducationModel::find($employee->EducationID);

            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => $education
            ],200);
        }

    }

    public function getGraduationDocument($pathOfFile){

    }

    public function getEducationInformationFields()
    {
        $fields = EducationModel::getEducationFields();
        return response([
            'status' => true,
            'message' => "İşlem Başarılı.",
            'data' => $fields
        ],200);

    }

}
