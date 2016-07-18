<?php

class PDOConnection
{
    protected static $instance = null;

    /**
     * PDOConnection constructor.
     */
    private function __construct() {}

    /**
     * @return null|PDOConnection
     */
    public static function getInstance()
    {
        if(is_null(self::$instance))
        {
            self::$instance = new PDOConnection();
        }
        return self::$instance;
    }

    /**
     * @return bool|PDO
     */
    public function getConnection()
    {
        if(file_exists(__DIR__.'/../config.php'))
        {
            include(__DIR__.'/../config.php');
            try
            {
                $pdo = new PDO($config['db']['dsn'], $config['db']['user'], $config['db']['password']);
            }
            catch (Exception $e)
            {
                echo $e->getMessage();
            }
            return $pdo;
        }

        return false;
    }
}