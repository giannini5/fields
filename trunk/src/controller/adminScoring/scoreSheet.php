<?php

use \DAG\Domain\Schedule\GameDate;
use \DAG\Domain\Schedule\Facility;

/**
 * Class Controller_AdminScoring_ScoreSheet
 *
 * @brief Controller for scoring
 */
class Controller_AdminScoring_ScoreSheet extends Controller_AdminScoring_Base
{
    const GAME_DISPLAY_FOR_SCORING  = 'gameDisplay';

    public $m_scoringType;
    public $m_gameDate;
    public $m_gameDateId;
    public $m_facility;

    public function __construct()
    {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->m_scoringType    = $this->getPostAttribute(View_Base::SCORING_TYPE, '', false, false);

            if ($this->m_scoringType == self::GAME_DISPLAY_FOR_SCORING) {
                $this->m_filterFacilityId   = $this->getPostAttribute(View_Base::FILTER_FACILITY_ID, '', true, true);
                $this->m_gameDateId         = $this->getPostAttribute(View_Base::GAME_DATE, null, true, false);

                if (isset($this->m_gameDateId)) {
                    $this->m_gameDate = GameDate::lookupById((int)$this->m_gameDateId);
                }

                if (isset($this->m_filterFacilityId) and $this->m_filterFacilityId != 0) {
                    $this->m_facility = Facility::lookupById((int)$this->m_filterFacilityId);
                }
            } else {
                $this->m_email = $this->getPostAttribute(
                    Model_Fields_CoachDB::DB_COLUMN_EMAIL,
                    '* Email Address is required'
                );
                $this->m_password = $this->getPostAttribute(
                    Model_Fields_CoachDB::DB_COLUMN_PASSWORD,
                    '* Password is required'
                );
            }
        }
    }

    /**
     * @brief On GET, render the page to administer seasons
     *        On POST, complete the transaction and then render the page (with error message if any)
     */
    public function process()
    {
        if ($this->m_operation == View_Base::SIGN_OUT) {
            $this->signOut();
        } else if ($this->m_missingAttributes == 0) {
            switch ($this->m_scoringType) {
                case View_Base::SIGN_OUT:
                    $this->signOut();
                    break;

                case self::GAME_DISPLAY_FOR_SCORING:
                    break;

                default:
                    $this->_login();
                    break;
            }
        }

        $view = new View_AdminScoring_ScoreSheet($this);

        $view->displayPage();
    }
}
