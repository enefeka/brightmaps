<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = ['name', 'password', 'email', 'status', 'role_id'];

    protected $table = 'users';

    public function places()
    {
        return $this->hasMany('App\Place');
    }

    public function roles()
    {
        return $this->belongsTo('App\Role');
    }
}
