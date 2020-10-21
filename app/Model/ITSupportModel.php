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
        "SubCategoryContentName",
        "FileUrl"
    ];

    public function getRequestTypeNameAttribute()
    {
        return $this->hasMany(ITSupportRequestTypeModel::class,"RequestType","id")->first()->Name;
    }

    public function getPriorityNameAttribute()
    {
        return $this->hasMany(PriorityModel::class,"Priority","id")->first()->Name;
    }

    public function getCategoryNameAttribute()
    {
        return $this->hasMany(ITSupportCategoryModel::class,"Category","id")->first()->Name;
    }
    public function getSubCategoryNameAttribute()
    {
        if($this->attributes["SubCategory"]!==null)
            return $this->hasMany(ITSupportCategoryModel::class,"Category","id")->first()->Name;
        else
            return null;
    }
    public function getSubCategoryContentNameAttribute()
    {
        if($this->attributes["SubCategoryContent"]!==null)
            return $this->hasMany(ITSupportCategoryModel::class,"Category","id")->first()->Name;
        else
            return null;
    }
}
