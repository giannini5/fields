<?php
/**
 * This file contains Model_Fields_Location
 */

/**
 * Model_Fields_Location class
 */
class Model_Fields_Location extends Model_Fields_Base implements SaveModelInterface {

    /**
     * @brief: Constructor
     *
     * @param int    $id - unique identifier
     * @param int    $leagueId - League identifier
     * @param string $name - Unique name of location within league
     *
     * @return Model_Fields_Location
     */
    public function __construct($id = NULL, $leagueId = NULL, $name = '') {
        parent::__construct('Model_Fields_LocationDB', Model_Base::AUTO_DECLARE_CLASS_VARIABLE_ON);

        $this->{Model_Fields_LocationDB::DB_COLUMN_ID}          = $id;
        $this->{Model_Fields_LocationDB::DB_COLUMN_LEAGUE_ID}   = $leagueId;
        $this->{Model_Fields_LocationDB::DB_COLUMN_NAME}        = $name;
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
        /** @var Model_Fields_LocationDB $dbHandle */
        $dbHandle = $this->_getDBHandle();
        if (!is_null($this->{Model_Fields_LocationDB::DB_COLUMN_ID})) {
            $dataObj = $dbHandle->getById($this->{Model_Fields_LocationDB::DB_COLUMN_ID});

        } else if (!is_null($this->{Model_Fields_LocationDB::DB_COLUMN_NAME})) {
            $dataObj = $dbHandle->getByName(
                $this->{Model_Fields_FacilityDB::DB_COLUMN_LEAUGE_ID},
                $this->{Model_Fields_FacilityDB::DB_COLUMN_NAME});
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
        return array(Model_Fields_LocationDB::DB_COLUMN_ID => $this->{Model_Fields_LocationDB::DB_COLUMN_ID});
    }

    /**
     * @brief: Get and instance of this object from databaes data.
     *
     * @param DataObject $dataObject - data object representing the content of the object
     *
     * @return Model_Fields_Location
     */
    public static function GetInstance($dataObject) {
        $location = new Model_Fields_Location(
            $dataObject->{Model_Fields_LocationDB::DB_COLUMN_ID},
            $dataObject->{Model_Fields_LocationDB::DB_COLUMN_LEAGUE_ID},
            $dataObject->{Model_Fields_LocationDB::DB_COLUMN_NAME});

        return $location;
    }

    /**
     * @brief: Create a new Location
     *
     * @param int    $leagueId: League Identifier
     * @param string $name: unique name for the location
     *
     * @return Model_Fields_Location
     */
    public static function Create($leagueId, $name) {
        $dbHandle = new Model_Fields_LocationDB();
        $dataObject = $dbHandle->create($leagueId, $name);
        assertion(!empty($dataObject), "Unable to create location with name '$leagueId, $name''");

        return Model_Fields_Location::GetInstance($dataObject);
    }

    /**
     * @brief: Get Model_Fields_Location instance for the specified location identifier
     *
     * @param bigint $locationId: Unique location identifier
     *
     * @return Model_Fields_Location
     */
    public static function LookupById($locationId) {
        $dbHandle = new Model_Fields_LocationDB();
        $dataObject = $dbHandle->getById($locationId);
        assertion(!empty($dataObject), "Location row for id: '$locationId' not found");

        return Model_Fields_Location::GetInstance($dataObject);
    }

    /**
     * @brief: Get Model_Fields_Location instance for the specified location name
     *
     * @param string $leagueId : League Identifier
     * @param string $name : Unique location name
     * @param bool $assertIfNotFound - If TRUE then assert object if found.  Otherwise return NULL when object not found
     *
     * @return Model_Fields_Location or NULL if object not found and $assertIfNotFound is FALSE
     * @throws AssertionException
     */
    public static function LookupByName($leagueId, $name, $assertIfNotFound = TRUE) {
        $dbHandle = new Model_Fields_LocationDB();
        $dataObject = $dbHandle->getByName($leagueId, $name);

        if ($assertIfNotFound) {
            assertion(!empty($dataObject), "Location row for league: $leagueId, name: $name not found");
        } else if (empty($dataObject)) {
            return NULL;
        }

        return Model_Fields_Location::GetInstance($dataObject);
    }

    /**
     * @brief: Get Model_Fields_Location instances for the specified facility
     *
     * @param $facilityId : Identifier of the facility used to get locations
     *
     * @return Array of Model_Fields_Location
     */
    public static function GetLocations($leagueId) {
        $dbHandle = new Model_Fields_LocationDB();
        $dataObjects = $dbHandle->getByLeague($leagueId);

        $locations = array();
        foreach ($dataObjects as $dataObject) {
            $locations[] = Model_Fields_Location::GetInstance($dataObject);
        }

        return $locations;
    }

    /**
     * @brief: Delete if exists
     *
     * @param string $name: Unique location name
     */
    public static function Delete($leagueId, $name) {
        $location = Model_Fields_Location::LookupByName($leagueId, $name, FALSE);
        if (isset($location)) {
            $location->_delete();
        }
    }
}