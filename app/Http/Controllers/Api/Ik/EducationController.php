<?php


namespace App\Http\Controllers\Api\Ik;


use App\Http\Controllers\Api\ApiController;
use App\Model\DocumentFileModel;
use App\Model\EducationModel;
use App\Model\EmployeeModel;
use App\Model\LocationModel;
use App\Model\ObjectFileModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EducationController extends ApiController
{

    public function saveEducation(Request $request)
    {
        $status = EducationModel::saveEducation($request);

        if ($status)
            return response([
                'status' => true,
                'message' => 'İşlem Başarılı'
            ], 200);
        else
            return response([
                'status' => false,
                'message' => "İşlem Başarısız.", 'data' => $request->EducationID
            ], 200);

    }

    public function saveEducationDocument(Request $request)
    {
        $file = $request->file('blob');

        $extension = $file->getClientOriginalExtension();

        if ($extension != 'pdf') {
            return response([
                'status' => false,
                'message' => "Desteklenmeyen Dosya Formatı.",
                'data' => $request->file('blob')->getClientOriginalExtension()
            ], 200);
        }

        $uploadFilePath = DocumentFileModel::uploadEducationDocument($file, $request->employeeID);

        return response([
            'status' => false,
            'message' => "Dosya Başarıyla Yüklendi",
            'data' => $uploadFilePath
        ], 200);

    }

    public function getEducationInformations($employeeid)
    {
        $employee = EmployeeModel::find($employeeid);

        if ($employee->EducationID == null)
            return response([
                'status' => false,
                'message' => 'Eğitim Bilgisi Bulunamadı!',
                'data' => null
            ], 200);
        else {
            $education = EducationModel::where(['EmployeeID' => $employee->EducationID, 'Active' => 1])->get();

            return response([
                'status' => true,
                'message' => 'İşlem Başarılı',
                'data' => $education
            ], 200);
        }

    }

    public function downloadEducationDocument(Request $request)
    {
        $request = $request->all();

        $employee = EmployeeModel::find($request['employeeid']);

        $education = EducationModel::find($employee->EducationID);

        $document = DocumentFileModel::find($education->DocumentID);
        //return response()->file(Storage::url($document->URL), ['Content-Type : application/pdf']);

        return response()->file(Storage::disk('local')->getDriver()->getAdapter()->applyPathPrefix($document->URL));

    }

    public function getEducationInformationFields()
    {
        $fields = EducationModel::getEducationFields();
        return response([
            'status' => true,
            'message' => "İşlem Başarılı.",
            'data' => $fields
        ], 200);

    }

    public function deleteEducation(Request $request)
    {

        $education = EducationModel::find($request->EducationID);


        $objectFile = ObjectFileModel::where(['ObjectType' => 5, 'ObjectId' => $education->Id])->first();

        $education->Active = 0;
        if ($objectFile) {
            $objectFile->Active = 0;
            $objectFile->save();
        }


        if ($education->save())
            return response([
                'status' => true,
                'message' => 'Silme İşlemi Başarılı'
            ], 200);

        return response([
            'status' => false,
            'message' => 'Silme İşlemi Başarısız'
        ], 200);

    }

}
