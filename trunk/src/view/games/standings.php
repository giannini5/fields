<?php

use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\Schedule;
use \DAG\Domain\Schedule\Game;
use \DAG\Domain\Schedule\Flight;
use \DAG\Domain\Schedule\Pool;
use \DAG\Domain\Schedule\Coach;
use \DAG\Orm\Schedule\ScheduleOrm;
use \DAG\Domain\Schedule\Team;
use \DAG\Orm\Schedule\GameOrm;
use \DAG\Framework\Exception\Assertion;
use \DAG\Framework\Exception\Precondition;

/**
 * @brief Show the Standings Viewing page
 */
class View_Games_Standings extends View_Games_Base
{
    const WINS          = 'wins';
    const LOSSES        = 'losses';
    const TIES          = 'ties';
    const GOALS_FOR     = 'goalsFor';
    const GOALS_AGAINST = 'goalsAgains';
    const SHUTOUTS      = 'shutouts';
    const POINTS        = 'points';
    const YELLOWS       = 'yellows';
    const REDS          = 'reds';

    /**
     * @brief Construct he View
     *
     * @param Controller_Games_Standings $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller)
    {
        parent::__construct(self::GAMES_STANDINGS_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        // Print selectors
        $this->_printViewStandingsByDivision();

        // Print standings for division if selected
        if (isset($this->m_controller->m_division)) {
            $this->printStandingsForDivision($this->m_controller->m_division);
        }
    }

    /**
     * @brief Print standings by divisions(s)
     *        - Division
     */
    private function _printViewStandingsByDivision() {
        $divisionsSelector = $this->getDivisionsSelector(true, false, true, true);

        // Print the form
        print "
            <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td valign='top' bgcolor='" . View_Base::VIEW_COLOR  . "'>
                        <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                            <tr>
                                <th nowrap colspan='2' align='left'>View Standings by Division</th>
                            </tr>
                        <form method='get' action='" . self::GAMES_STANDINGS_PAGE . "'>";

        $this->displaySelector('Division:', View_Base::DIVISION_NAME, '', $divisionsSelector, $this->m_controller->m_divisionName, NULL, true, 140, 'left', 'Select a Division');

        // Print View button and end form
        print "
                            <tr>
                                <td align='left'>
                                    <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::SUBMIT . "'>
                                </td>
                            </tr>
                        </form>
                        </table>
                    </td>
                </tr>
            </table>
            <br>";
    }

    /**
     * @param Division $division
     */
    private function printStandingsForDivision($division)
    {
        $schedules      = Schedule::lookupByDivision($division);
        $teams          = [];
        $teamStats      = [];
        $teamPoints     = [];
        $flightsData    = [];
        $flightTeams    = [];
        $poolTeams      = [];
        $scheduleTitle  = "";

        foreach ($schedules as $schedule) {
            if ($schedule->published != 1) {
                continue;
            }

            switch ($schedule->scheduleType) {
                case ScheduleOrm::SCHEDULE_TYPE_LEAGUE:
                    if ($division->combineLeagueSchedules) {
                        $this->compileStandingsForLeaguePlay($schedule, $teams, $teamStats, $teamPoints, $flightsData, $flightTeams, $poolTeams);
                        $scheduleTitle .= $schedule->name . "<br>";
                    } else {
                        $this->printStandingsForLeaguePlayBySchedule($division, $schedule);
                    }
                    break;

                case ScheduleOrm::SCHEDULE_TYPE_BRACKET:
                    $this->printStandingsForBracketPlay($schedule);
                    break;

                case ScheduleOrm::SCHEDULE_TYPE_TOURNAMENT:
                default:
                    $this->printStandingsForTournamentPlay($schedule);
                    break;
            }
        }

        if ($division->combineLeagueSchedules) {
            $this->printStandingsForLeaguePlay($scheduleTitle, $division, $teams, $teamStats, $teamPoints, $flightsData, $flightTeams, $poolTeams);
        }
    }

    /**
     * @param Schedule  $schedule
     * @param array     $teams
     * @param array     $teamStats
     * @param array     $teamPoints
     * @param array     $flightsData
     * @param array     $flightTeams
     * @param array     $poolTeams
     */
    private function compileStandingsForLeaguePlay($schedule, &$teams, &$teamStats, &$teamPoints, &$flightsData, &$flightTeams, &$poolTeams)
    {
        $flights = Flight::lookupBySchedule($schedule);

        foreach ($flights as $flight) {
            $games = Game::lookupByFlight($flight);

            foreach ($games as $game) {
                if (isset($game->homeTeam) and empty($game->title)) {
                    $team                   = $game->homeTeam;
                    $teams[$team->id]       = $team;
                    $stats                  = isset($teamStats[$team->id]) ? $teamStats[$team->id] : [];
                    $stats                  = $this->getGameStats($game, true, $stats, true);
                    $teamStats[$team->id]   = $stats;
                    $teamPoints[$team->id]  = $stats[self::POINTS];

                    // Do not track flightsData for game if cross-pool play since only one pool is recorded when cross-pool is used
                    if ($game->pool->gamesAgainstPool->id == $game->pool->id) {
                        $flightsData[$flight->name][$game->pool->name][$team->id] = $team;
                    }

                    $team                   = $game->visitingTeam;
                    $teams[$team->id]       = $team;
                    $stats                  = isset($teamStats[$team->id]) ? $teamStats[$team->id] : [];
                    $stats                  = $this->getGameStats($game, false, $stats, true);
                    $teamStats[$team->id]   = $stats;
                    $teamPoints[$team->id]  = $stats[self::POINTS];

                    // Do not track flightsData for game if cross-pool play since only one pool is recorded when cross-pool is used
                    if ($game->pool->gamesAgainstPool->id == $game->pool->id) {
                        $flightsData[$flight->name][$game->pool->name][$team->id] = $team;
                        $poolTeams[isset($game->pool) ? $game->pool->name : ''][] = $game->homeTeam->id;
                        $poolTeams[isset($game->pool) ? $game->pool->name : ''][] = $game->visitingTeam->id;
                    }

                    $flightTeams[$flight->name][] = $game->homeTeam->id;
                    $flightTeams[$flight->name][] = $game->visitingTeam->id;
                }
            }
        }
    }

    /**
     * @param Division  $division
     * @param Schedule  $schedule
     */
    private function printStandingsForLeaguePlayBySchedule($division, $schedule)
    {
        // TODO cross pool support
        $standingsPrinted   = false;

        $flights    = Flight::lookupBySchedule($schedule);
        $teams      = [];
        $teamStats  = [];
        $teamPoints = [];
        $poolTeams  = [];

        print "
                <table valign='top' align='center' bgcolor='yellow' width='800' border='0' cellpadding='5' cellspacing='0'>
                    <tr>
                        <th><h1>$schedule->name</h1></th>
                    </tr>
                    <tr>
                    <tr>
                        <td valign='top'>";

        foreach ($flights as $flight) {
            $games = Game::lookupByFlight($flight);

            foreach ($games as $game) {
                if (isset($game->homeTeam) and empty($game->title)) {
                    $poolTeams[isset($game->pool) ? $game->pool->id : 0][] = $game->homeTeam->id;

                    $team = $game->homeTeam;
                    $teams[$team->id] = $team;
                    $stats = isset($teamStats[$team->id]) ? $teamStats[$team->id] : [];
                    $stats = $this->getGameStats($game, true, $stats, true);
                    $teamStats[$team->id] = $stats;
                    $teamPoints[$team->id] = $stats[self::POINTS];
                }

                if (isset($game->visitingTeam) and empty($game->title)) {
                    $poolTeams[isset($game->pool) ? $game->pool->id : 0][] = $game->visitingTeam->id;

                    $team                   = $game->visitingTeam;
                    $teams[$team->id]       = $team;
                    $stats                  = isset($teamStats[$team->id]) ? $teamStats[$team->id] : [];
                    $stats                  = $this->getGameStats($game, false, $stats, true);
                    $teamStats[$team->id]   = $stats;
                    $teamPoints[$team->id]  = $stats[self::POINTS];
                }
            }
        }

        // Add in volunteer points
        foreach ($teams as $teamId => $team) {
            $teamPoints[$teamId]                += $team->volunteerPoints;
            $teamStats[$teamId][self::POINTS]   += $team->volunteerPoints;
        }

        // Sort by points, highest to lowest
        arsort($teamPoints);

        foreach ($flights as $flight) {
            // Skip flights where no games are scheduled
            if ($flight->scheduleGames != 1) {
                continue;
            }

            $standingsPrinted   = true;

            print "
                <table valign='top' align='center' bgcolor='lightgray' width='800' border='0' cellpadding='5' cellspacing='0'>
                    <tr>
                        <td valign='top'>";

            $pools = Pool::lookupByFlight($flight);
            foreach ($pools as $pool) {
                print "
                    <table valign='top' align='center' bgcolor='white' width='800' border='1' cellpadding='5' cellspacing='0'>
                        <tr bgcolor='lightskyblue'>
                            <th colspan='12'>$schedule->name - Flight $flight->name: $pool->name</th>
                        </tr>
                        <tr bgcolor='lightskyblue'>
                            <th>Team</th>
                            <th>Coach</th>
                            <th>Wins</th>
                            <th>Losses</th>
                            <th>Ties</th>
                            <th>Goals For</th>
                            <th>Goals Against</th>
                            <th>Shutouts</th>
                            <th>Yellow Cards</th>
                            <th>Red Cards</th>
                            <th>Volunteer Points</th>
                            <th>Total Points</th>
                        </tr>";

                foreach ($teamPoints as $teamId => $points) {
                    if (isset($poolTeams[$pool->id]) and in_array($teamId, $poolTeams[$pool->id])) {
                        $stats              = $teamStats[$teamId];
                        $teamName           = $teams[$teamId]->name;
                        $wins               = $stats[self::WINS];
                        $losses             = $stats[self::LOSSES];
                        $ties               = $stats[self::TIES];
                        $goalsFor           = $stats[self::GOALS_FOR];
                        $goalsAgainst       = $stats[self::GOALS_AGAINST];
                        $shutouts           = $stats[self::SHUTOUTS];
                        $volunteerPoints    = $teams[$teamId]->volunteerPoints;
                        $points             = $stats[self::POINTS];
                        $yellows            = $stats[self::YELLOWS];
                        $reds               = $stats[self::REDS];
                        $coach              = Coach::lookupByTeam($teams[$teamId]);
                        $coachName          = $coach->shortName;

                        print "
                            <tr>
                                <td nowrap>$teamName</td>
                                <td nowrap>$coachName</td>
                                <td align='right'>$wins</td>
                                <td align='right'>$losses</td>
                                <td align='right'>$ties</td>
                                <td align='right'>$goalsFor</td>
                                <td align='right'>$goalsAgainst</td>
                                <td align='right'>$shutouts</td>
                                <td align='right'>$yellows</td>
                                <td align='right'>$reds</td>
                                <td align='right'>$volunteerPoints</td>
                                <td align='right'>$points</td>
                            </tr>";
                    }
                }

                print "
                    </table><br>";
            }

            print "
                        </td>
                    </tr>
                </table><br>";
        }

        if (!$standingsPrinted) {
            print "<p style='color: red; font-size: medium' align='center'>Standings are not yet available for Division: $division->name $division->gender.</p>";
        }

        print "
                        </td>
                    </tr>
                </table><br><br>";
    }

    /**
     * @param string    $scheduleTitle
     * @param Division  $division
     * @param array     $teams
     * @param array     $teamStats
     * @param array     $teamPoints
     * @param array     $flightsData
     * @param array     $flightTeams
     * @param array     $poolTeams
     */
    private function printStandingsForLeaguePlay($scheduleTitle, $division, $teams, $teamStats, $teamPoints, $flightsData, $flightTeams, $poolTeams)
    {
        $standingsPrinted   = false;

        // Add in volunteer points
        foreach ($teams as $teamId => $team) {
            $teamPoints[$teamId]                += $team->volunteerPoints;
            $teamStats[$teamId][self::POINTS]   += $team->volunteerPoints;
        }

        // Sort by points, highest to lowest
        arsort($teamPoints);

        print "
                <table bgcolor='yellow' valign='top' align='center' width='850' border='0' cellpadding='5' cellspacing='0'>
                    <tr>
                        <th><h1>$scheduleTitle</h1></th>
                    </tr>
                    <tr>
                        <td valign='top'>";

        foreach ($flightsData as $flightName => $poolsData) {
            $standingsPrinted   = true;

            print "
                <table bgcolor='lightgray' valign='top' align='center' width='850' border='0' cellpadding='5' cellspacing='0'>
                    <tr>
                        <td valign='top'>";

            foreach ($poolsData as $poolName => $teamData) {
                print "
                    <table bgcolor='white' valign='top' align='center' width='850' border='1' cellpadding='5' cellspacing='0'>
                        <tr bgcolor='lightskyblue'>
                            <th colspan='12'>Flight $flightName: $poolName</th>
                        </tr>
                        <tr bgcolor='lightskyblue'>
                            <th>Team</th>
                            <th>Coach</th>
                            <th>Wins</th>
                            <th>Losses</th>
                            <th>Ties</th>
                            <th>Goals For</th>
                            <th>Goals Against</th>
                            <th>Shutouts</th>
                            <th>Yellow Cards</th>
                            <th>Red Cards</th>
                            <th>Volunteer Points</th>
                            <th>Total Points</th>
                        </tr>";

                foreach ($teamPoints as $teamId => $points) {
                    // Check for teams in flight/pool based on name instead of ID for cases where there are multiple
                    // Schedules due to odd team counts (14UGirls w/ 8 Teams - for 10 games:
                    //    Schedule 1: Pool play (3 games)
                    //    Schedule 2: Cross pool play (4 games)
                    //    Schedule 3: Pool play (3 games)
                    if (isset($poolTeams[$poolName]) and in_array($teamId, $poolTeams[$poolName])
                        and isset($flightTeams[$flightName]) and in_array($teamId, $flightTeams[$flightName])) {
                        $stats              = $teamStats[$teamId];
                        $teamName           = $teams[$teamId]->name;
                        $wins               = $stats[self::WINS];
                        $losses             = $stats[self::LOSSES];
                        $ties               = $stats[self::TIES];
                        $goalsFor           = $stats[self::GOALS_FOR];
                        $goalsAgainst       = $stats[self::GOALS_AGAINST];
                        $shutouts           = $stats[self::SHUTOUTS];
                        $volunteerPoints    = $teams[$teamId]->volunteerPoints;
                        $points             = $stats[self::POINTS];
                        $yellows            = $stats[self::YELLOWS];
                        $reds               = $stats[self::REDS];
                        $coach              = Coach::lookupByTeam($teams[$teamId]);
                        $coachName          = $coach->shortName;

                        print "
                            <tr>
                                <td nowrap>$teamName</td>
                                <td nowrap>$coachName</td>
                                <td align='right'>$wins</td>
                                <td align='right'>$losses</td>
                                <td align='right'>$ties</td>
                                <td align='right'>$goalsFor</td>
                                <td align='right'>$goalsAgainst</td>
                                <td align='right'>$shutouts</td>
                                <td align='right'>$yellows</td>
                                <td align='right'>$reds</td>
                                <td align='right'>$volunteerPoints</td>
                                <td align='right'>$points</td>
                            </tr>";
                    }
                }

                print "
                    </table><br>";
            }

            print "
                        </td>
                    </tr>
                </table><br>";
        }

        if (!$standingsPrinted) {
            print "<p style='color: red; font-size: medium' align='center'>Standings are not yet available for Division: $division->name $division->gender.</p>";
        }

        print "
                        </td>
                    </tr>
                </table>";
    }

    /**
     * @param Schedule  $schedule
     */
    private function printStandingsForTournamentPlay($schedule)
    {
        $flights = Flight::lookupBySchedule($schedule);

        foreach ($flights as $flight) {
            // Skip flights where no games are scheduled
            if ($flight->scheduleGames != 1) {
                continue;
            }

            $pools = Pool::lookupByFlight($flight);

            print "
            <table bgcolor='lightgray' valign='top' align='center' width='850' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td>";

            foreach ($pools as $pool) {
                print "
                    <table bgcolor='white' valign='top' align='center' width='850' border='1' cellpadding='5' cellspacing='0'>
                        <tr bgcolor='lightskyblue'>
                            <th colspan='11'>$flight->name: $pool->name</th>
                        </tr>
                        <tr bgcolor='lightskyblue'>
                            <th>Team</th>
                            <th>Coach</th>";

                $teams = Team::lookupByPool($pool);

                for ($i = 1; $i < count($teams); $i++) {
                    print "
                            <th>Game $i</th>";
                }

                print "
                            <th>Points</th>
                        </tr>";

                foreach ($teams as $team) {
                    $coach  = Coach::lookupByTeam($team);
                    $games  = Game::lookupByTeam($team);
                    $points = '';

                    print "
                        <tr>
                            <td>$team->nameId: $team->name<br>&nbsp&nbsp&nbsp&nbsp$team->region - $team->city</td>
                            <td>$coach->shortName</td>";

                    foreach ($games as $game) {
                        // Skip Title Games and games not in same pool
                        if ($game->title != '' or $game->pool->id != $team->pool->id) {
                            continue;
                        }

                        $isHomeTeam         = $game->homeTeam->id == $team->id;
                        $gameResultString   = "&nbsp";
                        if (isset($game->homeTeamScore)) {
                            $gamePoints         = $game->computeGamePoints($isHomeTeam);
                            $gameResultString   = $this->getGameResultString($game, $isHomeTeam);
                            $points             = $points == '' ? 0 : $points;
                            $points             += $gamePoints;
                        }

                        print "
                            <td align='center'>$gameResultString</td>";
                    }

                    print "
                            <td align='center'>$points</td>";

                    print "
                            </tr>";
                }

                print "
                    </table><br>";
            }

            // Print title game info (if any)
            $this->printTitleGames($flight);

            print "                    
                        </td>
                    </tr>
                </table><br><br>";
        }
    }

    /**
     * @param Schedule  $schedule
     */
    private function printStandingsForBracketPlay($schedule)
    {
        if ($schedule->published != 1) {
            return;
        }

        print "
                <p style='page-break-before: always;'>&nbsp;</p>";

        print "
                <table bgcolor='lightgreen' valign='top' align='center' width='800' border='0' cellpadding='5' cellspacing='0'>
                    <tr>
                        <th><h1>" . $schedule->name . "<br>" . "</h1></th>
                    </tr>
                    <tr>
                        <td valign='top'>";

        print "                    
                        </td>
                    </tr>
                </table>";

        print "
                <p style='page-break-before: always;'>&nbsp;</p>";

        print "
                <table bgcolor='lightgreen' valign='top' align='center' width='800' border='0' cellpadding='5' cellspacing='0'>
                    <tr>
                        <td valign='top'>";

        $flights = Flight::lookupBySchedule($schedule);
        foreach ($flights as $flight) {
            // Skip flights where no games are scheduled
            if ($flight->scheduleGames != 1) {
                continue;
            }

            print "
                <table style='-webkit-print-color-adjust: exact;' bgcolor='lightgray' valign='top' align='center' width='850' border='0' cellpadding='5' cellspacing='0'>
                    <tr>
                        <th><strong style='font-size: medium'>" . $flight->name . "</strong></th>
                    </tr>
                    <tr>
                        <td valign='top'>";

            // Print title game info (if any)
            $this->printTitleGames($flight);

            print "                    
                        </td>
                    </tr>
                </table><br><br>";

            print "
                <p style='page-break-before: always;'>&nbsp;</p>";
        }

        print "                    
                        </td>
                    </tr>
                </table><br><br>";
    }

    /**
     * @param Schedule  $schedule
     */
    private function printStandingsForBracketPlayWorkInProgress($schedule)
    {
        // TODO:
        // Add gameOrdering
        // Display by gameOrdering
        // Figure out how to display gameId, coachName, game result
        //
        Precondition::isTrue($schedule->scheduleType == ScheduleOrm::SCHEDULE_TYPE_BRACKET, "Invalid scheduleType: " . $schedule->scheduleType);

        $flights = Flight::lookupBySchedule($schedule);

        foreach ($flights as $flight) {
            // Skip flights where no games are scheduled
            if ($flight->scheduleGames != 1) {
                continue;
            }

            $pools  = Pool::lookupByFlight($flight);
            Assertion::isTrue(count($pools) == 1, "Too many pools, only one supporte: " . count($pools));

            $pool   = $pools[0];
            $teams  = Team::lookupByPool($pool);
            $rounds = count($teams) == 10 ? 4 : 0;
            Assertion::isTrue($rounds == 4, "Unsupported number of rounds: $rounds.  Only 4 supported at this time");

            print "
            <table bgcolor='lightgray' valign='top' align='center' width='800' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td>
                    <table bgcolor='white' valign='top' align='center' width='800' border='1' cellpadding='5' cellspacing='0'>
                        <tr bgcolor='lightskyblue'>
                            <th colspan='11'>$flight->name</th>
                        </tr>
                        <tr bgcolor='lightskyblue'>";

            for ($i=1; $i<=$rounds; ++$i) {
                $label = "Playoff";
                switch ($rounds - $i) {
                    case 2:
                        $label = "Quarter-Finals";
                        break;
                    case 1:
                        $label = "Semi-Finals";
                        break;
                    case 0:
                        $label = "Finals";
                        break;
                }

                print "
                            <th>$label</th>";
            }

            print "
                        </tr>";

            $games          = Game::lookupByPool($pool);
            $gamesByRound   = [];
            foreach ($games as $game) {
                Assertion::isTrue(!empty($game->title), "Invalid game title for gameId " . $game->id);
                $gamesByRound[$game->title][] = $game;
            }

            $rows = $rounds == 4 ? 16 : 0;
            Assertion::isTrue($rows == 16, "Unsupported number of rows: $rows.  Only 16 supported at this time");

            for ($row = 1; $row <= $rows; ++$row) {
                print "
                        <tr>";

                for ($i=1; $i<=$rounds; ++$i) {
                    $game           = null;
                    $team           = null;
                    $homeAwayLabel  = null;
                    $playInLabel    = "Winner of ";

                    switch ($rounds - $i) {
                        case 3:
                            $gameIndex = ceil($row / 2);
                            $game = count($gamesByRound[GameOrm::TITLE_PLAYOFF]) < $gameIndex ? null : $gamesByRound[GameOrm::TITLE_PLAYOFF][$gameIndex - 1];
                            if (isset($game)) {
                                $team = $row % 2 == 0 ? $game->visitingTeam : $game->homeTeam;
                            }
                            $homeAwayLabel = $row % 2 == 0 ? "V:" : "H:";
                            break;
                        case 2:
                            if ($row == 2 or $row == 3 or $row == 6 or $row == 7 or $row == 10 or $row == 11 or $row == 14 or $row == 15) {
                                $gameIndex = ceil($row / 4);
                                $game = count($gamesByRound[GameOrm::TITLE_QUARTER_FINAL]) < $gameIndex ? null : $gamesByRound[GameOrm::TITLE_QUARTER_FINAL][$gameIndex - 1];
                                if (isset($game)) {
                                    $team = $row % 2 == 0 ? $game->homeTeam : $game->visitingTeam;
                                }
                                $homeAwayLabel = $row % 2 == 0 ? "H:" : "V:";
                            }
                            break;
                        case 1:
                            if ($row == 4 or $row == 5 or $row == 12 or $row == 13) {
                                $gameIndex = ceil($row / 8);
                                $game = count($gamesByRound[GameOrm::TITLE_SEMI_FINAL]) < $gameIndex ? null : $gamesByRound[GameOrm::TITLE_SEMI_FINAL][$gameIndex - 1];
                                if (isset($game)) {
                                    $team = $row % 2 == 0 ? $game->homeTeam : $game->visitingTeam;
                                }
                                $homeAwayLabel = $row % 2 == 0 ? "H:" : "V:";
                            }
                            break;
                        case 0:
                            if ($row == 8 or $row == 9) {
                                Assertion::isTrue(count($gamesByRound[GameOrm::TITLE_CHAMPIONSHIP]) == 1, "Invalid number of championship games");
                                $game = $gamesByRound[GameOrm::TITLE_CHAMPIONSHIP][0];
                                $team = $row % 2 == 0 ? $game->homeTeam : $game->visitingTeam;
                                $homeAwayLabel = $row % 2 == 0 ? "H:" : "V:";
                            } else if ($row == 15 or $row == 16) {
                                Assertion::isTrue(count($gamesByRound[GameOrm::TITLE_3RD_4TH]) == 1, "Invalid number of 3rd/4th games");
                                $game = $gamesByRound[GameOrm::TITLE_3RD_4TH][0];
                                $team = $row % 2 == 0 ? $game->homeTeam : $game->visitingTeam;
                                $homeAwayLabel  = $row % 2 == 0 ? "H:" : "V:";
                                $playInLabel    = "Loser of ";
                            }
                            break;
                    }

                    if (isset($game)) {
                        $teamLabel = isset($team) ? $homeAwayLabel . " " . $team->nameIdWithSeed : $playInLabel . $game->id;
                        print "
                            <th>$teamLabel</th>";
                    } else {
                        print "
                            <th>&nbsp</th>";
                    }
                }

                print "
                        </tr>";
            }

            print "
                    </table><br>
                        </td>
                    </tr>
                </table><br><br>";
        }
    }

    private function printTitleGames($flight)
    {
        $games      = Game::lookupByFlight($flight);
        $titleGames = [];
        foreach ($games as $game) {
            if ($game->title != '') {
                $day                                            = $game->gameTime->gameDate->day;
                $startTime                                      = $game->gameTime->actualStartTime;
                $titleGames[$game->title][$day][$startTime][]   = $game;
            }
        }

        if (count($titleGames) > 0) {
            foreach (GameOrm::$titles as $title) {
                if (!isset($titleGames[$title]))
                {
                    continue;
                }

                print "
                    <table bgcolor='white' valign='top' align='center' width='850' border='1' cellpadding='5' cellspacing='0'>
                        <tr bgcolor='lightskyblue'>
                            <th colspan='8'>$flight->name: $title</th>
                        </tr>
                        <tr bgcolor='lightskyblue'>
                            <th nowrap>Date</th>
                            <th nowrap>Time</th>
                            <th nowrap>Field</th>
                            <th nowrap>Game Id</th>
                            <th nowrap>Home Team</th>
                            <th nowrap>Visiting Team</th>
                            <th nowrap>Score</th>
                            <th nowrap>Winning Team</th>
                        </tr>";

                $gamesByDay = $titleGames[$title];
                ksort($gamesByDay);

                foreach ($gamesByDay as $day => $gamesByTime) {
                    ksort($gamesByTime);

                    foreach ($gamesByTime as $startTime => $games) {
                        foreach ($games as $game) {
                            $date   = $game->gameTime->gameDate->day;
                            $time   = substr($game->gameTime->actualStartTime, 0, 5);
                            $field  = $game->gameTime->field->fullName;

                            $homeLabels     = View_AdminSchedules_Base::getDisplayLabels($game, true);
                            $homeTeamName   = $homeLabels[View_Base::TEAM_ID_COACH_SHORT_NAME];
                            $homeTeamTitle  = "title='" . $homeLabels[View_Base::HOVER_TEXT] . "'";

                            $visitingLabels     = View_AdminSchedules_Base::getDisplayLabels($game, false);
                            $visitingTeamName   = $visitingLabels[View_Base::TEAM_ID_COACH_SHORT_NAME];
                            $visitingTeamTitle  = "title='" . $visitingLabels[View_Base::HOVER_TEXT] . "'";

                            $score              = "&nbsp";
                            $winningTeam        = "&nbsp";
                            $winningTeamTitle   = '';
                            if (isset($game->homeTeamScore)) {
                                $score              = $game->homeTeamScore . " - " . $game->visitingTeamScore;
                                $winningTeam        = $game->homeTeamScore > $game->visitingTeamScore ? $homeTeamName : $visitingTeamName;
                                $winningTeamTitle   = $game->homeTeamScore > $game->visitingTeamScore ? $homeTeamTitle : $visitingTeamTitle;
                            }

                            print "
                    <tr>
                        <td nowrap>$date</td>
                        <td nowrap>$time</td>
                        <td nowrap>$field</td>
                        <td nowrap>$game->id</td>
                        <td nowrap $homeTeamTitle>$homeTeamName</td>
                        <td nowrap $visitingTeamTitle>$visitingTeamName</td>
                        <td nowrap align='center'>$score</td>
                        <td nowrap $winningTeamTitle bgcolor='lightgreen'>$winningTeam</td>
                    </tr>";
                        }
                    }
                }

                print "
                    </table><br>";
            }
        }
    }

    /**
     * @param Game  $game
     * @param bool  $computeForHomeTeam
     * @param bool  $isLeaguePlay defaults to false
     *
     * @return array
     */
    private function getGameStats($game, $computeForHomeTeam, $stats, $isLeaguePlay = false)
    {
        // Initialize if not already initialized
        $stats[self::WINS]          = isset($stats[self::WINS]) ? $stats[self::WINS] : 0;
        $stats[self::LOSSES]        = isset($stats[self::LOSSES]) ? $stats[self::LOSSES] : 0;
        $stats[self::TIES]          = isset($stats[self::TIES]) ? $stats[self::TIES] : 0;
        $stats[self::POINTS]        = isset($stats[self::POINTS]) ? $stats[self::POINTS] : 0;
        $stats[self::GOALS_FOR]     = isset($stats[self::GOALS_FOR]) ? $stats[self::GOALS_FOR] : 0;
        $stats[self::GOALS_AGAINST] = isset($stats[self::GOALS_AGAINST]) ? $stats[self::GOALS_AGAINST] : 0;
        $stats[self::SHUTOUTS]      = isset($stats[self::SHUTOUTS]) ? $stats[self::SHUTOUTS] : 0;
        $stats[self::YELLOWS]       = isset($stats[self::YELLOWS]) ? $stats[self::YELLOWS] : 0;
        $stats[self::REDS]          = isset($stats[self::REDS]) ? $stats[self::REDS] : 0;

        // Update with game data
        if (isset($game->homeTeamScore)) {
            $stats[self::WINS]          += $computeForHomeTeam ? ($game->homeTeamScore > $game->visitingTeamScore ? 1 : 0) : 0;
            $stats[self::WINS]          += !$computeForHomeTeam ? ($game->homeTeamScore < $game->visitingTeamScore ? 1 : 0) : 0;
            $stats[self::LOSSES]        += $computeForHomeTeam ? ($game->homeTeamScore < $game->visitingTeamScore ? 1 : 0) : 0;
            $stats[self::LOSSES]        += !$computeForHomeTeam ? ($game->homeTeamScore > $game->visitingTeamScore ? 1 : 0) : 0;
            $stats[self::TIES]          += $game->homeTeamScore == $game->visitingTeamScore ? 1 : 0;
            $stats[self::POINTS]        += $game->computeGamePoints($computeForHomeTeam, $isLeaguePlay);
            $stats[self::GOALS_FOR]     += $computeForHomeTeam ? $game->homeTeamScore : $game->visitingTeamScore;
            $stats[self::GOALS_AGAINST] += $computeForHomeTeam ? $game->visitingTeamScore : $game->homeTeamScore;
            $stats[self::SHUTOUTS]      += $computeForHomeTeam ? ($game->visitingTeamScore == 0 ? 1 : 0) : ($game->homeTeamScore == 0 ? 1 : 0);
            $stats[self::YELLOWS]       += $computeForHomeTeam ? $game->homeTeamYellowCards : $game->visitingTeamYellowCards;
            $stats[self::REDS]          += $computeForHomeTeam ? $game->homeTeamRedCards : $game->visitingTeamRedCards;
        }

        return $stats;
    }

    /**
     * Game result string is an HTML formatted string.  Example:
     *      2 - 1 W
     *     (2R, 1Y)
     *        4
     *
     * Top line represents the game result.  Bottom line represents the points earned.
     *
     * @param Game  $game
     * @param bool  $computeForHomeTeam
     *
     * @return int  $points for desired team
     */
    private function getGameResultString($game, $computeForHomeTeam)
    {
        $points             = $game->computeGamePoints($computeForHomeTeam);
        $resultString       = "";
        $redCardString      = "";
        $yellowCardString   = "";

        if ($computeForHomeTeam) {
            $resultString       .= $game->homeTeamScore . " - " . $game->visitingTeamScore;
            $redCardString      .= $game->homeTeamRedCards > 0 ? "$game->homeTeamRedCards" . "R" : "";
            $yellowCardString   .= $game->homeTeamYellowCards > 0 ? "$game->homeTeamYellowCards" . "Y" : "";
            $score1             = $game->homeTeamScore;
            $score2             = $game->visitingTeamScore;
        } else {
            $score1             = $game->visitingTeamScore;
            $score2             = $game->homeTeamScore;
            $resultString       .= $game->visitingTeamScore . " - " . $game->homeTeamScore;
            $redCardString      .= $game->visitingTeamRedCards > 0 ? "$game->visitingTeamRedCards" . "R" : "";
            $yellowCardString   .= $game->visitingTeamYellowCards > 0 ? "$game->visitingTeamYellowCards" . "Y" : "";
        }

        $resultString .= $score1 > $score2 ? " W" : ($score2 > $score1 ? " L" : " T");

        if ($redCardString != "" and $yellowCardString != "") {
            $resultString .= "<br>" . "(" . $redCardString . ", " . "$yellowCardString" . ")";
        } else if ($redCardString != "") {
            $resultString .= "<br>" . "(" . $redCardString . ")";
        } else if ($yellowCardString != "") {
            $resultString .= "<br>" . "(" . "$yellowCardString" . ")";
        }

        $resultString .= "<br>" . $points;

        return $resultString;
    }
}