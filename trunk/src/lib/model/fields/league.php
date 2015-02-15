<?php
/**
 * This file contains Model_Fields_League
 */

/**
 * Model_Fields_League class
 */
class Model_Fields_League extends Model_Fields_Base implements SaveModelInterface {

    /**
     * @brief: Constructor
     *
     * @param int    $id - unique identifier
     * @param string $name - name of state
     *
     * @return Model_Fields_League
     */
    public function __construct($id = NULL, $name = '') {
        parent::__construct('Model_Fields_LeagueDB', Model_Base::AUTO_DECLARE_CLASS_VARIABLE_ON);

        $this->{Model_Fields_LeagueDB::DB_COLUMN_ID}   = $id;
        $this->{Model_Fields_LeagueDB::DB_COLUMN_NAME} = $name;
    }

    /**
     * @brief: destructor
     */
    public function __destruct() {
    }

    /**
     * @brief: _load will load the object from the data storage.
     *
     * @return bool - TRUE if successfully loaded model, else FALSE
     */
    public function _load() {
        /** @var Model_Fields_LeagueDB $dbHandle */
        $dbHandle = $this->_getDBHandle();
        if (!is_null($this->{Model_Fields_LeagueDB::DB_COLUMN_ID})) {
            $dataObj = $dbHandle->getById($this->{Model_Fields_LeagueDB::DB_COLUMN_ID});

        } else if (!is_null($this->{Model_Fields_LeagueDB::DB_COLUMN_NAME})) {
            $dataObj = $dbHandle->getByName($this->{Model_Fields_LeagueDB::DB_COLUMN_NAME});
        }

        if (!empty($dataObj)) {
            $this->assignModel($dataObj);
            return TRUE;
        }

        return FALSE;
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
     * @brief: Get and instance of this object from databaes data.
     *
     * @param DataObject $dataObject - data object representing the content of the object
     *
     * @return Model_Fields_League
     */
    public static function GetInstance($dataObject) {
        $league = new Model_Fields_League(
            $dataObject->{Model_Fields_LeagueDB::DB_COLUMN_ID},
            $dataObject->{Model_Fields_LeagueDB::DB_COLUMN_NAME});

        return $league;
    }

    /**
     * @brief: Create a new League
     *
     * @param string $name: unique name for the league
     *
     * @return Model_Fields_League
     */
    public static function Create($name) {
        $dbHandle = new Model_Fields_LeagueDB();
        $dataObject = $dbHandle->create($name);
        assertion(!empty($dataObject), "Unable to create league with name '$name''");

        $league = new Model_Fields_League(
            $dataObject->{Model_Fields_LeagueDB::DB_COLUMN_ID},
            $dataObject->{Model_Fields_LeagueDB::DB_COLUMN_NAME});

        return $league;
    }

    /**
     * @brief: Get Model_Fields_League instance for the specified league identifier
     *
     * @param bigint $leagueId: Unique league identifier
     *
     * @return Model_Fields_League
     */
    public static function LookupById($leagueId) {
        $dbHandle = new Model_Fields_LeagueDB();
        $dataObject = $dbHandle->getById($leagueId);
        assertion(!empty($dataObject), "League row for id: '$leagueId' not found");

        $league = new Model_Fields_League(
            $dataObject->{Model_Fields_LeagueDB::DB_COLUMN_ID},
            $dataObject->{Model_Fields_LeagueDB::DB_COLUMN_NAME});

        return $league;
    }

    /**
     * @brief: Get Model_Fields_League instance for the specified league name
     *
     * @param string $name : Unique league name
     * @param bool $assertIfNotFound - If TRUE then assert object if found.  Otherwise return NULL when object not found
     *
     * @return Model_Fields_League or NULL if object not found and $assertIfNotFound is FALSE
     * @throws AssertionException
     */
    public static function LookupByName($name, $assertIfNotFound = TRUE) {
        $dbHandle = new Model_Fields_LeagueDB();
        $dataObject = $dbHandle->getByName($name);

        if ($assertIfNotFound) {
            assertion(!empty($dataObject), "League row for name: $name not found");
        } else if (empty($dataObject)) {
            return NULL;
        }

        $league = new Model_Fields_League(
            $dataObject->{Model_Fields_LeagueDB::DB_COLUMN_ID},
            $dataObject->{Model_Fields_LeagueDB::DB_COLUMN_NAME});

        return $league;
    }

    /**
     * @brief: Delete if exists
     *
     * @param string $name: Unique league name
     */
    public static function Delete($name) {
        $league = Model_Fields_League::LookupByName($name, FALSE);
        if (isset($league)) {
            $league->_delete();
        }
    }
}