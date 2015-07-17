<?php

/**
 * Class Controller_SelectDayTime
 *
 * @brief On GET, render page to get day/time selection.
 *        On POST, verify data and then process reservation.
 */
class Controller_SelectDayTime extends Controller_Base {
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
        $view = new View_SelectDayTime($this);
        $view->displayPage();
    }
}