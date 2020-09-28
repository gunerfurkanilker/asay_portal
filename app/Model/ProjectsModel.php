<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ProjectsModel extends Model
{
    protected $table = "Projects";
    protected $primaryKey = "id";
    protected $appends = [
        'Manager',
        'ProjectCategory'
    ];

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];

    public function getManagerAttribute()
    {

        $manager = $this->hasOne(EmployeeModel::class,"Id","manager_id");

        if ($manager)
        {
            return $manager->first();
        }
        else
        {
            return null;
        }

    }

    public function getProjectCategoryAttribute()
    {

        $category = $this->hasOne(ProjectCategoriesModel::class,"project_id","id");

        if ($category)
        {
            return $category->first();
        }
        else
        {
            return null;
        }

    }



}
