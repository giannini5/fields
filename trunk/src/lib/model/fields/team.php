<?php
/**
 * This file contains Model_Fields_Team
 */

/**
 * Model_Fields_Team class
 */
class Model_Fields_Team extends Model_Fields_Base implements SaveModelInterface {

    public $m_division;
    public $m_coach;

    /**
     * @brief: Constructor
     *
     * @param $division - Model_Fields_Division instance
     * @param $coach - Model_Fields_Coach instance
     * @param $id - unique identifier
     * @param $divisionId - unique division identifier
     * @param $coachId - unique coach identifier
     * @param $gender - B for Boys; G for girls
     * @param string $name - name of the field
     */
    public function __construct($division = NULL, $coach = NULL, $id = NULL, $divisionId = NULL, $coachId = NULL, $gender = 0, $name = '') {
        parent::__construct('Model_Fields_TeamDB', Model_Base::AUTO_DECLARE_CLASS_VARIABLE_ON);

        $this->m_division = $division;
        $this->m_coach = $coach;
        $this->{Model_Fields_TeamDB::DB_COLUMN_ID}   = $id;
        $this->{Model_Fields_TeamDB::DB_COLUMN_DIVISION_ID} = $divisionId;
        $this->{Model_Fields_TeamDB::DB_COLUMN_COACH_ID} = $coachId;
        $this->{Model_Fields_TeamDB::DB_COLUMN_GENDER} = $gender;
        $this->{Model_Fields_TeamDB::DB_COLUMN_NAME} = $name;
        $this->_setDivision();
        $this->_setCoach();
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

        }

        if (!empty($dataObj)) {
            $this->assignModel($dataObj);
            $this->_setDivision();
            $this->_setCoach();
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
     * @brief: Set the coach member variable if not already set
     */
    private function _setCoach() {
        if (!isset($this->m_coach)) {
            $this->m_coach = Model_Fields_Coach::LookupById($this->{Model_Fields_TeamDB::DB_COLUMN_COACH_ID});
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
    public static function GetInstance($dataObject, $division = NULL, $coach = NULL) {
        $team = new Model_Fields_Team(
            $division,
            $coach,
            $dataObject->{Model_Fields_TeamDB::DB_COLUMN_ID},
            $dataObject->{Model_Fields_TeamDB::DB_COLUMN_DIVISION_ID},
            $dataObject->{Model_Fields_TeamDB::DB_COLUMN_COACH_ID},
            $dataObject->{Model_Fields_TeamDB::DB_COLUMN_GENDER},
            $dataObject->{Model_Fields_TeamDB::DB_COLUMN_NAME});

        $team->setLoaded();

        return $team;
    }

    /**
     * @brief: Create a new Field
     *
     * @param $division - Model_Fields_Division instance
     * @param int $gender - B for boys; G for girls
     * @param string $name - name of the team
     *
     * @return Model_Fields_Team
     * @throws AssertionException
     */
    public static function Create($division, $coach, $gender, $name) {
        $dbHandle = new Model_Fields_TeamDB();
        $dataObject = $dbHandle->create($division, $coach, $gender, $name);
        assertion(!empty($dataObject), "Unable to create Team with name:'$name', gender:'$gender'");

        return Model_Fields_Team::GetInstance($dataObject, $division, $coach);
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
     * @brief: Get Model_Fields_Team instance for the specified Coach
     *
     * @param bigint $coachId: Unique Coach identifier
     * @param char $gender: Gender - B for Boys, G for Girls
     *
     * @return Model_Fields_Team or NULL if team not found
     */
    public static function LookupByCoach($coach, $gender) {
        $dbHandle = new Model_Fields_TeamDB();
        $dataObject = $dbHandle->getByCoach($coach, $gender);

        if (!isset($dataObject)) {
            return NULL;
        }

        return Model_Fields_Team::GetInstance($dataObject);
    }
}