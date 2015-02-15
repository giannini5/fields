<?php
/**
 * factory.php is the factory loader for the DAG modules
 */

require_once(SRC_LIB . 'common/mysql.php');

/**
 *
 * DAG_Factory is the factory loader for the DAG application
 *
 * All classes requiring cache and database object should use this factory
 */
class DAG_Factory extends DAG_Object
{
    /**
     * factoryInstances - static array for all the factory instances
     *
     * @access private
     * @var array
     */
    private static $factoryInstances = NULL;

    /* Factory types for singleton */
    const FACTORY_TYPE_CACHE    = 0;

    /* DB Types */
    const DB_ADAPTER_DEFAULTSQL = 0;

    /**
     * getDatabase will get the db object
     *
     * @return Database class object
     */
    public static function getDatabase(){
        $args = func_get_args();
        $databaseType = array_shift($args);

        if ($databaseType == self::DB_ADAPTER_DEFAULTSQL) {
            $reflect  = new ReflectionClass('Database_MySQL');
        }
        else {
            assertion(FALSE, 'Invalid database type passed to Pinger_Factory::getDatabase');
        }

        return $reflect->newInstanceArgs($args);
    }
}

?>
