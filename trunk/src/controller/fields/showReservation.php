<?php

/**
 * Class Controller_ShowReservation
 *
 * @brief On GET, render page to show all reservations.
 *        On POST, show filtered reservations.
 */
class Controller_Fields_ShowReservation extends Controller_Fields_Base {
    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->m_filterFacilityId = $this->getPostAttribute(View_Base::FILTER_FACILITY_ID, 0);
            $this->m_filterDivisionId = $this->getPostAttribute(View_Base::FILTER_DIVISION_ID, 0);
            $this->m_filterTeamId = $this->getPostAttribute(View_Base::FILTER_TEAM_ID, 0);

            if ($this->m_operation == View_Base::DELETE) {
                $this->m_reservationId = $this->getPostAttribute(
                    View_Base::RESERVATION_ID,
                    '* Reservation Id Required'
                );
                $this->m_filterTeamId = $this->m_team->id;
            }
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

                case View_Base::DELETE:
                    Model_Fields_Reservation::DeleteById($this->m_reservationId);
                    break;
            }
        }

        $view = new View_Fields_ShowReservation($this);
        $view->displayPage();
    }
}