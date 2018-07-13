<?php

/**
 * Class Controller_AdminPractice_Facility
 *
 * @brief Select a facility to administer or create a new facility
 */
class Controller_AdminPractice_Facility extends Controller_AdminPractice_Base {
    public $m_name = NULL;
    public $m_address1;
    public $m_address2;
    public $m_city;
    public $m_state;
    public $m_postalCode;
    public $m_country;
    public $m_contactName;
    public $m_contactEmail;
    public $m_contactPhone;
    public $m_image;
    public $m_preApproved;
    public $m_enabled = NULL;
    public $m_facilityId = NULL;
    public $m_selectedLocations = [];

    private $m_facilityUpdates = [];

    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->m_operation == View_Base::CREATE) {
                $this->m_name = $this->getPostAttribute(
                    Model_Fields_FacilityDB::DB_COLUMN_NAME,
                    '* Name required'
                );
                $this->m_address1 = $this->getPostAttribute(
                    Model_Fields_FacilityDB::DB_COLUMN_ADDRESS1,
                    '',
                    FALSE
                );
                $this->m_address2 = $this->getPostAttribute(
                    Model_Fields_FacilityDB::DB_COLUMN_ADDRESS2,
                    '',
                    FALSE
                );
                $this->m_city = $this->getPostAttribute(
                    Model_Fields_FacilityDB::DB_COLUMN_CITY,
                    '',
                    FALSE
                );
                $this->m_state = $this->getPostAttribute(
                    Model_Fields_FacilityDB::DB_COLUMN_STATE,
                    '',
                    FALSE
                );
                $this->m_postalCode = $this->getPostAttribute(
                    Model_Fields_FacilityDB::DB_COLUMN_POSTAL_CODE,
                    '',
                    FALSE
                );
                $this->m_country = $this->getPostAttribute(
                    Model_Fields_FacilityDB::DB_COLUMN_COUNTRY,
                    '',
                    FALSE
                );
                $this->m_contactName = $this->getPostAttribute(
                    Model_Fields_FacilityDB::DB_COLUMN_CONTACT_NAME,
                    '',
                    FALSE
                );
                $this->m_contactEmail = $this->getPostAttribute(
                    Model_Fields_FacilityDB::DB_COLUMN_CONTACT_EMAIL,
                    '',
                    FALSE
                );
                $this->m_contactPhone = $this->getPostAttribute(
                    Model_Fields_FacilityDB::DB_COLUMN_CONTACT_PHONE,
                    '',
                    FALSE
                );
                $this->m_image = $this->getPostAttribute(
                    Model_Fields_FacilityDB::DB_COLUMN_IMAGE,
                    '',
                    FALSE
                );
                $this->m_preApproved = $this->getPostAttribute(
                    Model_Fields_FacilityDB::DB_COLUMN_PRE_APPROVED,
                    '* Pre-Approved required',
                    TRUE,
                    TRUE
                );
                $this->m_enabled = $this->getPostAttribute(
                    Model_Fields_FacilityDB::DB_COLUMN_ENABLED,
                    '* Enabled required',
                    TRUE,
                    TRUE
                );
                $this->m_selectedLocations = $this->getPostAttributeArray(
                    View_Base::LOCATION_IDS
                );
            }

            if ($this->m_operation == View_Base::UPDATE) {
                $this->m_facilityUpdates = $this->getPostAttributeArray(View_Base::FACILITY_UPDATE_DATA);
            }
        }
    }

    /**
     * @brief On GET, render the page to administer facilities
     *        On POST, complete the transaction and then render the page (with error message if any)
     */
    public function process() {
        if ($this->m_missingAttributes == 0) {
            switch ($this->m_operation) {
                case View_Base::CREATE:
                    $this->_createFacility();
                    break;

                case View_Base::UPDATE:
                    $this->_updateFacility();
                    break;
            }
        }

        if ($this->m_isAuthenticated) {
            $view = new View_AdminPractice_Facility($this);
        } else {
            $view = new View_AdminPractice_Home($this);
        }

        $view->displayPage();
    }

    /**
     * @brief Get the locations for the specified facility
     *
     * @param $facilityId - Model_Field_Facility identifier
     *
     * @return Model_Fields_FacilityLocation[]
     */
    public function getFacilityLocations($facilityId) {
        return Model_Fields_FacilityLocation::GetLocations($facilityId);
    }

    /**
     * @brief Create Facility.  If the facility already exists then set the errorString.
     *        Add the created Facility to the list of facilitys.
     */
    private function _createFacility() {
        $facility = Model_Fields_Facility::LookupByName($this->m_league, $this->m_name, FALSE);
        if (!isset($facility)) {
            $facility = Model_Fields_Facility::Create(
                $this->m_league,
                $this->m_name,
                $this->m_address1,
                $this->m_address2,
                $this->m_city,
                $this->m_state,
                $this->m_postalCode,
                $this->m_country,
                $this->m_contactName,
                $this->m_contactEmail,
                $this->m_contactPhone,
                $this->m_image,
                $this->m_preApproved,
                $this->m_enabled);

            $this->_setLocations($facility);

            $this->m_messageString = "Facility '$this->m_name' created.";
        } else {
            $this->m_errorString = "Facility '$this->m_name' already exists<br>Scroll down and update to make a change";
        }
    }

    /**
     * @brief Update Facility.  Set the errorString if the Facility cannot be updated.
     */
    private function _updateFacility() {
        foreach ($this->m_facilityUpdates as $facilityId => $facilityData) {
            // Error check
            $updateFacility     = Model_Fields_Facility::LookupById($facilityId);
            $existingFacility   = Model_Fields_Facility::LookupByName($this->m_league, $facilityData[Model_Fields_FacilityDB::DB_COLUMN_NAME], FALSE);
            if (isset($existingFacility) and $existingFacility->id != $updateFacility->id) {
                $name = $facilityData[Model_Fields_FacilityDB::DB_COLUMN_NAME];
                $this->m_errorString = "Facility '$name' already exists<br>Scroll down and update to make a change";
                return;
            }

            // Update
            $updateFacility->name           = $facilityData[Model_Fields_FacilityDB::DB_COLUMN_NAME];
            $updateFacility->address1       = $facilityData[Model_Fields_FacilityDB::DB_COLUMN_ADDRESS1];
            $updateFacility->city           = $facilityData[Model_Fields_FacilityDB::DB_COLUMN_CITY];
            $updateFacility->state          = $facilityData[Model_Fields_FacilityDB::DB_COLUMN_STATE];
            $updateFacility->postalCode     = $facilityData[Model_Fields_FacilityDB::DB_COLUMN_POSTAL_CODE];
            $updateFacility->contactName    = $facilityData[Model_Fields_FacilityDB::DB_COLUMN_CONTACT_NAME];
            $updateFacility->contactEmail   = $facilityData[Model_Fields_FacilityDB::DB_COLUMN_CONTACT_EMAIL];
            $updateFacility->contactPhone   = $facilityData[Model_Fields_FacilityDB::DB_COLUMN_CONTACT_PHONE];
            $updateFacility->image          = $facilityData[Model_Fields_FacilityDB::DB_COLUMN_IMAGE];
            $updateFacility->preApproved    = $facilityData[Model_Fields_FacilityDB::DB_COLUMN_PRE_APPROVED];
            $updateFacility->enabled        = $facilityData[Model_Fields_FacilityDB::DB_COLUMN_ENABLED];

            $updateFacility->saveModel();

            $this->m_selectedLocations = $facilityData[View_Base::LOCATION_IDS];
            $this->_setLocations($updateFacility);
        }

        $this->m_messageString = "Facilities updated";
    }

    /**
     * @brief Update a facilities locations.  Delete ones that are no longer valid
     *        and add ones that are new
     *
     * @param $facility - Model_Fields_Facility instance being updated
     */
    private function _setLocations($facility) {
        // Delete current locations for facility if not in updated list
        $currentLocations = Model_Fields_FacilityLocation::GetLocations($facility->id);
        foreach ($currentLocations as $location) {
            if (!in_array($location->id, $this->m_selectedLocations)) {
                Model_Fields_FacilityLocation::Delete($facility->id, $location->id);
            }
        }

        // Create new locations for facility if they do not already exist
        foreach ($this->m_selectedLocations as $locationId) {
            $facilityLocation = Model_Fields_FacilityLocation::LookupByFacilityLocation($facility->id, $locationId);
            if (!isset($facilityLocation)) {
                Model_Fields_FacilityLocation::Create($facility->id, $locationId);
            }
        }
    }
}