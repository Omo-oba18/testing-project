<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VerifyEmail extends Model
{
    /** @var string get name table */
    protected $table = 'verify_emails';

    /** @var string get primary key of table */
    protected $primaryKey = 'email';

    /** @var array columns table */
    protected $fillable = ['email', 'token', 'expired_at'];
}
