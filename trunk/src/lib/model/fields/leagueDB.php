<?php
/**
 * This file contains Model_Fields_LeagueDB
 */

/**
 * Model_Fields_LeagueDB class is the DB class for the League database table
 */
class Model_Fields_LeagueDB extends Model_Fields_BaseDB {
    // Schema
    const DB_SCHEMA_NAME = DB_FIELDS_RW;

    // Table information
    const DB_TABLE_NAME = 'league';

    // Columns constant
    const DB_COLUMN_ID   = 'id';
    const DB_COLUMN_NAME = 'name';

    /**
     * @brief: Constructor
     *
     * @return Model_Fields_LeagueDB 
     */
    public function __construct() {
        parent::__construct(self::DB_SCHEMA_NAME, self::DB_TABLE_NAME, DAG_Factory::DB_ADAPTER_DEFAULTSQL);
    }

    /**
     * @brief: Check to make sure all the data passed for DB meets a minimum requirement
     *
     * @param: DataObject $dataObject
     */
    protected function _checkPreconditions(DataObject $dataObject) {
        // For an insert the name must be set
        // For a lookup the id or name must be set
        // Not sure if this precondition makes sense
        // precondition(!empty($dataObject->{self::DB_COLUMN_ID}), "fields.league." . self::DB_COLUMN_ID . " not set");
        precondition(!empty($dataObject->{self::DB_COLUMN_NAME}), "fields.league." . self::DB_COLUMN_NAME . " not set");
    }

    /**
     * Set default value for the elements that are not set
     *
     * @param DataObject $dataObject
     */
    protected function _setDefaults(DataObject &$dataObject) {
    }

    /**
     * Check to make sure all the data passed for DB update meets a minimum requirement
     *
     * @param DataObject $dataObject
     */
    protected function _checkUpdatePreconditions(DataObject $dataObject) {
    }

    /**
     * _getUpdateKeys will return the update keys
     *
     * @return array primaryKeys K => V
     */
    public function _getUpdateKeys() {
        return array(Model_Fields_LeagueDB::DB_COLUMN_ID => $this->{Model_Fields_LeagueDB::DB_COLUMN_ID});
    }

    /**
     * create a new league
     *
     * @param string $name - Name of the league (must be unique)
     *
     * @return DataObject[]
     */
    public function create($name) {
        $dataObject = new DataObject();
        $dataObject->{self::DB_COLUMN_NAME} = $name;

        $this->insert($dataObject);

        return $this->getByName($name);
    }

    /**
     * getById retrieves the league by unique identifier
     *
     * @param int $id - ID of league
     *
     * @return DataObject found or NULL if none found
     */
    public function getById($id) {
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_ID . "=" . $id);
        return (0 < count($dataObjectArray)) ? $dataObjectArray[0] : NULL;
    }

    /**
     * getByName retrieves the league by unique name
     *
     * @param string $name
     *
     * @return DataObject found or NULL if none found
     */
    public function getByName($name) {
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_NAME . " = '" . $name . "'");
        return (0 < count($dataObjectArray)) ? $dataObjectArray[0] : NULL;
    }
}
?>
