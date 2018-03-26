<?php

use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\Team;

/**
 * Class Controller_AdminScoring_Home
 *
 * @brief Controller for scoring
 */
class Controller_AdminScoring_VolunteerPoints extends Controller_AdminScoring_Base
{
    const VOLUNTEER_POINTS = 'volunteerPoints';

    public $m_scoringType;
    public $m_divisionName;
    public $m_division;

    private $m_volunteerPointsData = [];

    public function __construct()
    {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->m_scoringType    = $this->getPostAttribute(View_Base::SCORING_TYPE, '', false, false);

            if ($this->m_scoringType == self::VOLUNTEER_POINTS) {
                $this->m_divisionName = $this->getPostAttribute(View_Base::DIVISION_NAME, '', false, false);
                $divisionNameAttributes = explode(' ', $this->m_divisionName);
                if (count($divisionNameAttributes) == 2) {
                    $this->m_division = Division::lookupByNameAndGender($this->m_season, $divisionNameAttributes[0], $divisionNameAttributes[1]);
                    $this->m_volunteerPointsData = $this->getPostAttributeArray(View_Base::VOLUNTEER_POINTS_DATA);
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

                case self::VOLUNTEER_POINTS:
                    $this->processVolunteerPoints();
                    break;

                default:
                    $this->_login();
                    break;
            }
        }

        $view = new View_AdminScoring_VolunteerPoints($this);

        $view->displayPage();
    }

    /**
     * Update volunteer points for teams
     */
    private function processVolunteerPoints()
    {
        foreach ($this->m_volunteerPointsData as $teamId => $volunteerPoints) {
            $team = Team::lookupById($teamId);
            $team->volunteerPoints = $volunteerPoints;
        }

        $this->m_messageString = count($this->m_volunteerPointsData) > 0 ? "Volunteer Points Updated" : "";
    }
}
