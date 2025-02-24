<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OTP extends Model
{
    protected $table = 'otps';
    protected $fillable = ['user_id', 'otp_code', 'expired_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
