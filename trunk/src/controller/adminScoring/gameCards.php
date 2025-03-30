<?php

use \DAG\Domain\Schedule\GameDate;
use DAG\inLeague\Region;

/**
 * Class Controller_AdminScoring_GameCards
 *
 * @brief Controller for printing game cards
 */
class Controller_AdminScoring_GameCards extends Controller_AdminScoring_Base
{
    const DIVISION_BY_DAY   = 'divisionByDay';
    const FACILITY_BY_DAY   = 'facilityByDay';
    const MEDAL_BY_DAY      = 'medalByDay';

    public $displayType;
    public $divisionName;
    public $gender;
    public $gameDate;
    public $gameDateId;
    public $facilityId;
    public $medalRoundGames = false;
    public $refereeNote;

    public function __construct()
    {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->displayType     = $this->getPostAttribute(View_Base::GAME_CARD_TYPE, '', false, false);
            $this->gameDateId      = $this->getPostAttribute(View_Base::GAME_DATE_ID, null, false, false);
            $this->refereeNote     = $this->getPostAttribute(View_Base::REFEREE_NOTE, '', false, false);

            if (isset($this->gameDateId)) {
                $this->gameDate = GameDate::lookupById((int)$this->gameDateId);
            }

            switch ($this->displayType) {
                case self::DIVISION_BY_DAY:
                    $this->divisionName = $this->getPostAttribute(View_Base::DIVISION_NAME, null, true, false);
                    $this->gender       = $this->getPostAttribute(View_Base::GENDER, null, true, false);
                    break;

                case self::FACILITY_BY_DAY:
                    $this->facilityId   = $this->getPostAttribute(View_Base::FACILITY_ID, null, true, true);
                    break;

                case self::MEDAL_BY_DAY:
                    $this->gender       = $this->getPostAttribute(View_Base::GENDER, null, true, false);
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
                    $region = new Region();
                    $divisions = $region->getDivisionsForGameCards($this->divisionName, $this->gender);
                    $games = $region->getGames($divisions, $this->gameDate->day);
                    break;
                case self::FACILITY_BY_DAY:
                case self::MEDAL_BY_DAY:
                    $this->m_errorString = 'FACILITY_BY_DAY and MEDAL_BY_DAY are no longer supported';
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
