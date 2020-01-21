<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserTokensModel extends Model
{
    protected $table = "user_tokens";

    public $timestamps = false;
    protected $fillable = [];
    protected $hidden = [];
    protected $casts = [];

    public function getUserId($token)
    {
        $tokenSearch = self::where("user_token", $token)->first();
        return $tokenSearch->user_id;
    }
}
