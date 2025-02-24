<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SysInfo extends Model
{
    protected $table = 'sys_info';
    protected $fillable = ['weather_condition_id', 'sunrise', 'sunset', 'timezone_offset'];
}
