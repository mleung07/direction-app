<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $table = 'route';

    protected $fillable = ['distance', 'time', 'token', 'status'];

    const STATUS_PROGRESS = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_FAIL = 3;

    public function locations()
    {
        return $this->hasMany('App\Location');
    }
}
