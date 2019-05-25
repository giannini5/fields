<?php

use \DAG\Domain\Schedule\Referee;
use \DAG\Domain\Schedule\Family;

/**
 * Class Controller_AdminReferee_Referee
 *
 * @brief Select a referee to administer or create a new referee
 */
class Controller_AdminReferee_Referee extends Controller_AdminReferee_Base {

    public $m_filterRefereeId;
    public $m_refereeId;
    public $m_name;
    public $m_email;
    public $m_phone;
    public $m_badgeId;

    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->m_operation == View_Base::FILTER) {
                $this->m_filterDivisionId = $this->getPostAttribute(View_Base::FILTER_DIVISION_ID, 0);
                $this->m_filterRefereeId  = $this->getPostAttribute(View_Base::FILTER_REFEREE_ID, 0);
            }

            if ($this->m_operation == View_Base::CREATE or
                $this->m_operation == View_Base::UPDATE) {
                $this->m_refereeId   = $this->getPostAttribute(View_Base::REFEREE_ID, 0, false, true);
                $this->m_name        = $this->getPostAttribute(View_Base::NAME, '', true, false, "Name is missing");
                $this->m_email       = $this->getPostAttribute(View_Base::EMAIL_ADDRESS, '', true, false, "Email is missing");
                $this->m_phone       = $this->getPostAttribute(View_Base::PHONE1, '', true, false, "Phone is missing");
                $this->m_badgeId     = $this->getPostAttribute(View_Base::REFEREE_BADGE, '', true, false, "Badge is missing");
            }
        }
    }

    /**
     * @brief On GET, render the page to administer referee
     *        On POST, complete the transaction and then render the page (with error message if any)
     */
    public function process() {
        if ($this->m_isAuthenticated and $this->m_missingAttributes == 0) {
            switch ($this->m_operation) {
                case View_Base::CREATE:
                    $this->createReferee();
                    break;

                case View_Base::UPDATE:
                    $this->updateReferee();
                    break;
            }
        }

        if ($this->m_isAuthenticated) {
            $view = new View_AdminReferee_Referee($this);
        } else {
            $view = new View_AdminReferee_Home($this);
        }

        $view->displayPage();
    }

    /**
     * @brief Create Referee, Coach and Family as necessary:
     */
    private function createReferee() {
        $referee = null;
        if (Referee::findByEmailAndName($this->m_season, $this->m_email, $this->m_name, $referee)) {
            $this->m_errorString = "Referee with email $this->m_email and name $this->m_name already exist.  Use update instead";
            return;
        }

        // Find family (if any)
        $familily = null;
        Family::findByPhone($this->m_season, $this->m_phone, $family);

        // Create referee
        $referee = Referee::create($this->m_season,
            $family,
            $this->m_name,
            $this->m_email,
            $this->m_phone,
            $this->m_badgeId,
            0,
            '');

        $this->m_messageString = "Referee " . $referee->name . " successfully created.";
    }

    /**
     * @brief Update Referee and Coach:
     *        - Update referee and coach meta data
     */
    private function updateReferee() {
        // Verify referee exists
        $referee   = Referee::lookupById((int)$this->m_refereeId);

        // Update Referee meta data
        $referee->name          = $this->m_name;
        $referee->email         = $this->m_email;
        $referee->phone         = $this->m_phone;
        $referee->badgeId       = $this->m_badgeId;

        $this->m_messageString = "Referee " . $referee->name . " successfully updated.";
    }
}