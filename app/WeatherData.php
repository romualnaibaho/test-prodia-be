<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WeatherData extends Model
{
    protected $table = 'weather_data';
    protected $fillable = ['weather_condition_id', 'temperature', 'feels_like', 'temp_min', 'temp_max', 'pressure', 'humidity', 'visibility'];
}
