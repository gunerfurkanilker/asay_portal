<?php


namespace App\Http\Controllers\Api\Processes;


use App\Http\Controllers\Api\ApiController;
use App\Library\Asay;
use App\Model\ContactUsModel;
use App\Model\ContactUsTypeModel;
use App\Model\EmployeeModel;
use Illuminate\Http\Request;

class ContactUsController extends ApiController
{
    public function saveContactUs(Request $request){

        $employee = EmployeeModel::find($request->Employee);

        if ($request->ContactUsID === null)
        {
            $contactUs = new ContactUsModel();
            $contactUs->ContactUsTypeID = $request->ContactUsTypeID;
            $contactUs->DepartmentID = $request->DepartmentID;
            $contactUs->EmployeeID = $request->Employee;
            $contactUs->Subject = $request->Subject;
            $contactUs->Description = $request->Description;

            $result = $contactUs->save();

            if ($result)
            {
                $mailTo = "ilker.guner@asay.com.tr";
                //$mailTo = "projeyonetim@ms.asay.com.tr";

                $mailTable = view("mails.contact-us",[
                    'employee' => $employee, 'contactUs' => $contactUs
                ]);

                Asay::sendMail($mailTo,"","Çalışan İletişim Talebi",$mailTable,"aSAY Group");

                return response([
                    'status' => true,
                    'message' => 'İşlem Başarılı'
                ],200);
            }
            return response([
                'status' => false,
                'message' => 'İşlem Başarısız'
            ],200);


        }

    }

    public function getContactUsTypes()
    {
        $contactUsTypes = ContactUsTypeModel::where(['Active' => 1])->get();
        return response([
            'status' => true,
            'message' => 'İşlem Başarılı',
            'data' => $contactUsTypes
        ],200);
    }
}
