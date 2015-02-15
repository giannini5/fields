<?php
/**
 * This is the config file for database and all the exceptions it throws
 */

/**
 * Database_Config is the config object database config
 *
 * @package Base
 * @subpackage Database
 */
class Database_Config extends DataObject {
    //configuration keys
    const DB_CONFIG_DATABASE    = 'database';
    const DB_CONFIG_COLUMNS     = 'columns';
    const DB_CONFIG_TABLE_NAME  = 'tableName';
}

/**
 *
 * Database_Config_Exception is the exception class for the
 * database config
 *
 * @package Base
 * @subpackage Database
 */
class Database_ConfigException extends DAG_Exception {
    public function __construct($missingAttribute){
        parent::__construct("Invalid Database_Config, missing attribute: $missingAttribute");
    }
}
?>
