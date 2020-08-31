<?php

namespace App\Http\Controllers\Api\Components;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Controller;
use App\Model\DiskFileModel;
use App\Model\DiskObjectModel;
use App\Model\DiskRightModel;
use App\Model\DiskStorageModel;
use App\Model\EmployeeHasGroupModel;
use Facade\FlareClient\Api;
use Illuminate\Http\Request;

class DiskController extends ApiController
{
    public function getStorage(Request $request)
    {
        //Yetkli Olduğu Storage Listesi
        $rights = self::rights($request);
        //TODO: Paylaştırılan klasörler listeye eklenecek
        $diskRights = DiskRightModel::whereIn("access_code",$rights)->pluck("object_id");
        $storages = DiskStorageModel::whereIn("root_object_id",$diskRights)
            ->leftJoin("disk_object","disk_object.id","=","disk_storage.root_object_id")->get();

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $storages
        ],200);
    }


    public function getFoldersAndFiles(Request $request)
    {
        //İlgili objenin klasör ve dosyaları
        $objectStorageId    = DiskObjectModel::find($request->objectId)->storage_id;
        $rootObjectId       = DiskStorageModel::find($objectStorageId)->root_object_id;

        $rights = self::rights($request);
        $rightStatus = DiskRightModel::whereIn("access_code",$rights)
            ->where(["object_id"=>$rootObjectId])->count();

        if($rightStatus==0){
            return response([
                'status' => false,
                'message' => 'Yetkisiz İşlem',
            ],200);
        }

        $objects = DiskObjectModel::where(["parent_id"=>$request->objectId,"deleted"=>0])->get();
        return response([
            'status' => true,
            'message' => 'Başarılı',
            'data' => $objects,
        ],200);
    }

    public function viewFile(Request $request,$storage="",$objectId="")
    {
        $rights = self::rights($request);
        $rootObjectId   = DiskStorageModel::where(["EmployeeID"=>$storage])->first()->root_object_id;
        $rightStatus    = DiskRightModel::whereIn("access_code",$rights)
            ->where(["object_id"=>$rootObjectId])->count();

        if($rightStatus==0){
            return response([
                'status' => false,
                'message' => 'Yetkisiz İşlem',
            ],200);
        }

        $object = DiskObjectModel::find($objectId);
        $file   = DiskFileModel::find($object->file_id);
        $headers = array(
            'Content-Type: '.$file->content_type
        );

        return response()->file($file->subdir."/".$file->filename,$headers);
    }

    public function downloadFile(Request $request,$storage="",$objectId="")
    {
        $rights = self::rights($request);
        $rootObjectId   = DiskStorageModel::where(["EmployeeID"=>$storage])->first()->root_object_id;
        $rightStatus    = DiskRightModel::whereIn("access_code",$rights)
            ->where(["object_id"=>$rootObjectId])->count();

        if($rightStatus==0){
            return response([
                'status' => false,
                'message' => 'Yetkisiz İşlem',
            ],200);
        }

        $object = DiskObjectModel::find($objectId);
        $file   = DiskFileModel::find($object->file_id);

        $headers = array(
            'Content-Type: '.$file->content_type,
            'Content-Disposition:attachment; filename="'.$file->original_name.'"',

        );

        return response()->download($file->subdir."/".$file->filename,$file->original_name,$headers);
    }


    public function addFile(Request $request)
    {

    }
    
    public function rights(Request $request)
    {
        $groups = EmployeeHasGroupModel::where(["EmployeeID"=>$request->EmployeeID])->pluck("group_id");
        $rights[] = "E_".$request->EmployeeID;
        foreach ($groups as $group) {
            $rights[] = "G_".$group;
        }
        
        return $rights;
    }


}
