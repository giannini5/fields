<?php

use \DAG\Domain\Schedule\Facility;

/**
 * Class Controller_Schedules_Facility
 *
 * @brief Select a facility to administer or create a new facility
 */
class Controller_Schedules_Facility extends Controller_Schedules_Base {
    public $m_name = NULL;
    public $m_address1;
    public $m_address2;
    public $m_city;
    public $m_state;
    public $m_postalCode;
    public $m_contactName;
    public $m_contactEmail;
    public $m_contactPhone;
    public $m_image;
    public $m_enabled = NULL;
    public $m_facilityId = NULL;

    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->m_name = $this->getPostAttribute(
                View_Base::NAME,
                null,
                true,
                false,
                "* name required"
            );
            $this->m_address1 = $this->getPostAttribute(
                View_Base::ADDRESS1,
                '',
                false
            );
            $this->m_address2 = $this->getPostAttribute(
                View_Base::ADDRESS2,
                '',
                false
            );
            $this->m_city = $this->getPostAttribute(
                View_Base::CITY,
                '',
                false
            );
            $this->m_state = $this->getPostAttribute(
                View_Base::STATE,
                '',
                false
            );
            $this->m_postalCode = $this->getPostAttribute(
                View_Base::POSTAL_CODE,
                '',
                FALSE
            );
            $this->m_contactName = $this->getPostAttribute(
                View_Base::CONTACT_NAME,
                '',
                false
            );
            $this->m_contactEmail = $this->getPostAttribute(
                View_Base::CONTACT_EMAIL,
                '',
                false
            );
            $this->m_contactPhone = $this->getPostAttribute(
                View_Base::CONTACT_PHONE,
                '',
                false
            );
            $this->m_image = $this->getPostAttribute(
                View_Base::IMAGE,
                '',
                false
            );
            $this->m_enabled = $this->getPostAttribute(
                View_Base::ENABLED,
                '',
                true,
                true,
                '* enabled required'
            );
            $this->m_facilityId = $this->getPostAttribute(
                View_Base::FACILITY_ID,
                null,
                false,
                true
            );
        }
    }

    /**
     * @brief On GET, render the page to administer seasons
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
            $view = new View_Schedules_Facility($this);
        } else {
            $view = new View_Schedules_Home($this);
        }

        $view->displayPage();
    }

    /**
     * @brief Create Facility
     */
    private function _createFacility() {
        if (Facility::findByName($this->m_season, $this->m_name, $facility)) {
            $this->m_errorString = "Facility '$facility->name' already exists<br>Scroll down and update to make a change";
        } else {
            $facility = Facility::create(
                $this->m_season,
                $this->m_name,
                $this->m_address1,
                $this->m_address2,
                $this->m_city,
                $this->m_state,
                $this->m_postalCode,
                $this->m_contactName,
                $this->m_contactEmail,
                $this->m_contactPhone,
                $this->m_image,
                $this->m_enabled
            );

            $this->m_messageString = "'$facility->name' successfully created.";
        }
    }

    /**
     * @brief Update Facility
     */
    private function _updateFacility() {
        $facility = Facility::lookupById($this->m_facilityId);

        $facility->name         = $this->m_name;
        $facility->address1     = $this->m_address1;
        $facility->address2     = $this->m_address2;
        $facility->city         = $this->m_city;
        $facility->state        = $this->m_state;
        $facility->postalCode   = $this->m_postalCode;
        $facility->contactName  = $this->m_contactName;
        $facility->contactEmail = $this->m_contactEmail;
        $facility->contactPhone = $this->m_contactPhone;
        $facility->image        = $this->m_image;
        $facility->enabled      = $this->m_enabled;

        $this->m_messageString = "'$facility->name' successfully updated.";
    }
}