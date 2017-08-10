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
     * @param $controller - Controller that contains data used when rendering this view.
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
        $divisionsSelector = $this->getDivisionsSelector(true, false, true);

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
        $schedules          = Schedule::lookupByDivision($division);

        foreach ($schedules as $schedule) {
            switch ($schedule->scheduleType) {
                case ScheduleOrm::SCHEDULE_TYPE_LEAGUE:
                    $this->printStandingsForLeaguePlay($division, $schedule);
                    break;

                case ScheduleOrm::SCHEDULE_TYPE_TOURNAMENT:
                default:
                    $this->printStandingsForTournamentPlay($division, $schedule);
                    break;
            }
        }
    }

    /**
     * @param Division  $division
     * @param Schedule  $schedule
     */
    private function printStandingsForLeaguePlay($division, $schedule)
    {
        $standingsPrinted   = false;

        $flights    = Flight::lookupBySchedule($schedule);
        $teams      = [];
        $teamStats  = [];
        $teamPoints = [];

        foreach ($flights as $flight) {
            $games = Game::lookupByFlight($flight);

            foreach ($games as $game) {
                if (isset($game->homeTeam) and empty($game->title)) {
                    $team                   = $game->homeTeam;
                    $teams[$team->id]       = $team;
                    $stats                  = isset($teamStats[$team->id]) ? $teamStats[$team->id] : [];
                    $stats                  = $this->getGameStats($game, true, $stats);
                    $teamStats[$team->id]   = $stats;
                    $teamPoints[$team->id]  = $stats[self::POINTS];

                    $team                   = $game->visitingTeam;
                    $teams[$team->id]       = $team;
                    $stats                  = isset($teamStats[$team->id]) ? $teamStats[$team->id] : [];
                    $stats                  = $this->getGameStats($game, false, $stats);
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
                <table valign='top' align='center' width='800' border='0' cellpadding='5' cellspacing='0'>
                    <tr>
                        <td valign='top'>";

            $pools = Pool::lookupByFlight($flight);
            foreach ($pools as $pool) {
                print "
                    <table valign='top' align='center' width='800' border='1' cellpadding='5' cellspacing='0'>
                        <tr bgcolor='lightskyblue'>
                            <th colspan='12'>$flight->name: $pool->name</th>
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
                    if ($teams[$teamId]->pool->id == $pool->id) {
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
    }

    /**
     * @param Division  $division
     * @param Schedule  $schedule
     */
    private function printStandingsForTournamentPlay($division, $schedule)
    {
        $flights = Flight::lookupBySchedule($schedule);

        foreach ($flights as $flight) {
            // Skip flights where no games are scheduled
            if ($flight->scheduleGames != 1) {
                continue;
            }

            $pools = Pool::lookupByFlight($flight);

            print "
            <table bgcolor='lightgray' valign='top' align='center' width='800' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td>";

            foreach ($pools as $pool) {
                print "
                    <table bgcolor='white' valign='top' align='center' width='800' border='1' cellpadding='5' cellspacing='0'>
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
                        // Skip Title Games
                        if ($game->title != '') {
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

    private function printTitleGames($flight)
    {
        $games = Game::lookupByFlight($flight);
        $titleGames = [];
        foreach ($games as $game) {
            if ($game->title != '') {
                $titleGames[$game->title][] = $game;
            }
        }

        if (count($titleGames) > 0) {
            foreach (GameOrm::$titles as $title) {
                if (!isset($titleGames[$title]))
                {
                    continue;
                }
                $games = $titleGames[$title];

                print "
                    <table bgcolor='white' valign='top' align='center' width='800' border='1' cellpadding='5' cellspacing='0'>
                        <tr bgcolor='lightskyblue'>
                            <th colspan='7'>$flight->name: $title</th>
                        </tr>
                        <tr bgcolor='lightskyblue'>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Field</th>
                            <th>Home Team</th>
                            <th>Visiting Team</th>
                            <th>Score</th>
                            <th>Winning Team</th>
                        </tr>";

                foreach ($games as $game) {
                    $date               = $game->gameTime->gameDate->day;
                    $time               = substr($game->gameTime->startTime, 0, 5);
                    $field              = $game->gameTime->field->fullName;
                    $homeTeamName       = "&nbsp";
                    $visitingTeamName   = "&nbsp";
                    $homeTeamTitle      = '';
                    $visitingTeamTitle  = '';
                    $score              = "&nbsp";
                    $winningTeam        = "&nbsp";
                    $winningTeamTitle   = '';
                    if (isset($game->homeTeam)) {
                        $team           = $game->homeTeam;
                        $coach          = Coach::lookupByTeam($team);
                        $homeTeamName   = $team->nameId . ": " . $coach->shortName;
                        $homeTeamTitle  = "title='" . $team->name . " " . $team->region . " (" . $team->city . ")'";

                        $team               = $game->visitingTeam;
                        $coach              = Coach::lookupByTeam($team);
                        $visitingTeamName   = $team->nameId . ": " . $coach->shortName;
                        $visitingTeamTitle  = "title='" . $team->name . " " . $team->region . " (" . $team->city . ")'";

                        $score          = '';
                        $winningTeam    = '';
                        if (isset($game->homeTeamScore)) {
                            $score              = $game->homeTeamScore . " - " . $game->visitingTeamScore;
                            $winningTeam        = $game->homeTeamScore > $game->visitingTeamScore ? $homeTeamName : $visitingTeamName;
                            $winningTeamTitle   = "title='" . $team->name . " " . $team->region . " (" . $team->city . ")'";
                        }
                    }
                    print "
                        <tr>
                            <td>$date</td>
                            <td>$time</td>
                            <td>$field</td>
                            <td $homeTeamTitle>$homeTeamName</td>
                            <td $visitingTeamTitle>$visitingTeamName</td>
                            <td align='center'>$score</td>
                            <td $winningTeamTitle bgcolor='lightgreen'>$winningTeam</td>
                        </tr>";
                }

                print "
                    </table><br>";
            }
        }
    }

    /**
     * @param Game  $game
     * @param bool  $computeForHomeTeam
     * @return array
     */
    private function getGameStats($game, $computeForHomeTeam, $stats)
    {
        $team = $computeForHomeTeam ? $game->homeTeam : $game->visitingTeam;

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
            $stats[self::POINTS]        += $game->computeGamePoints($computeForHomeTeam);
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