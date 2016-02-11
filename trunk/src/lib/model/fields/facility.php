<?php
/**
 * This file contains Model_Fields_Facility
 */

/**
 * Model_Fields_Facility class
 */
class Model_Fields_Facility extends Model_Fields_Base implements SaveModelInterface {

    public $m_league;

    /**
     * @brief: Constructor
     *
     * @param $league - Model_Fields_League instance
     * @param $id - unique identifier
     * @param $leagueId - unique league identifier
     * @param string $name - name of the facility
     * @param string $address1 - Address1 of the facility
     * @param string $address2 - Address2 of the facility
     * @param string $city - City where facility is located
     * @param string $state - State where facility is located
     * @param string $postalCode - Postal code of the facility
     * @param string $country - Country where the facility is located
     * @param string $contactName - Name of person in charge of facility
     * @param string $contactEmail - Email of person in charge of facility
     * @param string $contactPhone - Phone number of person in charge of facility
     * @param string $image - File name that contains image of the fields
     * @param string $preApproved - 1 if pre-approved; !1 if additional approval required
     * @param bool $enabled - 1 if facility is enabled; 0 otherwise
     */
    public function __construct($league = NULL, $id = NULL, $leagueId = NULL, $name = '', $address1 = '', $address2 = '', $city = '', $state = '',
                                $postalCode = '', $country = '', $contactName = '', $contactEmail = '', $contactPhone = '', $image = '', $preApproved = 1, $enabled = 0) {
        parent::__construct('Model_Fields_FacilityDB', Model_Base::AUTO_DECLARE_CLASS_VARIABLE_ON);

        $this->m_league = $league;
        $this->{Model_Fields_FacilityDB::DB_COLUMN_ID}   = $id;
        $this->{Model_Fields_FacilityDB::DB_COLUMN_LEAGUE_ID}   = $leagueId;
        $this->{Model_Fields_FacilityDB::DB_COLUMN_NAME} = $name;
        $this->{Model_Fields_FacilityDB::DB_COLUMN_ADDRESS1} = $address1;
        $this->{Model_Fields_FacilityDB::DB_COLUMN_ADDRESS2} = $address2;
        $this->{Model_Fields_FacilityDB::DB_COLUMN_CITY} = $city;
        $this->{Model_Fields_FacilityDB::DB_COLUMN_STATE} = $state;
        $this->{Model_Fields_FacilityDB::DB_COLUMN_POSTAL_CODE} = $postalCode;
        $this->{Model_Fields_FacilityDB::DB_COLUMN_COUNTRY} = $country;
        $this->{Model_Fields_FacilityDB::DB_COLUMN_CONTACT_NAME} = $contactName;
        $this->{Model_Fields_FacilityDB::DB_COLUMN_CONTACT_EMAIL} = $contactEmail;
        $this->{Model_Fields_FacilityDB::DB_COLUMN_CONTACT_PHONE} = $contactPhone;
        $this->{Model_Fields_FacilityDB::DB_COLUMN_IMAGE} = $image;
        $this->{Model_Fields_FacilityDB::DB_COLUMN_PRE_APPROVED} = $preApproved;
        $this->{Model_Fields_FacilityDB::DB_COLUMN_ENABLED} = $enabled;
        $this->_setLeague();
    }

    /**
     * @brief: destructor
     */
    public function __destruct() {
    }

    /**
     * @brief: Check to see if the facility is in the specified location
     *
     * @param $locationId : Identifier of the location
     *
     * @return TRUE if facility is in the location; FALSE otherwise
     */
    public function isInLocation($locationId) {
        $facilityLocations = Model_Fields_FacilityLocation::GetLocations($this->id);
        foreach ($facilityLocations as $location) {
            if ($location->id == $locationId) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * @brief Check to see if this facility has any fields for the specified division
     *
     * @param $divisionId - Division's unique identifier
     *
     * @return bool - TRUE if fields found; FALSE otherwise
     */
    public function hasFieldsInDivision($divisionId) {
        $facilityFields = Model_Fields_DivisionField::GetFacilityFields($divisionId, $this->id);
        return (count($facilityFields) > 0);
    }

    /**
     * @brief Get the fields from this facility that are supported for the specified division
     *
     * @param $divisionId - Division's unique identifier
     * @param bool $enabledOnly - If TRUE then get enabled fields only; otherwise get ALL fields
     *
     * @return array of Model_Fields_Field
     */
    public function getFieldsInDivision($divisionId, $enabledOnly = FALSE) {
        $facilityFields = Model_Fields_DivisionField::GetFacilityFields($divisionId, $this->id, $enabledOnly);
        return $facilityFields;
    }

    /**
     * @brief: _load will load the object from the data storage.
     *
     * @return bool - TRUE if successfully loaded model, else FALSE
     */
    public function _load() {
        /** @var Model_Fields_FacilityDB $dbHandle */
        $dbHandle = $this->_getDBHandle();
        if (!is_null($this->{Model_Fields_FacilityDB::DB_COLUMN_ID})) {
            $dataObj = $dbHandle->getById($this->{Model_Fields_FacilityDB::DB_COLUMN_ID});

        } else if (!is_null($this->{Model_Fields_FacilityDB::DB_COLUMN_NAME})) {
            $dataObj = $dbHandle->getByName(
                NULL,
                $this->{Model_Fields_FacilityDB::DB_COLUMN_NAME},
                TRUE,
                $this->{Model_Fields_FacilityDB::DB_COLUMN_LEAUGE_ID});
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
            $this->m_league = Model_Fields_League::LookupById($this->{Model_Fields_FacilityDB::DB_COLUMN_LEAGUE_ID});
        }
    }

    /**
     * _getUpdateKeys will return the update keys
     *
     * @return array primaryKeys K => V
     */
    public function _getUpdateKeys() {
        return array(Model_Fields_FacilityDB::DB_COLUMN_ID => $this->{Model_Fields_FacilityDB::DB_COLUMN_ID});
    }

    /**
     * @brief: Get and instance of this object from databases data.
     *
     * @param $dataObject - data object representing the content of the object
     * @param $league - Model_Fields_League instance
     *
     * @return Model_Fields_Facility
     */
    public static function GetInstance($dataObject, $league = NULL) {
        $facility = new Model_Fields_Facility(
            $league,
            $dataObject->{Model_Fields_FacilityDB::DB_COLUMN_ID},
            $dataObject->{Model_Fields_FacilityDB::DB_COLUMN_LEAGUE_ID},
            $dataObject->{Model_Fields_FacilityDB::DB_COLUMN_NAME},
            $dataObject->{Model_Fields_FacilityDB::DB_COLUMN_ADDRESS1},
            $dataObject->{Model_Fields_FacilityDB::DB_COLUMN_ADDRESS2},
            $dataObject->{Model_Fields_FacilityDB::DB_COLUMN_CITY},
            $dataObject->{Model_Fields_FacilityDB::DB_COLUMN_STATE},
            $dataObject->{Model_Fields_FacilityDB::DB_COLUMN_POSTAL_CODE},
            $dataObject->{Model_Fields_FacilityDB::DB_COLUMN_COUNTRY},
            $dataObject->{Model_Fields_FacilityDB::DB_COLUMN_CONTACT_NAME},
            $dataObject->{Model_Fields_FacilityDB::DB_COLUMN_CONTACT_EMAIL},
            $dataObject->{Model_Fields_FacilityDB::DB_COLUMN_CONTACT_PHONE},
            $dataObject->{Model_Fields_FacilityDB::DB_COLUMN_IMAGE},
            $dataObject->{Model_Fields_FacilityDB::DB_COLUMN_PRE_APPROVED},
            $dataObject->{Model_Fields_FacilityDB::DB_COLUMN_ENABLED});

        $facility->setLoaded();

        return $facility;
    }

    /**
     * @brief: Create a new Facility
     *
     * @param $league - Model_Fields_League instance
     * @param string $name - name of the facility
     * @param string $address1 - Address1 of the facility
     * @param string $address2 - Address2 of the facility
     * @param string $city - City where facility is located
     * @param string $state - State where facility is located
     * @param string $postalCode - Postal code of the facility
     * @param string $country - Country where the facility is located
     * @param string $contactName - Name of person in charge of facility
     * @param string $contactEmail - Email of person in charge of facility
     * @param string $contactPhone - Phone number of person in charge of facility
     * @param string $image - http path to image
     * @param string $preApproved - 1 if fields are preApproved !1 if additional approval required
     * @param bool $enabled - 1 if facility is enabled; 0 otherwise
     *
     * @return Model_Fields_Facility
     * @throws AssertionException
     */
    public static function Create($league, $name, $address1, $address2, $city, $state, $postalCode, $country, $contactName, $contactEmail, $contactPhone, $image, $preApproved, $enabled) {
        $dbHandle = new Model_Fields_FacilityDB();
        $dataObject = $dbHandle->create($league, $name, $address1, $address2, $city, $state, $postalCode, $country, $contactName, $contactEmail, $contactPhone, $image, $preApproved, $enabled);
        assertion(!empty($dataObject), "Unable to create Facility with name:'$name'");

        return Model_Fields_Facility::GetInstance($dataObject, $league);
    }

    /**
     * @brief: Get Model_Fields_Facility instance for the specified Facility identifier
     *
     * @param bigint $facilityId: Unique Facility identifier
     *
     * @return Model_Fields_Facility
     */
    public static function LookupById($facilityId) {
        $dbHandle = new Model_Fields_FacilityDB();
        $dataObject = $dbHandle->getById($facilityId);
        assertion(!empty($dataObject), "Facility row for id: '$facilityId' not found");

        return Model_Fields_Facility::GetInstance($dataObject);
    }

    /**
     * @brief: Get Model_Fields_Facility instance for the specified Facility league and name
     *
     * @param $league - Model_Fields_League instance
     * @param $name - Facility's name
     * @param $assertIfNotFound - If TRUE then assert object if found.  Otherwise return NULL when object not found
     *
     * @return Model_Fields_Facility or NULL if object not found and $assertIfNotFound is FALSE
     * @throws AssertionException
     */
    public static function LookupByName($league, $name, $assertIfNotFound = TRUE, $leagueId = NULL) {
        $dbHandle = new Model_Fields_FacilityDB();
        $dataObject = $dbHandle->getByName($league, $name, $leagueId);

        if ($assertIfNotFound) {
            assertion(!empty($dataObject), "Facility row for name: $name not found");
        } else if (empty($dataObject)) {
            return NULL;
        }

        return Model_Fields_Facility::GetInstance($dataObject, $league);
    }

    /**
     * @brief: Get Model_Fields_Facility instances for the specified league
     *
     * @param $league - Model_Fields_League instance
     * @param $assertIfNotFound - If TRUE then assert object if found.  Otherwise return NULL when object not found
     *
     * @return array of Model_Fields_Facility objects (empty if no facilities found)
     * @throws AssertionException
     */
    public static function LookupByLeague($league) {
        $dbHandle = new Model_Fields_FacilityDB();
        $dataObjects = $dbHandle->getByLeague($league);

        $facilities = array();
        foreach ($dataObjects as $dataObject) {
            $facilities[] = Model_Fields_Facility::GetInstance($dataObject, $league);
        }

        return $facilities;
    }

    /**
     * @brief: Delete if exists
     *
     * @param $league - Model_Fields_League instance
     * @param $name - Facility's name
     */
    public static function Delete($league, $name) {
        $facility = Model_Fields_Facility::LookupByName($league, $name, FALSE);
        if (isset($facility)) {
            $facility->_delete();
        }
    }
}