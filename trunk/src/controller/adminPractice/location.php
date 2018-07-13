<?php

/**
 * Class Controller_AdminPractice_Location
 *
 * @brief Select a location to administer or create a new location
 */
class Controller_AdminPractice_Location extends Controller_AdminPractice_Base {
    public $m_name = NULL;
    public $m_locationId = NULL;

    private $m_locationUpdates;

    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->m_operation == View_Base::CREATE) {
                $this->m_name = $this->getPostAttribute(
                    Model_Fields_LocationDB::DB_COLUMN_NAME,
                    '* Name required'
                );
            }

            if ($this->m_operation == View_Base::UPDATE) {
                $this->m_locationUpdates = $this->getPostAttributeArray(View_Base::LOCATION_UPDATE_DATA);
            }
        }
    }

    /**
     * @brief On GET, render the page to administer locations
     *        On POST, complete the transaction and then render the page (with error message if any)
     */
    public function process() {
        if ($this->m_missingAttributes == 0) {
            switch ($this->m_operation) {
                case View_Base::CREATE:
                    $this->_createLocation();
                    break;

                case View_Base::UPDATE:
                    $this->_updateLocation();
                    break;
            }
        }

        if ($this->m_isAuthenticated) {
            $view = new View_AdminPractice_Location($this);
        } else {
            $view = new View_AdminPractice_Home($this);
        }

        $view->displayPage();
    }

    /**
     * @brief Create Location.  If the location already exists then set the errorString.
     *        Add the created Location to the list of locations.
     */
    private function _createLocation() {
        $location = Model_Fields_Location::LookupByName($this->m_league->id, $this->m_name, FALSE);
        if (!isset($location)) {
            Model_Fields_Location::Create($this->m_league->id, $this->m_name);
            $this->m_messageString = "Location $this->m_name created";
        } else {
            $this->m_errorString = "Location '$this->m_name' already exists<br>Scroll down and update to make a change";
        }
    }

    /**
     * @brief Update Location.  Set the errorString if the Location cannot be updated.
     */
    private function _updateLocation() {
        foreach ($this->m_locationUpdates as $locationId => $locationData) {
            // Error check
            $updateLocation     = Model_Fields_Location::LookupById($locationId);
            $existingLocation   = Model_Fields_Location::LookupByName($this->m_league->id, $locationData[Model_Fields_LocationDB::DB_COLUMN_NAME], FALSE);
            if (isset($existingLocation) and $existingLocation->id != $updateLocation->id) {
                $name = $locationData[Model_Fields_LocationDB::DB_COLUMN_NAME];
                $this->m_errorString = "Location '$name' already exists<br>Scroll down and update to make a change";
                return;
            }

            // Update
            if ($updateLocation->name != $locationData[Model_Fields_LocationDB::DB_COLUMN_NAME]) {
                $updateLocation->name = $locationData[Model_Fields_LocationDB::DB_COLUMN_NAME];
                $updateLocation->saveModel();
            }
        }

        $this->m_messageString = "Locations updated";
    }
}