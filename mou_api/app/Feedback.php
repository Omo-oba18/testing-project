<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $fillable = ['user_id', 'content'];

    protected static function boot()
    {
        parent::boot();
        self::creating(function (Feedback $model) {
            if (\Auth::check()) {
                $model->user_id = \Auth::user()->user->id;
            }
        });
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
