<?php

namespace Remp\LaravelSso\Contracts\Jwt;

use Illuminate\Foundation\Auth\User as BaseUser;
//use Illuminate\Database\Eloquent\Model;
use Remp\LaravelSso\Contracts\SsoException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Arrayable;
use Spatie\Permission\Traits\HasRoles;

class User extends BaseUser implements Authenticatable, Arrayable
{
    use HasRoles;

    public $id;

    public $name;

    public $email;

    public $scopes;

    //required for spatie/laravel-permissions
    public $guard_name = 'jwtx';

    /**
     * Get the name of the unique identifier for the user.
     * @return string
     * @throws SsoException
     */
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    /**
     * Get the unique identifier for the user.
     * @return mixed
     * @throws SsoException
     */
    public function getAuthIdentifier()
    {
        return $this->id;
    }

    /**
     * Get the password for the user.
     * @return string
     * @throws SsoException
     */
    public function getAuthPassword()
    {
        throw new SsoException("jwt doesn't support password authentication");
    }

    /**
     * Get the token value for the "remember me" session.
     * @return string
     * @throws SsoException
     */
    public function getRememberToken()
    {
        throw new SsoException("jwt doesn't support remember token");
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string $value
     * @return void
     * @throws SsoException
     */
    public function setRememberToken($value)
    {
        throw new SsoException("jwt doesn't support remember token");
    }

    /**
     * Get the column name for the "remember me" token.
     * @return string
     * @throws SsoException
     */
    public function getRememberTokenName()
    {
        throw new SsoException("jwt doesn't support remember token");
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'scopes' => $this->scopes,
        ];
    }

    public function getKey()
    {
        return $this->getAuthIdentifier();
    }
}
