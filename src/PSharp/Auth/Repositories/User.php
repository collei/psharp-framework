<?php
namespace PSharp\Auth\Repositories;

use PSharp\Auth\Authenticatable;
use PSharp\Auth\Traits\UserTrait;
use PSharp\Auth\Traits\AuthenticatableTrait;
use PSharp\Support\Str;

/**
 * User repository from file.
 */
class User implements Authenticatable
{
    use UserTrait;
    use AuthenticatableTrait;

    /**
     * Initialize user instance.
     * 
     * @param int|string|null $id
     * @param string $username
     * @param string $password
     * @param string|null $token
     */
    public function __construct($id = null, $username = '', $password = '', $token = null)
    {
        if (empty($id)) {
            $this->id = Str::uuid4();
        }

        $this->username = $username;
        $this->password = $password;
        $this->token = $token;
    }
}