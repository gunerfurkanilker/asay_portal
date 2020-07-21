<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DomainModel extends Model
{
    protected $table = "Domains";
    protected $primaryKey = "id";
    public $timestamps = false;

}
