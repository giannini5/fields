<?php
/**
 * This file contains Model_Fields_TeamDB
 */

/**
 * Model_Fields_TeamDB class is the DB class for the Field database table
 */
class Model_Fields_TeamDB extends Model_Fields_BaseDB {
    // Schema
    const DB_SCHEMA_NAME = DB_FIELDS_RW;

    // Table information
    const DB_TABLE_NAME = 'team';

    // Columns constant
    const DB_COLUMN_ID            = 'id';
    const DB_COLUMN_DIVISION_ID   = 'divisionId';
    const DB_COLUMN_TEAM_NUMBER   = 'teamNumber';
    const DB_COLUMN_NAME          = 'name';

    /**
     * @brief: Constructor
     *
     * @return Model_Fields_TeamDB
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
        precondition(!empty($dataObject->{self::DB_COLUMN_DIVISION_ID}), "fields.team." . self::DB_COLUMN_DIVISION_ID . " not set");
        precondition(!empty($dataObject->{self::DB_COLUMN_TEAM_NUMBER}), "fields.team." . self::DB_COLUMN_TEAM_NUMBER . " not set");
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
        return array(Model_Fields_TeamDB::DB_COLUMN_ID => $this->{Model_Fields_TeamDB::DB_COLUMN_ID});
    }

    /**
     * create a new team
     *
     * @param $division - Model_Fields_Division instance
     * @param string $teamNumber - Unique number for the team in the division
     * @param string $name - name of the team
     *
     * @return DataObject[]
     */
    public function create($division, $teamNumber, $name) {
        $dataObject = new DataObject();
        $dataObject->{self::DB_COLUMN_DIVISION_ID} = $division->id;
        $dataObject->{self::DB_COLUMN_TEAM_NUMBER} = $teamNumber;
        $dataObject->{self::DB_COLUMN_NAME} = $name;

        $this->insert($dataObject);

        return $this->getByNumber($division, $teamNumber);
    }

    /**
     * getById retrieves the team by unique identifier
     *
     * @param int $id - ID of team
     *
     * @return DataObject found or NULL if none found
     */
    public function getById($id) {
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_ID . "=" . $id);
        return (0 < count($dataObjectArray)) ? $dataObjectArray[0] : NULL;
    }

    /**
     * getByNumber retrieves the team by unique division/teamNumber
     *
     * @param $division - The division that the practice field coordinator represents
     * @param $teamNumber - Team's unique number within the division
     * @param $divisionId - Optional division identifier
     *
     * @return DataObject found or NULL if none found
     */
    public function getByNumber($division, $teamNumber, $divisionId = NULL) {
        $divisionId = isset($divisionId) ? $divisionId : $division->id;
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_DIVISION_ID . " = '" . $divisionId . "' and " . self::DB_COLUMN_TEAM_NUMBER . " ='" . $teamNumber . "'");
        return (0 < count($dataObjectArray)) ? $dataObjectArray[0] : NULL;
    }
}