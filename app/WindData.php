<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WindData extends Model
{
    protected $table = 'wind_data';
    protected $fillable = ['weather_condition_id', 'speed', 'direction'];
}
