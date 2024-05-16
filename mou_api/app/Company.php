<?php

namespace App;

use App\Helpers\Util;
use App\Traits\ImageResize;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use ImageResize;

    public static $subFolder = 'avatar/';

    public const AVATAR_MAXSIZE = 1024 * 20;

    protected $primaryKey = 'id';

    protected $table = 'companies';

    protected $fillable = ['name', 'email', 'country_code', 'city', 'address', 'logo', 'creator_id', 'working_days'];

    /*
     * RELATION SHIP
     */
    public function employees()
    {
        return $this->hasMany(CompanyEmployee::class);
    }

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    protected function logoUrl(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->logo ? Util::file_url($this->logo) : $this->logo,
        );
    }

    protected function workingDays(): Attribute
    {
        return new Attribute(
            get: function ($value) {
                if (is_string($value)) {
                    return json_decode($value, true);
                }

                if (is_array($value)) {
                    return $value;
                }

                return config('constant.working_days');
            }
        );
    }
}
