<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\DiskFileModel;
use App\Model\DiskObjectModel;
use App\Model\DiskRightModel;
use App\Model\DiskStorageModel;
use App\Model\EducationModel;
use App\Model\EmployeeHasGroupModel;
use App\Model\EmployeeModel;
use App\Model\IdCardModel;
use Facade\FlareClient\Api;
use Faker\Provider\File;
use Illuminate\Support\Facades\Storage;


class GetFileController extends Controller
{

    public function viewFile(Request $request, $module_id = "", $fileId = "")
        {
            $fileQ = DiskFileModel::where(["module_id" => $module_id, "id" => $fileId]);
            if ($fileQ->count() == 0) {
                return response([
                    'status' => false,
                    'message' => 'Dosya Bulunamadı',
                ], 200);
            }

            $file = $fileQ->first();
            $headers = array(
                'Content-Type: ' . $file->content_type,
                'Content-Disposition', 'filename=' . $file->original_name . ';'
            );

            return response(Storage::disk("connect")->get($file->subdir . "/" . $file->filename))
                ->header('Content-Type', $file->content_type)
                ->header('Content-Disposition', 'filename=' . $file->original_name . ';');
        }

    public function index(Request $request){
                       if ($request->fileId === null) {
                           return response([
                               'status' => false,
                               'message' => 'Dosya Bulunamadı',
                           ], 200);
                       }

                       $file = DiskFileModel::find($request->fileId);
                       $fileObject["viewFile"] = "http://" . parse_url(request()->root())['host'] . "/rest/file1/" . $file->module_id . "/" . $file->id . "&filename=" . $file->original_name;
//                        $fileObject["downloadFile"] = "http://" . parse_url(request()->root())['host'] . "/rest/file1/" . $file->module_id . "/downloadFile/" . $file->id . "&filename=" . $file->original_name;
                       return response([
                           'status' => true,
                           'data' => $fileObject,
                           'file' => $file
                       ], 200);
    }
}
