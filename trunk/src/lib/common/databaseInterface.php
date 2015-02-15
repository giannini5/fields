<?php
/**
 * This File consists of database interface to implement when developing a DB abstraction layer
 */

/**
 * Class Database_Interface
 */
interface Database_Interface
{

    /**
     * setDatabase will set the database based on the options passed
     * @param Database_Config $databaseConfig - The database configuration object
     */
    public function setDatabase(Database_Config $databaseConfig);

    /**
     * validateDatabaseConfig will throw an exception if the database config is not valid
     *
     * @param Database_Config $databaseConfig - The database configuration object
     */
    public function validateDatabaseConfig(Database_Config $databaseConfig);

    /**
     * insert will insert a row in the database
     * @param DataObject $insertObject is the object data to insert column => value
     * @param Database_Config $databaseConfig - The database configuration object
     */
    public function insert(DataObject $insertObject, Database_Config $databaseConfig = NULL);

    /**
     * insertMulti will perform a multi row insert into the database
     * @param array $insertObjects is the object data to insert column => value
     * @param Database_Config $databaseConfig - The database configuration object
     */
    public function insertMulti(array $insertObjects, Database_Config $databaseConfig = NULL);

    /**
     * update will update a row in the database
     * @param DataObject $updateObject - the dataobject to update column => value
     * @param array $updateKeys - the primary key values of the table to update
     * @param Database_Config $databaseConfig - The database configuration object
     */
    public function update(DataObject $updateObject, array $updateKeys, Database_Config $databaseConfig = NULL);

    /**
     * delete will delete a row from the database
     * @param array $deleteKeys - the primaryKey value to delete
     * @param Database_Config $databaseConfig - The database configuration object
     */
    public function delete(array $deleteKeys, Database_Config $databaseConfig = NULL);

    /**
     * deleteWhere will delete a row from the database given a where clause
     * @param mixed $where - the where value for the sql
     * @param Database_Config $databaseConfig - The database configuration object
     */
    public function deleteWhere($where, Database_Config $databaseConfig = NULL);

    /**
     * get will return a row from the database
     * @param array $getKeys - the getKeys value to get
     * @param Database_Config $databaseConfig - The database configuration object
     * @return DataObject result
     *
     */
    public function get(array $getKeys, Database_Config $databaseConfig = NULL);

    /**
     * getWhere will return rows from the database
     * @param string $where - the where clause to run
     * @param Database_Config $databaseConfig - The database configuration object
     * @return DataObject results
     *
     */
    public function getWhere($where, Database_Config $databaseConfig = NULL);

    /**
     * fetch will return a row from the database given a query
     * @param string $query - sql to run
     * @param Database_Config $databaseConfig - The database configuration object
     * @return a single database object from the query results
     *
     */
    public function fetch($query, Database_Config $databaseConfig = NULL);

    /**
     * fetchMulti will return database objects from the database given a query
     * @param string $query - sql to run
     * @param Database_Config $databaseConfig - The database configuration object
     * @return Data objects result of the query
     *
     */
    public function fetchMulti($query, Database_Config $databaseConfig = NULL);

    /**
     * escapeString will db escape a string
     * @param string $stringToEscape is the string to escape
     * @return the string with escaped values
     *
     */
    public function escapeString($stringToEscape);

    /**
     * runCustomQuery will run a custom sql query
     * @param $query is query to run
     * @param Database_Config $databaseConfig
     * @return Data objects result of the query
     *
     */
    public function runCustomQuery($query, Database_Config $databaseConfig = NULL);

    /**
     * Insert or update multiple rows
     * @param array $insertObjects - are the data object to perform and insert or update multi on
     * @param Database_Config $databaseConfig
     * @return mixed
     */
    public function insertOrUpdateMulti(array $insertObjects, Database_Config $databaseConfig = NULL);
}

?>
