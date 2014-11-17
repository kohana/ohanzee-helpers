<?php

namespace Ohanzee\Helper;

class Cookie
{
    // Separate the salt and the value
    const SALT_SEPARATOR = '~';

    /**
     * @var  string  Magic salt to add to the cookie
     */
    public static $salt = null;

    /**
     * @var  integer  Number of seconds before the cookie expires
     */
    public static $expiration = 0;

    /**
     * @var  string  Restrict the path that the cookie is available to
     */
    public static $path = '/';

    /**
     * @var  string  Restrict the domain that the cookie is available to
     */
    public static $domain = null;

    /**
     * @var  boolean  Only transmit cookies over secure connections
     */
    public static $secure = false;

    /**
     * @var  boolean  Only transmit cookies over HTTP, disabling Javascript access
     */
    public static $httponly = false;

    /**
     * Gets the value of a signed cookie. Cookies without signatures will not
     * be returned. If the cookie signature is present, but invalid, the cookie
     * will be deleted.
     *
     *     // Get the "theme" cookie, or use "blue" if the cookie does not exist
     *     $theme = Cookie::get('theme', 'blue');
     *
     * @param   string  $key        cookie name
     * @param   mixed   $default    default value to return
     * @return  string
     */
    public static function get($key, $default = null)
    {
        if (!isset($_COOKIE[$key])) {
            // The cookie does not exist
            return $default;
        }

        // Get the cookie value
        $cookie = $_COOKIE[$key];

        // Find the position of the split between salt and contents
        $split = strlen(static::salt($key, null));

        if (isset($cookie[$split]) && $cookie[$split] == static::SALT_SEPARATOR) {
            // Separate the salt and the value
            list ($hash, $value) = explode(static::SALT_SEPARATOR, $cookie, 2);

            if (static::salt($key, $value) === $hash) {
                // Cookie signature is valid
                return $value;
            }

            // The cookie signature is invalid, delete it
            static::delete($key);
        }

        return $default;
    }

    /**
     * Sets a signed cookie. Note that all cookie values must be strings and no
     * automatic serialization will be performed!
     *
     *     // Set the "theme" cookie
     *     Cookie::set('theme', 'red');
     *
     * @param   string  $name       name of cookie
     * @param   string  $value      value of cookie
     * @param   integer $expiration lifetime in seconds
     * @return  boolean
     * @uses    Cookie::salt
     */
    public static function set($name, $value, $expiration = null)
    {
        if ($expiration === null) {
            // Use the default expiration
            $expiration = static::$expiration;
        }

        if ($expiration !== 0) {
            // The expiration is expected to be a UNIX timestamp
            $expiration += time();
        }

        // Add the salt to the cookie value
        $value = static::salt($name, $value) . static::SALT_SEPARATOR. $value;

        return setcookie($name, $value, $expiration, static::$path, static::$domain, static::$secure, static::$httponly);
    }

    /**
     * Deletes a cookie by making the value null and expiring it.
     *
     *     Cookie::delete('theme');
     *
     * @param   string  $name   cookie name
     * @return  boolean
     */
    public static function delete($name)
    {
        // Remove the cookie
        unset($_COOKIE[$name]);

        // Nullify the cookie and make it expire
        return setcookie($name, null, -86400, static::$path, static::$domain, static::$secure, static::$httponly);
    }

    /**
     * Generates a salt string for a cookie based on the name and value.
     *
     *     $salt = Cookie::salt('theme', 'red');
     *
     * @param   string  $name   name of cookie
     * @param   string  $value  value of cookie
     * @return  string
     */
    public static function salt($name, $value)
    {
        // Require a valid salt
        if (!static::$salt) {
            throw new UnexpectedValueException(
                'A valid cookie salt is required. Please set Cookie::$salt before calling this method.' .
                'For more information check the documentation.'
            );
        }

        // Determine the user agent
        $agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : 'unknown';

        return sha1($agent . $name . $value . static::$salt);
    }
}
