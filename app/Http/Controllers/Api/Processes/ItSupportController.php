<?php

namespace App\Http\Controllers\Api\Processes;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Controller;
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
        $itSupport->Category            = $request->Category;
        $itSupport->SubCategory         = $request->SubCategory;
        $itSupport->SubCategoryContent  = $request->SubCategoryContent;
        $itSupport->Subject             = $request->Subject;
        $itSupport->Content             = $request->Content;
        $itSupport->File                = $request->File;

        if($itSupport->save()){
            if($request->File!==null){
                $fileQ   = DiskFileModel::where(["module_id"=>"disk","id"=>$itSupport->File]);

                $file = $fileQ->first();
                $itSupport->FileUrl = Storage::disk("connect")->get($file->subdir."/".$file->filename);
            }

            $employee = EmployeeModel::find($request->Employee);

            $mailTable = view('mails.it-support', ["itSupport"=>$itSupport,"employee"=>$employee]);
        }
    }
}
