<?php

/**
 * Class Controller_TestPost
 *
 * @brief Test sending Post Attributes to the Welcome Page
 */
class Controller_Fields_TestPost extends Controller_Fields_Base {

    public function __construct() {
        parent::__construct();
    }

    /**
     * @brief On GET, render the page to ask user for create account attributes
     */
    public function process() {
        $view = new View_Fields_TestPost($this);
        $view->displayPage();
    }
}