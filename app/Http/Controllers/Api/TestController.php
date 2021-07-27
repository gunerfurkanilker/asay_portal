<?php


namespace App\Http\Controllers\Api;


use App\Model\IsgEducationModel;
use Hamcrest\Core\Is;
use Illuminate\Http\Request;

class TestController extends ApiController
{

    public function educationGet(Request $request){

        $educationID = $request->EducationID;
        $education = null;
        if ($educationID)
            $education = IsgEducationModel::find($educationID);
        else
        {
            $educationQ = IsgEducationModel::where(['Active' => 1]);
            if ($request->EducationType)
            {
                $educationQ->where("Name",$request->EducationType);
            }
            $education = $educationQ->get();
        }


        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $education,
            'educationType' => $request->EducationType
        ],200);

    }

    public function educationPost(Request $request){

        $education = new IsgEducationModel();

        $education->Name = $request->Name;
        $education->Description = $request->Description;
        $education->EducationBy = $request->EducationBy;
        $education->EducationTo = $request->EducationTo;
        $education->EducationCompany = $request->EducationCompany;
        $education->EducationStartDate = $request->EducationStartDate;
        $education->EducationValidDate = $request->EducationValidDate;

        $result = $education->save();

        return response([
            'status' => $result,
            'message' => $result ? 'İşlem Başarılı' : 'İşlem Başarısız'
        ],200);

    }

    public function educationPut(Request $request){

        $educationID = $request->EducationID;
        $education = null;
        if ($educationID)
            $education = IsgEducationModel::find($educationID);
        else
            return response([
                'status' => false,
                'message' => 'İşlem Başarısız'
            ],200);

        $education->Name = $request->Name;
        $education->Description = $request->Description;
        $education->EducationBy = $request->EducationBy;
        $education->EducationTo = $request->EducationTo;
        $education->EducationCompany = $request->EducationCompany;
        $education->EducationStartDate = $request->EducationStartDate;
        $education->EducationValidDate = $request->EducationValidDate;

        $result = $education->save();

        return response([
            'status' => $result,
            'message' => $result ? 'İşlem Başarılı' : 'İşlem Başarısız'
        ],200);

    }

    public function educationDelete(Request $request){

        $educationID = $request->EducationID;
        $education = null;
        if ($educationID)
            $education = IsgEducationModel::find($educationID);
        else
            return response([
                'status' => false,
                'message' => 'İşlem Başarısız'
            ],200);


        $result = $education->delete();

        return response([
            'status' => $result,
            'message' => $result ? 'İşlem Başarılı' : 'İşlem Başarısız'
        ],200);

    }



}
