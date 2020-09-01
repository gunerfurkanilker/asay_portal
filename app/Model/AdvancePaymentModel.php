<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AdvancePaymentModel extends Model
{
    protected $table = "AdvancePayment";
    protected $primaryKey = "id";

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];
    protected $appends = [
        'Category',
        'Project'
    ];

    public function getCategoryAttribute()
    {

        $category = $this->hasOne(ProjectCategoriesModel::class,"id","CategoryId");
        if ($category)
        {
            return $category->where("active",1)->first();
        }
        else
        {
            return "";
        }
    }

    public function getProjectAttribute()
    {

        $project = $this->hasOne(ProjectsModel::class,"id","ProjectId");
        if ($project)
        {
            return $project->where("active",1)->first();
        }
        else
        {
            return "";
        }
    }
}
