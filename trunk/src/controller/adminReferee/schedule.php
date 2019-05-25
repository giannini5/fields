<?php

use \DAG\Domain\Schedule\GameDate;

/**
 * Class Controller_AdminReferee_Schedule
 *
 * @brief View/Adjust schedule for a referees
 */
class Controller_AdminReferee_Schedule extends Controller_AdminReferee_Base {

    public $m_filterGameDateId;
    public $m_filterDivisionName;
    public $m_filterRefereeDisplayType;

    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->m_operation == View_Base::VIEW
                or $this->m_operation == View_Base::POPULATE
                or $this->m_operation == View_Base::PUBLISH
                or $this->m_operation == View_Base::UN_PUBLISH
                or $this->m_operation == View_Base::CLEAR) {

                $this->m_filterDivisionName         = $this->getPostAttribute(View_Base::FILTER_DIVISION_NAME, '', true);
                $this->m_filterGameDateId           = $this->getPostAttribute(View_Base::GAME_DATE_ID, 0, true, true);
                $this->m_filterRefereeDisplayType   = $this->getPostAttribute(View_Base::REFEREE_DISPLAY_TYPE, '', false, false);
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
                case View_Base::POPULATE:
                    $this->populateSchedule();
                    break;
                case View_Base::PUBLISH:
                    $this->publishSchedule();
                    break;
                case View_Base::UN_PUBLISH:
                    $this->unPublishSchedule();
                    break;
                case View_Base::CLEAR:
                    $this->clearSchedule();
                    break;
            }
        }

        if ($this->m_isAuthenticated) {
            $view = new View_AdminReferee_Schedule($this);
        } else {
            $view = new View_AdminReferee_Home($this);
        }

        $view->displayPage();
    }

    private function populateSchedule()
    {
        $gameDate = GameDate::lookupById($this->m_filterGameDateId);
        $this->m_season->populateGameDayReferees($gameDate, $this->m_filterDivisionName);
        $this->m_messageString = "Referees successfully assigned for Division $this->m_filterDivisionName for $gameDate->day";
    }

    private function publishSchedule()
    {
        GameDate::lookupById($this->m_filterGameDateId);
        // $this->m_season->publishRefereeSchedule($gameDate, $this->m_filterDivisionName);
        $this->m_messageString = "TODO";
        // $this->m_messageString = "Referee schedule published and emails sent: $this->m_filterDivisionName for $gameDate->day";
    }

    private function unPublishSchedule()
    {
        GameDate::lookupById($this->m_filterGameDateId);
        // $this->m_season->unpublishRefereeSchedule($gameDate, $this->m_filterDivisionName);
        $this->m_messageString = "TODO";
        // $this->m_messageString = "Referee schedule made private: $this->m_filterDivisionName for $gameDate->day";
    }

    private function clearSchedule()
    {
        $gameDate = GameDate::lookupById($this->m_filterGameDateId);
        $this->m_season->clearGameDayReferees($gameDate, $this->m_filterDivisionName);
        $this->m_messageString = "Referee schedule cleared: $this->m_filterDivisionName for $gameDate->day";
    }
}