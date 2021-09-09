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
    protected $guarded = [];

    public static function saveTrainingCategory($request){

        $trainingCategoryInstance = TrainingCategoryModel::firstOrNew([
            'id' => $request->id,
        ]);

        $codeStringArray = explode(" ",$request->Name);
        $codeString = "";
        foreach ($codeStringArray as $item)
        {
            $firstLetter = mb_strtoupper(substr($item,0,1));
            $codeString.=$firstLetter;
        }
        $trainingCategoryInstance->Name = $request->Name;
        $trainingCategoryInstance->Code = $trainingCategoryInstance->Code ? $trainingCategoryInstance->Code : $codeString ;
        $trainingCategoryInstance->TypeID = $request->TypeID;

        $result = $trainingCategoryInstance->save();

        return $result;

    }


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
