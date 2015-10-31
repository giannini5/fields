<?php

/**
 * Class Controller_Admin_Facility
 *
 * @brief Select a facility to administer or create a new facility
 */
class Controller_Admin_Facility extends Controller_Admin_Base {
    public $m_facilities = NULL;
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
    public $m_enabled = NULL;
    public $m_facilityId = NULL;
    public $m_selectedLocations = array();

    public function __construct() {
        parent::__construct();

        $this->m_facilities = Model_Fields_Facility::LookupByLeague($this->m_league, FALSE);

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
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
            $this->m_enabled = $this->getPostAttribute(
                Model_Fields_FacilityDB::DB_COLUMN_ENABLED,
                '* Enabled required',
                TRUE,
                TRUE
            );
            $this->m_facilityId = $this->getPostAttribute(
                View_Base::FACILITY_ID,
                NULL,
                FALSE
            );
            $this->m_selectedLocations = $this->getPostAttributeArray(
                View_Base::LOCATION_IDS
            );
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
            $view = new View_Admin_Facility($this);
        } else {
            $view = new View_Admin_Home($this);
        }

        $view->displayPage();
    }

    /**
     * @brief Get the locations for the specified facility
     *
     * @param $facilityId - Model_Field_Facility identifier
     *
     * @return Array of Model_Field_Location objects
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
                $this->m_enabled);
            $this->m_facilities[] = $facility;

            $this->_setLocations($facility);
        } else {
            $this->m_errorString = "Facility '$this->m_name' already exists<br>Scroll down and update to make a change";
        }
    }

    /**
     * @brief Update Facility.  Set the errorString if the Facility cannot be updated.
     */
    private function _updateFacility() {
        // Error check
        foreach ($this->m_facilites as $facility) {
            if ($facility->name == $this->m_name and $facility->id != $this->m_facilityId) {
                $this->m_errorString = "Facility '$this->m_name' already exists<br>Scroll down and update to make a change";
                return;
            }
        }

        // Update
        foreach ($this->m_facilities as $facility) {
            if ($facility->id == $this->m_facilityId) {
                $facility->name = $this->m_name;
                $facility->address1 = $this->m_address1;
                $facility->address2 = $this->m_address2;
                $facility->city = $this->m_city;
                $facility->state = $this->m_state;
                $facility->postalCode = $this->m_postalCode;
                $facility->country = $this->m_country;
                $facility->contactName = $this->m_contactName;
                $facility->contactEmail = $this->m_contactEmail;
                $facility->contactPhone = $this->m_contactPhone;
                $facility->image = $this->m_image;
                $facility->enabled = $this->m_enabled;
                $facility->saveModel();

                $this->_setLocations($facility);

                return;
            }
        }
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
        $currentLocations = Model_Fields_FacilityLocation::GetLocations($facility->id);
        foreach ($this->m_selectedLocations as $locationId) {
            $facilityLocation = Model_Fields_FacilityLocation::LookupByFacilityLocation($facility->id, $locationId);
            if (!isset($facilityLocation)) {
                Model_Fields_FacilityLocation::Create($facility->id, $locationId);
            }
        }
    }
}