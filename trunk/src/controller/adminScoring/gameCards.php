<?php

use \DAG\Domain\Schedule\GameDate;

/**
 * Class Controller_AdminScoring_GameCards
 *
 * @brief Controller for printing game cards
 */
class Controller_AdminScoring_GameCards extends Controller_AdminScoring_Base
{
    const DIVISION_BY_DAY   = 'divisionByDay';
    const FACILITY_BY_DAY   = 'facilityByDay';

    public $displayType;
    public $divisionId;
    public $gameDate;
    public $gameDateId;
    public $facilityId;

    public function __construct()
    {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->displayType    = $this->getPostAttribute(View_Base::GAME_CARD_TYPE, '', false, false);
            $this->gameDateId     = $this->getPostAttribute(View_Base::GAME_DATE_ID, null, false, false);

            if (isset($this->gameDateId)) {
                $this->gameDate = GameDate::lookupById((int)$this->gameDateId);
            }

            switch ($this->displayType) {
                case self::DIVISION_BY_DAY:
                    $this->divisionId = $this->getPostAttribute(View_Base::DIVISION_ID, null, true, true);
                    break;

                case self::FACILITY_BY_DAY:
                    $this->facilityId   = $this->getPostAttribute(View_Base::FACILITY_ID, null, true, true);
                    break;

                default:
                    $this->email = $this->getPostAttribute(
                        Model_Fields_CoachDB::DB_COLUMN_EMAIL,
                        '* Email Address is required'
                    );
                    $this->password = $this->getPostAttribute(
                        Model_Fields_CoachDB::DB_COLUMN_PASSWORD,
                        '* Password is required'
                    );
                    break;
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
            switch ($this->displayType) {
                case self::DIVISION_BY_DAY:
                case self::FACILITY_BY_DAY:
                    break;

                default:
                    $this->_login();
                    break;
            }
        }

        $view = new View_AdminScoring_GameCards($this);
        $view->displayPage();
    }
}