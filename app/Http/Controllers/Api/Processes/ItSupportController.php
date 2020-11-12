<?php

namespace App\Http\Controllers\Api\Processes;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Controller;
use App\Library\Asay;
use App\Model\DiskFileModel;
use App\Model\EmployeeModel;
use App\Model\EmployeePositionModel;
use App\Model\NotificationsModel;
use App\Model\PriorityModel;
use App\Model\ITSupportCategoryModel;
use App\Model\ITSupportModel;
use App\Model\ITSupportRequestTypeModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItSupportController extends ApiController
{

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


    public function supportCategories(Request $request)
    {
        $parent = $request->Parent;
        $categories = ITSupportCategoryModel::where(["Parent"=>$parent,"Active"=>1])->get();
        return response([
            "status"    => true,
            "message"   => "Success",
            "data"      => $categories,
        ], 200);
    }

    public function requestTypes(Request $request)
    {
        $requestTypes = ITSupportRequestTypeModel::where(["Active"=>1])->get();
        return response([
            "status"    => true,
            "message"   => "Success",
            "data"      => $requestTypes,
        ], 200);
    }

    public function priority(Request $request)
    {
        $priority = PriorityModel::where(["Active"=>1])->get();
        return response([
            "status"    => true,
            "message"   => "Success",
            "data"      => $priority,
        ], 200);
    }

    public function supportSave(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'RequestType'   => 'required',
            'Priority'      => 'required',
            'Category'      => 'required',
            'Subject'       => 'required',
            'Content'       => 'required',
        ]);
        if ($validator->fails()) {
            return response([
                "status" => false,
                "message" => $validator->messages()], 200);
        }
        if($request->supportId!==null){
            $itSupport  = ITSupportModel::find($request->supportId);
        }
        else{
            $itSupport  = new ITSupportModel();
        }

        $itSupport->RequestedFrom       = $request->RequestedFrom;
        $itSupport->RequestType         = $request->RequestType;
        $itSupport->Priority            = $request->Priority;
        $itSupport->Category            = $request->Category;
        $itSupport->SubCategory         = $request->SubCategory;
        $itSupport->SubCategoryContent  = $request->SubCategoryContent;
        $itSupport->Subject             = $request->Subject;
        $itSupport->Content             = $request->Content;
        $itSupport->CreatedDate         = date("Y-m-d H:i:s");
        $itSupport->File                = $request->File;

        if($itSupport->save()){
            $itSupport->FileUrl = "";
            $itSupport->FileName = "";
            $itSupport->Mime = "";
            if($request->File!==null){
                $fileQ   = DiskFileModel::where(["module_id"=>"disk","id"=> (int) $itSupport->File]);

                $file = $fileQ->first();
                $itSupport->FileUrl = Storage::disk("connect")->path($file->subdir."/".$file->filename);
                $itSupport->FileName = $file->original_name;
                $itSupport->Mime    = Storage::disk("connect")->mimeType($file->subdir."/".$file->filename);
            }

            $employee = EmployeeModel::find($request->RequestedFrom);

            $mail = view('mails.it-support', ["itSupport"=>$itSupport,"employee"=>$employee]);
            NotificationsModel::saveNotification($itSupport->RequestedFrom,11,$itSupport->id,"IT Destek",$itSupport->Subject." için oluşturmuş olduğunuz IT destek kaydı sistemimize kaydedilmiştir","");
            Asay::sendMail("ilker.guner@asay.com.tr",$employee->JobEmail,"It Support",$mail,"aSAY Group",$itSupport->FileUrl,$itSupport->FileName,$itSupport->Mime);

            return response([
                "status"    => true,
                "message"   => "Success",
            ], 200);
        }
    }
}
