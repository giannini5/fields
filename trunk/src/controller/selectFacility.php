<?php

/**
 * Class Controller_SelectFacility
 *
 * @brief On GET, render page to select facility.
 *        On POST, verify data and then continue to process reservation.
 */
class Controller_SelectFacility extends Controller_Base {
    public function __construct() {
        parent::__construct();
    }

    /**
     * @brief Process the GET or POST
     */
    public function process() {
        // Re-direct to Login page if use is not authenticated
        if (!$this->m_isAuthenticated) {
            $view = new View_Login($this);
            $view->displayPage();
            return;
        }

        switch ($this->m_operation) {
            case View_Base::SELECT:
                $view = new View_SelectDayTime($this);
                $view->displayPage();
                break;

            default:
                // Get the list of facilities

                // Get the list of fields that are age appropriate for each facility

                // Get the availability for each field

                $view = new View_SelectFacility($this);
                $view->displayPage();
                break;
        }
    }
}