<?php declare(strict_types = 1);

/**
 * A session wrapper class to handle the PHP sessions.
 */
class Session
{

    /**
     * Flag indicating if session was created.
     *
     * @static
     *
     * @var bool
     */
    protected static $session_started = false;

    /**
     * Initialize session.
     *
     * @static
     *
     * @throws Exception if session cannot be started.
     */
    public static function start(): void
    {
        if (!self::$session_started) {
            if (!session_start()) {
                throw new Exception(__('Error! Cannot start session.'));
            }

            self::$session_started = true;
        }
    }

    /**
     * Generate a new Session ID.
     *
     * @static
     */
    public static function restart(): void
    {
        if (self::$session_started) {
            session_regenerate_id();
        }
    }

    /**
     * If session was started, get the Session ID and return it.
     *
     * @static
     */
    public static function getId()
    {
        if (self::$session_started) {
            return session_id();
        }
    }

    /**
     * Sets Session value by key.
     *
     * @static
     *
     * @param string $key
     * @param mixed  $value
     */
    public static function setValue(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Check if Session value exists by given key.
     *
     * @static
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return bool     Returns true if given key is found or returns false if key is not found.
     */
    public static function exists(string $key): bool
    {
        return array_key_exists($key, $_SESSION);
    }

    /**
     * Returns value stored in Session by given key.
     *
     * @param string $key
     *
     * @return mixed|null   Returns arrays or strings if key is found in session or null if nothing was found.
     */
    public static function getValue(string $key)
    {
        return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : null;
    }

    /**
     * Removes the value from Session by given keys.
     *
     * @param array $keys
     */
    public static function unsetValues(array $keys): void
    {
        foreach ($keys as $key) {
            unset($_SESSION[$key]);
        }
    }
}
