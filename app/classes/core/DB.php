<?php declare(strict_types = 1);

/**
 * A simple PDO wrapper class.
 */
final class DB
{

    /**
     * Class instance.
     *
     * @static
     *
     * @var PDO
     */
    protected static $instance = null;

    /**
     * If PDO instance does not exist, create a new connection to DB.
     *
     * @return PDO  Returns the PDO instance.
     */
    public static function instance(): PDO
    {
        if (self::$instance === null) {
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_AUTOCOMMIT         => 0
            ];

            $dsn = DB_TYPE.':host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHARSET;

            self::$instance = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
        }

        return self::$instance;
    }

    /**
     * Allows to call PDO function using the wrapper.
     *
     * @param string $method    Method name.
     * @param array  $args      List of arguments passed to method.
     *
     * @return mixed
     */
    public static function __callStatic(string $method, array $args)
    {
        return call_user_func_array(array(self::instance(), $method), $args);
    }

    /**
     * Overwrites the PDO execute method.
     *
     * @param string $sql   SQL query.
     * @param array  $name  List of arguments that are used to prepare the SQL.
     *
     * @return PDOStatement|int|false
     */
    public static function execute(string $sql, array $args = [])
    {
        // If no arguments given, simply execute the query.
        if (!$args) {
            return self::instance()->query($sql);
        }

        // Otherwise start the transaction, prepare query, execute it and close the transaction.
        self::instance()->beginTransaction();
        $stmt = self::instance()->prepare($sql);
        $stmt->execute($args);

        self::instance()->commit();

        return $stmt;
    }
}
