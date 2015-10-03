<?php
/**
 * This file contains Model_Fields_Coach
 */

/**
 * Model_Fields_Coach class
 */
class Model_Fields_Coach extends Model_Fields_Base implements SaveModelInterface {

    /**
     * @brief: Constructor
     *
     * @param $id - Unique identifier for the Coach
     * @param $seasonId - Unique identifier for the season
     * @param $divisionId - Unique identifier for the division
     * @param $name - Name for the Coach
     * @param $email - Email for the Coach
     * @param $phone - Phone Number for the Coach
     * @param $password - Password for the Coach
     */
    public function __construct($id = NULL, $seasonId = NULL, $divisionId = NULL, $name = '', $email = '', $phone = '', $password = '') {
        parent::__construct('Model_Fields_CoachDB', Model_Base::AUTO_DECLARE_CLASS_VARIABLE_ON);

        $this->{Model_Fields_CoachDB::DB_COLUMN_ID} = $id;
        $this->{Model_Fields_CoachDB::DB_COLUMN_SEASON_ID} = $seasonId;
        $this->{Model_Fields_CoachDB::DB_COLUMN_DIVISION_ID} = $divisionId;
        $this->{Model_Fields_CoachDB::DB_COLUMN_NAME} = $name;
        $this->{Model_Fields_CoachDB::DB_COLUMN_EMAIL} = $email;
        $this->{Model_Fields_CoachDB::DB_COLUMN_PHONE} = $phone;
        $this->{Model_Fields_CoachDB::DB_COLUMN_PASSWORD} = $password;
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
        } else if (!is_null($this->{Model_Fields_CoachDB::DB_COLUMN_SEASON_ID})) {
            $dataObj = $dbHandle->getByEmail(
                $this->{Model_Fields_CoachDB::DB_COLUMN_SEASON_ID},
                $this->{Model_Fields_CoachDB::DB_COLUMN_DIVISION_ID},
                $this->{Model_Fields_CoachDB::DB_COLUMN_EMAIL});
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
        return array(Model_Fields_CoachDB::DB_COLUMN_ID => $this->{Model_Fields_CoachDB::DB_COLUMN_ID});
    }

    /**
     * @brief: Get and instance of this object from databases data.
     *
     * @param $dataObject - data object representing the content of the object
     *
     * @return Model_Fields_Coach
     */
    public static function GetInstance($dataObject) {
        $coach = new Model_Fields_Coach(
            $dataObject->{Model_Fields_CoachDB::DB_COLUMN_ID},
            $dataObject->{Model_Fields_CoachDB::DB_COLUMN_SEASON_ID},
            $dataObject->{Model_Fields_CoachDB::DB_COLUMN_DIVISION_ID},
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
     * @param $season - Model_Fields_Season instance
     * @param $division - Model_Fields_Division instance
     * @param $name - Name for the Coach
     * @param $email - Email for the Coach
     * @param $phone - Phone Number for the Coach
     * @param $password - Password for the Coach
     *
     * @return Model_Fields_Coach
     * @throws AssertionException
     */
    public static function Create($season, $division, $name, $email, $phone, $password) {
        $dbHandle = new Model_Fields_CoachDB();
        $dataObject = $dbHandle->create($season->id, $division->id, $name, $email, $phone, $password);
        assertion(!empty($dataObject), "Unable to create Coach with name:'$name' for season:'$season->name' and division:'$division->name'");

        return Model_Fields_Coach::GetInstance($dataObject);
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
     * @param $season - Model_Fields_Season instance
     * @param $division - Model_Fields_Division instance
     * @param $email - Email of the Coach
     * @param bool $assertIfNotFound - If TRUE then assert object if found.  Otherwise return NULL when object not found
     *
     * @return Model_Fields_Coach|null
     * @throws AssertionException
     */
    public static function LookupByEmail($season, $division, $email, $assertIfNotFound = TRUE) {
        $dbHandle = new Model_Fields_CoachDB();
        $dataObject = $dbHandle->getByEmail($season->id, $division->id, $email);

        if ($assertIfNotFound) {
            assertion(!empty($dataObject), "Coach '$email' for season: '$season->name' for division: '$division->name' not found");
        } else if (empty($dataObject)) {
            return NULL;
        }

        return Model_Fields_Coach::GetInstance($dataObject);
    }

    /**
     * @brief Get array of coaches for the specified season
     *
     * @param $season - Model_Fields_Season instance
     *
     * @return array - List of coaches
     */
    public static function GetCoaches($season) {
        $dbHandle = new Model_Fields_CoachDB();
        $dataObjects = $dbHandle->getBySeason($season->id);

        $coaches = array();
        foreach ($dataObjects as $dataObject) {
            $coaches[] = Model_Fields_Coach::LookupById($dataObject->{Model_Fields_CoachDB::DB_COLUMN_ID});
        }

        return $coaches;
    }

    /**
     * @brief: Delete if exists
     *
     * @param $season - Model_Fields_Season instance
     * @param $division - Model_Fields_Division instance
     * @param $email - Email of the Coach
    =     */
    public static function Delete($season, $division, $email) {
        $coach = Model_Fields_Coach::LookupByEmail($season, $division, $email, FALSE);
        if (isset($coach)) {
            $coach->_delete();
        }
    }
}