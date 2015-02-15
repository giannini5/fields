<?php
/**
 * Fields_Database class is the 'base' class for the Fields schema
 */
abstract class Model_Fields_BaseDB extends DAG_Database {

    public function __construct($schemaName, $tableName, $databaseType) {
        precondition(!empty($schemaName), "Database schema name is empty ... check your config file");
        precondition(!empty($tableName), "Database tableName is empty ... check your modelDB definition");

        $databaseConfig = new Database_Config();
        $databaseConfig->set('database', $schemaName);
        $databaseConfig->set('columns', $this->getDatabaseColumns());
        $databaseConfig->set('tableName', $tableName);

        parent::__construct($databaseType, $databaseConfig);
    }
}
?>
