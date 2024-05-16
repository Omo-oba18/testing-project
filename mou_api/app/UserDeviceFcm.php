<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDeviceFcm extends Model
{
    public const DEVICES = [
        'ios', 'android', 'website',
    ];

    public const PERSONAL_APP = 'PERSONAL_APP';

    public const BUSINESS_APP = 'BUSINESS_APP';

    protected $fillable = ['user_id', 'token', 'device', 'app'];
}
