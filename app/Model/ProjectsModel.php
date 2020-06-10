<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ProjectsModel extends Model
{
    protected $table = "Projects";
    protected $primaryKey = "id";

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];
}
