<?php
/**
 * This file contains Model_Fields_LocationDB
 */

/**
 * Model_Fields_LocationDB class is the DB class for the Location database table
 */
class Model_Fields_LocationDB extends Model_Fields_BaseDB {
    // Schema
    const DB_SCHEMA_NAME = DB_FIELDS_RW;

    // Table information
    const DB_TABLE_NAME = 'location';

    // Columns constant
    const DB_COLUMN_ID          = 'id';
    const DB_COLUMN_LEAGUE_ID   = 'leagueId';
    const DB_COLUMN_NAME        = 'name';

    /**
     * @brief: Constructor
     *
     * @return Model_Fields_LocationDB
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
        // precondition(!empty($dataObject->{self::DB_COLUMN_ID}), "fields.location." . self::DB_COLUMN_ID . " not set");
        precondition(!empty($dataObject->{self::DB_COLUMN_LEAGUE_ID}), "fields.location." . self::DB_COLUMN_LEAGUE_ID . " not set");
        precondition(!empty($dataObject->{self::DB_COLUMN_NAME}), "fields.location." . self::DB_COLUMN_NAME . " not set");
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
        return array(Model_Fields_LocationDB::DB_COLUMN_ID => $this->{Model_Fields_LocationDB::DB_COLUMN_ID});
    }

    /**
     * create a new location
     *
     * @param int $leagueId - League identifier
     * @param string $name - Name of the location (must be unique within the league)
     *
     * @return DataObject[]
     */
    public function create($leagueId, $name) {
        $dataObject = new DataObject();
        $dataObject->{self::DB_COLUMN_LEAGUE_ID} = $leagueId;
        $dataObject->{self::DB_COLUMN_NAME} = $name;

        $this->insert($dataObject);

        return $this->getByName($leagueId, $name);
    }

    /**
     * getById retrieves the location by unique identifier
     *
     * @param int $id - ID of location
     *
     * @return DataObject found or NULL if none found
     */
    public function getById($id) {
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_ID . "=" . $id);
        return (0 < count($dataObjectArray)) ? $dataObjectArray[0] : NULL;
    }

    /**
     * getByName retrieves the location by unique name
     *
     * @param int $leagueId - League identifier
     * @param string $name - Name of the location
     *
     * @return DataObject found or NULL if none found
     */
    public function getByName($leagueId, $name) {
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_LEAGUE_ID . " = '" . $leagueId . "' and " . self::DB_COLUMN_NAME . " = '" . $name . "'");
        return (0 < count($dataObjectArray)) ? $dataObjectArray[0] : NULL;
    }


    /**
     * getByLeague retrieves the list of location data objects
     *
     * @param int $leagueId - League identifier
     *
     * @return DataObject found or NULL if none found
     */
    public function getByLeague($leagueId) {
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_LEAGUE_ID . " = '" . $leagueId . "'");
        return $dataObjectArray;
    }
}
