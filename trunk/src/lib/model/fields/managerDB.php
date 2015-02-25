<?php
/**
 * This file contains Model_Fields_ManagerDB
 */

/**
 * Model_Fields_ManagerDB class is the DB class for the Field database table
 */
class Model_Fields_ManagerDB extends Model_Fields_BaseDB {
    // Schema
    const DB_SCHEMA_NAME = DB_FIELDS_RW;

    // Table information
    const DB_TABLE_NAME = 'manager';

    // Columns constant
    const DB_COLUMN_ID         = 'id';
    const DB_COLUMN_TEAM_ID    = 'teamId';
    const DB_COLUMN_NAME       = 'name';
    const DB_COLUMN_EMAIL      = 'email';
    const DB_COLUMN_PHONE      = 'phone';
    const DB_COLUMN_PASSWORD   = 'password';

    /**
     * @brief: Constructor
     *
     * @return Model_Fields_ManagerDB
     */
    public function __construct() {
        parent::__construct(self::DB_SCHEMA_NAME, self::DB_TABLE_NAME, DAG_Factory::DB_ADAPTER_DEFAULTSQL);
    }

    /**
     * @brief: Check to make sure all the data passed for DB meets a minimum requirement
     *
     * @param DataObject $dataObject
     *
     * @throws PreconditionException
     */
    protected function _checkPreconditions(DataObject $dataObject) {
        precondition(!empty($dataObject->{self::DB_COLUMN_TEAM_ID}), "fields.manager." . self::DB_COLUMN_TEAM_ID . " not set");
        precondition(!empty($dataObject->{self::DB_COLUMN_NAME}), "fields.manager." . self::DB_COLUMN_NAME . " not set");
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
        return array(Model_Fields_ManagerDB::DB_COLUMN_ID => $this->{Model_Fields_ManagerDB::DB_COLUMN_ID});
    }

    /**
     * create a new manager
     *
     * @param $team - Model_Fields_Team instance
     * @param $name - Name for the Manager
     * @param $email - Email for the Manager
     * @param $phone - Phone Number for the Manager
     * @param $password - Password for the Manager
     *
     * @return DataObject[]
     */
    public function create($team, $name, $email, $phone, $password) {
        $dataObject = new DataObject();
        $dataObject->{self::DB_COLUMN_TEAM_ID} = $team->id;
        $dataObject->{self::DB_COLUMN_NAME} = $name;
        $dataObject->{self::DB_COLUMN_EMAIL} = $email;
        $dataObject->{self::DB_COLUMN_PHONE} = $phone;
        $dataObject->{self::DB_COLUMN_PASSWORD} = $password;

        $this->insert($dataObject);

        return $this->getByEmail($team, $email);
    }

    /**
     * getById retrieves the manager by unique identifier
     *
     * @param int $id - ID of manager
     *
     * @return DataObject found or NULL if none found
     */
    public function getById($id) {
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_ID . "=" . $id);
        return (0 < count($dataObjectArray)) ? $dataObjectArray[0] : NULL;
    }

    /**
     * getByTeam retrieves the manager by unique team, field and team combination
     *
     * @param $team - Team associated with the manager
     * @param $email - Email for the Manager
     * @param $teamId - Optional team identifier
     *
     * @return DataObject found or NULL if none found
     */
    public function getByEmail($team, $email, $teamId = NULL) {
        $teamId = isset($teamId) ? $teamId : $team->id;

        $dataObjectArray = $this->getWhere(
            self::DB_COLUMN_TEAM_ID . " = " . $teamId
            . " and " . self::DB_COLUMN_EMAIL . " = '" . $email . "'");

        return (0 < count($dataObjectArray)) ? $dataObjectArray[0] : NULL;
    }
}