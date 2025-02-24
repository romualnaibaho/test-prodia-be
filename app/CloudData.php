<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CloudData extends Model
{
    protected $table = 'cloud_data';
    protected $fillable = ['weather_condition_id', 'cloudiness'];
}
