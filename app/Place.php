<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    protected $fillable = ['name','x_coordinate','y_coordinate','initial_date','end_date','user_id'];

    protected $table = 'places';

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
