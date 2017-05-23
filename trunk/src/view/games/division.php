<?php

use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\GameTime;
use \DAG\Domain\Schedule\Coach;
use \DAG\Domain\Schedule\Schedule;
use \DAG\Domain\Schedule\Season;
use \DAG\Domain\Schedule\Game;
use \DAG\Domain\Schedule\Flight;

/**
 * @brief Show the Division Schedule Viewing page
 */
class View_Games_Division {
    /**
     * @brief Display schedule
     *
     * @param Season    $season     - Season associated with Schedule
     * @param Schedule  $schedule   - Schedule to be edited
     */
    public static function printSchedule($season, $schedule) {
        $startTime          = $season->startTime;
        $endTime            = $season->endTime;
        $interval           = new \DateInterval("PT" . $schedule->division->gameDurationMinutes . "M");
        $defaultGameTimes   = GameTime::getDefaultGameTimes($startTime, $endTime, $interval);
        $flights            = Flight::lookupBySchedule($schedule);

        foreach ($flights as $flight) {
            $games = Game::lookupByFlight($flight);
            $gamesByPool    = [];
            $poolsById      = [];
            foreach ($games as $game) {
                if (isset($game->pool)) {
                    $poolsById[$game->pool->id]     = $game->pool;
                    $gamesByPool[$game->pool->id][] = $game;
                } else {
                    $gamesByPool[0][] = $game;
                }
            }

            foreach ($gamesByPool as $poolId => $poolGames) {
                // No games for pools with cross-pool play so suppress output
                if (count($poolGames) == 0 or $poolId == 0) {
                    continue;
                }

                $pool                       = $poolsById[$poolId];
                $gamesByDateByFieldByTime   = [];
                foreach ($poolGames as $game) {
                    $day        = $game->gameTime->gameDate->day;
                    $startTime  = $game->gameTime->startTime;
                    $fieldName  = $game->gameTime->field->facility->name . ": " . $game->gameTime->field->name;
                    $gamesByDateByFieldByTime[$day][$fieldName][$startTime] = $game;
                }

                if (isset($gamesByPool[0])) {
                    $games = $gamesByPool[0];
                    foreach ($games as $game) {
                        $day        = $game->gameTime->gameDate->day;
                        $startTime  = $game->gameTime->startTime;
                        $fieldName  = $game->gameTime->field->facility->name . ": " . $game->gameTime->field->name;
                        $gamesByDateByFieldByTime[$day][$fieldName][$startTime] = $game;
                    }
                }
                ksort($gamesByDateByFieldByTime);

                // Print table header
                print "
            <table valign='top' align='center' border='1' cellpadding='5' cellspacing='0' width='900'>";

                $colspan    = 2 + count($defaultGameTimes);
                $headerRow  = $schedule->division->name . ' ' . $schedule->division->gender . ' ' . $pool->flight->name . ": " . $pool->name;
                if ($pool->gamesAgainstPool->id != $pool->id) {
                    $name = $pool->gamesAgainstPool->name;
                    $headerRow .= "<font color='red'> (Cross-pool play with $name)</font>";
                }

                print "
                <thead>
                <tr bgcolor='lightskyblue'>
                    <th colspan='$colspan' align='center'>$headerRow</th>
                </tr>
                <tr bgcolor='lightskyblue'>
                    <th>Day</th>
                    <th>Field</th>";

                foreach ($defaultGameTimes as $gameTime) {
                    $gameTime = ltrim(substr($gameTime, 0, 5), "0");
                    print "
                    <th nowrap>$gameTime</th>";
                }

                print "
                </tr>
                </thead>";


                // Print table rows
                foreach ($gamesByDateByFieldByTime as $day => $gameFieldData) {
                    ksort($gameFieldData);

                    $rowspan = count($gameFieldData);
                    $loopIndex = 0;
                    foreach ($gameFieldData as $fieldName => $gameTimeData) {
                        ksort($gameTimeData);

                        if ($loopIndex == 0) {
                            print "
                        <tr>
                            <td nowrap rowspan='$rowspan'>$day</td>";
                        } else {
                            print "
                        <tr>";
                        }
                        $loopIndex += 1;

                        print "
                    <td nowrap>$fieldName</td>";

                        foreach ($defaultGameTimes as $defaultGameTime) {
                            $entryFound = false;
                            foreach ($gameTimeData as $gameTime => $game) {
                                if ($gameTime == $defaultGameTime) {
                                    if (isset($game->homeTeam)) {
                                        $homeTeamCoach      = Coach::lookupByTeam($game->homeTeam);
                                        $visitingTeamCoach  = Coach::lookupByTeam($game->visitingTeam);
                                        $gameData           = "Game Id: $game->id<br>";
                                        $gameData           .= "H: " . $game->homeTeam->name . ": " . $homeTeamCoach->lastName . "<br>";
                                        $gameData           .= "V: " . $game->visitingTeam->name . ": " . $visitingTeamCoach->lastName;
                                        $title              = "title='" . $homeTeamCoach->name . " vs " . $visitingTeamCoach->name . "'";
                                    } else {
                                        $gameData           = "Game Id: $game->id<br>$game->title";
                                        $title              = "title='$game->title'";
                                    }
                                    $gender             = $game->flight->schedule->division->gender;
                                    $bgHTML             = $gender == 'Boys' ? "bgcolor='lightblue'" : "bgcolor='lightyellow'";

                                    print "
                                    <td nowrap $bgHTML $title>$gameData</td>";
                                    $entryFound = true;
                                }
                            }

                            if (!$entryFound) {
                                print "
                                    <td nowrap>&nbsp</td>";
                            }
                        }

                        print "
                </tr>";
                    }
                }

                print "
            </table><br>";

            }
        }
    }
}