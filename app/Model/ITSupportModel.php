<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ITSupportModel extends Model
{
    protected $table = "ITSupport";

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];
    protected $appends = [
        "RequestTypeName",
        "PriorityName",
        "CategoryName",
        "SubCategoryName",
        "SubCategoryContentName"
    ];

    public function getRequestTypeNameAttribute()
    {
        return $this->hasMany(ITSupportRequestTypeModel::class,"id","RequestType")->first()->Name;
    }

    public function getPriorityNameAttribute()
    {
        return $this->hasMany(PriorityModel::class,"id","Priority")->first()->Name;
    }

    public function getCategoryNameAttribute()
    {
        return $this->hasMany(ITSupportCategoryModel::class,"id","Category")->first()->Name;
    }
    public function getSubCategoryNameAttribute()
    {
        if($this->attributes["SubCategory"]!==null)
            return $this->hasMany(ITSupportCategoryModel::class,"id","SubCategory")->first()->Name;
        else
            return null;
    }
    public function getSubCategoryContentNameAttribute()
    {
        if($this->attributes["SubCategoryContent"]!==null)
            return $this->hasMany(ITSupportCategoryModel::class,"id","SubCategoryContent")->first()->Name;
        else
            return null;
    }
}
