<?php


namespace App\Http\Controllers\Api\Common;

use App\Http\Controllers\Api\ApiController;
use App\Model\ObjectFileModel;
use Illuminate\Http\Request;

class ObjectFileController extends ApiController
{
    public function setObjectFile(Request $request)
    {
        $ObjectType             = $request->ObjectType;
        $FlowFile               = ObjectFileModel::firstOrNew(["ObjectType"=>$ObjectType,"ObjectId"=>$request->ObjectId,"Type"=>$request->Type]);
        $FlowFile->File         = $request->File;
        $FlowFile->EmployeeID   = $request->Employee;
        $FlowFile->save();

        return response([
            'status' => true
        ], 200);
    }
}
