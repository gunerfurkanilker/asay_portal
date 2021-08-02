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
        "RequestedFromName",
        "RequestTypeName",
        "PriorityName",
        "CategoryName",
        "SubCategoryName",
        "SubCategoryContentName",
    ];

    public function getRequestedFromNameAttribute()
    {
        $RequestedFromName = $this->hasOne(EmployeeModel::class,"Id","RequestedFrom");
        if ($RequestedFromName)
        {
            $requestType = $RequestedFromName->first();
            return $requestType->UsageName . " " . $requestType->LastName;
        }
        else
            return null;
    }

    public function getRequestTypeNameAttribute()
    {
        $requestType = $this->hasOne(ITSupportRequestTypeModel::class,"id","RequestType");
        if ($requestType)
        {
            $requestType = $requestType->first();
            return $requestType->Name;
        }
        else
            return null;
    }

    public function getPriorityNameAttribute()
    {
        $priorityName = $this->hasOne(PriorityModel::class,"id","Priority");
        if ($priorityName)
        {
            $priorityName = $priorityName->first();
            return $priorityName->Name;
        }
        else
            return null;
    }

    public function getCategoryNameAttribute()
    {
        $categoryName = $this->hasOne(ITSupportCategoryModel::class,"id","Category");
        if ($categoryName)
        {
            $categoryName = $categoryName->first();
            return $categoryName->Name;
        }
        else
            return null;
    }

    public function getSubCategoryNameAttribute()
    {
        if($this->attributes["SubCategory"]!==null)
            return $this->hasOne(ITSupportCategoryModel::class,"id","SubCategory")->first()->Name;
        else
            return null;
    }

    public function getSubCategoryContentNameAttribute()
    {
        if($this->attributes["SubCategoryContent"]!==null)
            return $this->hasOne(ITSupportCategoryModel::class,"id","SubCategoryContent")->first()->Name;
        else
            return null;
    }

}
