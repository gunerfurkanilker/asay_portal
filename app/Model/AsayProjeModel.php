<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AsayProjeModel extends Model
{
    protected $table = "asay_proje";

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];
}
