<?php

use \DAG\Domain\Schedule\Division;

/**
 * Class Controller_Games_TeamStats
 *
 * @brief Control for the display team statistics for a division
 */
class Controller_Games_TeamStats extends Controller_Games_Base {
    public $m_divisionName;
    public $m_division;


    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'GET') {
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
        $view = new View_Games_TeamStats($this);
        $view->displayPage();
    }
}