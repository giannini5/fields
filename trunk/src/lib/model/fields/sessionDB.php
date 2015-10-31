<?php
/**
 * This file contains Model_Fields_SessionDB
 */

/**
 * Model_Fields_SessionDB class is the DB class for the Field database table
 */
class Model_Fields_SessionDB extends Model_Fields_BaseDB
{
    // Schema
    const DB_SCHEMA_NAME = DB_FIELDS_RW;

    // Table information
    const DB_TABLE_NAME = 'session';

    // Columns constant
    const DB_COLUMN_ID = 'id';
    const DB_COLUMN_CREATION_DATE = 'creationDate';
    const DB_COLUMN_USER_ID = 'userId';
    const DB_COLUMN_USER_TYPE = 'userType';
    const DB_COLUMN_TEAM_ID = 'teamId';

    /**
     * @brief: Constructor
     *
     * @return Model_Fields_SessionDB
     */
    public function __construct()
    {
        parent::__construct(self::DB_SCHEMA_NAME, self::DB_TABLE_NAME, DAG_Factory::DB_ADAPTER_DEFAULTSQL);
    }

    /**
     * @brief: Check to make sure all the data passed for DB meets a minimum requirement
     *
     * @param DataObject $dataObject
     *
     * @throws PreconditionException
     */
    protected function _checkPreconditions(DataObject $dataObject)
    {
    }

    /**
     * Set default value for the elements that are not set
     *
     * @param DataObject $dataObject
     */
    protected function _setDefaults(DataObject &$dataObject)
    {
    }

    /**
     * Check to make sure all the data passed for DB update meets a minimum requirement
     *
     * @param DataObject $dataObject
     */
    protected function _checkUpdatePreconditions(DataObject $dataObject)
    {
    }

    /**
     * _getUpdateKeys will return the update keys
     *
     * @return array primaryKeys K => V
     */
    public function _getUpdateKeys()
    {
        return array(Model_Fields_SessionDB::DB_COLUMN_ID => $this->{Model_Fields_SessionDB::DB_COLUMN_ID});
    }

    /**
     * create a new session
     *
     * @param $userId - Identifier of the user (coach, manager, practiceFieldCoordinator)
     * @param $userType - Type of user (COACH, MANAGER, PRACTICE_FIELD_COORDINATOR)
     * @param $teamId - Team identifier
     *
     * @return DataObject[]
     */
    public function create($userId, $userType, $teamId)
    {
        $dataObject = new DataObject();
        $dataObject->{self::DB_COLUMN_USER_ID} = $userId;
        $dataObject->{self::DB_COLUMN_USER_TYPE} = $userType;
        $dataObject->{self::DB_COLUMN_TEAM_ID} = $teamId;

        $this->insert($dataObject);

        return $this->getByUser($userId, $userType, $teamId);
    }

    /**
     * getByUser retrieves the session by unique user identifier, type and team id
     *
     * @param int $userId - ID of user
     * @param int $userType - Type of the user
     * @param int $teamId - ID of team
     *
     * @return DataObject found or NULL if none found
     */
    public function getByUser($userId, $userType, $teamId)
    {
        $dataObjectArray = $this->getWhere(
            self::DB_COLUMN_USER_ID . " = " . $userId .
            " and " . self::DB_COLUMN_USER_TYPE . " = " . $userType .
            " and " . self::DB_COLUMN_TEAM_ID . " = " . $teamId);

        return (0 < count($dataObjectArray)) ? $dataObjectArray[0] : null;
    }

    /**
     * getById retrieves the session by unique identifier
     *
     * @param int $id - ID of session
     *
     * @return DataObject found or NULL if none found
     */
    public function getById($id)
    {
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_ID . " = " . $id);

        return (0 < count($dataObjectArray)) ? $dataObjectArray[0] : null;
    }
}