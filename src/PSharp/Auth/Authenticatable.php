<?php
namespace PSharp\Auth;

/**
 * Interface for user objects for authentication.
 */
interface Authenticatable
{
    /**
     * Retrieves the user ID.
     * 
     * @return mixed
     */
    public function getUserID();

    /**
     * Retrieves the field name for the user ID.
     * 
     * @return mixed
     */
    public function getUserIDField();

    /**
     * Retrieves the user name.
     * 
     * @return mixed
     */
    public function getUserName();

    /**
     * Retrieves the field name for the user name.
     * 
     * @return mixed
     */
    public function getUserNameField();

    /**
     * Retrieves the user password.
     * 
     * @return mixed
     */
    public function getUserPassword();

    /**
     * Retrieves the field name for the user password.
     * 
     * @return mixed
     */
    public function getUserPasswordField();

    /**
     * Retrieves the user token.
     * 
     * @return mixed
     */
    public function getUserToken();

    /**
     * Retrieves the user token.
     * 
     * @param string|null $token
     * @return mixed
     */
    public function setUserToken(string $token = null);

    /**
     * Retrieves the field name for the user token.
     * 
     * @return mixed
     */
    public function getUserTokenField();
}