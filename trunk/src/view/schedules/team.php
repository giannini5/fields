<?php

use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\Team;
use \DAG\Domain\Schedule\Coach;
use \DAG\Domain\Schedule\Schedule;
use \DAG\Domain\Schedule\Pool;
use \DAG\Domain\Schedule\Game;

/**
 * @brief Show the Team Schedule Viewing page
 */
class View_Schedules_Team extends View_Schedules_Base
{
    /**
     * @brief Construct he View
     *
     * @param $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller)
    {
        parent::__construct(self::SCHEDULE_TEAM_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        $this->printTeamSelectors($this->m_controller->m_filterCoachId);

        if ($this->m_controller->m_filterCoachId != 0) {
            $this->printScheduleForTeam($this->m_controller->m_filterCoachId);
        }
    }

    /**
     * @param int $filterCoachId - Coach identifier
     */
    private function printTeamSelectors($filterCoachId)
    {
        // <form method='post' action='" . self::SCHEDULE_TEAM_PAGE . $this->m_urlParams . "'>";
        print "
            <table bgcolor='" . View_Base::VIEW_COLOR . "' valign='top' align='center' border='1' cellpadding='5' cellspacing='0'>
                <tr><td>
                    <table valign='top' align='center' border='0' cellpadding='5' cellspacing='0'>
                            <tr>
                                <th nowrap colspan='2' align='left'>View Schedules by Team</th>
                            </tr>
                        <form method='get' action='" . self::SCHEDULE_TEAM_PAGE . "'>";

        $this->printTeamCoachSelector($filterCoachId);

        // Print Filter button and end form
        print "
                        <tr>
                            <td align='left'>
                                <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::SUBMIT . "'>
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
     * @param int $filterCoachId - Default selection
     */
    public function printTeamCoachSelector($filterCoachId)
    {
        $sortedCoaches = [];
        $divisions = [];

        if (isset($this->m_controller->m_season)) {
            $divisions = Division::lookupBySeason($this->m_controller->m_season);
        }

        foreach ($divisions as $division) {
            $teams = Team::lookupByDivision($division);

            foreach ($teams as $team) {
                $coach = Coach::lookupByTeam($team);
                $sortedCoaches[$coach->id] = $team->name . ": " . $coach->lastName;
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
     * @param int $filterCoachId - Coach identifier
     */
    private function printScheduleForTeam($filterCoachId)
    {
        $coach      = Coach::lookupById($filterCoachId);
        $team       = $coach->team;
        $division   = $team->division;
        $schedules  = Schedule::lookupByDivision($division, true);
        $teamName   = $division->name . ": " . $team->name . " (" . $coach->lastName . ")";

        if (count($schedules) == 0) {
            print "<p style='color: red; font-size: medium' align='center'>Schedules have not yet been published for Division: $division->name $division->gender.</p>";
        } else {
            foreach ($schedules as $schedule) {
                // Print Schedule header
                print "
            <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0' width='600'>
                <thead>
                    <tr bgcolor='lightskyblue'>
                        <th align='center' colspan='5'>$teamName</th>
                    </tr>
                    <tr bgcolor='lightskyblue'>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Field</th>
                        <th>Home Team</th>
                        <th>Visiting Team</th>
                    </tr>
                </thead>";

                $gameCount = 0;
                $pools = Pool::lookupBySchedule($schedule);
                foreach ($pools as $pool) {
                    $games = Game::lookupByPool($pool);
                    foreach ($games as $game) {
                        if ($game->homeTeam->id != $team->id and $game->visitingTeam->id != $team->id) {
                            continue;
                        }

                        $day                = $game->gameTime->gameDate->day;
                        $field              = $game->gameTime->field;
                        $facility           = $field->facility;
                        $fieldName          = $facility->name . ": " . $field->name;
                        $homeCoach          = Coach::lookupByTeam($game->homeTeam);
                        $visitingCoach      = Coach::lookupByTeam($game->visitingTeam);
                        $homeTeamName       = $game->homeTeam->name . ": " . $homeCoach->lastName;
                        $visitingTeamName   = $game->visitingTeam->name . ": " . $visitingCoach->lastName;
                        $startTime          = $game->gameTime->startTime;

                        $homeTeamStyle      = '';
                        $visitingTeamStyle  = '';
                        if ($team->name == $game->homeTeam->name) {
                            $homeTeamStyle = "style='color: red'";
                        } else {
                            $visitingTeamStyle = "style='color: red'";
                        }

                        $bgcolor = ($gameCount % 2 == 0) ? "" : "bgcolor='lightgray'";

                        print "
                    <tr $bgcolor>
                        <td>$day</td>
                        <td>$startTime</td>
                        <td>$fieldName</td>
                        <td $homeTeamStyle>$homeTeamName</td>
                        <td $visitingTeamStyle>$visitingTeamName</td>
                    </tr>";

                        $gameCount += 1;
                    }
                }

                print "
            </table><br>";
            }
        }
    }
}