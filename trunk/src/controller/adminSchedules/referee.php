<?php

/**
 * Class Controller_AdminSchedules_Referee
 *
 * @brief Select a facility to administer
 */
class Controller_AdminSchedules_Referee extends Controller_AdminSchedules_Base {

    public $m_facilityId;

    public function __construct()
    {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->m_facilityId = $this->getPostAttribute(
                View_Base::FACILITY_ID,
                null,
                true,
                true,
                '* facility identifier is missing');
        }
    }

    /**
     * @brief On GET, render the page to administer seasons
     *        On POST, complete the transaction and then render the page (with error message if any)
     */
    public function process() {
        if ($this->m_missingAttributes == 0) {
            switch ($this->m_operation) {
                default:
                    break;
            }
        }

        $view = new View_AdminSchedules_Referee($this);
        $view->displayPage();
    }
}