<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ProjectCategoriesModel extends Model
{
    protected $table = "ProjectCategories";
    protected $primaryKey = "id";

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];
}
