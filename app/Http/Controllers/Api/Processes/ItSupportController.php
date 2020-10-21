<?php

namespace App\Http\Controllers\Api\Processes;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Controller;
use App\Library\Asay;
use App\Model\DiskFileModel;
use App\Model\EmployeeModel;
use App\Model\PriorityModel;
use App\Model\ITSupportCategoryModel;
use App\Model\ITSupportModel;
use App\Model\ITSupportRequestTypeModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItSupportController extends ApiController
{
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
        if($request->supportId!==null){
            $itSupport  = ITSupportModel::find($request->supportId);
        }
        else{
            $itSupport  = new ITSupportModel();
        }

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
                $fileQ   = DiskFileModel::where(["module_id"=>"disk","id"=>$itSupport->File]);

                $file = $fileQ->first();
                $itSupport->FileUrl = Storage::disk("connect")->path($file->subdir."/".$file->filename);
                $itSupport->FileName = $file->original_name;
                $itSupport->Mime    = Storage::disk("connect")->mimeType($file->subdir."/".$file->filename);
            }

            $employee = EmployeeModel::find($request->Employee);

            $mail = view('mails.it-support', ["itSupport"=>$itSupport,"employee"=>$employee]);
            Asay::sendMail("arge@asay.com.tr","","It Support",$mail,"aSAY Group",$itSupport->FileUrl,$itSupport->FileName,$itSupport->Mime);

            return response([
                "status"    => true,
                "message"   => "Success",
            ], 200);
        }
    }
}
