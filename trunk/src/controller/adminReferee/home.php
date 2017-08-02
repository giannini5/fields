<?php

/**
 * Class Controller_AdminReferee_Home
 *
 * @brief Controller for referee administration
 */
class Controller_AdminReferee_Home extends Controller_AdminReferee_Base
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @brief On GET, render the page to administer seasons
     *        On POST, complete the transaction and then render the page (with error message if any)
     */
    public function process()
    {
        $view = new View_AdminReferee_Home($this);

        $view->displayPage();
    }
}
