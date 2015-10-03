<?php
/**
 * This file contains Model_Fields_SeasonDB
 */

/**
 * Model_Fields_SeasonDB class is the DB class for the Season database table
 */
class Model_Fields_SeasonDB extends Model_Fields_BaseDB {
    // Schema
    const DB_SCHEMA_NAME = DB_FIELDS_RW;

    // Table information
    const DB_TABLE_NAME = 'season';

    // Columns constant
    const DB_COLUMN_ID            = 'id';
    const DB_COLUMN_LEAGUE_ID     = 'leagueId';
    const DB_COLUMN_NAME          = 'name';
    const DB_COLUMN_ENABLED       = 'enabled';

    /**
     * @brief: Constructor
     *
     * @return Model_Fields_SeasonDB
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
        precondition(!empty($dataObject->{self::DB_COLUMN_LEAGUE_ID}), "fields.season." . self::DB_COLUMN_LEAGUE_ID . " not set");
        precondition(!empty($dataObject->{self::DB_COLUMN_NAME}), "fields.season." . self::DB_COLUMN_NAME . " not set");
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
        return array(Model_Fields_SeasonDB::DB_COLUMN_ID => $this->{Model_Fields_SeasonDB::DB_COLUMN_ID});
    }

    /**
     * create a new season
     *
     * @param $league - Model_Fields_League instance
     * @param string $name - name of the season
     * @param bool $enabled - 1 if season is enabled; 0 otherwise
     *
     * @return DataObject[]
     */
    public function create($league, $name, $enabled) {
        $dataObject = new DataObject();
        $dataObject->{self::DB_COLUMN_LEAGUE_ID} = $league->id;
        $dataObject->{self::DB_COLUMN_NAME} = $name;
        $dataObject->{self::DB_COLUMN_ENABLED} = $enabled;


        $this->insert($dataObject);

        return $this->getByName($league, $name);
    }

    /**
     * getById retrieves the season by unique identifier
     *
     * @param int $id - ID of season
     *
     * @return DataObject found or NULL if none found
     */
    public function getById($id) {
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_ID . "=" . $id);
        return (0 < count($dataObjectArray)) ? $dataObjectArray[0] : NULL;
    }

    /**
     * getByName retrieves the season by unique league/email
     *
     * @param $league - The league that the practice field coordinator represents
     * @param $name - Season's name (must be unique in the league)
     * @param $leagueId - Optional league identifier
     *
     * @return DataObject found or NULL if none found
     */
    public function getByName($league, $name, $leagueId = NULL) {
        $leagueId = isset($leagueId) ? $leagueId : $league->id;
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_LEAGUE_ID . " = '" . $leagueId . "' and " . self::DB_COLUMN_NAME . " ='" . $name . "'");
        return (0 < count($dataObjectArray)) ? $dataObjectArray[0] : NULL;
    }

    /**
     * getByLeague retrieves the seasons by league
     *
     * @param $league - The league that the practice field coordinator represents
     *
     * @return array of DataObjects, empty array if none found
     */
    public function getByLeague($league) {
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_LEAGUE_ID . " = " . $league->id);
        return $dataObjectArray;
    }
}