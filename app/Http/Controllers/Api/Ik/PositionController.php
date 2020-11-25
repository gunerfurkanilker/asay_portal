<?php


namespace App\Http\Controllers\Api\Ik;


use App\Http\Controllers\Api\ApiController;
use App\Model\EmployeeModel;
use App\Model\EmployeePositionModel;
use App\Model\ServiceCodesModel;
use Illuminate\Http\Request;

class PositionController extends ApiController
{
    public function getJobPositionInformations($id)
    {
        $positions = EmployeePositionModel::where('EmployeeID',$id)->whereIn('Active',[0,1,2])->get()->toArray();

        if ($positions != null)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı.',
                'data' => $positions,
            ],200);
        else
            return response([
                'status' => true,
                'message' => 'İşlem Başarısız.',
            ],200);
    }


    public function getJobPositionInformationFields()
    {
        return response([
            'status' => true,
            'message' => 'İşlem Başarılı.',
            'data' => EmployeePositionModel::getPositionFields()
        ],200);
    }



    public function saveJobPosition(Request $request)
    {



        if (isset($request->PositionID) || $request->PositionID != null)
        {
            $positionOfEmployee = EmployeePositionModel::where(['Id' => $request->PositionID, 'EmployeeID' => $request->EmployeeID])->first();
            $freshData = EmployeePositionModel::editJobPosition($positionOfEmployee,$request);
        }
        else
            $freshData = EmployeePositionModel::addJobPosition($request);

        if ($freshData)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => $freshData
            ]);
        else
            return response([
                'status' => false,
                'message' => 'İşlem Başarısız.'
            ]);
    }

    public function deleteJobPosition(Request $request)
    {
        $request = $request->all();
        $status = EmployeePositionModel::deleteJobPosition($request['positionid']);

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $status
        ]);


    }

    public function getServiceCodes(Request $request)
    {

        $company = $request->CompanyID;
        $organization = $request->OrganizationID;
        $department = $request->DepartmentID;

        $serviceCode = ServiceCodesModel::where(['CompanyID' => $company, 'OrganizationID' => $organization, 'DepartmentID' => $department])->get();

        if ($serviceCode)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => $serviceCode
            ],200);
        else
            return response([
                'status' => false,
                'message' => 'Hizmet Kodu Bulunamadı'
            ],200);

    }

}
