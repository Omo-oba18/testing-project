<?php

namespace App;

use App\Helpers\Country;
use App\Helpers\Util;
use App\Traits\ImageResize;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use ImageResize, Notifiable;

    public static $subFolder = 'avatar/';

    public const AVATAR_MAXSIZE = 1024 * 20;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'dial_code',
        'country_code',
        'city',
        'avatar',
        'birthday',
        'gender',
        'facebook_id',
        'user_type',
        'connectycube_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        self::created(function ($model) {
            //link contact with user
            Contact::where('phone_number', $model->phone_number)
                ->where('dial_code', $model->dial_code)
                ->update(['user_contact_id' => $model->id]);
        });
    }

    public function getFullAddressAttribute()
    {
        $countries = Country::getCountries();

        $address = $this->city ? $this->city.', ' : '';
        $address .= $this->country_code ? $countries[strtolower($this->country_code)] : '';

        return $address;
    }

    public function getCityAttribute($value)
    {
        return ucfirst($value);
    }

    /**
     * Relationship
     */
    public function events()
    {
        return $this->hasMany('App\Event')->with('setting');
    }

    public function setting()
    {
        return $this->hasOne('App\UserSetting');
    }

    public function linkContacts()
    {
        return $this->hasMany('App\Contact', 'user_contact_id');
    }

    public function contacts()
    {
        return $this->hasMany('App\Contact');
    }

    public function deviceFCMs()
    {
        return $this->hasMany('App\UserDeviceFcm', 'user_id');
    }

    public function company()
    {
        return $this->hasOne('App\Company', 'creator_id');
    }

    /**
     * ------------------------------
     * Notification TO
     * ------------------------------
     */
    /**
     * Customer FCM Channel
     * Specifies the user's FCM token - send to Business app (toFcmBusiness)
     *
     * @return array
     */
    public function routeNotificationForFcmBusiness()
    {
        return $this->deviceFCMs()->where('app', UserDeviceFcm::BUSINESS_APP)->pluck('token')->toArray();
    }

    /**
     * Specifies the user's FCM token - send to Personal app
     *
     * @return array
     */
    public function routeNotificationForFcm()
    {
        return $this->deviceFCMs()->where('app', UserDeviceFcm::PERSONAL_APP)->pluck('token')->toArray();
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

    protected function avatarUrl(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->avatar ? Util::file_url($this->avatar) : $this->avatar,
        );
    }

    /**
     * Relationship
     */
    public function todos()
    {
        return $this->hasMany(Todo::class, 'creator_id');
    }
}
