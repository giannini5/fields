<?php

/**
 * Class Controller_Admin_Location
 *
 * @brief Select a location to administer or create a new location
 */
class Controller_Admin_Location extends Controller_Admin_Base {
    public $m_locations = NULL;
    public $m_name = NULL;
    public $m_enabled = NULL;
    public $m_locationId = NULL;

    public function __construct() {
        parent::__construct();

        $this->m_locations = $this->getLocations();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->m_name = $this->getPostAttribute(
                Model_Fields_LocationDB::DB_COLUMN_NAME,
                '* Name required'
            );
            $this->m_locationId = $this->getPostAttribute(
                View_Base::LOCATION_ID,
                NULL,
                FALSE
            );
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
            $view = new View_Admin_Location($this);
        } else {
            $view = new View_Admin_Home($this);
        }

        $view->displayPage();
    }

    /**
     * @brief Create Location.  If the location already exists then set the errorString.
     *        Add the created Location to the list of locations.
     */
    private function _createLocation() {
        $location = Model_Fields_Location::LookupByName($this->m_league, $this->m_name, FALSE);
        if (!isset($location)) {
            $location = Model_Fields_Location::Create($this->m_league, $this->m_name);
            $this->m_locations[] = $location;
        } else {
            $this->m_errorString = "Location '$this->m_name' already exists<br>Scroll down and update to make a change";
        }
    }

    /**
     * @brief Update Location.  Set the errorString if the Location cannot be updated.
     */
    private function _updateLocation() {
        // Error check
        foreach ($this->m_locations as $location) {
            if ($location->name == $this->m_name and $location->id != $this->m_locationId) {
                $this->m_errorString = "Location '$this->m_name' already exists<br>Scroll down and update to make a change";
                return;
            }
        }

        // Update
        foreach ($this->m_locations as $location) {
            if ($location->id == $this->m_locationId) {
                $location->name = $this->m_name;
                $location->saveModel();
                return;
            }
        }
    }
}