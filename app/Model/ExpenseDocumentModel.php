<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ExpenseDocumentModel extends Model
{
    protected $table = "ExpenseDocument";

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];
    protected $appends = [
        'ObjectFile'
    ];

    public function getObjectFileAttribute(){
        $objectFile = $this->hasOne(ObjectFileModel::class,'ObjectId','id');
        return $objectFile->where('ObjectType',1)->first();//Harcama Belgesi
    }


}
