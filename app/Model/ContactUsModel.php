<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ContactUsModel extends Model
{
    protected $table = "ContactUs";
    protected $appends = [
        'ContactUsType'
    ];

    public function getContactUsTypeAttribute(){

        $contactUsTypeModel = $this->hasOne(ContactUsTypeModel::class,"id","ContactUsTypeID");
        if ($contactUsTypeModel)
        {
            $contactUsType = $contactUsTypeModel->where(['Active' => 1])->first();
            return $contactUsType;
        }
        else
            return null;

    }
}
