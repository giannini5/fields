<?php


/**
 * Class Controller_Games_Home
 *
 * @brief Default to Schedule display since that should be the most frequently visited
 */
class Controller_Games_Home extends Controller_Games_Base {
    public function __construct() {
        parent::__construct();
    }

    /**
     * @brief Process the request based on provided filters
     */
    public function process() {
        $view = new View_Games_Schedule($this);
        $view->displayPage();
    }
}