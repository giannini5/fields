<?php

use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\Schedule;
use \DAG\Domain\Schedule\Flight;
use \DAG\Domain\Schedule\Pool;
use \DAG\Domain\Schedule\Coach;
use \DAG\Domain\Schedule\Team;

/**
 * @brief Show the Flights Viewing page
 */
class View_Games_Flights extends View_Games_Base
{
    /**
     * @brief Construct he View
     *
     * @param Controller_Base $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller)
    {
        parent::__construct(self::GAMES_FLIGHTS_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render()
    {
        $divisions = Division::lookupBySeason($this->m_controller->m_season);
        foreach ($divisions as $division) {
            $this->printFlightsForDivision($division);
        }
    }

    /**
     * @param Division  $division
     */
    private function printFlightsForDivision($division)
    {
        $flightColors       = ['red', 'blue', 'green', 'black'];

        print "
            <h1 align='center'>$division->name $division->gender</h1>";

        $schedules  = Schedule::lookupByDivision($division, true);
        if (count($schedules) == 0) {
            print "
            <p align='center' style='color:red'>Schedules for this division have not been published.  Come back later.</p>";
        }

        foreach ($schedules as $schedule) {
            $flightColorIndex   = -1;
            $flights    = Flight::lookupBySchedule($schedule);
            foreach ($flights as $flight) {
                // Skip flights where game scheduling is disabled
                if ($flight->scheduleGames != 1) {
                    continue;
                }

                // Skip if no teams in flight
                $teams = [];
                $pools = Pool::lookupByFlight($flight);
                foreach ($pools as $pool) {
                    $teams = Team::lookupByPool($pool);
                    if (count($teams) > 0) {
                        break;
                    }
                }

                if (count($teams) == 0) {
                    // No teams found for flight
                    continue;
                }

                $flightColorIndex = ($flightColorIndex + 1) % count($flightColors);

                print "
                <table bgcolor='lightgray' valign='top' align='center' width='700' border='0' cellpadding='5' cellspacing='0'>
                    <tr>
                        <th bgcolor='lightgray' align='center' style='font-size:16px'>Flight $flight->name</th>
                     </tr>
                     <tr>
                        <td>";

                $pools = Pool::lookupByFlight($flight);
                foreach ($pools as $pool) {
                    $teams          = Team::lookupByPool($pool);
                    $flightColor    = $flightColors[$flightColorIndex];
                    $playType       = count($teams) == 5 ? "(round robin play)" : "";

                    print "
                        <table bgcolor='white' valign='top' align='center' width='700' border='1' cellpadding='5' cellspacing='0'>
                            <tr>
                                <th colspan='5' bgcolor='$flightColor' style='color:white'>$pool->name $playType</th>
                            </tr>
                            <tr>
                                <th>Team Id</th>
                                <th>Region</th>
                                <th>Team Name</th>
                                <th>City</th>
                                <th>Coach</th>
                            </tr>";

                    foreach ($teams as $team) {
                        $coach = Coach::lookupByTeam($team);

                        print "
                            <tr>
                                <td align='center'>$team->nameId</td>
                                <td align='center'>$team->region</td>
                                <td align='center'>$team->name</td>
                                <td align='center'>$team->city</td>
                                <td align='center'>$coach->lastName</td>
                            </tr>";
                    }
                    print "
                        </table>";
                }

                print "
                        </td>
                    </tr>
                </table>";
            }
        }
    }
}