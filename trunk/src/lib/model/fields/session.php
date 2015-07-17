<?php
/**
 * This file contains Model_Fields_Session
 */

/**
 * Model_Fields_Session class
 */
class Model_Fields_Session extends Model_Fields_Base implements SaveModelInterface {

    const COACH_USER_TYPE = 0;
    const MANAGER_USER_TYPE = 1;
    const PRACTICE_FIELD_COORDINATOR_USER_TYPE = 2;

    /**
     * @brief: Constructor
     *
     * @param $id - unique identifier
     * @param $creationDate - Date the session was created
     * @param int $userId - ID of user
     * @param int $userType - Type of the user
     */
    public function __construct($id = NULL, $creationDate = NULL, $userId = NULL, $userType = NULL) {
        parent::__construct('Model_Fields_SessionDB', Model_Base::AUTO_DECLARE_CLASS_VARIABLE_ON);

        $this->{Model_Fields_SessionDB::DB_COLUMN_ID}   = $id;
        $this->{Model_Fields_SessionDB::DB_COLUMN_CREATION_DATE} = $creationDate;
        $this->{Model_Fields_SessionDB::DB_COLUMN_USER_ID} = $userId;
        $this->{Model_Fields_SessionDB::DB_COLUMN_USER_TYPE} = $userType;
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
        /** @var Model_Fields_SessionDB $dbHandle */
        $dbHandle = $this->_getDBHandle();
        if (!is_null($this->{Model_Fields_SessionDB::DB_COLUMN_ID})) {
            $dataObj = $dbHandle->getById($this->{Model_Fields_SessionDB::DB_COLUMN_ID});

        } else if (!is_null($this->{Model_Fields_SessionDB::DB_COLUMN_USER_ID}) and
            !is_null($this->{Model_Fields_SessionDB::DB_COLUMN_USER_TYPE})) {
            $dataObj = $dbHandle->getByUser(
                $this->{Model_Fields_SessionDB::DB_COLUMN_USER_ID},
                $this->{Model_Fields_SessionDB::DB_COLUMN_USER_TYPE});
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
        return array(Model_Fields_SessionDB::DB_COLUMN_ID => $this->{Model_Fields_SessionDB::DB_COLUMN_ID});
    }

    /**
     * Check to see if this session is valid
     *
     * @return TRUE if the session is valid, FALSE otherwise.
     */
    public function isValid() {
        // TODO: Return FALSE if the session is older than X hours
        return TRUE;
    }

    /**
     * Check to see if this session is valid
     *
     * @return TRUE if the session is valid, FALSE otherwise.
     */
    public function renew() {
        // TODO: Update the session creation_date to the current date/time
        return;
    }

    /**
     * @brief: Get and instance of this object from databaes data.
     *
     * @param $dataObject - data object representing the content of the object
     *
     * @return Model_Fields_Session
     */
    public static function GetInstance($dataObject) {
        $session = new Model_Fields_Session(
            $dataObject->{Model_Fields_SessionDB::DB_COLUMN_ID},
            $dataObject->{Model_Fields_SessionDB::DB_COLUMN_CREATION_DATE},
            $dataObject->{Model_Fields_SessionDB::DB_COLUMN_USER_ID},
            $dataObject->{Model_Fields_SessionDB::DB_COLUMN_USER_TYPE});

        $session->setLoaded();

        return $session;
    }

    /**
     * @brief: Create a new Session
     *
     * @param int $userId - ID of user
     * @param int $userType - Type of the user
     *
     * @return Model_Fields_Session
     * @throws AssertionException
     */
    public static function Create($userId, $userType) {
        $dbHandle = new Model_Fields_SessionDB();
        $dataObject = $dbHandle->create($userId, $userType);
        assertion(!empty($dataObject), "Unable to create Session with userId:'$userId' and userType:'$userType'");

        return Model_Fields_Session::GetInstance($dataObject);
    }

    /**
     * @brief: Get Model_Fields_Session instance for the specified Session identifier
     *
     * @param bigint $sessionId: Unique Session identifier
     *
     * @return Model_Fields_Session
     */
    public static function LookupById($sessionId) {
        $dbHandle = new Model_Fields_SessionDB();
        $dataObject = $dbHandle->getById($sessionId);
        assertion(!empty($dataObject), "Session row for id: '$sessionId' not found");

        return Model_Fields_Session::GetInstance($dataObject);
    }

    /**
     * @brief: Get Model_Fields_Session instance for the specified user
     *
     * @param int $userId - ID of user
     * @param int $userType - Type of the user
     *
     * @return Model_Fields_Session or NULL if object not found and $assertIfNotFound is FALSE
     * @throws AssertionException
     */
    public static function LookupByUser($userId, $userType, $assertIfNotFound = TRUE) {
        $dbHandle = new Model_Fields_SessionDB();
        $dataObject = $dbHandle->getByUser($userId, $userType);

        if ($assertIfNotFound) {
            assertion(!empty($dataObject), "Session row for userId: $userId, userType: $userType not found");
        } else if (empty($dataObject)) {
            return NULL;
        }

        return Model_Fields_Session::GetInstance($dataObject);
    }

    /**
     * @brief: Delete if exists
     *
     * @param int $userId - ID of user
     * @param int $userType - Type of the user
     */
    public static function Delete($userId, $userType) {
        $session = Model_Fields_Session::LookupByUser($userId, $userType, FALSE);
        if (isset($session)) {
            $session->_delete();
        }
    }
}