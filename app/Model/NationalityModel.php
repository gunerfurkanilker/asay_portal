<?php

namespace App\Model;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class NationalityModel extends Model
{
    protected $primaryKey = "Id";
    protected $table = "Nationality";
    public $timestamps =false;
    protected $appends=[
        'Sym'
    ];

    public function getSymAttribute()
    {
        try {
            return Crypt::decryptString($this->attributes['Sym']);
        } catch (DecryptException $e) {
            return $e->getMessage();
        }
    }
}
