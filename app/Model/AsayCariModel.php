<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AsayCariModel extends Model
{
    protected $table = "asay_cari";
    protected $primaryKey = "ID";

    public $timestamps = false;
    protected $fillable = [];

    protected $hidden = [];
    protected $casts = [];
}
