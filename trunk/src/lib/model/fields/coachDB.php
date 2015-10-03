<?php
/**
 * This file contains Model_Fields_CoachDB
 */

/**
 * Model_Fields_CoachDB class is the DB class for the Field database table
 */
class Model_Fields_CoachDB extends Model_Fields_BaseDB
{
    // Schema
    const DB_SCHEMA_NAME = DB_FIELDS_RW;

    // Table information
    const DB_TABLE_NAME = 'coach';

    // Columns constant
    const DB_COLUMN_ID = 'id';
    const DB_COLUMN_SEASON_ID = 'seasonId';
    const DB_COLUMN_DIVISION_ID = 'divisionId';
    const DB_COLUMN_NAME = 'name';
    const DB_COLUMN_EMAIL = 'email';
    const DB_COLUMN_PHONE = 'phone';
    const DB_COLUMN_PASSWORD = 'password';

    /**
     * @brief: Constructor
     *
     * @return Model_Fields_CoachDB
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
        precondition(!empty($dataObject->{self::DB_COLUMN_NAME}), "fields.coach.".self::DB_COLUMN_NAME." not set");
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
        return array(Model_Fields_CoachDB::DB_COLUMN_ID => $this->{Model_Fields_CoachDB::DB_COLUMN_ID});
    }

    /**
     * create a new coach
     *
     * @param $seasonId - Unique season identifier
     * @param $divisionId - Unique division identifier
     * @param $name - Name for the Coach
     * @param $email - Email for the Coach
     * @param $phone - Phone Number for the Coach
     * @param $password - Password for the Coach
     * @return DataObject[]
     */
    public function create($seasonId, $divisionId, $name, $email, $phone, $password)
    {
        $dataObject = new DataObject();
        $dataObject->{self::DB_COLUMN_SEASON_ID} = $seasonId;
        $dataObject->{self::DB_COLUMN_DIVISION_ID} = $divisionId;
        $dataObject->{self::DB_COLUMN_NAME} = $name;
        $dataObject->{self::DB_COLUMN_EMAIL} = $email;
        $dataObject->{self::DB_COLUMN_PHONE} = $phone;
        $dataObject->{self::DB_COLUMN_PASSWORD} = $password;

        $id = $this->insert($dataObject);

        return $this->getById($id);
    }

    /**
     * getById retrieves the coach by unique identifier
     *
     * @param int $id - ID of coach
     *
     * @return DataObject found or NULL if none found
     */
    public function getById($id)
    {
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_ID."=".$id);

        return (0 < count($dataObjectArray)) ? $dataObjectArray[0] : null;
    }

    /**
     * getByEmail retrieves the coach by unique team and coach email
     *
     * @param $seasonId - Unique identifier for the season
     * @param $divisionId - Unique identifier for the division
     * @param $email - Email for the Coach
     * @return DataObject found or NULL if none found
     */
    public function getByEmail($seasonId, $divisionId, $email)
    {
        $dataObjectArray = $this->getWhere(
            self::DB_COLUMN_SEASON_ID . " = " . $seasonId
            . " and " . self::DB_COLUMN_DIVISION_ID . " = " . $divisionId
            . " and " . self::DB_COLUMN_EMAIL . " = '" . $email . "'"
        );

        return (0 < count($dataObjectArray)) ? $dataObjectArray[0] : null;
    }

    /**
     * getBySeason retrieves the coaches for the specified seasonId
     *
     * @param $seasonId - Unique identifier for the season
     *
     * @return DataObjects found or empty array if non found
     */
    public function getBySeason($seasonId)
    {
        $dataObjectArray = $this->getWhere(
            self::DB_COLUMN_SEASON_ID . " = " . $seasonId
        );

        return $dataObjectArray;
    }
}