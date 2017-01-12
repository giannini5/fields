<?php

/**
 * Class Controller_AdminPractice_Reservations
 *
 * @brief On GET, render page to show all reservations.
 *        On POST, show filtered reservations.
 */
class Controller_AdminPractice_Reservations extends Controller_AdminPractice_Base {
    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->m_filterFacilityId = $this->getPostAttribute(View_Base::FILTER_FACILITY_ID, 0);
            $this->m_filterDivisionId = $this->getPostAttribute(View_Base::FILTER_DIVISION_ID, 0);
            $this->m_filterTeamId = $this->getPostAttribute(View_Base::FILTER_TEAM_ID, 0);
        }
    }

    /**
     * @brief Process the GET or POST
     */
    public function process() {
        $reservations = array();

        if ($this->m_isAuthenticated) {
            switch ($this->m_operation) {
                case View_Base::SIGN_OUT:
                    $this->signOut();
                    $view = new View_Fields_Login($this, View_Base::LOGIN_PAGE);
                    $view->displayPage();
                    return;
            }

            $view = new View_AdminPractice_Reservations($this);
        } else {
            $view = new View_AdminPractice_Home($this);
        }

        $view->displayPage();
    }
}