<?php

use \DAG\Domain\Schedule\Division;

/**
 * Class Controller_Schedules_Division
 *
 * @brief Control for the display of Division schedules
 */
class Controller_Schedules_Division extends Controller_Schedules_Base {

    public $m_divisionName;
    public $m_division;

    public function __construct() {
        parent::__construct();

        $this->m_divisionName = $this->getRequestAttribute(View_Base::DIVISION_NAME, '');

        if (!empty($this->m_divisionName)) {
            $divisionNameAttributes = explode(' ', $this->m_divisionName);
            if (count($divisionNameAttributes) == 2) {
                $this->m_division = Division::lookupByNameAndGender($this->m_season, $divisionNameAttributes[0], $divisionNameAttributes[1]);
            }
        }
    }

    /**
     * @brief Process the request based on provided filters
     */
    public function process() {
        // Display Division Schedule page
        $view = new View_Schedules_Division($this);
        $view->displayPage();
    }
}