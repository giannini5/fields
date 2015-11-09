<?php

/**
 * Class Controller_ShowReservation
 *
 * @brief On GET, render page to show current reservation (if any).
 *        On POST, verify data and then continue navigation to requested page.
 */
class Controller_Fields_ShowReservation extends Controller_Fields_Base {
    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->m_operation == View_Base::DELETE) {
                $this->m_reservationId = $this->getPostAttribute(
                    View_Base::RESERVATION_ID,
                    '* Reservation Id Required'
                );
            }
        }
    }

    /**
     * @brief Process the GET or POST
     */
    public function process() {
        // Re-direct to Login page if use is not authenticated
        if (!$this->m_isAuthenticated) {
            $this->getAuthenticated(View_Base::SHOW_RESERVATION_PAGE);
            return;
        }

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

        // If reservation(s) found then show reservation(s); otherwise go to select facility
        $reservations = $this->getReservationsForTeam(TRUE);
        if (count($reservations) > 0) {
            $view = new View_Fields_ShowReservation($this);
        } else {
            $view = new View_Fields_SelectFacility($this);
        }

        $view->displayPage();
    }
}