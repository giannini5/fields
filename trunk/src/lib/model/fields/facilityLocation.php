<?php
/**
 * This file contains Model_Fields_FacilityLocation
 */

/**
 * Model_Fields_FacilityLocation class
 */
class Model_Fields_FacilityLocation extends Model_Fields_Base implements SaveModelInterface {

    /**
     * @brief: Constructor
     *
     * @param int $id - unique identifier
     * @param int $facilityId - Facility identifier
     * @param int $locationId - Location identifier
     *
     * @return Model_Fields_FacilityLocation
     */
    public function __construct($id = NULL, $facilityId = NULL, $locationId = NULL) {
        parent::__construct('Model_Fields_FacilityLocationDB', Model_Base::AUTO_DECLARE_CLASS_VARIABLE_ON);

        $this->{Model_Fields_FacilityLocationDB::DB_COLUMN_ID}   = $id;
        $this->{Model_Fields_FacilityLocationDB::DB_COLUMN_FACILITY_ID} = $facilityId;
        $this->{Model_Fields_FacilityLocationDB::DB_COLUMN_LOCATION_ID} = $locationId;
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
        /** @var Model_Fields_FacilityLocationDB $dbHandle */
        $dbHandle = $this->_getDBHandle();
        $dataObj = $dbHandle->getById($this->{Model_Fields_FacilityLocationDB::DB_COLUMN_ID});

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
        return array(Model_Fields_FacilityLocationDB::DB_COLUMN_ID => $this->{Model_Fields_FacilityLocationDB::DB_COLUMN_ID});
    }

    /**
     * @brief: Get and instance of this object from databaes data.
     *
     * @param DataObject $dataObject - data object representing the content of the object
     *
     * @return Model_Fields_FacilityLocation
     */
    public static function GetInstance($dataObject) {
        $facilityLocation = new Model_Fields_FacilityLocation(
            $dataObject->{Model_Fields_FacilityLocationDB::DB_COLUMN_ID},
            $dataObject->{Model_Fields_FacilityLocationDB::DB_COLUMN_FACILITY_ID},
            $dataObject->{Model_Fields_FacilityLocationDB::DB_COLUMN_LOCATION_ID});

        return $facilityLocation;
    }

    /**
     * @brief: Create a new FacilityLocation
     *
     * @param int $facilityId - Facility identifier
     * @param int $locationId - Location identifier
     *
     * @return Model_Fields_FacilityLocation
     */
    public static function Create($facilityId, $locationId) {
        $dbHandle = new Model_Fields_FacilityLocationDB();
        $dataObject = $dbHandle->create($facilityId, $locationId);
        assertion(!empty($dataObject), "Unable to create facilityLocation with name '$facilityId, $locationId''");

        return Model_Fields_FacilityLocation::GetInstance($dataObject);
    }

    /**
     * @brief: Get Model_Fields_FacilityLocation instance for the specified facilityLocation identifier
     *
     * @param bigint $facilityLocationId: Unique facilityLocation identifier
     *
     * @return Model_Fields_FacilityLocation
     */
    public static function LookupById($facilityLocationId) {
        $dbHandle = new Model_Fields_FacilityLocationDB();
        $dataObject = $dbHandle->getById($facilityLocationId);
        assertion(!empty($dataObject), "FacilityLocation row for id: '$facilityLocationId' not found");

        return Model_Fields_FacilityLocation::GetInstance($dataObject);
    }

    /**
     * @brief: Get Model_Fields_FacilityLocation instance for the specified facilityLocation name
     *
     * @param int $facilityId - Facility identifier
     * @param int $locationId - Location identifier
     *
     * @return Model_Fields_FacilityLocation or NULL if object not found
     */
    public static function LookupByFacilityLocation($facilityId, $locationId) {
        $dbHandle = new Model_Fields_FacilityLocationDB();
        $dataObject = $dbHandle->getByFacilityLocation($facilityId, $locationId);

        if (empty($dataObject)) {
            return NULL;
        }

        return Model_Fields_FacilityLocation::GetInstance($dataObject);
    }

    /**
     * @brief: Get Model_Fields_Location instances for the specified facility
     *
     * @param $facilityId : Identifier of the facility used to get locations
     *
     * @return Array of Model_Fields_Location
     */
    public static function GetLocations($facilityId) {
        $dbHandle = new Model_Fields_FacilityLocationDB();
        $dataObjects = $dbHandle->getByFacility($facilityId);

        $locations = array();
        foreach ($dataObjects as $dataObject) {
            $locations[] = Model_Fields_Location::LookupById($dataObject->{Model_Fields_FacilityLocationDB::DB_COLUMN_LOCATION_ID});
        }

        return $locations;
    }

    /**
     * @brief: Delete if exists
     *
     * @param int $facilityId - Facility identifier
     * @param int $locationId - Location identifier
     */
    public static function Delete($facilityId, $locationId) {
        $facilityLocation = Model_Fields_FacilityLocation::LookupByFacilityLocation($facilityId, $locationId);
        if (isset($facilityLocation)) {
            $facilityLocation->_delete();
        }
    }
}