<?php

/**
 * Class Controller_ShowReservation
 *
 * @brief On GET, render page to show current reservation (if any).
 *        On POST, verify data and then continue navigation to requested page.
 */
class Controller_ShowReservation extends Controller_Base {
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

        $view = new View_ShowReservation($this);
        $view->displayPage();
    }
}