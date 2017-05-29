<?php

use \DAG\Domain\Schedule\Division;

/**
 * Class Controller_Games_Flights
 *
 * @brief Control for the display flights
 */
class Controller_Games_Flights extends Controller_Games_Base {

    public function __construct() {
        parent::__construct();
    }

    /**
     * @brief Process the request based on provided filters
     */
    public function process() {
        $view = new View_Games_Flights($this);
        $view->displayPage();
    }
}