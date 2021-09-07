<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TrainingCategoryModel extends Model
{
    //
    protected $table = "TrainingCategories";
    public $timestamps = false;
    protected $appends = [
        "Type"
    ];

    public function getTypeAttribute(){
        $type = $this->hasOne(TrainingTypeModel::class,"id","TypeID");
        if($type)
        {
            $type = $type->where("Active",1)->first();
            if ($type)
                return $type;
            else
                return null;
        }
        else
            return null;
    }

}
