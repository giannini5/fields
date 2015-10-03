<?php
/**
 * This file contains Model_Fields_PracticeFieldCoordinatorDB
 */

/**
 * Model_Fields_PracticeFieldCoordinatorDB class is the DB class for the PracticeFieldCoordinator database table
 */
class Model_Fields_PracticeFieldCoordinatorDB extends Model_Fields_BaseDB {
    // Schema
    const DB_SCHEMA_NAME = DB_FIELDS_RW;

    // Table information
    const DB_TABLE_NAME = 'practiceFieldCoordinator';

    // Columns constant
    const DB_COLUMN_ID          = 'id';
    const DB_COLUMN_LEAGUE_ID   = 'leagueId';
    const DB_COLUMN_EMAIL       = 'email';
    const DB_COLUMN_NAME        = 'name';
    const DB_COLUMN_PASSWORD    = 'password';

    /**
     * @brief: Constructor
     *
     * @return Model_Fields_PracticeFieldCoordinatorDB
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
        // For an insert the leagueId and email must be set
        // For a lookup the id or leagueId and email must be set
        // Not sure if this precondition makes sense
        // precondition(!empty($dataObject->{self::DB_COLUMN_ID}), "fields.practiceFieldCoordinator." . self::DB_COLUMN_ID . " not set");
        precondition(!empty($dataObject->{self::DB_COLUMN_LEAGUE_ID}), "fields.practiceFieldCoordinator." . self::DB_COLUMN_LEAGUE_ID . " not set");
        precondition(!empty($dataObject->{self::DB_COLUMN_EMAIL}), "fields.practiceFieldCoordinator." . self::DB_COLUMN_EMAIL . " not set");
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
        return array(Model_Fields_PracticeFieldCoordinatorDB::DB_COLUMN_ID => $this->{Model_Fields_PracticeFieldCoordinatorDB::DB_COLUMN_ID});
    }

    /**
     * create a new practiceFieldCoordinator
     *
     * @param $league - The league that the practice field coordinator represents
     * @param $email - Practice Field Coordinator's email (must be unique in the league)
     * @param $name - Name of the Practice Field Coordinator
     * @param $password - Practice Field Coordinator's password
     *
     * @return DataObject[]
     */
    public function create($league, $email, $name, $password) {
        $dataObject = new DataObject();
        $dataObject->{self::DB_COLUMN_LEAGUE_ID} = $league->id;
        $dataObject->{self::DB_COLUMN_EMAIL} = $email;
        $dataObject->{self::DB_COLUMN_NAME} = $name;
        $dataObject->{self::DB_COLUMN_PASSWORD} = $password;

        $this->insert($dataObject);

        return $this->getByEmail($league, $email);
    }

    /**
     * getById retrieves the practiceFieldCoordinator by unique identifier
     *
     * @param int $id - ID of practiceFieldCoordinator
     *
     * @return DataObject found or NULL if none found
     */
    public function getById($id) {
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_ID . "=" . $id);
        return (0 < count($dataObjectArray)) ? $dataObjectArray[0] : NULL;
    }

    /**
     * getByName retrieves the practiceFieldCoordinator by unique league/email
     *
     * @param $league - The league that the practice field coordinator represents
     * @param $email - Practice Field Coordinator's email (must be unique in the league)
     *
     * @return DataObject found or NULL if none found
     */
    public function getByEmail($league, $email) {
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_LEAGUE_ID . " = '" . $league->id . "' and " . self::DB_COLUMN_EMAIL . " ='" . $email . "'");
        return (0 < count($dataObjectArray)) ? $dataObjectArray[0] : NULL;
    }

    /**
     * getByLeague retrieves all of the practiceFieldCoordinator for the league
     *
     * @param $league - The league that the practice field coordinator represents
     *
     * @return array of DataObjects empty array if none found
     */
    public function getByLeague($league) {
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_LEAGUE_ID . " = " . $league->id);
        return $dataObjectArray;
    }
}