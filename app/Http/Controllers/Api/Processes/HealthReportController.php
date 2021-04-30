<?php


namespace App\Http\Controllers\Api\Processes;

use App\Http\Controllers\Api\ApiController;
use App\Model\EmployeeModel;
use App\Model\HealthReportModel;
use App\Model\HealthReportTypeModel;
use App\Model\UserTokensModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class HealthReportController extends ApiController
{
    public function saveHealthReport(Request $request){

        $reportID = $request->reportId;
        $report = null;
        if ($reportID)
            $report = HealthReportModel::find($reportID);
        else
            $report = new HealthReportModel();

        $report->DocumentTypeID = $request->DocumentTypeID;
        $report->EmployeeID     = $request->EmployeeID;
        $report->DocumentNumber = $request->DocumentNumber;
        $report->start_date     = $request->start_date;
        $report->end_date       = $request->end_date;

        $result = $report->save();

        if ($result)
            return response([
                'status' => true,
                'message' => 'Kayıt Başarılı'
            ],200);
        else
            return response([
                'status' => false,
                'message' => 'Kayıt Başarısız'
            ],200);



    }

    public function getEmployeesWithTCKN(){

        $employees = EmployeeModel::where(['Active' => 1])
            ->where("Employee.Id" ,">=","1000")
            ->whereNotNull("IDCardID")
            ->get();

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $employees
        ],200);

    }

    public function getHealthReports(Request $request){

        $reportsQ = HealthReportModel::where(['Active' => 1]);

        if ($request->EmployeeID)
            $reportsQ->where("EmployeeID",$request->EmployeeID);

        $reportsQ->orderBy("start_date","desc");
        $reports = $reportsQ->get();

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $reports
        ],200);

    }

    public function deleteReport(Request $request){

        $report = HealthReportModel::find($request->reportId);

        if (!$report)
            return response([
                'status' => false,
                'message' => 'Kayıt Bulunamadı'
            ],200);

        $report->Active = 0;
        $result = $report->save();

        if (!$result)
            return response([
                'status' => false,
                'message' => 'İşlem Başarısız'
            ],200);
        else
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı'
            ],200);

    }

    public function getHealthReportTypes(){

        $reportTypes = HealthReportTypeModel::where(['Active' => 1])->get();

        return response([
            'status' => true,
            'data' => $reportTypes
        ],200);

    }

}
