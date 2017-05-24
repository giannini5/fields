<?php

use \DAG\Domain\Schedule\Season;
use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\Team;
use \DAG\Domain\Schedule\Coach;
use \DAG\Domain\Schedule\Schedule;
use \DAG\Domain\Schedule\Game;
use \DAG\Domain\Schedule\Flight;

/**
 * @brief Show the Team Schedule Viewing page
 */
class View_Games_Team
{
    /**
     * @param Season    $season
     * @param int       $filterCoachId  - Coach identifier
     * @param string    $header         - Header to display prior to input request
     * @param string    $page           - Page to render on submit
     * @param int       $sessionId      - Session identifier (if any)
     * @param string    $border         - '0' or '1', defaults to '1' to display border
     * @param string    $method
     * @param string    $command        - default to View_Base::SUBMIT
     * @param null      $scoringType
     */
    public static function printTeamSelectors(
        $season,
        $filterCoachId  = 0,
        $header         = "View Schedules by Team",
        $page           = View_Base::GAMES_SCHEDULE_PAGE,
        $sessionId      = null,
        $border         = '1',
        $method         = 'get',
        $command        = View_Base::SUBMIT,
        $scoringType    = null)
    {
        print "
            <table bgcolor='" . View_Base::VIEW_COLOR . "' valign='top' align='center' border=$border cellpadding='5' cellspacing='0'>
                <tr><td>
                    <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                            <tr>
                                <th nowrap colspan='2' align='left'>$header</th>
                            </tr>
                        <form method='$method' action='" . $page . "'>";

        self::printTeamCoachSelector($season, $filterCoachId);

        // Print Filter button and end form
        $sessionInput       = isset($sessionId) ? "<input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>" : '';
        $scoringTypeInput   = isset($scoringType) ?
            "<input type='hidden' id='". View_Base::SCORING_TYPE . "' name='". View_Base::SCORING_TYPE . "' value='$scoringType'>"
            : '';
        print "
                        <tr>
                            <td align='left'>
                                <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . $command . "'>
                                $scoringTypeInput
                                $sessionInput
                            </td>
                        </tr>
                        </form>
                    </table>
                </td></tr>
            </table><br>";
    }

    /**
     * @brief Print the drop down list of coaches sorted by division for selection
     *
     * @param Season    $season
     * @param int       $filterCoachId - Default selection
     */
    public static function printTeamCoachSelector($season, $filterCoachId)
    {
        $sortedCoaches = [];
        $divisions = [];

        if (isset($season)) {
            $divisions = Division::lookupBySeason($season);
        }

        foreach ($divisions as $division) {
            $teams = Team::lookupByDivision($division);

            foreach ($teams as $team) {
                $coach = Coach::lookupByTeam($team);
                $sortedCoaches[$coach->id] = $team->nameId . ": " . $coach->lastName;
            }
        }

        $selected = $filterCoachId == 0 ? 'selected' : '';
        $selectorHTML = "<option value='' disabled $selected>Select a Team</option>";
        foreach ($sortedCoaches as $id => $name) {
            $selected = ($id == $filterCoachId) ? ' selected ' : '';
            $selectorHTML .= "<option value='$id' $selected>$name</option>";
        }

        print "
                <tr>
                    <td><font color='#069'><b>Team:&nbsp</b></font></td>
                    <td><select name='" . View_Base::FILTER_COACH_ID . "' required>" . $selectorHTML . "</select></td>
                </tr>";
    }

    /**
     * @param Team  $team               - Display schedule for this team
     * @param Coach $coach              - Coach of team or null if not known
     * @param array $gamesByDay         - List of games by day, defaults to null
     * @param array $schedules          - List of schedules, defaults to null
     * @param bool  $showHomeTeamCount  - If true then show the number of home vs away games
     * @param bool  $publishedOnly      - Defaults to true
     */
    public static function printScheduleForTeam($team, $coach = null, $gamesByDay = null, $schedules = null, $showHomeTeamCount = false, $publishedOnly = true)
    {
        $coach      = isset($coach) ? $coach : Coach::lookupByTeam($team);
        $division   = $team->division;
        $schedules  = Schedule::lookupByDivision($division, $publishedOnly);
        $teamName   = $division->name . ": " . $team->name . " (" . $coach->lastName . ")";

        if (!isset($gamesByDay)) {
            // Get games across all schedules for division
            $games = [];
            foreach ($schedules as $schedule) {
                $flights = Flight::lookupBySchedule($schedule);
                foreach ($flights as $flight) {
                    $games = array_merge($games, Game::lookupByFlight($flight));
                }
            }

            // Track games by day, filter by team if displaying for one team
            $gamesByDay = [];
            foreach ($games as $game) {
                if ($game->isForTeam($team)) {
                    $gamesByDay[$game->gameTime->gameDate->day][] = $game;
                }
            }
            ksort($gamesByDay);
        }

        if (count($schedules) == 0) {
            print "<p style='color: red; font-size: medium' align='center'>Schedules have not yet been published for Division: $division->name $division->gender.</p>";
            return;
        } else {
            // Print Schedule header
            print "
            <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0' width='650'>
                <thead>
                    <tr bgcolor='lightskyblue'>
                        <th align='center' colspan='7'>$teamName</th>
                    </tr>
                    <tr bgcolor='lightskyblue'>
                        <th>Date</th>
                        <th>Id</th>
                        <th>Time</th>
                        <th>Field</th>
                        <th>Home Team</th>
                        <th>Visiting Team</th>
                        <th>Result</th>
                    </tr>
                </thead>";

            $gameCount          = 0;
            $homeTeamCount      = 0;
            $visitingTeamCount  = 0;
            $dayCount           = 0;
            foreach ($gamesByDay as $day => $allGames) {
                $gamesByTime    = [];
                $rowSpan        = 0;
                foreach ($allGames as $game) {
                    if ($game->isForTeam($team)) {
                        $gamesByTime[$game->gameTime->startTime][] = $game;
                        $rowSpan += 1;
                    }
                }
                ksort($gamesByTime);

                $dayCount       += 1;
                $dayCellPrinted = false;
                $lastStartTime  = '';
                foreach ($gamesByTime as $startTime => $games) {
                    foreach ($games as $game) {
                        $field              = $game->gameTime->field;
                        $facility           = $field->facility;
                        $startTime          = substr($game->gameTime->startTime, 0, 5);
                        $dayCell            = $dayCellPrinted ? '' : "<td nowrap rowspan='$rowSpan'>$day</td>";
                        $dayCellPrinted     = true;
                        $fieldName          = $facility->name . ": " . $field->name;
                        $homeTeamName       = '';
                        $visitingTeamName   = '';
                        $homeTeamStyle      = '';
                        $visitingTeamStyle  = '';
                        $result             = '';
                        $homeTeamTitle      = '';
                        $visitingTeamTitle  = '';
                        $startTimeBgColor   = self::diffInHours($lastStartTime, $startTime) < 2 ? "bgcolor='red'" : "";
                        $lastStartTime      = $startTime;

                        if (isset($game->homeTeam)) {
                            $homeCoach      = Coach::lookupByTeam($game->homeTeam);
                            $homeTeamName   = $game->homeTeam->nameId . ": " . $homeCoach->lastName;
                            $homeTeamTitle  = "title='" . $game->homeTeam->name . ": " . $game->homeTeam->region . " (" . $game->homeTeam->city . ")'";

                            if ($team->id == $game->homeTeam->id) {
                                $homeTeamCount  += 1;
                                $homeTeamStyle  = "style='color: red'";
                                $result         = self::computeResult($game, true);
                            } else {
                                $visitingTeamCount += 1;
                                $visitingTeamStyle = "style='color: red'";
                                $result         = self::computeResult($game, false);
                            }
                        }

                        if (isset($game->visitingTeam)) {
                            $visitingCoach = Coach::lookupByTeam($game->visitingTeam);
                            $visitingTeamName   = $game->visitingTeam->nameId . ": " . $visitingCoach->lastName;
                            $visitingTeamTitle  = "title='" . $game->visitingTeam->name . ": " . $game->visitingTeam->region . " (" . $game->visitingTeam->city . ")'";
                        }

                        $bgcolor = ($dayCount % 2 == 0) ? "" : "bgcolor='lightgray'";

                        if ($homeTeamName != '') {
                            print "
                            <tr $bgcolor>
                                $dayCell
                                <td>$game->id</td>
                                <td $startTimeBgColor>$startTime</td>
                                <td>$fieldName</td>
                                <td $homeTeamStyle $homeTeamTitle>$homeTeamName</td>
                                <td $visitingTeamStyle $visitingTeamTitle>$visitingTeamName</td>
                                <td>$result</td>
                            </tr>";
                        } else {
                            print "
                            <tr $bgcolor>
                                $dayCell
                                <td>$game->id</td>
                                <td>$startTime</td>
                                <td>$fieldName</td>
                                <td colspan='2'>$game->title</td>
                                <td>$result</td>
                            </tr>";
                        }

                        $gameCount += 1;
                    }
                }
            }

            if ($showHomeTeamCount) {
                print "
                <tr>
                    <td colspan='7'>Home Games: $homeTeamCount, Visiting Games: $visitingTeamCount</td>
                </tr>";
            }

            print "
            </table><br>";
        }
    }

    /**
     * @param Game  $game
     * @param bool  $isHomeTeam - True if computing result for homeTeam
     *
     * @return string  Result of game represented as a string
     */
    static private function computeResult($game, $isHomeTeam)
    {
        $result = '';
        if (!isset($game->homeTeamScore)) {
            return $result;
        }

        if (!isset($game->visitingTeamScore)) {
            return $result;
        }

        if ($isHomeTeam) {
            if ($game->homeTeamScore > $game->visitingTeamScore) {
                $result = "Win: $game->homeTeamScore - $game->visitingTeamScore";
            } elseif ($game->homeTeamScore < $game->visitingTeamScore) {
                $result = "Loss: $game->homeTeamScore - $game->visitingTeamScore";
            } else {
                $result = "Tie: $game->homeTeamScore - $game->visitingTeamScore";
            }
        } else {
            if ($game->homeTeamScore > $game->visitingTeamScore) {
                $result = "Loss: $game->homeTeamScore - $game->visitingTeamScore";
            } elseif ($game->homeTeamScore < $game->visitingTeamScore) {
                $result = "Win: $game->homeTeamScore - $game->visitingTeamScore";
            } else {
                $result = "Tie: $game->homeTeamScore - $game->visitingTeamScore";
            }
        }

        return $result;
    }

    /**
     * @param string    $startTime  - Of the form HH:MM:SS
     * @param string    $endTime    - Of the form HH:MM:SS
     * @return int      hours between startTime and endTime
     */
    static private function diffInHours($startTime, $endTime)
    {
        date_default_timezone_set('America/Los_Angeles');
        $startDate  = new DateTime("2017-01-01T$startTime");
        $endDate    = new DateTime("2017-01-01T$endTime");

        $diff = $endDate->diff($startDate);

        return $diff->h;
    }
}