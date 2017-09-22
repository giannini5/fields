<?php

use \DAG\Domain\Schedule\Game;
use \DAG\Domain\Schedule\Coach;
use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\GameDate;
use \DAG\Framework\Exception\Precondition;
use \DAG\Domain\Schedule\Pool;
use \DAG\Domain\Schedule\Team;
use \DAG\Domain\Schedule\Facility;
use \DAG\Domain\Schedule\Field;
use \DAG\Domain\Schedule\GameTime;

/**
 * @brief Show the Schedule page and get the user to select a schedule to administer or create a new schedule.
 */
class View_AdminScoring_Home extends View_AdminScoring_Base
{
    /**
     * @brief Construct the View
     *
     * @param Controller_Base $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller)
    {
        parent::__construct(self::SCORING_HOME_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        if ($this->m_controller->m_isAuthenticated) {
            $this->renderScoring();
        } else {
            $this->renderLogin();
        }
    }

    /**
     * @brief Render sign-in screen
     */
    public function renderLogin() {
        print "
            <table align='center' valign='top' border='1' cellpadding='5' cellspacing='0'>
            <tr><td>
            <table align='center' valign='top' border='0' cellpadding='5' cellspacing='0'>";

        print "
            <form method='post' action='" . self::SCORING_HOME_PAGE . $this->m_urlParams . "'>";

        print "
                <tr>
                    <td colspan='2' style='font-size:24px'><font color='darkblue'><b>Sign In</b></font></td>
                </tr>";

        $this->displayInput('Email Address:', 'text', Model_Fields_PracticeFieldCoordinatorDB::DB_COLUMN_EMAIL, 'email address', $this->m_controller->m_email);
        $this->displayInput('Password:', 'password', Model_Fields_PracticeFieldCoordinatorDB::DB_COLUMN_PASSWORD, 'password', $this->m_controller->m_password);

        print "
                <tr>
                    <td colspan='2' align='right'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::SUBMIT . "'>
                    </td>
                    <td>&nbsp</td>
                </tr>
            </form>";

        print "
            </table>
            </td></tr></table>";
    }

    /**
     * @brief Render data for display on the page.
     */
    public function renderScoring()
    {
        $sessionId          = $this->m_controller->getSessionId();
        $divisionsSelector  = $this->getDivisionsSelector(true, false, true);
        $gameDateSelector   = $this->getGameDateSelector();

        $messageString = $this->m_controller->m_messageString;
        if (!empty($messageString)) {
            print "
                <p style='color: green' align='center'><strong>$messageString</strong></p><br>";
        }

        print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td valign='top' bgcolor='" . View_Base::CREATE_COLOR . "'>";

        $this->_printEnterScoreForGame($sessionId);

        print "
                    </td>
                    <td valign='top' bgcolor='" . View_Base::VIEW_COLOR . "'>";

        $this->_printEnterScoreForTeam($sessionId);

        print "
                    </td>
                    <td valign='top' bgcolor='" . View_Base::VIEW_COLOR . "'>";

        $this->_printEnterScoreForDay($sessionId, $divisionsSelector, $gameDateSelector);

        print "
                    </td>
                    <td valign='top' bgcolor='" . View_Base::VIEW_COLOR . "'>";

        $this->_printEnterVolunteerPoints($sessionId, $divisionsSelector);

        print "
                    </td>
                    <td valign='top' bgcolor='" . View_Base::VIEW_COLOR . "'>";

        $this->printSelectFacilityAndDay($sessionId, $gameDateSelector);

        print "
                    </td>
                </tr>
            </table>
            <br><br>";

        switch ($this->m_controller->m_scoringType) {
            case Controller_AdminScoring_Home::GAME_SCORING:
                if (Game::findById($this->m_controller->m_gameId, $game)) {
                    $this->printUpdateGameForm($sessionId, $game, Controller_AdminScoring_Home::GAME_SCORING);
                }
                break;
            case Controller_AdminScoring_Home::TEAM_SCORING:
                $coach = Coach::lookupById($this->m_controller->m_coachId);
                $this->printUpdateTeamGamesForm($sessionId, $coach);
                break;
            case Controller_AdminScoring_Home::DIVISION_SCORING:
                $this->printUpdateDivisionGamesForm($sessionId, $this->m_controller->m_division, $this->m_controller->m_gameDate);
                break;
            case Controller_AdminScoring_Home::VOLUNTEER_POINTS:
                $this->printUpdateDivisionVolunteerPointsForm($sessionId, $this->m_controller->m_division);
                break;
            case Controller_AdminScoring_Home::GAME_DISPLAY_FOR_SCORING:
                if (isset($this->m_controller->m_facility)) {
                    $this->printGamesForFacilityForDay($this->m_controller->m_facility, $this->m_controller->m_gameDate, true);
                } else {
                    $this->printGamesForForDay($this->m_controller->m_gameDate);
                }
                break;
        }
    }

    /**
     * @brief Print the form to enter the score for a game.  Form includes the following
     *          - GameId
     *          - Home Team Score
     *          - Visiting Team Score
     *          - Home Team Yellow and Red Cards
     *          - Visiting Team Yellow and Red Cards
     *          - Game notes
     *
     * @param $sessionId - Session Identifier
     */
    private function _printEnterScoreForGame($sessionId)
    {
        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <th nowrap align='left' colspan='7'>Enter Score for Game</th>
                </tr>
                <form method='post' action='" . self::SCORING_HOME_PAGE . $this->m_urlParams . "'>";

        $this->displayInput('Game Id:', 'number', View_Base::GAME_ID, '', '', "", null, 6, true, 50, false, true);

        print "
                <tr>
                    <td colspan='7' style='color: " . View_Base::AQUA . "'><strong>Home Team:</strong></td>
                </tr>
                <tr>
                    <td>&nbsp</td>";

        $numberWidth = 25;
        $this->displayInput('Score:', 'number', View_Base::HOME_SCORE, '', '', '', null, 1, false, $numberWidth, false, true);
        $this->displayInput('Yellow Cards:', 'number', View_Base::HOME_YELLOW_CARDS, '', '', 0, null, 1, false, $numberWidth, false, true);
        $this->displayInput('Red Cards:', 'number', View_Base::HOME_RED_CARDS, '', '', 0, null, 1, false, $numberWidth, false, true);
        print "
                </tr>";

        print "
                <tr>
                    <td colspan='7' style='color: " . View_Base::AQUA . "'><strong>Visiting Team:</strong></td>
                </tr>
                <tr>
                    <td>&nbsp</td>";

        $this->displayInput('Score:', 'number', View_Base::VISITING_SCORE, '', '', '', null, 1, false, $numberWidth, false, true);
        $this->displayInput('Yellow Cards:', 'number', View_Base::VISITING_YELLOW_CARDS, '', '', 0, null, 1, false, $numberWidth, false, true);
        $this->displayInput('Red Cards:', 'number', View_Base::VISITING_RED_CARDS, '', '', 0, null, 1, false, $numberWidth, false, true);
        print "
                </tr>";
        $this->displayInput('Game Notes:', 'text', View_Base::GAME_NOTES, '', '', '', null, 6, true, 340, false);

        // Print Enter button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::ENTER . "'>
                        <input type='hidden' id='" . View_Base::SCORING_TYPE . "' name='" . View_Base::SCORING_TYPE . "' value='" . Controller_AdminScoring_Home::GAME_SCORING . "'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
    }

    /**
     * @brief Print the form to enter the scores for a team's games.  Form includes the following
     *          - Team drop down
     *
     * @param $sessionId - Session Identifier
     */
    private function _printEnterScoreForTeam($sessionId)
    {
        $coachId = isset($this->m_controller->m_coachId) ? $this->m_controller->m_coachId : 0;
        View_Games_Team::printTeamSelectors(
            $this->m_controller->m_season,
            $coachId,
            "Enter Scores For Team",
            self::SCORING_HOME_PAGE,
            $sessionId,
            '0',
            'post',
            View_Base::ENTER,
            Controller_AdminScoring_Home::TEAM_SCORING);
    }

    /**
     * @brief Print the form to enter scores for day.  Form includes the following
     *        - List of Divisions
     *        - Day to enter/update scores
     *
     * @param int   $sessionId          - Session Identifier
     * @param array $divisionsSelector  - List of divisionId => name
     * @param array $gameDateSelector   - List of gameDateId => day
     */
    private function _printEnterScoreForDay($sessionId, $divisionsSelector, $gameDateSelector)
    {
        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <th nowrap align='left' colspan='2'>Enter/Update Scores</th>
                </tr>
            <form method='post' action='" . self::SCORING_HOME_PAGE . $this->m_urlParams . "'>";

        $this->displaySelector('Division:', View_Base::DIVISION_NAME, '', $divisionsSelector, $this->m_controller->m_divisionName);
        $this->displaySelector('Game Date:', View_Base::GAME_DATE, '', $gameDateSelector, $this->m_controller->m_gameDate->day);

        // Print Update button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::ENTER . "'>
                        <input type='hidden' id='" . View_Base::SCORING_TYPE . "' name='" . View_Base::SCORING_TYPE . "' value='" . Controller_AdminScoring_Home::DIVISION_SCORING . "'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
    }

    /**
     * @brief Print the form to enter volunteer points for teams by division.  Form includes the following
     *        - List of Divisions
     *
     * @param int   $sessionId          - Session Identifier
     * @param array $divisionsSelector  - List of divisionId => name
     */
    private function _printEnterVolunteerPoints($sessionId, $divisionsSelector)
    {
        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <th nowrap align='left' colspan='2'>Enter/Update Volunteer Points</th>
                </tr>
            <form method='post' action='" . self::SCORING_HOME_PAGE . $this->m_urlParams . "'>";

        $this->displaySelector('Division:', View_Base::DIVISION_NAME, '', $divisionsSelector, $this->m_controller->m_divisionName);

        // Print Enter button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::ENTER . "'>
                        <input type='hidden' id='" . View_Base::SCORING_TYPE . "' name='" . View_Base::SCORING_TYPE . "' value='" . Controller_AdminScoring_Home::VOLUNTEER_POINTS . "'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
    }

    /**
     * @brief Print the form to select the facility and date to display games for score keeping.
     *        - List of Facilities
     *        - Day to enter/update scores
     *
     * @param int   $sessionId          - Session Identifier
     * @param array $gameDateSelector   - List of gameDateId => day
     */
    private function printSelectFacilityAndDay($sessionId, $gameDateSelector)
    {
        $facilitySelector       = $this->getFacilitySelector();
        $selectedFacilityName   = isset($this->m_controller->m_facility) ? $this->m_controller->m_facility->name : '';

        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <th nowrap align='left' colspan='2'>View Games For Scoring</th>
                </tr>
            <form method='post' action='" . self::SCORING_HOME_PAGE . $this->m_urlParams . "'>";

        $this->displaySelector('Facility:', View_Base::FILTER_FACILITY_ID, '', $facilitySelector, $selectedFacilityName);
        $this->displaySelector('Game Date:', View_Base::GAME_DATE, '', $gameDateSelector, $this->m_controller->m_gameDate->day);

        // Print Update button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::ENTER . "'>
                        <input type='hidden' id='" . View_Base::SCORING_TYPE . "' name='" . View_Base::SCORING_TYPE . "' value='" . Controller_AdminScoring_Home::GAME_DISPLAY_FOR_SCORING . "'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
    }

    /**
     * @param int       $sessionId
     * @param Game      $game
     * @param string    $scoringType
     * @param Coach     $coachFilter (defaults to null)
     * @param Division  $divisionFilter (defaults to null)
     * @param GameDate  $gameDateFilter (defaults to null)
     */
    private function printUpdateGameForm($sessionId, $game, $scoringType, $coachFilter = null, $divisionFilter = null, $gameDateFilter = null)
    {
        // Print message and return if game does not have a team
        if (!isset($game->homeTeam) or $game->title != '') {
            $this->printUpdateTitleGameForm($sessionId, $game, $scoringType, $coachFilter, $divisionFilter, $gameDateFilter);
            return;
        }

        $bgcolor        = isset($game->homeTeamScore) ? 'lightyellow' : 'lightblue';
        $addClearButton = isset($game->homeTeamScore);
        $command        = isset($game->homeTeamScore) ? View_Base::UPDATE : View_Base::ENTER;
        $day            = $game->gameTime->gameDate->day;
        $time           = $game->gameTime->startTime;
        $fieldName      = $game->gameTime->field->fullName;
        $flightName     = $game->flight->name;
        $poolName       = isset($game->pool) ? $game->pool->name : $game->title;
        $division       = $game->flight->schedule->division;
        $divisionName   = $division->nameWithGender;

        print "
            <table bgcolor='$bgcolor' valign='top' align='center' border='1' cellpadding='5' cellspacing='0'>
            <tr><td>
            <table bgcolor='$bgcolor' valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <td colspan='7' style='color: " . View_Base::AQUA . "'><strong>$divisionName $flightName $poolName</strong></td>
                </tr>
                <tr>
                    <td style='color: " . View_Base::AQUA . "'><strong>Game Id:</strong></td>
                    <td colspan='1'>$game->id</td>
                    <td style='color: " . View_Base::AQUA . "'><strong>Date:</strong></td>
                    <td colspan='1'>$day</td>
                    <td style='color: " . View_Base::AQUA . "'><strong>Time:</strong></td>
                    <td colspan='1'>$time</td>
                    <td colspan='1'>$fieldName</td>
                </tr>
            <form method='post' action='" . self::SCORING_HOME_PAGE . $this->m_urlParams . "'>";

        $coach = Coach::lookupByTeam($game->homeTeam);
        $name = $game->homeTeam->nameId . ' ' . $coach->shortName . ' ' . $game->homeTeam->region . ' (' . $game->homeTeam->city . ')';
        print "
                <tr>
                    <td style='color: " . View_Base::AQUA . "'><strong>Home Team:</strong></td>
                    <td colspan='6'>$name</td>
                </tr>
                <tr>
                    <td>&nbsp</td>";

        $numberWidth = 25;
        $this->displayInput('Goals:', 'int', View_Base::HOME_SCORE, '', '', $game->homeTeamScore, null, 1, false, $numberWidth, false, true);
        $this->displayInput('Yellow Cards:', 'int', View_Base::HOME_YELLOW_CARDS, '', '', $game->homeTeamYellowCards, null, 1, false, $numberWidth, false, true);
        $this->displayInput('Red Cards:', 'int', View_Base::HOME_RED_CARDS, '', '', $game->homeTeamRedCards, null, 1, false, $numberWidth, false, true);
        print "
                </tr>";

        $coach = Coach::lookupByTeam($game->visitingTeam);
        $name = $game->visitingTeam->nameId . ' ' . $coach->shortName . ' ' . $game->visitingTeam->region . ' (' . $game->visitingTeam->city . ')';
        print "
                <tr>
                    <td style='color: " . View_Base::AQUA . "'><strong>Visiting Team:</strong></td>
                    <td colspan='6'>$name</td>
                </tr>
                <tr>
                    <td>&nbsp</td>";

        $this->displayInput('Goals:', 'int', View_Base::VISITING_SCORE, '', '', $game->visitingTeamScore, null, 1, false, $numberWidth, false, true);
        $this->displayInput('Yellow Cards:', 'int', View_Base::VISITING_YELLOW_CARDS, '', '', $game->visitingTeamYellowCards, null, 1, false, $numberWidth, false, true);
        $this->displayInput('Red Cards:', 'int', View_Base::VISITING_RED_CARDS, '', '', $game->visitingTeamRedCards, null, 1, false, $numberWidth, false, true);
        print "
                </tr>";
        $this->displayInput('Game Notes:', 'text', View_Base::GAME_NOTES, '', '', $game->notes, null, 6, true, 340, false);

        // Print Update button and end form
        $coachInput = isset($coachFilter) ?
            "<input type='hidden' id='" . View_Base::FILTER_COACH_ID . "' name='" . View_Base::FILTER_COACH_ID . "' value='$coachFilter->id'>"
            : '';
        $divisionInput = isset($divisionFilter) ?
            "<input type='hidden' id='" . View_Base::FILTER_DIVISION_ID . "' name='" . View_Base::FILTER_DIVISION_ID . "' value='$divisionFilter->id'>"
            : '';
        $gameDateInput = isset($gameDateFilter) ?
            "<input type='hidden' id='" . View_Base::GAME_DATE . "' name='" . View_Base::GAME_DATE . "' value='$gameDateFilter->id'>"
            : '';

        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . $command . "'>
                        <input type='hidden' id='" . View_Base::GAME_ID . "' name='" . View_Base::GAME_ID . "' value='$game->id'>
                        <input type='hidden' id='" . View_Base::SCORING_TYPE . "' name='" . View_Base::SCORING_TYPE . "' value='$scoringType'>
                        $coachInput
                        $divisionInput
                        $gameDateInput
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>";

        if ($addClearButton) {
            print "
                    <td align='left'>
                        <input style='background-color: salmon' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::CLEAR . "'>
                        <input type='hidden' id='" . View_Base::GAME_ID . "' name='" . View_Base::GAME_ID . "' value='$game->id'>
                        <input type='hidden' id='" . View_Base::SCORING_TYPE . "' name='" . View_Base::SCORING_TYPE . "' value='$scoringType'>
                        $coachInput
                        $divisionInput
                        $gameDateInput
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>";
        }

        print "
                </tr>
            </form>
            </table>
            </td></tr>
            </table>";
    }

    /**
     * @param int       $sessionId
     * @param Game      $game
     * @param string    $scoringType
     * @param Coach     $coachFilter (defaults to null)
     * @param Division  $divisionFilter (defaults to null)
     * @param GameDate  $gameDateFilter (defaults to null)
     */
    private function printUpdateTitleGameForm($sessionId, $game, $scoringType, $coachFilter = null, $divisionFilter = null, $gameDateFilter = null)
    {
        Precondition::isTrue(!isset($game->homeTeam) or $game->title != '',
            "printUpdateTitleGameForm should only be called for title games");

        $bgcolor        = isset($game->homeTeamScore) ? 'orange' : 'orangered';
        $addClearButton = isset($game->homeTeamScore);
        $command        = isset($game->homeTeamScore) ? View_Base::UPDATE : View_Base::ENTER;
        $day            = $game->gameTime->gameDate->day;
        $time           = $game->gameTime->startTime;
        $fieldName      = $game->gameTime->field->fullName;
        $flightName     = $game->flight->name;
        $poolName       = isset($game->pool) ? $game->pool->name : $game->title;
        $division       = $game->flight->schedule->division;
        $divisionName   = $division->nameWithGender;

        // Get teams in game's flight
        $pools          = Pool::lookupByFlight($game->flight);
        $teamSelector   = [];
        foreach ($pools as $pool) {
            $teams = Team::lookupByPool($pool);
            foreach ($teams as $team) {
                $coach  = Coach::lookupByTeam($team);
                $name   = $team->nameId . ' ' . $coach->lastName . ' ' . $team->region . ' (' . $team->city . ')';

                $teamSelector[$team->id] = $name;
            }
        }

        print "
            <table bgcolor='$bgcolor' valign='top' align='center' border='1' cellpadding='5' cellspacing='0'>
            <tr><td>
            <table bgcolor='$bgcolor' valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <td colspan='7' style='color: " . View_Base::AQUA . "'><strong>$divisionName $flightName $poolName</strong></td>
                </tr>
                <tr>
                    <td style='color: " . View_Base::AQUA . "'><strong>Game Id:</strong></td>
                    <td colspan='1'>$game->id</td>
                    <td style='color: " . View_Base::AQUA . "'><strong>Date:</strong></td>
                    <td colspan='1'>$day</td>
                    <td style='color: " . View_Base::AQUA . "'><strong>Time:</strong></td>
                    <td colspan='1'>$time</td>
                    <td colspan='1'>$fieldName</td>
                </tr>
            <form method='post' action='" . self::SCORING_HOME_PAGE . $this->m_urlParams . "'>";

        $defaultTeamSelection = '';
        if (isset($game->homeTeam)) {
            $coach                  = Coach::lookupByTeam($game->homeTeam);
            $defaultTeamSelection   = $game->homeTeam->nameId . ' ' . $coach->lastName . ' ' . $game->homeTeam->region . ' (' . $game->homeTeam->city . ')';
        }

        print "
                <tr>";

        $this->displaySelector('Home Team:',
            View_Base::HOME_TEAM_ID,
            '',
            $teamSelector,
            $defaultTeamSelection,
            null,
            false,
            140,
            'left',
            'Select a Team',
            '',
            6);

        print "
                </tr>";

        if (isset($game->homeTeam)) {
            print "
                <tr>
                    <td>&nbsp</td>";

            $numberWidth = 25;
            $this->displayInput('Goals:', 'int', View_Base::HOME_SCORE, '', '', $game->homeTeamScore, null, 1, false, $numberWidth, false, true);
            $this->displayInput('Yellow Cards:', 'int', View_Base::HOME_YELLOW_CARDS, '', '', $game->homeTeamYellowCards, null, 1, false, $numberWidth, false, true);
            $this->displayInput('Red Cards:', 'int', View_Base::HOME_RED_CARDS, '', '', $game->homeTeamRedCards, null, 1, false, $numberWidth, false, true);

            print "
                </tr>";
        }

        $defaultTeamSelection = '';
        if (isset($game->visitingTeam)) {
            $coach                  = Coach::lookupByTeam($game->visitingTeam);
            $defaultTeamSelection   = $game->visitingTeam->nameId . ' ' . $coach->lastName . ' ' . $game->visitingTeam->region . ' (' . $game->visitingTeam->city . ')';
        }

        print "
                <tr>";

        $this->displaySelector('Visiting Team:',
            View_Base::VISITING_TEAM_ID,
            '',
            $teamSelector,
            $defaultTeamSelection,
            null,
            false,
            140,
            'left',
            'Select a Team',
            '',
            6);

        print "
                </tr>";

        if (isset($game->homeTeam)) {
            print "
                <tr>
                    <td>&nbsp</td>";

            $numberWidth = 25;
            $this->displayInput('Goals:', 'int', View_Base::VISITING_SCORE, '', '', $game->visitingTeamScore, null, 1, false, $numberWidth, false, true);
            $this->displayInput('Yellow Cards:', 'int', View_Base::VISITING_YELLOW_CARDS, '', '', $game->visitingTeamYellowCards, null, 1, false, $numberWidth, false, true);
            $this->displayInput('Red Cards:', 'int', View_Base::VISITING_RED_CARDS, '', '', $game->visitingTeamRedCards, null, 1, false, $numberWidth, false, true);
            print "
                    </tr>";
            $this->displayInput('Game Notes:', 'text', View_Base::GAME_NOTES, '', '', $game->notes, null, 6, true, 340, false);
        }

        // Print Update button and end form
        $coachInput = isset($coachFilter) ?
            "<input type='hidden' id='" . View_Base::FILTER_COACH_ID . "' name='" . View_Base::FILTER_COACH_ID . "' value='$coachFilter->id'>"
            : '';
        $divisionInput = isset($divisionFilter) ?
            "<input type='hidden' id='" . View_Base::FILTER_DIVISION_ID . "' name='" . View_Base::FILTER_DIVISION_ID . "' value='$divisionFilter->id'>"
            : '';
        $gameDateInput = isset($gameDateFilter) ?
            "<input type='hidden' id='" . View_Base::GAME_DATE . "' name='" . View_Base::GAME_DATE . "' value='$gameDateFilter->id'>"
            : '';

        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . $command . "'>
                        <input type='hidden' id='" . View_Base::GAME_ID . "' name='" . View_Base::GAME_ID . "' value='$game->id'>
                        <input type='hidden' id='" . View_Base::SCORING_TYPE . "' name='" . View_Base::SCORING_TYPE . "' value='$scoringType'>
                        $coachInput
                        $divisionInput
                        $gameDateInput
                        <input type='hidden' id='isTitleGame' name='isTitleGame' value='yes'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>";

        if ($addClearButton) {
            print "
                    <td align='left'>
                        <input style='background-color: salmon' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::CLEAR . "'>
                        <input type='hidden' id='" . View_Base::GAME_ID . "' name='" . View_Base::GAME_ID . "' value='$game->id'>
                        <input type='hidden' id='" . View_Base::SCORING_TYPE . "' name='" . View_Base::SCORING_TYPE . "' value='$scoringType'>
                        $coachInput
                        $divisionInput
                        $gameDateInput
                        <input type='hidden' id='isTitleGame' name='isTitleGame' value='yes'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>";
        }

        print "
                </tr>
            </form>
            </table>
            </td></tr>
            </table>";
    }

    /**
     * @param int   $sessionId
     * @param Coach $coach
     */
    private function printUpdateTeamGamesForm($sessionId, $coach)
    {
        // Get games for team
        $games = Game::lookupByTeam($coach->team);

        // For each game, create form for score input/update
        foreach ($games as $game) {
            $this->printUpdateGameForm($sessionId, $game, Controller_AdminScoring_Home::TEAM_SCORING, $coach);
        }
    }

    /**
     * @param int       $sessionId
     * @param Division  $division
     * @param GameDate  $gameDate
     */
    private function printUpdateDivisionGamesForm($sessionId, $division, $gameDate)
    {
        if (!isset($division)) {
            return;
        }

        $games = Game::lookupByDivisionDay($division, $gameDate->day, true);
        foreach ($games as $game) {
            $this->printUpdateGameForm($sessionId, $game, Controller_AdminScoring_Home::DIVISION_SCORING, null, $division, $gameDate);
        }
    }

    /**
     * @param int       $sessionId
     * @param Division  $division
     */
    private function printUpdateDivisionVolunteerPointsForm($sessionId, $division)
    {
        if (!isset($division)) {
            return;
        }

        $divisionName   = $division->nameWithGender;
        $teams          = Team::lookupByDivision($division);
        $scoringType    = Controller_AdminScoring_Home::VOLUNTEER_POINTS;
        $bgColor        = 'lightskyblue';

        print "
            <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0'>
                <tr bgcolor='$bgColor'>
                    <th colspan='3'>$divisionName</th>
                </tr>
                <tr bgcolor='$bgColor'>
                    <th>Team Id</th>
                    <th>Coach</th>
                    <th>Volunteer Points</th>
                </tr>
                <form method='post' action='" . self::SCORING_HOME_PAGE . $this->m_urlParams . "'>";

        foreach ($teams as $team) {
            $coach = Coach::lookupByTeam($team);
            print "
                <tr>
                    <td>$team->nameId</td>
                    <td>$coach->shortName</td>";

            $name = View_Base::VOLUNTEER_POINTS_DATA . "[$team->id]";
            $this->displayInput('', 'int', $name, '', '', $team->volunteerPoints, null, 1, false, 50, false, true, 'center');

            print "
                </tr>";
        }

        print "
                <tr>
                    <td colspan='3' align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::UPDATE . "'>
                        <input type='hidden' id='" . View_Base::SCORING_TYPE . "' name='" . View_Base::SCORING_TYPE . "' value='$scoringType'>
                        <input type='hidden' id='" . View_Base::DIVISION_NAME . "' name='" . View_Base::DIVISION_NAME . "' value='$divisionName'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
    }

    /**
     * @param GameDate  $gameDate
     */
    private function printGamesForForDay($gameDate)
    {
        $facilities = Facility::lookupBySeason($this->m_controller->m_season);
        foreach ($facilities as $facility) {
            $this->printGamesForFacilityForDay($facility, $gameDate, true);
        }
    }

    /**
     * @param Facility  $facility
     * @param GameDate  $gameDate
     * @param bool      $suppressNoGamesMessage
     */
    private function printGamesForFacilityForDay($facility, $gameDate, $suppressNoGamesMessage = false)
    {
        $fields     = Field::lookupByFacility($facility);
        $gameTimes  = GameTime::lookupByGameDateAndFields($gameDate, $fields);
        $gameData   = [];

        foreach ($gameTimes as $gameTime) {
            if (isset($gameTime->game)) {
                $field              = $gameTime->field;
                $game               = $gameTime->game;
                $homeTeam           = $game->homeTeam;
                $visitingTeam       = $game->visitingTeam;
                $division           = $homeTeam->division;
                $homeCoach          = Coach::lookupByTeam($homeTeam);
                $visitingCoach      = Coach::lookupByTeam($visitingTeam);
                $teams['home']      = 'H: ' . $homeTeam->nameId . " " . $homeCoach->name;
                $teams['visiting']  = 'V: ' . $visitingTeam->nameId . " " . $visitingCoach->name;

                if ($division->isScoringTracked) {
                    $gameData[$gameTime->actualStartTime][$division->nameWithGender][$field->name][$game->id] = $teams;
                }
            }
        }
        ksort($gameData);

        if (count($gameData) == 0) {
            if (!$suppressNoGamesMessage) {
                print "
                    <p align='center' style='color: red; font-size: medium'>No games being played at $facility->name on $gameDate->day.</p>";
            }
            return;
        }

        $this->printGamesForFacilityForDayHeader($facility, $gameDate);

        $timeCount      = 0;
        $gameCount      = 0;
        $startNewTable  = false;
        foreach ($gameData as $actualStartTime => $divisionData) {
            $startNewTable      = $this->startNewTableIfNecessary($startNewTable, $facility, $gameDate);
            $timePrinted        = false;
            $backgroundColor    = $timeCount % 2 == 0 ? 'white' : 'lightskyblue';
            $fontColor          = $timeCount % 2 == 0 ? 'black' : 'black';
            $timeCount          += 1;
            $timeRowSpan        = 0;

            foreach ($divisionData as $divisionName => $fieldData) {
                $timeRowSpan += count($fieldData) * 2;
            }

            foreach ($divisionData as $divisionName => $fieldData) {
                $startNewTable      = $this->startNewTableIfNecessary($startNewTable, $facility, $gameDate);
                $divisionPrinted    = false;
                $divisionRowSpan    = count($fieldData) * 2;

                foreach ($fieldData as $fieldName => $gameData) {
                    foreach ($gameData as $gameId => $teams) {
                        $startNewTable      = $this->startNewTableIfNecessary($startNewTable, $facility, $gameDate);
                        $homeTeamData       = $teams['home'];
                        $visitingTeamData   = $teams['visiting'];
                        $style              = "style='background-color: $backgroundColor; color: $fontColor; -webkit-print-color-adjust: exact; height: .5in'";

                        $gameBackgroundColor    = $gameCount % 2 == 0 ? 'lightyellow' : 'white';
                        $gameFontColor          = $gameCount % 2 == 0 ? 'black' : 'black';
                        $gameStyle              = "style='background-color: $gameBackgroundColor; color: $gameFontColor; -webkit-print-color-adjust: exact; height: .3in'";
                        $gameCount              += 1;

                        // Print home team
                        if (!$timePrinted) {
                            $timePrinted        = true;
                            $divisionPrinted    = true;

                            print "
                                <tr>
                                    <td rowspan='$timeRowSpan' $style>$actualStartTime</td>
                                    <td rowspan='$divisionRowSpan' $style>$divisionName</td>";
                        } else if (!$divisionPrinted) {
                            $divisionPrinted = true;

                            print "
                                <tr>
                                    <td rowspan='$divisionRowSpan' $style>$divisionName</td>";
                        } else {
                            print "
                                <tr>";
                        }

                        print "
                                    <td rowspan='2' $gameStyle>$fieldName</td>
                                    <td rowspan='2' align='center' $gameStyle>$gameId</td>
                                    <td $gameStyle>$homeTeamData</td>
                                    <td $gameStyle>&nbsp</td>
                                    <td $gameStyle>&nbsp</td>
                                    <td $gameStyle>&nbsp</td>
                                    <td $gameStyle width='400px'>&nbsp</td>
                                </tr>";

                        // Print visiting team
                        print "
                                <tr>
                                    <td $gameStyle>$visitingTeamData</td>
                                    <td $gameStyle>&nbsp</td>
                                    <td $gameStyle>&nbsp</td>
                                    <td $gameStyle>&nbsp</td>
                                    <td $gameStyle width='400px'>&nbsp</td>
                                </tr>";

                        // Adjust rowspans for page break accuracy
                        $timeRowSpan        -= 2;
                        $divisionRowSpan    -= 2;

                        // Only print 12 games per page
                        if ($gameCount % 12 == 0) {
                            print "
                                </table>";

                            $startNewTable      = true;
                            $timePrinted        = false;
                            $divisionPrinted    = false;
                        }
                    }
                }
            }
        }

        if (!$startNewTable) {
            print "</table><br>";
        }
    }

    /**
     * @param bool      $startNewTable
     * @param Facility  $facility
     * @param GameDate  $gameDate
     *
     * @return bool
     */
    private function startNewTableIfNecessary($startNewTable, $facility, $gameDate)
    {
        if ($startNewTable) {
            $this->printGamesForFacilityForDayHeader($facility, $gameDate, false);
        }
        return false;
    }


    /**
     * @param Facility  $facility
     * @param GameDate  $gameDate
     * @param bool      $beginningLook
     */
    private function printGamesForFacilityForDayHeader($facility, $gameDate, $beginningLook = true)
    {
        $beginningStyle = $beginningLook ? "; height: .5in; font-size: 24px" : "";

        print "
            <p style='page-break-before: always;'>&nbsp;</p>
            <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0'>
                <thead>
                    <tr style='background-color: lightskyblue; color: black; -webkit-print-color-adjust: exact${beginningStyle}'>
                        <th colspan='9'>$facility->name ($gameDate->day)</th>
                    <tr style='background-color: lightskyblue; color: black; -webkit-print-color-adjust: exact; height: .5in'>
                        <th>Start</th>
                        <th>Division</th>
                        <th>Field</th>
                        <th>GameId</th>
                        <th>Teams</th>
                        <th>Goals</th>
                        <th>Yellow</th>
                        <th>Red</th>
                        <th>Notes</th>
                    </tr>
                </thead>";
    }
}