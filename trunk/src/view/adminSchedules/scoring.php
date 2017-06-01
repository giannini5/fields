<?php

use \DAG\Domain\Schedule\Game;
use \DAG\Domain\Schedule\Coach;
use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\GameDate;
use \DAG\Framework\Exception\Precondition;
use \DAG\Domain\Schedule\Pool;
use \DAG\Domain\Schedule\Team;

/**
 * @brief Show the Schedule page and get the user to select a schedule to administer or create a new schedule.
 */
class View_AdminSchedules_Scoring extends View_AdminSchedules_Base
{
    /**
     * @brief Construct the View
     *
     * @param $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller)
    {
        parent::__construct(self::SCHEDULE_SCORING_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
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
                </tr>
            </table>
            <br><br>";

        if ($this->m_controller->m_scoringType == Controller_AdminSchedules_Scoring::GAME_SCORING) {
            if (Game::findById($this->m_controller->m_gameId, $game)) {
                $this->printUpdateGameForm($sessionId, $game, Controller_AdminSchedules_Scoring::GAME_SCORING);
            }
        } else if ($this->m_controller->m_scoringType == Controller_AdminSchedules_Scoring::TEAM_SCORING) {
            $coach = Coach::lookupById($this->m_controller->m_coachId);
            $this->printUpdateTeamGamesForm($sessionId, $coach);
        } else if ($this->m_controller->m_scoringType == Controller_AdminSchedules_Scoring::DIVISION_SCORING) {
            $this->printUpdateDivisionGamesForm($sessionId, $this->m_controller->m_division, $this->m_controller->m_gameDate);
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
                <form method='post' action='" . self::SCHEDULE_SCORING_PAGE . $this->m_urlParams . "'>";

        $this->displayInput('Game Id:', 'int', View_Base::GAME_ID, '', '', "", null, 6, true, 50, false, true);

        print "
                <tr>
                    <td colspan='7' style='color: " . View_Base::AQUA . "'><strong>Home Team:</strong></td>
                </tr>
                <tr>
                    <td>&nbsp</td>";

        $numberWidth = 25;
        $this->displayInput('Score:', 'int', View_Base::HOME_SCORE, '', '', '', null, 1, false, $numberWidth, false, true);
        $this->displayInput('Yellow Cards:', 'int', View_Base::HOME_YELLOW_CARDS, '', '', 0, null, 1, false, $numberWidth, false, true);
        $this->displayInput('Red Cards:', 'int', View_Base::HOME_RED_CARDS, '', '', 0, null, 1, false, $numberWidth, false, true);
        print "
                </tr>";

        print "
                <tr>
                    <td colspan='7' style='color: " . View_Base::AQUA . "'><strong>Visiting Team:</strong></td>
                </tr>
                <tr>
                    <td>&nbsp</td>";

        $this->displayInput('Score:', 'int', View_Base::VISITING_SCORE, '', '', '', null, 1, false, $numberWidth, false, true);
        $this->displayInput('Yellow Cards:', 'int', View_Base::VISITING_YELLOW_CARDS, '', '', 0, null, 1, false, $numberWidth, false, true);
        $this->displayInput('Red Cards:', 'int', View_Base::VISITING_RED_CARDS, '', '', 0, null, 1, false, $numberWidth, false, true);
        print "
                </tr>";
        $this->displayInput('Game Notes:', 'text', View_Base::GAME_NOTES, '', '', '', null, 6, true, 340, false);

        // Print Enter button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::ENTER . "'>
                        <input type='hidden' id='" . View_Base::SCORING_TYPE . "' name='" . View_Base::SCORING_TYPE . "' value='" . Controller_AdminSchedules_Scoring::GAME_SCORING . "'>
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
            self::SCHEDULE_SCORING_PAGE,
            $sessionId,
            '0',
            'post',
            View_Base::ENTER,
            Controller_AdminSchedules_Scoring::TEAM_SCORING);
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
            <form method='post' action='" . self::SCHEDULE_SCORING_PAGE . $this->m_urlParams . "'>";

        $this->displaySelector('Division:', View_Base::DIVISION_NAME, '', $divisionsSelector, $this->m_controller->m_divisionName);
        $this->displaySelector('Game Date:', View_Base::GAME_DATE, '', $gameDateSelector, $this->m_controller->m_gameDate->day);

        // Print Update button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::ENTER . "'>
                        <input type='hidden' id='" . View_Base::SCORING_TYPE . "' name='" . View_Base::SCORING_TYPE . "' value='" . Controller_AdminSchedules_Scoring::DIVISION_SCORING . "'>
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
            <form method='post' action='" . self::SCHEDULE_SCORING_PAGE . $this->m_urlParams . "'>";

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
        $this->displayInput('Score:', 'int', View_Base::HOME_SCORE, '', '', $game->homeTeamScore, null, 1, false, $numberWidth, false, true);
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

        $this->displayInput('Score:', 'int', View_Base::VISITING_SCORE, '', '', $game->visitingTeamScore, null, 1, false, $numberWidth, false, true);
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
            <form method='post' action='" . self::SCHEDULE_SCORING_PAGE . $this->m_urlParams . "'>";

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
            $this->displayInput('Score:', 'int', View_Base::HOME_SCORE, '', '', $game->homeTeamScore, null, 1, false, $numberWidth, false, true);
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

            $this->displayInput('Score:', 'int', View_Base::VISITING_SCORE, '', '', $game->visitingTeamScore, null, 1, false, $numberWidth, false, true);
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
            $this->printUpdateGameForm($sessionId, $game, Controller_AdminSchedules_Scoring::TEAM_SCORING, $coach);
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
            $this->printUpdateGameForm($sessionId, $game, Controller_AdminSchedules_Scoring::DIVISION_SCORING, null, $division, $gameDate);
        }
    }
}