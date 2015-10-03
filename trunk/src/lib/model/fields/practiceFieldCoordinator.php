<?php
/**
 * This file contains Model_Fields_PracticeFieldCoordinator
 */

/**
 * Model_Fields_PracticeFieldCoordinator class
 */
class Model_Fields_PracticeFieldCoordinator extends Model_Fields_Base implements SaveModelInterface {

    public $m_league;

    /**
     * @brief: Constructor
     *
     * @param $league - Model_Fields_League instance
     * @param $id - unique identifier
     * @param $leagueId - unique league identifier
     * @param $email - email of the coordinator
     * @param $name - name of the coordinator
     * @param $password - password of the coordinator
     *
     * @return Model_Fields_PracticeFieldCoordinator
     */
    public function __construct($league = NULL, $id = NULL, $leagueId = NULL, $email = '', $name = '', $password = '') {
        parent::__construct('Model_Fields_PracticeFieldCoordinatorDB', Model_Base::AUTO_DECLARE_CLASS_VARIABLE_ON);

        $this->m_league = $league;
        $this->{Model_Fields_PracticeFieldCoordinatorDB::DB_COLUMN_ID}   = $id;
        $this->{Model_Fields_PracticeFieldCoordinatorDB::DB_COLUMN_LEAGUE_ID}   = $leagueId;
        $this->{Model_Fields_PracticeFieldCoordinatorDB::DB_COLUMN_EMAIL} = $email;
        $this->{Model_Fields_PracticeFieldCoordinatorDB::DB_COLUMN_NAME} = $name;
        $this->{Model_Fields_PracticeFieldCoordinatorDB::DB_COLUMN_PASSWORD} = $password;
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
        /** @var Model_Fields_PracticeFieldCoordinatorDB $dbHandle */
        $dbHandle = $this->_getDBHandle();
        if (!is_null($this->{Model_Fields_PracticeFieldCoordinatorDB::DB_COLUMN_ID})) {
            $dataObj = $dbHandle->getById($this->{Model_Fields_PracticeFieldCoordinatorDB::DB_COLUMN_ID});

        } else if (!is_null($this->{Model_Fields_PracticeFieldCoordinatorDB::DB_COLUMN_EMAIL})) {
            $dataObj = $dbHandle->getByEmail($this->{Model_Fields_PracticeFieldCoordinatorDB::DB_COLUMN_LEAUGE_ID},
                $this->{Model_Fields_PracticeFieldCoordinatorDB::DB_COLUMN_LEAUGE_ID});
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
            $this->m_league = Model_Fields_League::LookupById($this->{Model_Fields_PracticeFieldCoordinatorDB::DB_COLUMN_LEAGUE_ID});
        }
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
     * @brief: Get and instance of this object from databaes data.
     *
     * @param $dataObject - data object representing the content of the object
     * @param $league - Model_Fields_League instance
     *
     * @return Model_Fields_PracticeFieldCoordinator
     */
    public static function GetInstance($dataObject, $league = NULL) {
        $practiceFieldCoordinator = new Model_Fields_PracticeFieldCoordinator(
            $league,
            $dataObject->{Model_Fields_PracticeFieldCoordinatorDB::DB_COLUMN_ID},
            $dataObject->{Model_Fields_PracticeFieldCoordinatorDB::DB_COLUMN_LEAGUE_ID},
            $dataObject->{Model_Fields_PracticeFieldCoordinatorDB::DB_COLUMN_EMAIL},
            $dataObject->{Model_Fields_PracticeFieldCoordinatorDB::DB_COLUMN_NAME},
            $dataObject->{Model_Fields_PracticeFieldCoordinatorDB::DB_COLUMN_PASSWORD});

        $practiceFieldCoordinator->setLoaded();

        return $practiceFieldCoordinator;
    }

    /**
     * @brief: Create a new PracticeFieldCoordinator
     *
     * @param $league - Model_Fields_League instance
     * @param $email - Practice Field Coordinator's email address
     * @param $name - Practice Field Coordinator's name
     * @param $password - Practice Field Coordinator's password
     *
     * @return Model_Fields_PracticeFieldCoordinator
     * @throws AssertionException
     */
    public static function Create($league, $email, $name, $password) {
        $dbHandle = new Model_Fields_PracticeFieldCoordinatorDB();
        $dataObject = $dbHandle->create($league, $email, $name, $password);
        assertion(!empty($dataObject), "Unable to create PracticeFieldCoordinator with email:'$email', name:'$name', password:'$password'");

        return Model_Fields_PracticeFieldCoordinator::GetInstance($dataObject, $league);
    }

    /**
     * @brief: Get Model_Fields_PracticeFieldCoordinator instance for the specified PracticeFieldCoordinator identifier
     *
     * @param bigint $practiceFieldCoordinatorId: Unique PracticeFieldCoordinator identifier
     *
     * @return Model_Fields_PracticeFieldCoordinator
     */
    public static function LookupById($practiceFieldCoordinatorId) {
        $dbHandle = new Model_Fields_PracticeFieldCoordinatorDB();
        $dataObject = $dbHandle->getById($practiceFieldCoordinatorId);
        assertion(!empty($dataObject), "PracticeFieldCoordinator row for id: '$practiceFieldCoordinatorId' not found");

        return Model_Fields_PracticeFieldCoordinator::GetInstance($dataObject);
    }

    /**
     * @brief: Get Model_Fields_PracticeFieldCoordinator instance for the specified PracticeFieldCoordinator league and email
     *
     * @param $league : Model_Fields_League instance
     * @param $email : Practice Field Coordinator's email address
     * @param $assertIfNotFound - If TRUE then assert object if found.  Otherwise return NULL when object not found
     *
     * @return Model_Fields_PracticeFieldCoordinator or NULL if object not found and $assertIfNotFound is FALSE
     * @throws AssertionException
     */
    public static function LookupByEmail($league, $email, $assertIfNotFound = TRUE) {
        $dbHandle = new Model_Fields_PracticeFieldCoordinatorDB();
        $dataObject = $dbHandle->getByEmail($league, $email);

        if ($assertIfNotFound) {
            assertion(!empty($dataObject), "PracticeFieldCoordinator row for email: $email not found");
        } else if (empty($dataObject)) {
            return NULL;
        }

        return Model_Fields_PracticeFieldCoordinator::GetInstance($dataObject, $league);
    }

    /**
     * @brief: Get list of Model_Fields_PracticeFieldCoordinator instances for the specified PracticeFieldCoordinator league
     *
     * @param $league : Model_Fields_League instance
     *
     * @return array of Model_Fields_PracticeFieldCoordinator empty array if none found
     */
    public static function LookupByLeague($league) {
        $dbHandle = new Model_Fields_PracticeFieldCoordinatorDB();
        $dataObjects = $dbHandle->getByLeague($league);

        $practiceFieldCoordinators = array();
        foreach ($dataObjects as $dataObject) {
            $practiceFieldCoordinators[] = Model_Fields_PracticeFieldCoordinator::LookupById($dataObject->{Model_Fields_PracticeFieldCoordinatorDB::DB_COLUMN_ID});
        }
        return $practiceFieldCoordinators;
    }

    /**
     * @brief: Delete if exists
     *
     * @param $league : Model_Fields_League instance
     * @param $email : Practice Field Coordinator's email address
     */
    public static function Delete($league, $email) {
        $practiceFieldCoordinator = Model_Fields_PracticeFieldCoordinator::LookupByEmail($league, $email, FALSE);
        if (isset($practiceFieldCoordinator)) {
            $practiceFieldCoordinator->_delete();
        }
    }
}