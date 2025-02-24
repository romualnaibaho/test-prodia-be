<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WeatherCondition extends Model
{
    protected $table = 'weather_conditions';
    protected $fillable = ['location_id', 'main', 'description', 'icon'];

    public function weatherData()
    {
        return $this->hasOne(WeatherData::class, 'weather_condition_id');
    }

    public function windData()
    {
        return $this->hasOne(WindData::class, 'weather_condition_id');
    }

    public function cloudData()
    {
        return $this->hasOne(CloudData::class, 'weather_condition_id');
    }

    public function sysInfo()
    {
        return $this->hasOne(SysInfo::class, 'weather_condition_id');
    }
}
