<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'locations';
    protected $fillable = ['name', 'longitude', 'latitude', 'country'];

    public function weatherConditions()
    {
        return $this->hasMany(WeatherCondition::class, 'location_id');
    }
}
