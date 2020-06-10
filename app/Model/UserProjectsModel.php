<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserProjectsModel extends Model
{
    protected $table = 'user_projects';
    protected $primaryKey = 'id';

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];



}
