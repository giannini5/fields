<?php


/**
 * Class Controller_Schedules_Team
 *
 * @brief Control for the display of a team's schedule
 */
class Controller_Schedules_Team extends Controller_Schedules_Base {
    public $m_filterCoachId = 0;

    public function __construct() {
        parent::__construct();

        $this->m_operation      = $this->getRequestAttribute(View_Base::SUBMIT, '');
        $this->m_filterCoachId  = $this->getRequestAttribute(View_Base::FILTER_COACH_ID, 0);
    }

    /**
     * @brief Process the request based on provided filters
     */
    public function process() {
        // Display Team Schedule page
        $view = new View_Schedules_Team($this);
        $view->displayPage();
    }
}