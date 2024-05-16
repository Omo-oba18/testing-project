<?php

namespace App\Firebase;

use App\User;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;

class UserFirebase implements Authenticatable
{
    /**
     * The claims decoded from the JWT token.
     *
     * @var array
     */
    private $claims;

    public $user;

    /**
     * Creates a new authenticatable user from Firebase.
     */
    public function __construct($claims)
    {
        $this->claims = $claims;
        $this->user = User::where(\DB::raw('CONCAT(`dial_code`, `phone_number`)'), $this->claims['phone_number'])->first();

    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'sub';
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return (string) $this->claims['sub'];
    }

    public function getAll()
    {
        return $this->claims;
    }

    /**
     * Get the password for the user.
     *
     * @return string
     *
     * @throws Exception
     */
    public function getAuthPassword()
    {
        throw new Exception('No password for Firebase UserFirebase');
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     *
     * @throws Exception
     */
    public function getRememberToken()
    {
        throw new Exception('No remember token for Firebase UserFirebase');
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string  $value
     * @return void
     *
     * @throws Exception
     */
    public function setRememberToken($value)
    {
        throw new Exception('No remember token for Firebase UserFirebase');
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     *
     * @throws Exception
     */
    public function getRememberTokenName()
    {
        throw new Exception('No remember token for Firebase UserFirebase');
    }
}
