<?php


namespace App\Http\Controllers\Api\Processes;


use App\Http\Controllers\Api\ApiController;
use App\Model\CarMissingCategoriesModel;
use App\Model\CarModel;
use App\Model\CarNotifyIssueKindModel;
use App\Model\CarNotifyKindModel;
use App\Model\CarNotifyModel;
use App\Model\CityModel;
use App\Model\DiskFileModel;
use App\Model\EmployeeModel;
use App\Model\EmployeePositionModel;
use App\Model\ITSupportModel;
use App\Model\RegionModel;
use App\Model\UserProjectsModel;
use Illuminate\Http\Request;


class CarNotifyController extends ApiController
{

    public function getFileLink(Request $request)
    {

        $diskId = $request->fileId;

        if ($diskId == null || $diskId == "")
            return response([
                'status' => false,
                'data' => null
            ],200);

        $file = DiskFileModel::find($diskId);


        $link = "http://" . parse_url(request()->root())['host'] . "/rest/file/" . $file->module_id . "/" . $file->id . "/?token=" . $request->token . "&filename=" . $file->original_name;

        return response([
            'status' => false,
            'data' => $link
        ],200);
    }

    public function vehicleNotifyList(Request $request){

        $filters = $request->all();
        $carNotifyListQ = CarNotifyModel::where(["Active" => 1]);

        foreach ($filters as $key => $filter)
        {
            if($key == "token")
                continue;
            else{
                $carNotifyListQ->where($key, $filter);
            }
        }
        try{
            $carNotifyList = $carNotifyListQ->orderBy("created_at","desc")->get();
            return response([
                'status' => true,
                'data' => $carNotifyList
            ],200);
        }
        catch (\Exception $ex)
        {
            return response([
                'status' => false,
                'data' => $ex->getMessage()
            ],200);
        }
    }

    public function saveCarNotify(Request $request){

        return response([
            'status' => false,
            'message' => 'Araç bildirim işlemi devre dışı bırakılmıştır'
        ], 200);

        $result = CarNotifyModel::saveCarNotify($request);

        return response([
            'status' => $result['status'],
            'message' => $result['message']
        ],200);


    }


    public function getEmployeeList(Request $request)
    {
        $employeePosition = EmployeePositionModel::where(['Active' => 2,'EmployeeID' => $request->Employee])->first();

        $employeePositions = EmployeePositionModel::where(['Active' => 2,'RegionID' => $employeePosition->RegionID])->get();

        $employeeArray = [];

        foreach ($employeePositions as $employeePosition)
        {
            $tempEmployee = EmployeeModel::find($employeePosition->EmployeeID);
            array_push($employeeArray,$tempEmployee);
        }

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $employeeArray
        ],200);

    }

    public function getNotifyKinds()
    {
        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => CarNotifyKindModel::where(['Active' => 1])->get()
        ],200);
    }

    public function getCarPlates(Request $request)
    {

        $employeePosition = EmployeePositionModel::where(['Active' => 2, 'EmployeeID' => $request->Employee])->first();
        $projectId = 0;

        switch ($employeePosition->Organization->id)
        {
            case 4:
                $projectId = 1;
                break;
            case 6:
                $projectId = 2;
                break;
        }

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => CarModel::where(['Active' => 1,'ProjectID' => $projectId])->get()
        ],200);
    }

    public function getRegions()
    {
        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => RegionModel::where(['Active' => 1])->whereNotIn("id",[6])->get()//İzmiri sonuç listesinden çıkarttım.
        ],200);
    }

    public function getCities(Request $request)
    {
        if ($request->RegionID == null)
            return response([
                'status' => false,
                'message' => 'Bölge ID\'si boş olamaz',
            ],200);

        $cities = CityModel::where(['Active' => 1,'RegionID' => $request->RegionID])->get();

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $cities
        ],200);

    }

    public function getIssueKinds(Request $request)
    {
        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => CarNotifyIssueKindModel::where(['Active' => 1])->get()
        ],200);
    }

    public function getCarDefects(Request $request)
    {
        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => CarMissingCategoriesModel::where(['Active' => 1])->get()
        ],200);
    }

    public function getTicketCode(Request $request)
    {
        $maxCode = CarNotifyModel::max("TicketNo");

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $maxCode ? "TKT-ARC-".($maxCode + 1) : "TKT-ARC-10000"
        ],200);

    }


}
