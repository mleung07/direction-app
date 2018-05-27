<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'location';

    protected $fillable = ['route_id', 'lat', 'lng', 'category'];

    public function route()
    {
        return $this->belongsTo('App\Route');
    }
}
