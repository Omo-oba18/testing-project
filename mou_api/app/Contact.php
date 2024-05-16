<?php

namespace App;

use App\Traits\ImageResize;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Contact extends Model
{
    use ImageResize, Notifiable;

    public static $subFolder = 'contact/';

    public const AVATAR_MAXSIZE = 1024 * 20;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'user_contact_id',
        'avatar',
        'name',
        'phone_number',
        'dial_code',
    ];

    /**
     * Relationship
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function userContact()
    {
        return $this->belongsTo('App\User', 'user_contact_id');
    }

    public function companyEmployees()
    {
        return $this->hasMany('App\CompanyEmployee', 'contact_id');
    }

    protected static function boot()
    {
        parent::boot();
        self::deleted(function ($model) {
            if ($model->avatar) {
                Contact::deleteImage($model->avatar);
            }
        });
    }

    /**
     * Send to SMS (with Twilio)
     *
     * @return string
     */
    public function routeNotificationForTwilio()
    {
        return $this->dial_code.$this->phone_number;
    }
}
