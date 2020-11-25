<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProjectCategoriesModel extends Model
{
    protected $table = "ProjectCategories";
    protected $primaryKey = "id";
    protected $appends = [
        'Manager',
    ];

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];

    public function getManagerAttribute()
    {
        if ($this->attributes['manager_id'])
        {
            return DB::table("Employee")->where(['Id' => $this->attributes['manager_id']])->first();
        }
        else
        {
            return null;
        }
    }


}
