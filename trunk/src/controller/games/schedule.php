<?php

use \DAG\Domain\Schedule\Division;

/**
 * Class Controller_Games_Schedule
 *
 * @brief Control for the display of a team's schedule
 */
class Controller_Games_Schedule extends Controller_Games_Base {
    public $m_filterCoachId = 0;
    public $m_divisionName;
    public $m_division;


    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'GET') {
            $this->m_filterCoachId = $this->getRequestAttribute(View_Base::FILTER_COACH_ID, 0);

            $this->m_divisionName = $this->getRequestAttribute(View_Base::DIVISION_NAME, '');
            if (!empty($this->m_divisionName)) {
                $divisionNameAttributes = explode(' ', $this->m_divisionName);
                if (count($divisionNameAttributes) == 2) {
                    $this->m_division = Division::lookupByNameAndGender($this->m_season, $divisionNameAttributes[0], $divisionNameAttributes[1]);
                }
            }
        }
    }

    /**
     * @brief Process the request based on provided filters
     */
    public function process() {
        $view = new View_Games_Schedule($this);
        $view->displayPage();
    }
}