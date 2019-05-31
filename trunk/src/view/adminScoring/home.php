<?php

use \DAG\Domain\Schedule\Game;
use \DAG\Domain\Schedule\Coach;
use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\GameDate;
use \DAG\Domain\Schedule\Pool;
use \DAG\Domain\Schedule\Team;
use \DAG\Domain\Schedule\AssistantCoach;
use \DAG\Domain\Schedule\Player;
use \DAG\Domain\Schedule\PlayerGameStats;
use \DAG\Orm\Schedule\PlayerOrm;
use \DAG\Framework\Exception\Assertion;
use \DAG\Orm\Schedule\ScheduleOrm;

/**
 * @brief Show the Schedule page and get the user to select a schedule to administer or create a new schedule.
 */
class View_AdminScoring_Home extends View_AdminScoring_Base
{
    private $uniqueId;

    /**
     * @brief Construct the View
     *
     * @param Controller_AdminScoring_Home $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller)
    {
        parent::__construct(self::SCORING_ENTER_SCORES_PAGE, $controller);
        $this->uniqueId = 0;
    }

    /**
     * @brief Render data for display on the page.
     */
    public function renderPage()
    {
        $sessionId          = $this->m_controller->getSessionId();
        $divisionsSelector  = $this->getDivisionsSelector(true, false, true, true);
        $gameDateSelector   = $this->getGameDateSelector();

        $messageString = $this->m_controller->m_messageString;
        if (!empty($messageString)) {
            print "
                <p style='color: green' align='center'><strong>$messageString</strong></p><br>";
        }

        print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td valign='top' bgcolor='" . View_Base::VIEW_COLOR . "'>";

        $this->_printEnterScoreForGame($sessionId);

        print "
                    </td>
                    <td valign='top' bgcolor='" . View_Base::VIEW_COLOR . "'>";

        $this->_printEnterScoreForDay($sessionId, $divisionsSelector, $gameDateSelector);

        print "
                    </td>
                </tr>
            </table>
            <br><br>";

        switch ($this->m_controller->m_scoringType) {
            case Controller_AdminScoring_Home::GAME_SCORING:
            case Controller_AdminScoring_Home::UPDATE_GAME_SCORING:
                if (Game::findById($this->m_controller->m_gameId, $game)) {
                    $this->printUpdateGameForm($sessionId, $game, $this->m_controller->m_scoringType);
                }
                break;
            case Controller_AdminScoring_Home::DIVISION_SCORING:
                $this->printUpdateDivisionGamesForm($sessionId, $this->m_controller->m_division, $this->m_controller->m_gameDate);
                break;
        }

        print "
            <script language=\"javascript\">
                function setRadioTrue(obj)
                {
                    obj.checked = true;
                }
                function setRadioFalse(obj)
                {
                    obj.checked = false;
                }
            </script>";
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
        $value = $this->m_controller->m_gameId;

        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <th nowrap align='left' colspan='7'>Enter Score for Game</th>
                </tr>
                <form method='post' action='" . self::SCORING_ENTER_SCORES_PAGE . $this->m_urlParams . "'>";

        $this->displayInput('Game Id:', 'number', View_Base::GAME_ID, '', '', $value, null, 6, true, 50, false, true);
        $this->printCheckboxSelector(View_Base::QUICK_SCORING, "Quick Scoring", $this->m_controller->m_quickScoring, 2);

        // Print Enter button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::ENTER . "'>
                        <input type='hidden' id='" . View_Base::SCORING_TYPE . "' name='" . View_Base::SCORING_TYPE . "' value='" . Controller_AdminScoring_Home::GAME_SCORING_LOOKUP . "'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
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
            <form method='post' action='" . self::SCORING_ENTER_SCORES_PAGE . $this->m_urlParams . "'>";

        $this->displaySelector('Division:', View_Base::DIVISION_NAME, '', $divisionsSelector, $this->m_controller->m_divisionName);
        $this->displaySelector('Game Date:', View_Base::GAME_DATE, '', $gameDateSelector, $this->m_controller->m_gameDate->day);
        $this->printCheckboxSelector(View_Base::QUICK_SCORING, "Quick Scoring", $this->m_controller->m_quickScoring, 2);

        // Print Update button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::ENTER . "'>
                        <input type='hidden' id='" . View_Base::SCORING_TYPE . "' name='" . View_Base::SCORING_TYPE . "' value='" . Controller_AdminScoring_Home::DIVISION_SCORING_LOOKUP . "'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>";
    }

    /**
     * @param int $sessionId
     * @param Game $game
     * @param string $scoringType
     * @param Coach $coachFilter (defaults to null)
     * @param Division $divisionFilter (defaults to null)
     * @param GameDate $gameDateFilter (defaults to null)
     * @throws AssertionException
     */
    private function printUpdateGameForm($sessionId, $game, $scoringType, $coachFilter = null, $divisionFilter = null, $gameDateFilter = null)
    {
        // Print message and return if game does not have a team

        // Force quick scoring -
        // if ($this->m_controller->m_quickScoring or !isset($game->homeTeam) or $game->title != '') {
        if (!isset($game->homeTeam) or $game->title != '' or $game->flight->schedule->scheduleType == ScheduleOrm::SCHEDULE_TYPE_TOURNAMENT) {
            $this->printQuickUpdateGameForm($sessionId, $game, $scoringType, $coachFilter, $divisionFilter, $gameDateFilter);
            return;
        }

        $bgcolor        = isset($game->homeTeamScore) ? 'lightyellow' : 'lightblue';
        $addClearButton = isset($game->homeTeamScore);
        $command        = isset($game->homeTeamScore) ? View_Base::UPDATE : View_Base::ENTER;
        $day            = $game->gameTime->gameDate->day;
        $time           = $game->gameTime->actualStartTime;
        $fieldName      = $game->gameTime->field->fullName;
        $division       = $game->flight->schedule->division;
        $divisionName   = $division->nameWithGender;
        $homeTeamId     = $game->homeTeam->nameId;
        $visitingTeamId = $game->visitingTeam->nameId;
        $useAccordian   = $scoringType != Controller_AdminScoring_Home::GAME_SCORING;
        $scoringType    = $scoringType == Controller_AdminScoring_Home::GAME_SCORING ?
            Controller_AdminScoring_Home::UPDATE_GAME_SCORING : $scoringType;
        $result         = !isset($game->homeTeamScore) ? "" : "$game->homeTeamScore - $game->visitingTeamScore";

        if ($useAccordian) {
            print "
            <div class='accordion' style='width: 1100px; background-color: $bgcolor'>";
        } else {
            print "
            <div class='noaccordion' style='width: 1100px; background-color: $bgcolor'>";
        }
        print "
                <h2 style='text-align: left'>
                    <table border='0'>
                        <tr>
                            <td colspan='2'><strong style='text-decoration: underline;'>$divisionName</strong></td>
                        </tr>
                        <tr>
                            <td><strong>Game Id:</strong></td>
                            <td>$game->id</td>
                        </tr>
                        <tr>
                            <td><strong>Date:</strong></td>
                            <td>$day $time</td>
                        </tr>
                        <tr>
                            <td><strong>Teams:</strong></td>
                            <td>$homeTeamId vs $visitingTeamId</td>
                        </tr>
                        <tr>
                            <td><strong>Field:</strong></td>
                            <td>$fieldName</td>
                        </tr>
                        <tr>
                            <td><strong>Result:</strong></td>
                            <td>$result</td>
                        </tr>

                    </table>
                </h2>";

        if ($useAccordian) {
            print "
                <div class='pane'>";
        }

        print "
            <form method='post' action='" . self::SCORING_ENTER_SCORES_PAGE . $this->m_urlParams . "'>";

        print "
            <table border='0' align='center'>
                <tr>
                    <td>";

        $this->printGameCard($game, true);

        print "
                    </td><td width='50px'>&nbsp;</td><td>";
        $this->printGameCard($game, false);

        print "
                    </td>
                </tr>
            </table>";

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
            <br>
            <table align='center'>
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
                    <td width='50px'>&nbsp;</td>
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
            </table>
            </form>
        </div>";    // Pane or no accordian

        if ($useAccordian) {
            print "
            </div>"; // Accordian
        } else {
            print "<br>";
        }

        print "<br>";
    }

    /**
     * @param Game $game
     * @param bool $isHomeTeam
     * @throws AssertionException
     */
    private function printGameCard($game, $isHomeTeam)
    {
        $homeOrVisitor          = $isHomeTeam ? "HOME" : "VISITOR";
        $team                   = $isHomeTeam ? $game->homeTeam : $game->visitingTeam;
        $teamId                 = isset($team) ? $team->nameId : "";
        $teamName               = isset($team) ? $team->name : "";
        $opposingTeam           = $isHomeTeam ? $game->visitingTeam : $game->homeTeam;
        $opposingTeamId         = isset($opposingTeam) ? $opposingTeam->nameId : "";
        $opposingTeamName       = isset($opposingTeam) ? $opposingTeam->name : "";
        $coach                  = isset($team) ? Coach::lookupByTeam($team) : null;
        $coachName              = isset($coach) ? $coach->name : "";
        $assistantCoaches       = isset($team) ? AssistantCoach::lookupByTeam($team) : [];
        $assistantCoachName     = count($assistantCoaches) > 0 ? $assistantCoaches[0]->name : "";
        $day                    = $game->gameTime->gameDate->day;
        $time                   = substr($game->gameTime->actualStartTime, 0, 5);
        $fieldName              = $game->gameTime->field->fullName;
        $fullTeamName           = $teamName == $teamId ? $teamId : "$teamId: $teamName";
        $fullOpposingTeamName   = $opposingTeamName == $opposingTeamId ? $opposingTeamId : "$opposingTeamId: $opposingTeamName";
        $players                = $this->getPlayersOrderedByNumber($team);
        $teamColor              = $team->color;

        $headerElementHeight    = "20px";

        $teamNametag    = $isHomeTeam ? View_Base::HOME_TEAM_NAME : View_Base::VISITING_TEAM_NAME;
        $teamColortag   = $isHomeTeam ? View_Base::HOME_TEAM_COLOR : View_Base::VISITING_TEAM_COLOR;

        print "
                    <table border='0' style='table-layout: fixed; width: 5.0in'>
                        <tr>
                            <td align='left'><alt img src='/images/aysoLogoBlackAndWhite.png' height='30px' width='30px'></td>
                            <td align='center' nowrap><strong style='font-size: larger'>REGION 122 GAME CARD</strong></td>
                            <td align='right'><strong style='font-size: larger'>$homeOrVisitor</strong></td>
                        </tr>
                    </table>
                    <table border='0' style='table-layout: fixed; width: 5.0in'>
                        <tr style='height: $headerElementHeight'>
                            <td nowrap align='left' style='font-size: larger'>$day $time</td>
                            <td>&nbsp</td>
                            <td nowrap align='left' style='font-size: larger'>$fieldName</td>
                            <td>&nbsp</td>
                            <td nowrap align='right'>GID: <strong style='font-size: larger'>$game->id</strong></td>
                        </tr>
                    </table>
                    <table border='0' style='table-layout: fixed; width: 5.0in'>
                        <tr style='height: $headerElementHeight'>
                            <td nowrap align='left' style='overflow: hidden; font-size: larger'><strong>TEAM: </strong>$teamId</td>
                            <td align='left'><strong>NAME: </strong>
                                <input type='text' size='16' name='$teamNametag' placeholder='Name' value='$teamName'>
                            </td>
                            <td align='right'><strong>COLOR: </strong>
                                <input type='text' size='12' name='$teamColortag' placeholder='Color' value='$teamColor'>
                            </td>
                            <!-- <td nowrap align='left' style='overflow: hidden; font-size: larger'><strong>TEAM: </strong>$fullTeamName</td> -->
                            <!-- <td nowrap align='right' style='overflow: hidden; font-size: larger'><strong>OPPOSING TEAM: </strong>$fullOpposingTeamName</td> -->
                        </tr>
                    </table>
                    <table border='0' style='table-layout: fixed; width: 5.0in'>
                        <tr style='height: $headerElementHeight'>
                            <td nowrap align='left' style='overflow: hidden; font-size: larger'><strong>COACH: </strong>$coachName</td>
                            <td nowrap align='right' style='overflow: hidden; font-size: larger'><strong>ASST. COACH: </strong>$assistantCoachName</td>
                        </tr>
                    </table>
                    <br>
                    <table border='2' style='table-layout: fixed; width: 5.0in' cellpadding='5' cellspacing='0'>
                            <tr>
                                <td rowspan='2' width='5px' align='center' style='border: 1px solid'><strong>#</strong></td>
                                <td rowspan='2' width='65px' align='center' style='border: 1px solid'><strong>Player's Name</strong></td>
                                <td rowspan='2' width='15px' colspan='1' align='center' style='border: 1px solid; border-right: double'><strong>Goals</strong></td>
                                <td width='95px' colspan='4' align='center' style='border: 1px solid; border-left: double'><strong>Sub:X, Keep:G<br>Injured:I, Absent:A</strong></td>
                                <td width='20' colspan='2' align='center' style='border: 1px solid'><strong>Cards</strong></td>
                            </tr>
                            <tr>
                                <td align='center' style='border: 1px solid; font-size: 10px; border-left: double'><strong>1</strong></td>
                                <td align='center' style='border: 1px solid; font-size: 10px'><strong>2</strong></td>
                                <td align='center' style='border: 1px solid; font-size: 10px'><strong>3</strong></td>
                                <td align='center' style='border: 1px solid; font-size: 10px'><strong>4</strong></td>
                                <td width='5px' align='center' style='border: 1px solid; font-size: 10px'><strong>Y</strong></td>
                                <td width='5px' align='center' style='border: 1px solid; font-size: 10px'><strong>R</strong></td>
                            </tr>";

        $playerCount = 0;
        Assertion::isTrue(count($players) < 18, "Count of players on a team cannot exceed 18. Team has " . count($players) . " players");
        foreach ($players as $player) {
            $this->printPlayerRow($game, $team, $player);
            $playerCount += 1;
        }

        // Add a "player add" row
        $this->printPlayerRow($game, $team);

        print "
                    </table>";
    }

    /**
     * @param Game      $game
     * @param Team      $team
     * @param Player    $player
     */
    private function printPlayerRow($game, $team, $player = null) {
        $playerName         = isset($player) ? $player->name : '';
        $playerId           = isset($player) ? $player->id : '-1';
        $baseInputName      = View_Base::GAME_CARD_DATA . "[$game->id][$team->id][$playerId]";
        $playerGameStats    = isset($player) ? PlayerGameStats::findOrCreate($game, $team, $player) : null;
        $playerGoals        = isset($playerGameStats) ? $playerGameStats->goals : 0;


        print "
                        <tr style='overflow: hidden'>
                            <td width='5px' align='center' style='font-size: larger'>";

        // Player Name
        $this->printEnterPlayerNumber($baseInputName, $player);

        $inputName = $baseInputName . "[" . View_Base::PLAYER_NAME . "]";
        print "
                            </td> 
                            <td width='65px'>
                                <input type='text' size='12' name='$inputName' placeholder='Name' value='$playerName'>
                            </td>
                            <td width='15px' align='center' style='border-right: double'>";

        // Goals Scored
        $bgColor        = $playerGoals == 0 ? 'white' : 'lightgreen';
        $inputName      = $baseInputName . "[" . View_Base::PLAYER_GOALS . "]";
        $playerGoals    = $playerGoals == 0 ? '' : $playerGoals;
        print "
            <input type='number' class='no-spinners' name='$inputName' style='width: 15px; background-color: $bgColor' placeholder='' value='$playerGoals'>";

        print "             
                            </td>";

        // Substitution/Keeper by quarter
        $this->printSubKeeperSelection($baseInputName, 1, $playerGameStats, true);
        $this->printSubKeeperSelection($baseInputName, 2, $playerGameStats);
        $this->printSubKeeperSelection($baseInputName, 3, $playerGameStats);
        $this->printSubKeeperSelection($baseInputName, 4, $playerGameStats);

        // Yellow or Red Cards
        $inputYellow1   = $baseInputName . "[" . View_Base::PLAYER_YELLOW1 . "]";
        $inputYellow2   = $baseInputName . "[" . View_Base::PLAYER_YELLOW2 . "]";
        $inputRed       = $baseInputName . "[" . View_Base::PLAYER_RED . "]";
        $yellow1Checked = $playerGameStats->yellowCards >= 1 ? 'checked' : '';
        $yellow2Checked = $playerGameStats->yellowCards >= 2 ? 'checked' : '';
        $redChecked     = $playerGameStats->redCard ? 'checked' : '';
        $yellowBgColor  = $playerGameStats->yellowCards > 0 ? 'yellow' : 'white';
        $redBgColor     = $playerGameStats->redCard ? 'red' : 'white';
        print "
                            <td style='background-color: $yellowBgColor'>
                                <input type=checkbox name='$inputYellow1' value='1' $yellow1Checked><br>
                                <input type=checkbox name='$inputYellow2' value='1' $yellow2Checked>
                            </td>
                            <td style='background-color: $redBgColor'>
                                <input type=checkbox name='$inputRed' value='1' $redChecked>
                            </td>";

        print "
                        </tr>";
    }

    /**
     * @param string            $baseInputName
     * @param int               $quarter
     * @param PlayerGameStats   $playerGameStats
     * @param bool              $addLeftDoubleBorder
     */
    private function printSubKeeperSelection($baseInputName, $quarter, $playerGameStats = null, $addLeftDoubleBorder = false)
    {
        // Figure out value that should be pre-selected
        $substitutionChecked    = '';
        $keeperChecked          = '';
        $injuredChecked         = '';
        $absentChecked          = '';
        $bgColor                = 'white';

        if ($playerGameStats) {
            $substitution   = 'substitutionQuarter' . $quarter;
            $keeper         = 'keeperQuarter' . $quarter;
            $injured        = 'injuredQuarter' . $quarter;
            $absent         = 'absentQuarter' . $quarter;

            if ($playerGameStats->{$substitution}) {
                $substitutionChecked = 'checked';
                $bgColor             = 'lightgray';
            } else if ($playerGameStats->{$keeper}) {
                $keeperChecked = 'checked';
                $bgColor       = 'lightblue';
            } else if ($playerGameStats->{$injured}) {
                $injuredChecked = 'checked';
                $bgColor        = 'lightpink';
            } else if ($playerGameStats->{$absent}) {
                $absentChecked = 'checked';
                $bgColor       = 'orange';
            }
        }

        $leftBorder = $addLeftDoubleBorder ? 'border-left: double' : '';
        $cellStyle = "style='background-color: $bgColor; $leftBorder'";

        print "
            <td $cellStyle>";

        // Radio Button - Sub
        $this->uniqueId += 1;
        $substitutionId = $this->uniqueId . "_sub_" . $quarter;
        $inputName      = $baseInputName . "[" . View_Base::PLAYER_BASE . $quarter . "]";

        print "
                <input type=radio id='$substitutionId' name='$inputName' value='X' $substitutionChecked>X";

        // Radio Button - Keeper
        print "
                <input type=radio name='$inputName' value='G' $keeperChecked>G";

        // Radio Button - Injured
        print "
                <input type=radio name='$inputName' value='I' $injuredChecked>I";

        // Radio Button - Absent
        print "
                <input type=radio name='$inputName' value='A' $absentChecked>A";

        // Radio Button - Empty
        print "
                <input type=radio name='$inputName' value='E'>";

        print "
            </td>";
    }

    /**
     * @param string    $baseInputName
     * @param Player    $player
     */
    private function printEnterPlayerNumber($baseInputName, $player)
    {
        $inputName      = $baseInputName . "[" . View_Base::PLAYER_NUMBER . "]";
        $playerNumber   = isset($player) ? $player->number : '';

        print "
            <input type='number' class='no-spinners' name='$inputName' style='width: 15px' placeholder='' value='$playerNumber'>";
    }

    /**
     * @param Team  $team
     * @return array|Player[]
     */
    private function getPlayersOrderedByNumber($team)
    {
        $players = isset($team) ? Player::lookupByTeam($team, PlayerOrm::ORDER_BY_NUMBER) : [];
        return $players;
    }

    /**
     * @param int       $sessionId
     * @param Game      $game
     * @param string    $scoringType
     * @param Coach     $coachFilter (defaults to null)
     * @param Division  $divisionFilter (defaults to null)
     * @param GameDate  $gameDateFilter (defaults to null)
     */
    private function printQuickUpdateGameForm($sessionId, $game, $scoringType, $coachFilter = null, $divisionFilter = null, $gameDateFilter = null)
    {
        $isTitleGame    = (!isset($game->homeTeam) or !isset($game->visitingTeam));
        $titleGameValue = !empty($game->title);
        $bgcolor        = isset($game->homeTeamScore) ? 'lightyellow' : 'lightblue';
        if ($isTitleGame) {
            $bgcolor = isset($game->homeTeamScore) ? 'orange' : 'orangered';
        }
        $addClearButton = isset($game->homeTeamScore);
        $command        = isset($game->homeTeamScore) ? View_Base::UPDATE : View_Base::ENTER;
        $day            = $game->gameTime->gameDate->day;
        $time           = $game->gameTime->actualStartTime;
        $fieldName      = $game->gameTime->field->fullName;
        $flightName     = $game->flight->name;
        $poolName       = isset($game->pool) ? $game->pool->name : "";
        $poolName       .= isset($game->title) ? ": " . $game->title : "";
        $division       = $game->flight->schedule->division;
        $divisionName   = $division->nameWithGender;

        // Get teams in game's flight
        $pools          = Pool::lookupByFlight($game->flight);
        $teamSelector   = [];
        foreach ($pools as $pool) {
            $teams = Team::lookupByPool($pool);
            foreach ($teams as $team) {
                $coach  = Coach::lookupByTeam($team);
                $name   = $team->nameIdWithSeed . ' ' . $coach->lastName . ' ' . $team->region . ' (' . $team->city . ')';

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
            <form method='post' action='" . self::SCORING_ENTER_SCORES_PAGE . $this->m_urlParams . "'>";

        $defaultTeamSelection = '';
        if (isset($game->homeTeam)) {
            $coach                  = Coach::lookupByTeam($game->homeTeam);
            $defaultTeamSelection   = $game->homeTeam->nameIdWithSeed . ' ' . $coach->lastName . ' ' . $game->homeTeam->region . ' (' . $game->homeTeam->city . ')';
        }

        print "
                <tr>";

        if ($isTitleGame) {
            $this->displaySelector('Home Team:',
                View_Base::HOME_TEAM_ID,
                '',
                $teamSelector,
                $defaultTeamSelection,
                null,
                false,
                300,
                'left',
                'Select a Team',
                '',
                6);
        } else {
            $name = $teamSelector[$game->homeTeam->id];
            print "
                    <td style='color: " . View_Base::AQUA . "'>Home Team:</td>
                    <td colspan=6>$name</td>";
        }

        print "
                </tr>";

        if (isset($game->homeTeam)) {
            print "
                <tr>
                    <td>&nbsp</td>";

            $numberWidth = 25;
            $this->displayInput('Goals:', 'number', View_Base::HOME_SCORE, '', '', $game->homeTeamScore, null, 1, false, $numberWidth, false, true);
            $this->displayInput('Yellow Cards:', 'number', View_Base::HOME_YELLOW_CARDS, '', '', $game->homeTeamYellowCards, null, 1, false, $numberWidth, false, true);
            $this->displayInput('Red Cards:', 'number', View_Base::HOME_RED_CARDS, '', '', $game->homeTeamRedCards, null, 1, false, $numberWidth, false, true);

            print "
                </tr>";
        }

        $defaultTeamSelection = '';
        if (isset($game->visitingTeam)) {
            $coach                  = Coach::lookupByTeam($game->visitingTeam);
            $defaultTeamSelection   = $game->visitingTeam->nameIdWithSeed . ' ' . $coach->lastName . ' ' . $game->visitingTeam->region . ' (' . $game->visitingTeam->city . ')';
        }

        print "
                <tr>";

        if ($isTitleGame) {
            $this->displaySelector('Visiting Team:',
                View_Base::VISITING_TEAM_ID,
                '',
                $teamSelector,
                $defaultTeamSelection,
                null,
                false,
                300,
                'left',
                'Select a Team',
                '',
                6);
        } else {
            $name = $teamSelector[$game->visitingTeam->id];
            print "
                    <td style='color: " . View_Base::AQUA . "'>Visiting Team:</td>
                    <td colspan=6>$name</td>";
        }

        print "
                </tr>";

        if (isset($game->homeTeam)) {
            print "
                <tr>
                    <td>&nbsp</td>";

            $numberWidth = 25;
            $this->displayInput('Goals:', 'number', View_Base::VISITING_SCORE, '', '', $game->visitingTeamScore, null, 1, false, $numberWidth, false, true);
            $this->displayInput('Yellow Cards:', 'number', View_Base::VISITING_YELLOW_CARDS, '', '', $game->visitingTeamYellowCards, null, 1, false, $numberWidth, false, true);
            $this->displayInput('Red Cards:', 'number', View_Base::VISITING_RED_CARDS, '', '', $game->visitingTeamRedCards, null, 1, false, $numberWidth, false, true);
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
                        <input type='hidden' id='isTitleGame' name='isTitleGame' value='$titleGameValue'>
                        <input type='hidden' id='" . View_Base::QUICK_SCORING . "' name='" . View_Base::QUICK_SCORING . "' value='checked'> 
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
                        <input type='hidden' id='isTitleGame' name='isTitleGame' value='$titleGameValue'>
                        <input type='hidden' id='" . View_Base::QUICK_SCORING . "' name='" . View_Base::QUICK_SCORING . "' value='checked'> 
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
}
