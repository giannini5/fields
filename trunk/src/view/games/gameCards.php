<?php

use \DAG\Domain\Schedule\Game;
use \DAG\Domain\Schedule\Coach;
use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\GameDate;
use \DAG\Domain\Schedule\Team;
use \DAG\Domain\Schedule\AssistantCoach;
use \DAG\Domain\Schedule\Player;
use \DAG\Domain\Schedule\PlayerGameStats;
use \DAG\Orm\Schedule\PlayerOrm;
use \DAG\Framework\Exception\Assertion;

/**
 * @brief Show the Game Cards page to see game data
 */
class View_Games_GameCards extends View_Games_Base
{
    /**
     * @brief Construct the View
     *
     * @param Controller_Games_GameCards $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller)
    {
        parent::__construct(self::GAMES_GAME_CARDS_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        $divisionsSelector  = $this->getDivisionsSelector(true, false, true, true);
        $gameDateSelector   = $this->getGameDateSelector();

        if (!$this->m_controller->m_isPopup) {
            print "
            <table valign='top' align='center' width='400' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td valign='top' bgcolor='" . View_Base::VIEW_COLOR . "'>";

            $this->_printEnterGameId();

            print "
                    </td>
                    <td valign='top' bgcolor='" . View_Base::VIEW_COLOR . "'>";

            $this->_printGameCardsForDay($divisionsSelector, $gameDateSelector);

            print "
                    </td>
                </tr>
            </table>
            <br><br>";
        }

        switch ($this->m_controller->m_scoringType) {
            case Controller_Games_GameCards::GAME:
                if (Game::findById($this->m_controller->m_gameId, $game)) {
                    $this->printGame($game, $this->m_controller->m_scoringType);
                }
                break;
            case Controller_Games_GameCards::DIVISION_GAMES:
                $this->printGamesForDivisionAndDay($this->m_controller->m_division, $this->m_controller->m_gameDate);
                break;
        }
    }

    /**
     * @brief Print the form to get the gameId for game card display.  Form includes the following
     *          - GameId
     */
    private function _printEnterGameId()
    {
        $value = $this->m_controller->m_gameId;

        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <th nowrap align='left' colspan='7'>Enter Score for Game</th>
                </tr>
                <form method='get' action='" . self::GAMES_GAME_CARDS_PAGE . $this->m_urlParams . "'>";

        $this->displayInput('Game Id:', 'number', View_Base::GAME_ID, '', '', $value, null, 6, true, 50, false, true);

        // Print Enter button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::ENTER . "'>
                        <input type='hidden' id='" . View_Base::SCORING_TYPE . "' name='" . View_Base::SCORING_TYPE . "' value='" . Controller_Games_GameCards::GAME . "'>
                    </td>
                </tr>
            </form>
            </table>";
    }

    /**
     * @brief Print the form to display the game cards for a specified day.  Form includes the following
     *        - List of Divisions
     *        - Day of games
     *
     * @param array $divisionsSelector  - List of divisionId => name
     * @param array $gameDateSelector   - List of gameDateId => day
     */
    private function _printGameCardsForDay($divisionsSelector, $gameDateSelector)
    {
        print "
            <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <th nowrap align='left' colspan='2'>Game Cards By Divion/Day</th>
                </tr>
            <form method='get' action='" . self::GAMES_GAME_CARDS_PAGE . $this->m_urlParams . "'>";

        $this->displaySelector('Division:', View_Base::DIVISION_NAME, '', $divisionsSelector, $this->m_controller->m_divisionName);
        $this->displaySelector('Game Date:', View_Base::GAME_DATE, '', $gameDateSelector, $this->m_controller->m_gameDate->day);

        // Print Update button and end form
        print "
                <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::ENTER . "'>
                        <input type='hidden' id='" . View_Base::SCORING_TYPE . "' name='" . View_Base::SCORING_TYPE . "' value='" . Controller_Games_GameCards::DIVISION_GAMES . "'>
                    </td>
                </tr>
            </form>
            </table>";
    }

    /**
     * @param Game      $game
     * @param string    $scoringType
     */
    private function printGame($game, $scoringType)
    {
        // Print message and return if game does not have a team
        if (!isset($game->homeTeam)) {
            print "Teams not yet set for game";
            return;
        }

        $bgcolor        = isset($game->homeTeamScore) ? 'lightyellow' : 'lightblue';
        $day            = $game->gameTime->gameDate->day;
        $time           = $game->gameTime->actualStartTime;
        $fieldName      = $game->gameTime->field->fullName;
        $division       = $game->flight->schedule->division;
        $divisionName   = $division->nameWithGender;
        $homeTeamId     = $game->homeTeam->nameId;
        $visitingTeamId = $game->visitingTeam->nameId;
        $useAccordian   = $scoringType != Controller_Games_GameCards::GAME;
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

        if ($useAccordian) {
            print "
            </div>"; // Accordian Pane div
        }

        print "
            </div>"; // Accordian NoAccordian div

        if (!$useAccordian) {
            print "<br>";
        }

        print "<br>";
    }

    /**
     * @param Game      $game
     * @param bool      $isHomeTeam
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
        $fullTeamName           = $teamName == $teamId ? $teamId : "$teamId($teamName)";
        $fullOpposingTeamName   = $opposingTeamName == $opposingTeamId ? $opposingTeamId : "$opposingTeamId($opposingTeamName)";
        $players                = $this->getPlayersOrderedByNumber($team);

        $headerElementHeight    = "20px";

        print "
                    <table id='viewTable' class='table' border='0' style='table-layout: fixed; width: 4.5in'>
                        <tr>
                            <td align='left'><img src='/images/aysoLogoBlackAndWhite.png' height='30px' width='30px'></td>
                            <td align='center' nowrap><strong style='font-size: larger'>GAME CARD</strong></td>
                            <td align='right'><strong style='font-size: larger'>$homeOrVisitor</strong></td>
                        </tr>
                    </table>
                    <table id='viewTable' class='table' border='0' style='table-layout: fixed; width: 4.5in'>
                        <tr style='height: $headerElementHeight'>
                            <td nowrap align='left' style='font-size: larger'>$day $time</td>
                            <td>&nbsp</td>
                            <td nowrap align='left' style='font-size: larger'>$fieldName</td>
                            <td>&nbsp</td>
                            <td nowrap align='right'>GID: <strong style='font-size: larger'>$game->id</strong></td>
                        </tr>
                    </table>
                    <table id='viewTable' class='table' border='0' style='table-layout: fixed; width: 4.5in'>
                        <tr style='height: $headerElementHeight'>
                            <td nowrap align='left' style='overflow: hidden; font-size: larger'><strong>TEAM: </strong>$fullTeamName</td>
                            <td nowrap align='right' style='overflow: hidden; font-size: larger'><strong>VS: </strong>$fullOpposingTeamName</td>
                        </tr>
                    </table>
                    <table id='viewTable' class='table' border='0' style='table-layout: fixed; width: 4.5in'>
                        <tr style='height: $headerElementHeight'>
                            <td nowrap align='left' style='overflow: hidden; font-size: larger'><strong>COACH: </strong>$coachName</td>
                            <td nowrap align='right' style='overflow: hidden; font-size: larger'><strong>ASST. COACH: </strong>$assistantCoachName</td>
                        </tr>
                    </table>
                    <br>
                    <table id='viewTable' class='table' border='2' style='table-layout: fixed; width: 4.5in' cellpadding='5' cellspacing='0'>
                            <tr>
                                <td rowspan='2' width='5px' align='center' style='border: 1px solid'><strong>#</strong></td>
                                <td rowspan='2' width='65px' align='center' style='border: 1px solid'><strong>Player's Name</strong></td>
                                <td rowspan='2' width='15px' colspan='1' align='center' style='border: 1px solid; border-right: double'><strong>Goals</strong></td>
                                <td width='80px' colspan='4' align='center' style='border: 1px solid; border-left: double'><strong>Sub:X, Keep:G<br>Injured:I, Absent:A</strong></td>
                                <td width='15px' colspan='2' align='center' style='border: 1px solid'><strong>Cards</strong></td>
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

        print "
                    </table><br>";
    }

    /**
     * @param Game      $game
     * @param Team      $team
     * @param Player    $player
     */
    private function printPlayerRow($game, $team, $player = null) {
        $playerName         = isset($player) ? $player->firstName : '';
        $playerGameStats    = isset($player) ? PlayerGameStats::findOrCreate($game, $team, $player) : null;
        $playerGoals        = isset($playerGameStats) ? $playerGameStats->goals : 0;


        print "
                        <tr style='overflow: hidden'>
                            <td width='5px' align='center' style='font-size: larger'>";

        // Player Number
        $this->printPlayerNumber($player);

        $bgColor        = $playerGoals == 0 ? 'white' : 'lightgreen';
        $playerGoals    = $playerGoals == 0 ? '' : $playerGoals;
        print "
                            </td> 
                            <td width='65px'>$playerName</td>
                            <td width='15px' align='center' style='border-right: double; background-color: $bgColor'>$playerGoals</td>";

        // Substitution/Keeper by quarter
        $this->printSubKeeperSelection(1, $playerGameStats, true);
        $this->printSubKeeperSelection(2, $playerGameStats);
        $this->printSubKeeperSelection(3, $playerGameStats);
        $this->printSubKeeperSelection(4, $playerGameStats);

        // Yellow or Red Cards
        $yellows        = $playerGameStats->yellowCards >= 1 ? $playerGameStats->yellowCards : '';
        $reds           = $playerGameStats->redCard ? 1 : '';
        $yellowBgColor  = $playerGameStats->yellowCards > 0 ? 'yellow' : 'white';
        $redBgColor     = $playerGameStats->redCard ? 'red' : 'white';
        print "
                            <td style='background-color: $yellowBgColor'>$yellows</td>
                            <td style='background-color: $redBgColor'>$reds</td>
                        </tr>";
    }

    /**
     * @param int               $quarter
     * @param PlayerGameStats   $playerGameStats
     * @param bool              $addLeftDoubleBorder
     */
    private function printSubKeeperSelection($quarter, $playerGameStats = null, $addLeftDoubleBorder = false)
    {
        // Figure out value that should be shown
        $bgColor    = 'white';
        $value      = '&nbsp';

        if ($playerGameStats) {
            $substitution   = 'substitutionQuarter' . $quarter;
            $keeper         = 'keeperQuarter' . $quarter;
            $injured        = 'injuredQuarter' . $quarter;
            $absent         = 'absentQuarter' . $quarter;

            if ($playerGameStats->{$substitution}) {
                $value      = 'X';
                $bgColor    = 'lightgray';
            } else if ($playerGameStats->{$keeper}) {
                $value      = 'G';
                $bgColor    = 'lightblue';
            } else if ($playerGameStats->{$injured}) {
                $value      = 'I';
                $bgColor    = 'lightpink';
            } else if ($playerGameStats->{$absent}) {
                $value      = 'A';
                $bgColor    = 'orange';
            }
        }

        $leftBorder = $addLeftDoubleBorder ? 'border-left: double' : '';
        $cellStyle  = "style='background-color: $bgColor; $leftBorder'";

        print "
            <td align='center' $cellStyle>$value</td>";
    }

    /**
     * @param Player    $player
     */
    private function printPlayerNumber($player)
    {
        $playerNumber   = isset($player) ? $player->number : '';
        print "$playerNumber";
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
     * @param Division  $division
     * @param GameDate  $gameDate
     */
    private function printGamesForDivisionAndDay($division, $gameDate)
    {
        if (!isset($division)) {
            return;
        }

        $games = Game::lookupByDivisionDay($division, $gameDate->day, true);
        $countPrinted = 0;
        foreach ($games as $game) {
            if ($game->flight->schedule->isPublished()) {
                $this->printGame($game, $this->m_controller->m_scoringType);
                $countPrinted += 1;
            }
        }

        if ($countPrinted == 0) {
            print "<p style='color: red; font-size: medium' align='center'>Schedules have not yet been published for Division: $division->name $division->gender.</p>";
        }
    }
}