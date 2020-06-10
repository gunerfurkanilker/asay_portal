<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ProjectsModel extends Model
{
    protected $table = "Projects";
    protected $primaryKey = "id";
    protected $appends = [
        'Manager',
        'Category'
    ];

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];

    public function getManagerAttribute()
    {

        $manager = $this->hasOne(UserModel::class,"id","manager_id");

        if ($manager)
        {
            return $manager->first();
        }
        else
        {
            return null;
        }

    }

    public function getCategoryAttribute()
    {

        $manager = $this->hasMany(ProjectCategoriesModel::class,"project_id","id");
        if ($manager)
        {
            return $manager->get();
        }
        else
        {
            return null;
        }

    }


}
