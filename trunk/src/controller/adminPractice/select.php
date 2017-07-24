<?php

/**
 * Class Controller_AdminPractice_Select
 *
 * @brief On GET, render page to select facility.
 *        On POST, verify data and then continue to process reservation.
 */
class Controller_AdminPractice_Select extends Controller_AdminPractice_Base {
    /** @var  Controller_Fields_SelectFacility */
    private $selectFacilityController;

    public $m_startTime;
    public $m_endTime;
    public $m_daysSelected;
    public $m_newSelection;

    public function __construct() {
        parent::__construct();

        $this->selectFacilityController = new Controller_Fields_SelectFacility();
        $this->m_startTime              = $this->selectFacilityController->m_startTime;
        $this->m_endTime                = $this->selectFacilityController->m_endTime;
        $this->m_daysSelected           = $this->selectFacilityController->m_daysSelected;
        $this->m_filterFacilityId       = $this->selectFacilityController->m_filterFacilityId;
        $this->m_filterDivisionId       = $this->selectFacilityController->m_filterDivisionId;
        $this->m_filterLocationId       = $this->selectFacilityController->m_filterLocationId;
        $this->m_filterTeamId           = $this->selectFacilityController->m_filterTeamId;
        $this->m_newSelection           = $this->selectFacilityController->m_newSelection;
    }

    /**
     * @brief Process the GET or POST
     */
    public function process() {
        // Re-direct to Login page if use is not authenticated
        if (!$this->m_isAuthenticated) {
            $this->getAuthenticated(View_Base::ADMIN_SELECT_FIELD_PAGE);
            return;
        } else {
            switch ($this->m_operation) {
                case View_Base::SELECT:
                    if ($this->m_filterTeamId == 0) {
                        $this->m_createReservationError = "ERROR: Select a Team and click Filter then Select a reservation time.";
                        $view = new View_AdminPractice_Select($this);
                    }
                    else if ($this->selectFacilityController->createReservation(true)) {
                        $this->m_filterFacilityId = 0;
                        $this->m_filterDivisionId = 0;

                        $view = new View_AdminPractice_Reservations($this);
                    } else {
                        $this->m_createReservationError = $this->selectFacilityController->m_createReservationError;
                        $view = new View_AdminPractice_Select($this);
                    }
                    break;

                case View_Base::FILTER:
                    // $view = new View_Fields_SelectFacility($this);
                    $view = new View_AdminPractice_Select($this);
                    break;

                case View_Base::SIGN_OUT:
                    $this->signOut();
                    $view = new View_AdminPractice_Home($this);
                    break;

                default:
                    $view = new View_AdminPractice_Select($this);
                    break;
            }
        }

        $view->displayPage();
    }
}
