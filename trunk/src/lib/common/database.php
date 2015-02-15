<?php
/**
 * This File consists of abstract database implementations for all DAG databases
 */

require_once(SRC_LIB . 'common/databaseInterface.php');

/**
 * abstract class DAG_Database is the base class for all database classes
 *
 * All database classes should inherit from this class.
 *
 * Will provide the following functions
 *
 * insert
 * update
 * delete
 * deleteWhere
 * get
 * getWhere
 * fetch
 * fetchMulti
 *
 * Children must implement
 *
 * _checkPreconditions
 * _checkUpdatePreconditions
 * _setDefaults
 *
 * Suggested Overloads
 * _getDataObjectFromDbRow - Overload if you like using named DataObjects instead of the generic one.
 *
 */
class DAG_Database implements Database_Interface {

    /**
     * Database config file
     * @access protected
     * @var Database_Config
     */
    protected $m_databaseConfig;

    /**
     * database Handle
     * @access private
     * @var Database_Mysql
     */
    private $m_dbHandle;

    /**
     * Constructor will set the attributes for the object
     *
     * @param $databaseType   - Constant to define the database type to use. Must be one of DAG_Factory::DB_ADAPTER_*
     * @param Database_Config $databaseConfig - The database configuration object
     */
    public function __construct($databaseType, Database_Config $databaseConfig) {
        $this->m_dbHandle = DAG_Factory::getDatabase($databaseType);
        $this->setDatabase($databaseConfig);
    }

    /**
     * setDatabase will set the database using the configuration
     *
     * @param Database_Config $databaseConfig - The database configuration object
     */
    public function setDatabase(Database_Config $databaseConfig) {
        $this->m_dbHandle->setDatabase($databaseConfig);
    }

    /**
     * getDatabaseConfig get the config that is available or return the class object
     * child object
     *
     * @param Database_Config $databaseConfig - The database configuration object
     *
     * @return Database_Config
     */
    public function getDatabaseConfig(Database_Config $databaseConfig = NULL) {

        // If the one we have is not empty
        if (!empty($databaseConfig)) {
            $this->validateDatabaseConfig($databaseConfig);
            return $databaseConfig;
        }

        return $this->m_dbHandle->getDatabaseConfig();
    }

    /**
     * isValidDatabaseConfig will return true or false if the database config is valid
     *
     * @param Database_Config $databaseConfig - The database configuration object
     *
     * @throws Database_ConfigException
     */
    public function validateDatabaseConfig(Database_Config $databaseConfig) {
        $this->m_dbHandle->validateDatabaseConfig($databaseConfig);
    }

    /**
     * getDatabaseColumns will return the database columns defined in
     * child class with DB_COLUMN_*
     *
     * @return array
     */
    public function getDatabaseColumns() {
        $reflection = new ReflectionClass($this);
        $dbColumns = $reflection->getConstants();

        $databaseColumns = array();
        foreach ($dbColumns as $key => $dbField) {

            //Check for DB constants only
            if (strpos($key, "DB_COLUMN") !== FALSE) {
                $databaseColumns[$dbField] = $dbField;
            }
        }
        return $databaseColumns;
    }

    /**
     * insert inserts a row in the DB
     *
     * @param DataObject $insertObject          - The data object to insert column => value
     * @param Database_Config $databaseConfig   - The database configuration object
     *
     * @return int id of the new row or NULL
     */
    public function insert(DataObject $insertObject, Database_Config $databaseConfig = NULL) {
        precondition(
            ($insertObject instanceof DataObject),
            "Insert argument must be an array given -- " . gettype($insertObject)
        );

        // Call the children's checks and defaults
        $this->_setDefaults($insertObject);
        $this->_checkPreconditions($insertObject);

        $this->m_dbHandle->insert($insertObject, $this->getDatabaseConfig($databaseConfig));
        return $this->m_dbHandle->getLastId();
    }

    /**
     * insertIgnore inserts a row in the DB, but will ignore duplicate keys
     *
     * @param DataObject $insertObject          - The data object to insert column => value
     * @param Database_Config $databaseConfig   - The database configuration object
     *
     * @return int id of the new row or NULL
     */
    public function insertIgnore(DataObject $insertObject, Database_Config $databaseConfig = NULL) {
        precondition(
            ($insertObject instanceof DataObject),
            "Insert argument must be an array given -- " . gettype($insertObject)
        );

        // Call the childrens checks and defaults
        $this->_setDefaults($insertObject);
        $this->_checkPreconditions($insertObject);

        $this->m_dbHandle->insertIgnore($insertObject, $this->getDatabaseConfig($databaseConfig));
        return $this->m_dbHandle->getLastId();
    }

    /**
     * insertMulti will do an multi insert into the DB
     *
     * @param array           $insertObjects
     * @param Database_Config $databaseConfig - The database configuration object
     *
     * @return int - Number of rows affected
     */
    public function insertMulti(array $insertObjects, Database_Config $databaseConfig = NULL) {
        if (empty($insertObjects)) {
            return NULL;
        }

        foreach ($insertObjects as $insertObject) {

            if (!$insertObject instanceof DataObject) {
                // TODO exception of invalid object in array
            }

            $this->_setDefaults($insertObject);
            $this->_checkPreconditions($insertObject);

        }

        $this->m_dbHandle->insertMulti($insertObjects, $this->getDatabaseConfig($databaseConfig));
        return $this->getNumberOfRowsAffected();
    }

    /**
     * insertMulti will do an multi insert into the DB
     *
     * @param array           $insertObjects
     * @param Database_Config $databaseConfig - The database configuration object
     *
     * @return int - Number of rows affected
     */
    public function insertOrUpdateMulti(array $insertObjects, Database_Config $databaseConfig = NULL) {
        if (empty($insertObjects)) {
            return NULL;
        }

        foreach ($insertObjects as $insertObject) {

            if (!$insertObject instanceof DataObject) {
                // TODO exception of invalid object in array
            }

            $this->_setDefaults($insertObject);
            $this->_checkPreconditions($insertObject);
        }

        $this->m_dbHandle->insertOrUpdateMulti($insertObjects, $this->getDatabaseConfig($databaseConfig));
        return $this->getNumberOfRowsAffected();
    }

    /**
     * update will update a row in the DB
     *
     * @param DataObject      $updateObject
     * @param array           $updateKeys     ($column => $value) $updateKeys - the primary key => value pairs of the table to update
     * @param Database_Config $databaseConfig - The database configuration object
     *
     * @return int - Number of rows that were affected by the query
     */
    public function update(DataObject $updateObject, array $updateKeys, Database_Config $databaseConfig = NULL) {
        preconditionNonEmptyArray($updateKeys, "updateKeys");
        precondition(
            ($updateObject instanceof DataObject),
            "Update argument must be data object given -- " . gettype($updateObject)
        );

        // Call the childrens conditions
        $this->_checkUpdatePreconditions($updateObject);
        $this->m_dbHandle->update($updateObject, $updateKeys, $this->getDatabaseConfig($databaseConfig));
        return $this->getNumberOfRowsAffected();
    }

    /**
     * insert will insert a row in the DB
     *
     * @param DataObject      $insertorUpdateObject
     * @param Database_Config $databaseConfig - The database configuration object
     *
     * @return int id of the new row or NULL
     */
    public function insertOrUpdate(DataObject $insertorUpdateObject, Database_Config $databaseConfig = NULL) {
        precondition(
            ($insertorUpdateObject instanceof DataObject),
            "InsertOrUpdate argument must be an DataObject given -- " . gettype($insertorUpdateObject)
        );

        // Call the childrens checks and defaults
        $this->_setDefaults($insertorUpdateObject);
        $this->_checkPreconditions($insertorUpdateObject);

        $this->m_dbHandle->insertOrUpdate($insertorUpdateObject, $this->getDatabaseConfig($databaseConfig));
        return $this->m_dbHandle->getLastId();
    }

    /**
     * delete will delete a row in the DB
     *
     * @param array $deleteKeys - the primary key => value pairs of the table to delete
     * @param Database_Config $databaseConfig - The database configuration object
     *
     * @return int - Number of rows affected
     */
    public function delete(array $deleteKeys, Database_Config $databaseConfig = NULL) {
        precondition(count($deleteKeys) > 0, "deleteKeys");
        $this->m_dbHandle->delete($deleteKeys, $this->getDatabaseConfig($databaseConfig));

        return $this->getNumberOfRowsAffected();
    }

    /**
     * deleteWhere will delete rows from the DB given a where cause
     *
     * @param string $where - the where value for the sql
     * @param Database_Config $databaseConfig - The database configuration object
     *
     * @return int - Number of rows affected
     */
    public function deleteWhere($where, Database_Config $databaseConfig = NULL) {
        precondition(strlen($where) > 0, "where");
        $this->m_dbHandle->deleteWhere($where, $this->getDatabaseConfig($databaseConfig));

        return $this->getNumberOfRowsAffected();
    }

    /**
     * get will get a row from the DB
     *
     * @param array           $updateKeys
     * @param Database_Config $databaseConfig - The database configuration object
     *
     * @return DataObject from the db
     */
    public function get(array $updateKeys, Database_Config $databaseConfig = NULL) {
        precondition(count($updateKeys) > 0, "updateKeys");
        $results = $this->m_dbHandle->get($updateKeys, $this->getDatabaseConfig($databaseConfig));

        // We did not have a hit on the get
        if (empty($results)) {
            return array();
        }

        // It is a get we only want one value
        $dbObjects = $this->_getDataObjects($results);
        return $dbObjects[0];
    }

    /**
     * getWhere will get a row from the DB given a where clause
     *
     * @param string          $where          - the where clause to run
     * @param Database_Config $databaseConfig - The database configuration object
     *
     * @return array of DataObject from the db
     */
    public function getWhere($where, Database_Config $databaseConfig = NULL) {
        precondition(strlen($where) > 0, "where");
        $results = $this->m_dbHandle->getWhere($where, $this->getDatabaseConfig($databaseConfig));

        // We did not have a hit on the getWhere
        if (empty($results)) {
            $results = array();
        }

        // Return all the values
        return $this->_getDataObjects($results);
    }

    /**
     * runCustomQuery will run the query
     *
     * @param string          $query          - sql to run
     * @param Database_Config $databaseConfig - The database configuration object
     *
     * @return array|\Data of DataObjects from the db
     */
    public function runCustomQuery($query, Database_Config $databaseConfig = NULL) {
        precondition(strlen($query) > 0, "query");
        $results = $this->m_dbHandle->runCustomQuery($query, $this->getDatabaseConfig($databaseConfig));

        // We did not have a hit on the fetchMulti
        if (empty($results)) {
            return array();
        }

        // Return all the values
        return $this->_getDataObjects($results);
    }

    /**
     * getNumberOfRowsAffected gets number of rows that were affected in the last query
     *
     * @return int - Number of rows affected
     */
    public function getNumberOfRowsAffected() {
        return $this->m_dbHandle->getNumberOfRowsAffected();
    }

    /**
     * fetch will get a row from the DB given a query
     *
     * @param string          $query          - SQL query to run
     * @param Database_Config $databaseConfig - The database configuration object
     *
     * @return DataObject $dbObject
     */
    public function fetch($query, Database_Config $databaseConfig = NULL) {
        precondition(strlen($query) > 0, "query");
        $results = $this->m_dbHandle->fetch($query, $this->getDatabaseConfig($databaseConfig));

        // We did not have a hit on the fetch
        if (empty($results)) {
            return array();
        }

        // It is a single fetch we only want one value
        $dbObjects = $this->_getDataObjects($results);
        return $dbObjects[0];
    }

    /**
     * fetchMulti will get a rows from the DB given a query
     *
     * @param string          $query          - sql to run
     * @param Database_Config $databaseConfig - The database configuration object
     *
     * @return array - List of DataObjects
     */
    public function fetchMulti($query, Database_Config $databaseConfig = NULL) {
        precondition(strlen($query) > 0, "query");
        $results = $this->m_dbHandle->fetchMulti($query, $this->getDatabaseConfig($databaseConfig));

        // We did not have a hit on the fetchMulti
        if (empty($results)) {
            return array();
        }

        // Return all the values
        return $this->_getDataObjects($results);
    }

    /**
     * runStoredProcedure will build the stored procedure SQL and run it
     *
     * @param $name is the name of the stored procedure to run
     * @param array $parameters  is the array to use for the parameters in the stored procedure
     * @param string $mode is the mode to run in either CALL or EXECUTE OPTIONAL default CALL
     * @param Database_Config $databaseConfig is the name of the database connection to use for this stored proc. Defaults to DB_NAME_RW
     *
     * @return DB
     */
    public function runStoredProcedure($name, array $parameters, $mode = "CALL", Database_Config $databaseConfig = NULL) {
        return $this->fetchMulti($this->getStoredProcedureSQL($name, $parameters, $mode), $databaseConfig);
    }

    /**
     * escapeString will escape a string ready for the database
     *
     * @param string $escapeString is the string to escape
     *
     * @return string|\the that is database escaped
     */
    public function escapeString($escapeString) {
        if (!is_string($escapeString)) {
            return $escapeString;
        }

        return $this->m_dbHandle->EscapeString($escapeString);
    }

    /**
     * escapeStringArray will escape an array ready for the database
     *
     * @param array escapeStringArray is the array to escape
     *
     * @return array with each element escaped
     */
    public function escapeStringArray(array $escapeStringArray) {
        return array_map(array($this, 'escapeString'), $escapeStringArray);
    }

    /**
     * getStoredProcedureSQL will return a stored procedure SQL statement with values escaped
     *
     * @param $name
     * @param array $parameters is the array to use for the parameters in the stored procedure
     * @param string $mode
     *
     * @return string
     */
    public function getStoredProcedureSQL($name, array $parameters, $mode = "CALL") {
        $parameterString = NULL;
        if (!empty($parameters)) {
            $parameters         = $this->escapeStringArray($parameters);
            $parameterString    = "'" . implode("','", $parameters) . "'";
        }
        return " $mode $name ($parameterString) ";
    }

    /**
     * getDBObjects will take the database rows and return an array of objects
     *
     * @param array $rows are the rows to get objects for
     *
     * @return array of DataObject class
     */
    public function _getDataObjects(array $rows) {
        // Set the database objects
        $dataObjects = array();
        foreach($rows as $row){
            $dataObjects[] = $this->_getDataObjectFromDbRow($row);
        }

        return $dataObjects;
    }

    /**
     * Will get a DataObject from a database row.
     *
     * @param array $row
     *
     * @return DataObject
     */
    public function _getDataObjectFromDbRow($row) {
        $dataObject = new DataObject();
        $dataObject->setProperties($row);
        return $dataObject;
    }

    /**
     * _checkPreconditions will set the check the pre conditions for insert operations
     *
     * @abstract
     *
     * @param DataObject $dataObject is the object data to use to check
     */
    protected function _checkPreconditions(DataObject $dataObject) {}

    /**
    * _checkUpdatePreconditions will set the check the pre conditions for update operations
     *
    * @abstract
     *
    * @param DataObject $dataObject is the object data to use to check
    */
    protected function _checkUpdatePreconditions(DataObject $dataObject) {}

    /**
     * _setDefaults will set the default values for insert/update operations
     *
     * @abstract
     *
     * @param DataObject &$dataObject is the object data to set defaults for
     */
    protected function _setDefaults(DataObject &$dataObject) {}
}

?>
