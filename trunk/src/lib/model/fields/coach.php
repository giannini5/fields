<?php
/**
 * This file contains Model_Fields_Coach
 */

/**
 * Model_Fields_Coach class
 */
class Model_Fields_Coach extends Model_Fields_Base implements SaveModelInterface {

    public $m_team;

    /**
     * @brief: Constructor
     *
     * @param $team - Model_Fields_Team instance
     * @param $id - Unique identifier for the Coach
     * @param $teamId - Identifier of team for Coach
     * @param $name - Name for the Coach
     * @param $email - Email for the Coach
     * @param $phone - Phone Number for the Coach
     * @param $password - Password for the Coach
     */
    public function __construct($team = NULL, $id = NULL, $teamId = NULL, $name = '', $email = '', $phone = '', $password = '') {
        parent::__construct('Model_Fields_CoachDB', Model_Base::AUTO_DECLARE_CLASS_VARIABLE_ON);

        $this->m_team = $team;
        $this->{Model_Fields_CoachDB::DB_COLUMN_ID}   = $id;
        $this->{Model_Fields_CoachDB::DB_COLUMN_TEAM_ID} = $teamId;
        $this->{Model_Fields_CoachDB::DB_COLUMN_NAME} = $name;
        $this->{Model_Fields_CoachDB::DB_COLUMN_EMAIL} = $email;
        $this->{Model_Fields_CoachDB::DB_COLUMN_PHONE} = $phone;
        $this->{Model_Fields_CoachDB::DB_COLUMN_PASSWORD} = $password;

        $this->_setTeam();
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
        /** @var Model_Fields_CoachDB $dbHandle */
        $dbHandle = $this->_getDBHandle();
        if (!is_null($this->{Model_Fields_CoachDB::DB_COLUMN_ID})) {
            $dataObj = $dbHandle->getById($this->{Model_Fields_CoachDB::DB_COLUMN_ID});
        } else if (!is_null($this->{Model_Fields_CoachDB::DB_COLUMN_TEAM_ID})) {
            $dataObj = $dbHandle->getByEmail(
                NULL,
                $this->{Model_Fields_CoachDB::DB_COLUMN_EMAL},
                TRUE,
                $this->{Model_Fields_CoachDB::DB_COLUMN_TEAM_ID});
        }

        if (!empty($dataObj)) {
            $this->assignModel($dataObj);
            $this->_setTeam();
            return TRUE;
        }

        return FALSE;
    }

    /**
     * @brief: Set the team member variable if not already set
     */
    private function _setTeam() {
        if (!isset($this->m_team)) {
            $this->m_team = Model_Fields_Team::LookupById($this->{Model_Fields_CoachDB::DB_COLUMN_TEAM_ID});
        }
    }

    /**
     * _getUpdateKeys will return the update keys
     *
     * @return array primaryKeys K => V
     */
    public function _getUpdateKeys() {
        return array(Model_Fields_CoachDB::DB_COLUMN_ID => $this->{Model_Fields_CoachDB::DB_COLUMN_ID});
    }

    /**
     * @brief: Get and instance of this object from databases data.
     *
     * @param $dataObject - data object representing the content of the object
     * @param $team - Model_Fields_Team instance
     *
     * @return Model_Fields_Coach
     */
    public static function GetInstance($dataObject, $team = NULL) {
        $coach = new Model_Fields_Coach(
            $team,
            $dataObject->{Model_Fields_CoachDB::DB_COLUMN_ID},
            $dataObject->{Model_Fields_CoachDB::DB_COLUMN_TEAM_ID},
            $dataObject->{Model_Fields_CoachDB::DB_COLUMN_NAME},
            $dataObject->{Model_Fields_CoachDB::DB_COLUMN_EMAIL},
            $dataObject->{Model_Fields_CoachDB::DB_COLUMN_PHONE},
            $dataObject->{Model_Fields_CoachDB::DB_COLUMN_PASSWORD});

        $coach->setLoaded();

        return $coach;
    }

    /**
     * @brief: Create a new Coach
     *
     * @param $team - Model_Fields_Team instance
     *
     * @param $name - Name for the Coach
     * @param $email - Email for the Coach
     * @param $phone - Phone Number for the Coach
     * @param $password - Password for the Coach
     *
     * @return Model_Fields_Coach
     * @throws AssertionException
     */
    public static function Create($team, $name, $email, $phone, $password) {
        $dbHandle = new Model_Fields_CoachDB();
        $dataObject = $dbHandle->create($team, $name, $email, $phone, $password);
        assertion(!empty($dataObject), "Unable to create Coach with name:'$name' for team:'$team->name'");

        return Model_Fields_Coach::GetInstance($dataObject, $team);
    }

    /**
     * @brief: Get Model_Fields_Coach instance for the specified Coach identifier
     *
     * @param bigint $coachId: Unique Coach identifier
     *
     * @return Model_Fields_Coach
     */
    public static function LookupById($coachId) {
        $dbHandle = new Model_Fields_CoachDB();
        $dataObject = $dbHandle->getById($coachId);
        assertion(!empty($dataObject), "Coach row for id: '$coachId' not found");

        return Model_Fields_Coach::GetInstance($dataObject);
    }

    /**
     * @brief: Get Model_Fields_Coach instance for the specified Coach's team
     *
     * @param $team - Model_Fields_Team instance
     * @param $email - Email of the Coach
     * @param bool $assertIfNotFound - If TRUE then assert object if found.  Otherwise return NULL when object not found
     *
     * @return Model_Fields_Coach|null
     * @throws AssertionException
     */
    public static function LookupByEmail($team, $email, $assertIfNotFound = TRUE) {
        $dbHandle = new Model_Fields_CoachDB();
        $dataObject = $dbHandle->getByEmail($team, $email);

        if ($assertIfNotFound) {
            assertion(!empty($dataObject), "Coach '$email' for team: '$team->name' not found");
        } else if (empty($dataObject)) {
            return NULL;
        }

        return Model_Fields_Coach::GetInstance($dataObject, $team);
    }

    /**
     * @brief: Delete if exists
     *
     * @param $team - Model_Fields_Team instance
     * @param $email - Email of the Coach
    =     */
    public static function Delete($team, $email) {
        $coach = Model_Fields_Coach::LookupByEmail($team, $email, FALSE);
        if (isset($coach)) {
            $coach->_delete();
        }
    }
}