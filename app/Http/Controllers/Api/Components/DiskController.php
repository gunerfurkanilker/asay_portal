<?php

namespace App\Http\Controllers\Api\Components;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Controller;
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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DiskController extends ApiController
{
    public function getStorage(Request $request)
    {
        //Yetkli Olduğu Storage Listesi
        $rights = self::rights($request);
        //TODO: Paylaştırılan klasörler listeye eklenecek
        $diskRights = DiskRightModel::whereIn("access_code", $rights)->pluck("object_id");
        $storages = DiskStorageModel::whereIn("root_object_id", $diskRights)
            ->leftJoin("disk_object", "disk_object.id", "=", "disk_storage.root_object_id")->get();

        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $storages
        ], 200);
    }


    public function getFoldersAndFiles(Request $request)
    {
        //İlgili objenin klasör ve dosyaları
        $objectStorageId = DiskObjectModel::find($request->objectId)->storage_id;
        $rootObject = DiskStorageModel::find($objectStorageId);

        $rights = self::rights($request);
        $rightStatus = DiskRightModel::whereIn("access_code", $rights)
            ->where(["object_id" => $rootObject->root_object_id])->count();

        if ($rightStatus == 0) {
            return response([
                'status' => false,
                'message' => 'Yetkisiz İşlem',
            ], 200);
        }

        $objects = DiskObjectModel::where(["parent_id" => $request->objectId, "deleted" => 0])->get();
        foreach ($objects as $key => $object) {
            if ($object->type == 3) {
                //http://portal.asay.com.tr/disk/company/3?token=d268659be29bdb958c2105dd7f80e846&filename=ssss.pdf
                $object->viewFile = "http://" . parse_url(request()->root())['host'] . "/rest/file/disk/" . $rootObject->EmployeeID . "/" . $object->id . "/?token=" . $request->token . "&filename=" . $object->name;
                $object->downloadFile = "http://" . parse_url(request()->root())['host'] . "/rest/file/disk/downloadFile/" . $rootObject->EmployeeID . "/" . $object->id . "/?token=" . $request->token . "&filename=" . $object->name;
                $object->extension = $extension = pathinfo($object->name, PATHINFO_EXTENSION);
            } else {
                $object->viewFile = null;
                $object->downloadFile = null;
                $object->extension = null;
            }
        }
        return response([
            'status' => true,
            'message' => 'Başarılı',
            'data' => $objects,
        ], 200);
    }

    public function viewObjectFile(Request $request, $storage = "", $objectId = "")
    {
        $rights = self::rights($request);
        $rootObjectId = DiskStorageModel::where(["EmployeeID" => $storage])->first()->root_object_id;
        $rightStatus = DiskRightModel::whereIn("access_code", $rights)
            ->where(["object_id" => $rootObjectId])->count();

        if ($rightStatus == 0) {
            return response([
                'status' => false,
                'message' => 'Yetkisiz İşlem',
            ], 200);
        }

        $object = DiskObjectModel::find($objectId);
        $file = DiskFileModel::find($object->file_id);

        $headers = array(
            'Content-Type: ' . $file->content_type,
            'Content-Disposition', 'filename=' . $file->original_name . ';'
        );

        return response(Storage::disk("connect")->get($file->subdir . "/" . $file->filename))
            ->header('Content-Type', $file->content_type)
            ->header('Content-Disposition', 'filename=' . $file->original_name . ';');
    }

    public function downloadObjectFile(Request $request, $storage = "", $objectId = "")
    {
        $rights = self::rights($request);
        $rootObjectId = DiskStorageModel::where(["EmployeeID" => $storage])->first()->root_object_id;
        $rightStatus = DiskRightModel::whereIn("access_code", $rights)
            ->where(["object_id" => $rootObjectId])->count();

        if ($rightStatus == 0) {
            return response([
                'status' => false,
                'message' => 'Yetkisiz İşlem',
            ], 200);
        }

        $object = DiskObjectModel::find($objectId);
        $file = DiskFileModel::find($object->file_id);

        $headers = array(
            'Content-Type: ' . $file->content_type,
            'Content-Disposition:attachment; filename="' . $file->original_name . '"',

        );

        return Storage::disk("connect")->download($file->subdir . "/" . $file->filename, $file->original_name, $headers);
    }

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

    public function downloadFile(Request $request, $module_id = "", $fileId = "")
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
            'Content-Disposition:attachment; filename="' . $file->original_name . '"',

        );

        return Storage::disk("connect")->download($file->subdir . "/" . $file->filename, $file->original_name, $headers);
    }


    public function addObjectFolder(Request $request)
    {
        $storageIdQ = DiskStorageModel::where(["EmployeeID" => $request->storage]);
        if ($storageIdQ->count() == 0) {
            return response([
                'status' => false,
                'message' => 'Disk Bulunamadı',
            ], 200);
        }

        $storageId = $storageIdQ->first()->id;
        $diskObject = new DiskObjectModel();
        $diskObject->name = $request->folderName;
        $diskObject->storage_id = $storageId;
        $diskObject->parent_id = $request->directoryId;
        $diskObject->type = 2;
        $diskObject->created_by = $request->Employee;
        $diskObject->file_id = null;
        $diskObject->save();

        return response([
            'status' => true,
            'message' => 'Klasör Oluşturuldu',
        ], 200);
    }

    public function addObjectFile(Request $request)
    {
        $storageIdQ = DiskStorageModel::where(["EmployeeID" => $request->storage]);
        if ($storageIdQ->count() == 0) {
            return response([
                'status' => false,
                'message' => 'Disk Bulunamadı',
            ], 200);
        }

        $fileName = md5(uniqid("", true));
        $folderName = substr($fileName, 0, 3);

        if (!Storage::disk("connect")->exists($request->moduleId)) {
            Storage::disk("connect")->makeDirectory($request->moduleId, 0775, true); //creates directory
        }
        $path = $request->moduleId . "/" . $folderName;
        if (!Storage::disk("connect")->exists($path)) {
            Storage::disk("connect")->makeDirectory($path, 0775, true); //creates directory
        }

        if ($request->file('file') === null) {
            return response([
                'status' => false,
                'message' => 'Dosya Yüklenemedi',
            ], 200);
        }

        $file = $request->file('file');

        $uploadFile = $file->storeAs($path, $fileName, "connect");
        $diskFile = new DiskFileModel();
        $diskFile->module_id = $request->moduleId;
        $diskFile->subdir = $path;
        $diskFile->content_type = $file->getClientMimeType();
        $diskFile->filename = $fileName;
        $diskFile->original_name = $file->getClientOriginalName();
        $diskFile->save();

        $storageId = $storageIdQ->first()->id;
        $diskObject = new DiskObjectModel();
        $diskObject->name = $diskFile->original_name;
        $diskObject->storage_id = $storageId;
        $diskObject->parent_id = $request->directoryId;
        $diskObject->type = 3;
        $diskObject->created_by = $request->Employee;
        $diskObject->file_id = $diskFile->id;
        $diskObject->save();

        return response([
            'status' => true,
            'message' => 'Yükleme Başarılı',
        ], 200);
    }

    //Moduleıd yi string olarak dön
    public function addFile(Request $request)
    {
        $fileName = md5(uniqid("", true));
        $folderName = substr($fileName, 0, 3);

        if (!Storage::disk("connect")->exists($request->moduleId)) {
            Storage::disk("connect")->makeDirectory($request->moduleId, 0775, true); //creates directory
        }
        $path = $request->moduleId . "/" . $folderName;

        if ($request->file('file') === null) {
            return response([
                'status' => false,
                'message' => 'Dosya Yüklenemedi',
            ], 200);
        }

        $file = $request->file('file');

        $uploadFile = $file->storeAs($path, $fileName, "connect");
        $diskFile = new DiskFileModel();
        $diskFile->module_id = $request->moduleId;
        $diskFile->subdir = $path;
        $diskFile->content_type = $file->getClientMimeType();
        $diskFile->filename = $fileName;
        $diskFile->original_name = $file->getClientOriginalName();
        $diskFile->save();

        return response([
            'status' => true,
            'message' => 'Yükleme Başarılı',
            'data' => $diskFile->id
        ], 200);
    }

    public function getFile(Request $request)
    {
        if ($request->fileId === null) {
            return response([
                'status' => false,
                'message' => 'Dosya Bulunamadı',
            ], 200);
        }

        $file = DiskFileModel::find($request->fileId);
        $fileObject["viewFile"] = "http://" . parse_url(request()->root())['host'] . "/rest/file/" . $file->module_id . "/" . $file->id . "/?token=" . $request->token . "&filename=" . $file->original_name;
        $fileObject["downloadFile"] = "http://" . parse_url(request()->root())['host'] . "/rest/file/" . $file->module_id . "/downloadFile/" . $file->id . "/?token=" . $request->token . "&filename=" . $file->original_name;
        return response([
            'status' => true,
            'data' => $fileObject,
            'file' => $file
        ], 200);
    }

    public function rights(Request $request)
    {
        $groups = EmployeeHasGroupModel::where(["EmployeeID" => $request->Employee])->pluck("group_id");
        $rights[] = "E_" . $request->Employee;
        foreach ($groups as $group) {
            $rights[] = "G_" . $group;
        }

        return $rights;
    }

    public function deleteFile(Request $request)
    {
        $result = Storage::disk("connect")->deleteDirectory($request->directoryName);

        if ($result)
        {
            $status = false;
            switch ($request->tableName)
            {
                case 'education':
                    $object = EducationModel::find($request->objectID);
                    $object->EducationFile = null;
                    $status = $object->save();
                    break;
                case 'id_card':
                    $object = EmployeeModel::find($request->objectID);
                    $object->Photo = null;
                    $status = $object->save();
                    break;
                case 'id_card_copy':
                    $object = IdCardModel::find($request->objectID);
                    $object->CopyPhoto = null;
                    $status = $object->save();
                    break;
            }
            if ($status)
                return response([
                    'status' => true,
                    'message' => 'İşlem Başarılı'
                ], 200);
            else
                return response([
                    'status' => false,
                    'message' => 'İşlem Başarısız'
                ], 200);
        }
        else
            return response([
                'status' => false,
                'message' => 'İşlem Başarısız'
            ], 200);

    }


}
