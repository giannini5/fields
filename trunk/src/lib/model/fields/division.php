<?php
/**
 * This file contains Model_Fields_Division
 */

/**
 * Model_Fields_Division class
 */
class Model_Fields_Division extends Model_Fields_Base implements SaveModelInterface {

    public $m_league;

    /**
     * @brief: Constructor
     *
     * @param $league - Model_Fields_League instance
     * @param $id - unique identifier
     * @param $leagueId - unique league identifier
     * @param string $name - name of the division
     * @param bool $enabled - 1 if division is enabled; 0 otherwise
     */
    public function __construct($league = NULL, $id = NULL, $leagueId = NULL, $name = '', $enabled = 0) {
        parent::__construct('Model_Fields_DivisionDB', Model_Base::AUTO_DECLARE_CLASS_VARIABLE_ON);

        $this->m_league = $league;
        $this->{Model_Fields_DivisionDB::DB_COLUMN_ID}   = $id;
        $this->{Model_Fields_DivisionDB::DB_COLUMN_LEAGUE_ID}   = $leagueId;
        $this->{Model_Fields_DivisionDB::DB_COLUMN_NAME} = $name;
        $this->{Model_Fields_DivisionDB::DB_COLUMN_ENABLED} = $enabled;
        $this->_setLeague();
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
        /** @var Model_Fields_DivisionDB $dbHandle */
        $dbHandle = $this->_getDBHandle();
        if (!is_null($this->{Model_Fields_DivisionDB::DB_COLUMN_ID})) {
            $dataObj = $dbHandle->getById($this->{Model_Fields_DivisionDB::DB_COLUMN_ID});

        } else if (!is_null($this->{Model_Fields_DivisionDB::DB_COLUMN_NAME})) {
            $dataObj = $dbHandle->getByName(
                NULL,
                $this->{Model_Fields_DivisionDB::DB_COLUMN_NAME},
                TRUE,
                $this->{Model_Fields_DivisionDB::DB_COLUMN_LEAUGE_ID});
        }

        if (!empty($dataObj)) {
            $this->assignModel($dataObj);
            $this->_setLeague();
            return TRUE;
        }

        return FALSE;
    }

    /**
     * @brief: Set the league member variable if not already set
     */
    private function _setLeague() {
        if (!isset($this->m_league)) {
            $this->m_league = Model_Fields_League::LookupById($this->{Model_Fields_DivisionDB::DB_COLUMN_LEAGUE_ID});
        }
    }

    /**
     * _getUpdateKeys will return the update keys
     *
     * @return array primaryKeys K => V
     */
    public function _getUpdateKeys() {
        return array(Model_Fields_DivisionDB::DB_COLUMN_ID => $this->{Model_Fields_DivisionDB::DB_COLUMN_ID});
    }

    /**
     * @brief: Get and instance of this object from databaes data.
     *
     * @param $dataObject - data object representing the content of the object
     * @param $league - Model_Fields_League instance
     *
     * @return Model_Fields_Division
     */
    public static function GetInstance($dataObject, $league = NULL) {
        $division = new Model_Fields_Division(
            $league,
            $dataObject->{Model_Fields_DivisionDB::DB_COLUMN_ID},
            $dataObject->{Model_Fields_DivisionDB::DB_COLUMN_LEAGUE_ID},
            $dataObject->{Model_Fields_DivisionDB::DB_COLUMN_NAME},
            $dataObject->{Model_Fields_DivisionDB::DB_COLUMN_ENABLED});

        $division->setLoaded();

        return $division;
    }

    /**
     * @brief: Create a new Division
     *
     * @param $league - Model_Fields_League instance
     * @param string $name - name of the division
     * @param bool $enabled - 1 if division is enabled; 0 otherwise
     *
     * @return Model_Fields_Division
     * @throws AssertionException
     */
    public static function Create($league, $name, $enabled) {
        $dbHandle = new Model_Fields_DivisionDB();
        $dataObject = $dbHandle->create($league, $name, $enabled);
        assertion(!empty($dataObject), "Unable to create Division with name:'$name'");

        return Model_Fields_Division::GetInstance($dataObject, $league);
    }

    /**
     * @brief: Get Model_Fields_Division instance for the specified Division identifier
     *
     * @param bigint $divisionId: Unique Division identifier
     *
     * @return Model_Fields_Division
     */
    public static function LookupById($divisionId) {
        $dbHandle = new Model_Fields_DivisionDB();
        $dataObject = $dbHandle->getById($divisionId);
        assertion(!empty($dataObject), "Division row for id: '$divisionId' not found");

        return Model_Fields_Division::GetInstance($dataObject);
    }

    /**
     * @brief: Get Model_Fields_Division instance for the specified Division league and name
     *
     * @param $league - Model_Fields_League instance
     * @param $name - Division's name
     * @param $assertIfNotFound - If TRUE then assert object if found.  Otherwise return NULL when object not found
     *
     * @return Model_Fields_Division or NULL if object not found and $assertIfNotFound is FALSE
     * @throws AssertionException
     */
    public static function LookupByName($league, $name, $assertIfNotFound = TRUE, $leagueId = NULL) {
        $dbHandle = new Model_Fields_DivisionDB();
        $dataObject = $dbHandle->getByName($league, $name, $leagueId);

        if ($assertIfNotFound) {
            assertion(!empty($dataObject), "Division row for name: $name not found");
        } else if (empty($dataObject)) {
            return NULL;
        }

        return Model_Fields_Division::GetInstance($dataObject, $league);
    }

    /**
     * @brief: Get list of Model_Fields_Division instances for the specified Division league
     *
     * @param $league - Model_Fields_League instance
     *
     * @return Model_Fields_Division list
     */
    public static function GitList($league) {
        $dbHandle = new Model_Fields_DivisionDB();
        $dataObjects = $dbHandle->getList($league);

        $divisions = array();
        foreach ($dataObjects as $dataObject) {
            $divisions[] = Model_Fields_Division::GetInstance($dataObject, $league);
        }

        return $divisions;
    }

    /**
     * @brief: Delete if exists
     *
     * @param $league - Model_Fields_League instance
     * @param $name - Division's name
     */
    public static function Delete($league, $name) {
        $division = Model_Fields_Division::LookupByName($league, $name, FALSE);
        if (isset($division)) {
            $division->_delete();
        }
    }
}