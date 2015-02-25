<?php
/**
 * This file contains Model_Fields_Team
 */

/**
 * Model_Fields_Team class
 */
class Model_Fields_Team extends Model_Fields_Base implements SaveModelInterface {

    public $m_division;

    /**
     * @brief: Constructor
     *
     * @param $division - Model_Fields_Division instance
     * @param $id - unique identifier
     * @param $divisionId - unique division identifier
     * @param $teamNumber - unique team number within the division
     * @param string $name - name of the field
     */
    public function __construct($division = NULL, $id = NULL, $divisionId = NULL, $teamNumber = 0, $name = '') {
        parent::__construct('Model_Fields_TeamDB', Model_Base::AUTO_DECLARE_CLASS_VARIABLE_ON);

        $this->m_division = $division;
        $this->{Model_Fields_TeamDB::DB_COLUMN_ID}   = $id;
        $this->{Model_Fields_TeamDB::DB_COLUMN_DIVISION_ID} = $divisionId;
        $this->{Model_Fields_TeamDB::DB_COLUMN_TEAM_NUMBER} = $teamNumber;
        $this->{Model_Fields_TeamDB::DB_COLUMN_NAME} = $name;
        $this->_setDivision();
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
        /** @var Model_Fields_TeamDB $dbHandle */
        $dbHandle = $this->_getDBHandle();
        if (!is_null($this->{Model_Fields_TeamDB::DB_COLUMN_ID})) {
            $dataObj = $dbHandle->getById($this->{Model_Fields_TeamDB::DB_COLUMN_ID});

        } else if (!is_null($this->{Model_Fields_TeamDB::DB_COLUMN_TEAM_NUMBER})) {
            $dataObj = $dbHandle->getByNumber(
                NULL,
                $this->{Model_Fields_TeamDB::DB_COLUMN_TEAM_NUMBER},
                TRUE,
                $this->{Model_Fields_TeamDB::DB_COLUMN_DIVISION_ID});
        }

        if (!empty($dataObj)) {
            $this->assignModel($dataObj);
            $this->_setDivision();
            return TRUE;
        }

        return FALSE;
    }

    /**
     * @brief: Set the division member variable if not already set
     */
    private function _setDivision() {
        if (!isset($this->m_division)) {
            $this->m_division = Model_Fields_Division::LookupById($this->{Model_Fields_TeamDB::DB_COLUMN_DIVISION_ID});
        }
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
     * @brief: Get and instance of this object from databaes data.
     *
     * @param $dataObject - data object representing the content of the object
     * @param $division - Model_Fields_Division instance
     *
     * @return Model_Fields_Team
     */
    public static function GetInstance($dataObject, $division = NULL) {
        $team = new Model_Fields_Team(
            $division,
            $dataObject->{Model_Fields_TeamDB::DB_COLUMN_ID},
            $dataObject->{Model_Fields_TeamDB::DB_COLUMN_DIVISION_ID},
            $dataObject->{Model_Fields_TeamDB::DB_COLUMN_TEAM_NUMBER},
            $dataObject->{Model_Fields_TeamDB::DB_COLUMN_NAME});

        $team->setLoaded();

        return $team;
    }

    /**
     * @brief: Create a new Field
     *
     * @param $division - Model_Fields_Division instance
     * @param int $teamNumber - Unique team number within the division
     * @param string $name - name of the team
     *
     * @return Model_Fields_Team
     * @throws AssertionException
     */
    public static function Create($division, $teamNumber, $name) {
        $dbHandle = new Model_Fields_TeamDB();
        $dataObject = $dbHandle->create($division, $teamNumber, $name);
        assertion(!empty($dataObject), "Unable to create Team with name:'$name', number:'$teamNumber'");

        return Model_Fields_Team::GetInstance($dataObject, $division);
    }

    /**
     * @brief: Get Model_Fields_Team instance for the specified Team identifier
     *
     * @param bigint $teamId: Unique Team identifier
     *
     * @return Model_Fields_Team
     */
    public static function LookupById($teamId) {
        $dbHandle = new Model_Fields_TeamDB();
        $dataObject = $dbHandle->getById($teamId);
        assertion(!empty($dataObject), "Team row for id: '$teamId' not found");

        return Model_Fields_Team::GetInstance($dataObject);
    }

    /**
     * @brief: Get Model_Fields_Team instance for the specified Team division and name
     *
     * @param $division - Model_Fields_Division instance
     * @param $teamNumber - Unique team number within the division
     * @param $assertIfNotFound - If TRUE then assert object if found.  Otherwise return NULL when object not found
     *
     * @return Model_Fields_Team or NULL if object not found and $assertIfNotFound is FALSE
     * @throws AssertionException
     */
    public static function LookupByNumber($division, $teamNumber, $assertIfNotFound = TRUE, $divisionId = NULL) {
        $dbHandle = new Model_Fields_TeamDB();
        $dataObject = $dbHandle->getByNumber($division, $teamNumber, $divisionId);

        if ($assertIfNotFound) {
            assertion(!empty($dataObject), "Team row for team number: '$teamNumber' not found");
        } else if (empty($dataObject)) {
            return NULL;
        }

        return Model_Fields_Team::GetInstance($dataObject, $division);
    }

    /**
     * @brief: Delete if exists
     *
     * @param $division - Model_Fields_Division instance
     * @param $teamNumber - Team's number
     */
    public static function Delete($division, $teamNumber) {
        $team = Model_Fields_Team::LookupByName($division, $teamNumber, FALSE);
        if (isset($team)) {
            $team->_delete();
        }
    }
}