<?php
/**
 * This file consists of mysql implementation of database
 */

require_once SRC_LIB . 'common/mysqlWrapper.php';

/**
 * class Database_Mysql is the mysql implementation for the database
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
 * escapeString
 */
class Database_MySQL implements Database_Interface
{
    /**
     * Last insert Id
     * @access private
     * @var int
     */
    private $m_lastId;

    /**
     * Number of rows affected in the last query
     *
     * @access private
     * @var int
     */
    private $m_rowsAffected;

    /**
     * database Handle
     * @access private
     * @var MySQL_Wrapper
     */
    private $m_dbHandle;

    /**
     * Base Database Config
     * @access private
     * @var Database_Config
     */
    private $m_baseDatabaseConfig;

    /**
     * Database_MySQL constructor
     */
    public function __construct() {
        $this->m_lastId = 0;
        $this->m_rowsAffected = 0;
        // TODO: Set other attibutes too
    }

    /**
     * getDatabaseConfig will return the config object
     *
     * @return Database_Config
     */
    public function getDatabaseConfig()
    {
        return $this->m_baseDatabaseConfig;
    }

    /**
     * getDB will get the database instance
     *
     * @param $database
     *
     * @return object the database object
     */
    protected function getDB($database = self::DB_NAME_RW_DEFAULT)
    {
        return MySQL_Wrapper::GetDBConnection($database, FALSE, '');
    }

    /**
     * setLastId will set the last id parameter
     *
     * @param integer $lastId the last id parameter
     */
    protected function setLastId($lastId)
    {
        $this->m_lastId = $lastId;
    }

    /**
     * getLastId will get last id parameter
     *
     * @return integer the last insert id
     */
    public function getLastId()
    {
        return $this->m_lastId;
    }

    /**
     * setRowsAffected sets the number of rows for this connection
     *
     * @param int $rowsAffected - Number of rows that was affected
     */
    protected function setNumberOfRowsAffected($rowsAffected) {
        $this->m_rowsAffected = $rowsAffected;
    }

    /**
     * getRowsAffected gets number of rows affected for the last query
     *
     * @return int $rowsAffected - Number of rows affected
     */
    public function getNumberOfRowsAffected() {
        return $this->m_rowsAffected;
    }

    /**
     * setDatabase will set the database handle using the database config
     *
     * @param Database_Config $databaseConfig - the database configuration object
     *
     * @throws Database_ConfigException
     */
    public function setDatabase(Database_Config $databaseConfig) {
        $this->validateDatabaseConfig($databaseConfig);

        $this->_setConfigDefaults($databaseConfig);

        $this->m_baseDatabaseConfig = $databaseConfig;
        $this->m_dbHandle = $this->getDB($this->getDatabaseConfig()->get('database'));
    }

    /**
     * setConfigDefaults will set the defaults for the config object
     *
     * @param Database_Config $databaseConfig - The config to set the defaults for
     */
    private function _setConfigDefaults($databaseConfig){
        // Set the defaults
        $columns = $databaseConfig->get('columns');
        if (!empty($columns) && is_array($columns)) {
            $databaseConfig->set('columnString', $this->getColumnString($databaseConfig->get('columns')));
        }
    }

    /**
     * getColumnString will return the columns as a string imploded by ','
     *
     * @param $dbColumns
     *
     * @return string imploded database columns
     */
    protected function getColumnString($dbColumns) {
        $columns = NULL;
        foreach($dbColumns as $column){
            $columns .= "`$column`,";
        }
        $columns = trim($columns, ',');

        return $columns;
    }

    /**
     * Validate the database configuration attributes.  Make sure none are missing
     * and all are set.
     *
     * @param Database_Config $databaseConfig - The database configuration object
     *
     * @throws Database_ConfigException
     */
    public function validateDatabaseConfig(Database_Config $databaseConfig) {
        $requiredConfigAttributes = array('database', 'tableName', 'columns');

        foreach ($requiredConfigAttributes as $attribute ) {
            if (!isset($databaseConfig->$attribute) || empty($databaseConfig->$attribute)) {
                throw new Database_ConfigException($attribute);
            }
        }
    }

    /**
     * insert will insert a row in the DB
     *
     * @param array|\DataObject $insertObject is the object data to insert column => value
     * @param Database_Config $databaseConfig - The database configuration object
     *
     * @return int id of the new row or NULL
     */
    public function insert(DataObject $insertObject, Database_Config $databaseConfig = NULL)
    {
        $columns        = NULL;
        $values         = NULL;
        foreach($insertObject->getProperties() as $key => $value){
            $columns        .= "`$key`,";
            $values         .= (!is_null($value)) ? "'" . $this->escapeString($value) . "'," : "NULL,";
        }
        $columns        = trim($columns, ',');
        $values         = trim($values, ',');

        $tableName  = $databaseConfig->get('tableName');
        $query      = "INSERT INTO `$tableName` ($columns) VALUES ($values) ";

        $this->_runQuery($query, $databaseConfig->get('database'));
        return $this->getLastId();
    }

    /**
     * insertIgnore will insert ignore a row in the DB
     *
     * @param array|\DataObject $insertObject is the object data to insert column => value
     * @param Database_Config $databaseConfig - The database configuration object
     *
     * @return int id of the new row or NULL
     */
    public function insertIgnore(DataObject $insertObject, Database_Config $databaseConfig = NULL) {
        $columns = NULL;
        $values  = NULL;

        foreach($insertObject->getProperties() as $key => $value) {
            $columns .= "`$key`,";
            $values  .= (!is_null($value)) ? "'" . $this->escapeString($value) . "'," : "NULL,";
        }
        $columns        = trim($columns, ',');
        $values         = trim($values, ',');

        $tableName  = $databaseConfig->get('tableName');
        $query      = "INSERT IGNORE INTO `$tableName` ($columns) VALUES ($values) ";

        $this->_runQuery($query, $databaseConfig->get('database'));
        return $this->getLastId();
    }

    /**
     * insert will insert a row in the DB
     *
     * @param DataObject $insertOrUpdateObject - is the object data to insert column => value
     * @param Database_Config $databaseConfig  - The database configuration object
     *
     * @return int id of the new row or NULL
     */
    public function insertOrUpdate(DataObject $insertOrUpdateObject, Database_Config $databaseConfig = NULL)
    {
        $columns        = NULL;
        $values         = NULL;
        $duplicateKey   = NULL;
        foreach($insertOrUpdateObject->getProperties() as $key => $value){
            $columns        .= "`$key`,";
            $values         .= (!is_null($value)) ? "'" . $this->escapeString($value) . "'," : "NULL,";
            $duplicateKey   .= "`$key` = VALUES(`$key`),";
        }
        $columns        = trim($columns, ',');
        $values         = trim($values, ',');
        $duplicateKey   = trim($duplicateKey, ',');

        $tableName  = $databaseConfig->get('tableName');
        $query      = "INSERT INTO `$tableName` ($columns) VALUES ($values) ON DUPLICATE KEY UPDATE $duplicateKey ";

        $this->_runQuery($query, $databaseConfig->get('database'));
        return $this->getLastId();
    }

    /**
     * insertMulti will do an multi insert into the DB
     *
     * @param array $insertObjects  -  is the object data to insert column => value
     * @param Database_Config $databaseConfig - The database configuration object
     *
     * @return null|results
     */
    public function insertMulti(array $insertObjects, Database_Config $databaseConfig = NULL)
    {
        if( empty($insertObjects) ){
            return NULL;
        }

        $columns = NULL;
        $values  = NULL;
        $columnsBuilt = FALSE;
        foreach( $insertObjects as $insertObject ){

            // Get the columns once and build the value array
            $valueString = NULL;
            foreach($insertObject->getProperties() as $key => $value){
                if( !$columnsBuilt ){
                    $columns        .= "`{$key}`,";
                }
                $valueString       .= (!is_null($value)) ? "'" . $this->escapeString($value) . "'," : "NULL,";
            }

            $valueString    = trim($valueString, ',');
            $values         .= '(' . $valueString . '),';
            $columnsBuilt   = TRUE;
        }

        $columns    = trim($columns, ',');
        $values     = trim($values, ',');

        $tableName  = $databaseConfig->get('tableName');
        $query      = "INSERT INTO `{$tableName}` ({$columns}) VALUES {$values} ";

        return $this->_runQuery($query, $databaseConfig->get('database'));
    }

    /**
     * insertOrUpdateMulti will do an multi insert into the DB on duplicate keys update
     *
     * @param array $insertObjects  - is the object data to insert column => value
     * @param Database_Config $databaseConfig - The database configuration object
     *
     * @return null|results
     */
    public function insertOrUpdateMulti(array $insertObjects, Database_Config $databaseConfig = NULL)
    {
        if( empty($insertObjects) ){
            return NULL;
        }

        $columns = NULL;
        $values  = NULL;
        $columnsBuilt = FALSE;
        $duplicateKey = NULL;

        foreach( $insertObjects as $insertObject ){

            // Get the columns once and build the value array
            $valueString = NULL;
            foreach($insertObject->getProperties() as $key => $value){
                if( !$columnsBuilt ){
                    $columns        .= "`{$key}`,";
                    $duplicateKey   .= "`{$key}` = VALUES(`{$key}`),";
                }
                $valueString       .= (!is_null($value)) ? "'" . $this->escapeString($value) . "'," : "NULL,";
            }

            $valueString    = trim($valueString, ',');
            $values         .= '(' . $valueString . '),';
            $columnsBuilt   = TRUE;
        }

        $columns      = trim($columns, ',');
        $values       = trim($values, ',');
        $duplicateKey = trim($duplicateKey, ',');

        $tableName  = $databaseConfig->get('tableName');
        $query      = "INSERT INTO `{$tableName}` ({$columns}) VALUES {$values}  ON DUPLICATE KEY UPDATE {$duplicateKey}";

        return $this->_runQuery($query, $databaseConfig->get('database'));
    }

    /**
     * update will update a row in the DB
     *
     * @param DataObject $updateObject - the dataobject to update column => value
     * @param array $updateKeys - the primary key values of the table to update
     * @param Database_Config $databaseConfig - The database configuration object
     *
     * @return NULL
     */
    public function update(DataObject $updateObject, array $updateKeys, Database_Config $databaseConfig = NULL)
    {

        $updateQuery = NULL;
        foreach($updateObject->getProperties() as $key => $value){
            $updateQuery .= (!is_null($value)) ? "`$key` = '" . $this->escapeString($value) . "'," : "`$key` = NULL,";;
        }

        $updateQuery = trim($updateQuery, ',');

        // Check for multi key update
        $where = $this->_buildWhere($updateKeys);

        $tableName  = $databaseConfig->get('tableName');
        $query      = "UPDATE `$tableName` SET $updateQuery WHERE $where";

        return $this->_runQuery($query, $databaseConfig->get('database'));
    }

    /**
     * delete will delete a row in the DB
     *
     * @param array $deleteKeys - the primaryKey value to delete
     * @param Database_Config $databaseConfig - The database configuration object
     *
     * @return result of the query
     */
    public function delete(array $deleteKeys, Database_Config $databaseConfig = NULL)
    {
        $where = $this->_buildWhere($deleteKeys);
        $tableName  = $databaseConfig->get('tableName');
        $query      = "DELETE FROM `$tableName` WHERE $where";

        return $this->_runQuery($query, $databaseConfig->get('database'));
    }

    /**
     * deleteWhere will delete rows from the DB given a where cause
     *
     * @param mixed $where - the where value for the sql
     * @param Database_Config $databaseConfig - The database configuration object
     *
     * @return NULL|mixed
     */
    public function deleteWhere($where, Database_Config $databaseConfig = NULL)
    {
        $tableName  = $databaseConfig->get('tableName');
        $query      = "DELETE FROM `$tableName` WHERE $where";

        return $this->_runQuery($query, $databaseConfig->get('database'));
    }

    /**
     * get will get a row from the DB
     *
     * @param array $updateKeys - The key-value to update
     * @param Database_Config $databaseConfig - The database configuration object
     *
     * @return array from the db
     */
    public function get(array $updateKeys, Database_Config $databaseConfig = NULL)
    {
        $where = $this->_buildWhere($updateKeys);

        $tableName  = $databaseConfig->get('tableName');
        $columns    = $databaseConfig->get('columnString');

        $query      = "SELECT $columns FROM `$tableName` WHERE $where";

        return $this->_runQuery($query, $databaseConfig->get('database'));
    }

    /**
     * getWhere will get a row from the DB given a where clause
     *
     * @param string $where - the where clause to run
     * @param Database_Config $databaseConfig - The database configuration object
     *
     * @return array of DataObjects from the db
     */
    public function getWhere($where, Database_Config $databaseConfig = NULL)
    {
        $tableName  = $databaseConfig->get('tableName');
        $columns    = $databaseConfig->get('columnString');

        $query      = "SELECT $columns FROM `$tableName` WHERE {$where}";
        return $this->_runQuery($query, $databaseConfig->get('database'));
    }

    /**
     * runCustomQuery will run the query
     *
     * @param string $query - sql to run
     * @param Database_Config $databaseConfig - The database configuration object
     *
     * @return array of DataObjects from the db
     */
    public function runCustomQuery($query, Database_Config $databaseConfig = NULL)
    {
        return $this->_runQuery($query, $databaseConfig->get('database'));
    }

    /**
     * fetch will get a row from the DB given a query
     *
     * @param string $query - sql to run
     * @param Database_Config $databaseConfig - The database configuration object
     *
     * @return single DataObject
     */
    public function fetch($query, Database_Config $databaseConfig = NULL)
    {
        $query .= " LIMIT 1 ";
        return $this->_runQuery($query, $databaseConfig->get('database'));
    }

    /**
     * fetchMulti will get a rows from the DB given a query
     *
     * @param string $query - sql to run
     * @param Database_Config $databaseConfig - The database configuration object
     *
     * @return array of DataObjects from the db
     */
    public function fetchMulti($query, Database_Config $databaseConfig = NULL)
    {
        return $this->_runQuery($query, $databaseConfig->get('database'));
    }

    /**
     * _buildKeyValueArray will build an array for a where statement from a K => V pair
     *
     * @param array $whereKeyValues - the array key => values to use
     *
     * @return string for a WHERE clause
     */
    public function _buildKeyValueArray(array $whereKeyValues){

        if( empty($whereKeyValues) ){
            return $whereKeyValues;
        }

        $whereValues = array();

        foreach( $whereKeyValues as $key => $value ){
            $whereValues[] = " `$key` = '" . $this->escapeString($value) . "'";
        }

        return $whereValues;
    }

    /**
     * _buildWhere will build an AND clause for a where statement from a K => V pair
     *
     * @param array $whereKeyValues - the array key => values to use
     *
     * @return string for a WHERE clause
     */
    public function _buildWhere(array $whereKeyValues){
        return implode(' AND ', $this->_buildKeyValueArray($whereKeyValues));
    }


    /**
     * escapeString will escape a string ready for the database
     *
     * @param string $escapeString - the string to escape
     *
     * @return string that is database escaped
     */
    public function escapeString($escapeString)
    {
        return $this->m_dbHandle->EscapeString($escapeString);
    }

    /**
     * runQuery will run the query against the DB
     *
     * @param string $sql - the sql to run
     * @param string $database - the database to run the query on
     *
     * @return results of the query
     */
    public function _runQuery($sql, $database) {
        precondition(strlen($sql) > 0, "sql");

        $db = $this->m_dbHandle;
        if (!empty($database)) {
            $db = $this->getDB($database);
        }

        $result = $db->execSQLStmt($sql, NULL, NULL);

        $lastInsertId = $db->last_insert_id();
        if (!empty($lastInsertId)) {
            $this->setLastId($lastInsertId);
        }

        //number of rows affected
        $this->setNumberOfRowsAffected($db->getNumAffectedRows());

        return $result;
    }
}
?>
