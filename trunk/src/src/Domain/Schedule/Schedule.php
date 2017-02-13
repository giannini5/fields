<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Orm\Schedule\ScheduleOrm;
use DAG\Framework\Exception\Precondition;
use DAG\Framework\Exception\Assertion;


/**
 * @property int        $id
 * @property Division   $division
 * @property string     $name
 * @property string     $scheduleType
 * @property int        $gamesPerTeam
 * @property string     $startDate
 * @property string     $endDate
 * @property string     $startTime
 * @property string     $endTime
 * @property string     $daysOfWeek
 * @property int        $published
 */
class Schedule extends Domain
{
    /** @var ScheduleOrm */
    private $scheduleOrm;

    /** @var Division */
    private $division;

    /**
     * @param ScheduleOrm   $scheduleOrm
     * @param Division      $division (defaults to null)
     */
    protected function __construct(ScheduleOrm $scheduleOrm, $division = null)
    {
        $this->scheduleOrm = $scheduleOrm;
        $this->division = isset($division) ? $division : Division::lookupById($scheduleOrm->divisionId);
    }

    /**
     * @param Division  $division
     * @param string    $name
     * @param string    $scheduleType
     * @param int       $gamesPerTeam
     * @param string    $startDate (SQL Format)
     * @param string    $endDate (SQL Format)
     * @param string    $startTime (SQL Format)
     * @param string    $endTime (SQL Format)
     * @param string    $daysOfWeek defaults to "0000011" (Saturday, Sunday only)
     * @param int       $published defaults to 0
     *
     * @return Schedule
     */
    public static function create(
        $division,
        $name,
        $scheduleType,
        $gamesPerTeam,
        $startDate,
        $endDate,
        $startTime,
        $endTime,
        $daysOfWeek = "0000011",
        $published  = 0
    ) {
        self::verifyDate($startDate, $division->season, 'StartDate');
        self::verifyDate($endDate, $division->season, 'EndDate');
        self::verifyTime($startTime, $division->season, 'StartTime');
        self::verifyTime($endTime, $division->season, 'EndTime');
        self::verifyDaysOfWeek($daysOfWeek, $division->season);

        $scheduleOrm = ScheduleOrm::create($division->id, $name, $scheduleType, $gamesPerTeam, $startDate, $endDate, $startTime, $endTime, $daysOfWeek, $published);
        return new static($scheduleOrm, $division);
    }

    /**
     * @param int $scheduleId
     *
     * @return Schedule
     */
    public static function lookupById($scheduleId)
    {
        $scheduleOrm = ScheduleOrm::loadById($scheduleId);
        return new static($scheduleOrm);
    }

    /**
     * @param Division  $division
     * @param string    $name
     *
     * @return Schedule
     */
    public static function lookupByName($division, $name)
    {
        Precondition::isNonEmpty($name, 'name should not be empty');
        $scheduleOrm = ScheduleOrm::loadByDivisionIdAndName($division->id, $name);
        return new static($scheduleOrm, null, $division);
    }

    /**
     * @param Division  $division
     * @param bool      $publishedOnly defaults to false.  When true, only published schedules are returned
     *
     * @return Schedule[]
     */
    public static function lookupByDivision($division, $publishedOnly = false)
    {
        $schedules = [];

        $scheduleOrms = ScheduleOrm::loadByDivisionId($division->id);
        foreach ($scheduleOrms as $scheduleOrm) {
            $schedule = new static($scheduleOrm, $division);
            if (!$publishedOnly or $schedule->published == 1) {
                $schedules[] = $schedule;
            }
        }

        return $schedules;
    }

    /**
     * @param $propertyName
     * @return int|string
     */
    public function __get($propertyName)
    {
        switch ($propertyName) {
            case "id":
            case "name":
            case "scheduleType":
            case "gamesPerTeam":
            case "startDate":
            case "endDate":
            case "startTime":
            case "endTime":
            case "daysOfWeek":
            case "published":
                return $this->scheduleOrm->{$propertyName};

            case "division":
                return $this->{$propertyName};

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
        }
    }

    /**
     * @param $propertyName
     * @param $value
     * @return int|string
     */
    public function __set($propertyName, $value)
    {
        switch ($propertyName) {
            case "name":
            case "scheduleType":
            case "gamesPerTeam":
            case "published":
                $this->scheduleOrm->{$propertyName} = $value;
                $this->scheduleOrm->save();
                break;

            case "startDate":
                self::verifyDate($value, $this->division->season, 'StartDate');
                $this->scheduleOrm->{$propertyName} = $value;
                $this->scheduleOrm->save();
                break;

            case "endDate":
                self::verifyDate($value, $this->division->season, 'StartDate');
                $this->scheduleOrm->{$propertyName} = $value;
                $this->scheduleOrm->save();
                break;

            case "startTime":
                self::verifyTime($value, $this->division->season, 'StartTime');
                $this->scheduleOrm->{$propertyName} = $value;
                $this->scheduleOrm->save();
                break;

            case "endTime":
                self::verifyTime($value, $this->division->season, 'EndTime');
                $this->scheduleOrm->{$propertyName} = $value;
                $this->scheduleOrm->save();
                break;

            case "daysOfWeek":
                self::verifyDaysOfWeek($value, $this->division->season);
                $this->scheduleOrm->{$propertyName} = $value;
                $this->scheduleOrm->save();
                break;

            default:
                Precondition::isTrue(false, "Set not allowed for property: $propertyName");

        }
    }

    /**
     * @param $value
     * @param $season
     * @param $label
     */
    static private function verifyDate($value, $season, $label)
    {
        Precondition::isTrue($value >= $season->startDate, "$label '$value' must be >= to Season's startDate '$season->startDate'");
        Precondition::isTrue($value <= $season->endDate, "$label '$value' must be <= to Season's endDate '$season->endDate'");
    }

    /**
     * @param $value
     * @param $season
     * @param $label
     */
    static private function verifyTime($value, $season, $label)
    {
        // Normalize the time
        $seconds = strlen($value) >= 8 ? "" : ":00";
        $dateTime = \DateTime::createFromFormat('Y-m-d H:i:s', "2015-06-01 $value" . $seconds);
        $value = $dateTime->format('H:i:s');

        Precondition::isTrue($value >= $season->startTime, "$label '$value' must be >= to Season's startTime '$season->startTime'");
        Precondition::isTrue($value <= $season->endTime, "$label '$value' must be <= to Season's endTime '$season->endTime'");
    }

    /**
     * @param $value
     * @param $season
     */
    static private function verifyDaysOfWeek($value, $season)
    {
        for ($i = 0; $i < 7; $i++) {
            if ($value[$i] != 0) {
                Precondition::isTrue($value[$i] == $season->daysOfWeek[$i],
                    "DaysOfWeek must be a subset of the Season's daysOfWeek");
            }
        }
    }

    /**
     * Populate Pools
     */
    public function populatePools()
    {
        $teams              = Team::lookupByDivision($this->division);
        $numberOfTeams      = count($teams);
        $crossPoolSettings  = [];
        $poolSizes          = $this->getPoolSizes($this->division->name, $numberOfTeams, $crossPoolSettings);
        $numberOfPools      = count($poolSizes);
        $pools              = [];

        // Create Flights and Pools and add teams to pools (2 pools per flight)
        $teamIndex      = 0;
        $flightIndex    = 0;
        $flight         = null;
        for ($index = 0; $index < $numberOfPools; $index++) {
            // Create Flight (or re-use previousFlight)
            if ($index % 2 == 0) {
                $flightIndex += 1;
                $flightName = "$flightIndex";
                $flight = Flight::create($this, $flightName);
            }

            // Create Pool
            $poolName   = sprintf("Pool %s", chr(ord('A') + $index));
            $pool       = Pool::create($flight, $this, $poolName);
            $pools[]    = $pool;

            // Add Teams to pool
            $teamsInPool = $poolSizes[$index];
            while ($teamsInPool > 0 and $teamIndex <= $numberOfTeams) {
                $team       = $teams[$teamIndex++];
                $team->pool = $pool;
                $teamsInPool -= 1;
            }
        }

        // Configure cross-pool play settings
        foreach ($crossPoolSettings as $poolIndex => $crossPoolIndex) {
            if (isset($crossPoolIndex)) {
                Assertion::isTrue($poolIndex != $crossPoolIndex, "Cross-pool indexing bug - See Dave for help!, $poolIndex, $crossPoolIndex");
                Assertion::isTrue($poolIndex < count($pools), "Out-of-range poolIndex: $poolIndex");
                Assertion::isTrue($crossPoolIndex < count($pools), "Out-of-range crossPoolIndex: $crossPoolIndex");

                $pools[$poolIndex]->gamesAgainstPool = $pools[$crossPoolIndex];
            }
        }
    }

    /**
     * Populate Games
     */
    public function populateGames()
    {
        // TODO add logic for non-matching team count in cross-pool play (second game per day for one team)
        // TODO add logic to have carp teams always play in carp
        // TODO add logic to use specific fields based on picture day
        $divisionFields         = DivisionField::lookupByDivision($this->division);
        $processedPoolIds       = [];
        $season                 = $this->division->season;
        $flights                = Flight::lookupBySchedule($this);

        foreach ($flights as $flight) {
            $pools = Pool::lookupByFlight($flight);
            foreach ($pools as $pool) {
                // With cross-pool play, games may have already been created for this pool.  Check and skip if so.
                if (in_array($pool->id, $processedPoolIds)) {
                    continue;
                }
                $processedPoolIds[] = $pool->id;

                $gameDates      = GameDate::lookupBySeason($this->division->season, GameDate::SATURDAYS_ONLY, $this);
                $triedSundays   = false;
                $gameDateIndex  = 0;
                $teams          = Team::lookupByPool($pool);
                $poolType       = TeamPolygon::ROUND_ROBIN_EVEN;
                $crossPoolTeams = null;
                $shiftCount     = 0;

                if ($pool->gamesAgainstPool->id != $pool->id) {
                    $poolType           = TeamPolygon::CROSS_POOL_EVEN;
                    $crossPoolTeams     = Team::lookupByPool($pool->gamesAgainstPool);;
                    $processedPoolIds[] = $pool->gamesAgainstPool->id;
                } elseif (count($teams) % 2 != 0) {
                    $poolType           = TeamPolygon::ROUND_ROBIN_ODD;
                }

                $teamPolygon    = new TeamPolygon($teams, $poolType, $crossPoolTeams);


                // Right now everyone plays on saturdays and overflow games to adhere to game count happen on Sundays
                // Might need to change this to everyone plays on the same weekend if a division does not have enough
                // Field space for a single days worth of games.
                for ($gameNumber = 1; $gameNumber <= $this->gamesPerTeam; $gameNumber++) {
                    $gender                 = $this->division->gender;
                    $divisionFieldsIndex    = 0;

                    if ($gameDateIndex >= count($gameDates) and !$triedSundays) {
                        $triedSundays           = true;
                        $gameDates              = GameDate::lookupBySeason($this->division->season, GameDate::SUNDAYS_ONLY, $this);
                        $gameDateIndex          = 0;
                    }
                    // print "GameDate[$gameDateIndex]: " . $gameDates[$gameDateIndex]->day . "<br>";
                    Assertion::isTrue($gameDateIndex < count($gameDates), "Ran out of games dates - need to add more dates or write code to allow more than one game per day");

                    $gameDate       = $gameDates[$gameDateIndex];
                    $teamPairings   = $teamPolygon->getTeamPairings();
                    $gameTimes      = GameTime::lookupByGameDateAndFieldAndGender($gameDate, $divisionFields[$divisionFieldsIndex]->field, $gender, true, $this->startTime, $this->endTime);

                    $anyGamesAddedForThisDate   = false;
                    $triedOppositeGender        = false;
                    foreach ($teamPairings as $team1Index => $team2Index) {
                        // Find next available gameTime for division/field
                        while (count($gameTimes) == 0) {
                            $divisionFieldsIndex += 1;
                            if (!$anyGamesAddedForThisDate and $divisionFieldsIndex >= count($divisionFields)) {
                                break;
                            } elseif (!$triedOppositeGender and $divisionFieldsIndex >= count($divisionFields)) {
                                $triedOppositeGender = true;
                                $divisionFieldsIndex = 0;
                                $gender = $gender == 'Girls' ? 'Boys' : 'Girls';
                            }
                            Assertion::isTrue($divisionFieldsIndex < count($divisionFields), "Ran out of field space - Either configure more field space or update this program to allow teams to play on same weekend instead of same day.");
                            $gameTimes = GameTime::lookupByGameDateAndFieldAndGender($gameDate, $divisionFields[$divisionFieldsIndex]->field, $gender, true, $this->startTime, $this->endTime);
                        }

                        if (!$anyGamesAddedForThisDate and $divisionFieldsIndex >= count($divisionFields)) {
                            break;
                        }

                        Assertion::isTrue(count($gameTimes) > 0, "Uh, major logic problem.  Contact Dave!");
                        $selectedGameTimes = array_splice($gameTimes, rand(0, count($gameTimes) - 1), 1);

                        Assertion::isTrue(count($selectedGameTimes) == 1, "Uh, major logic problem using array_splice.  Contact Dave!");
                        $gameTime = $selectedGameTimes[0];

                        // Set the home team and visiting team based on the pool type
                        $homeTeam       = null;
                        $visitingTeam   = null;
                        switch ($poolType) {
                            case TeamPolygon::ROUND_ROBIN_EVEN:
                            case TeamPolygon::ROUND_ROBIN_ODD:
                                if ((rand() % 2) == 0) {
                                    $homeTeam       = $teams[$team1Index];
                                    $visitingTeam   = $teams[$team2Index];
                                } else {
                                    $homeTeam       = $teams[$team2Index];
                                    $visitingTeam   = $teams[$team1Index];
                                }
                                break;

                            case TeamPolygon::CROSS_POOL_EVEN:
                                if ($shiftCount % 2 == 0) {
                                    $homeTeam       = $teams[$team1Index];
                                    $visitingTeam   = $crossPoolTeams[$team2Index];
                                } else {
                                    $homeTeam       = $crossPoolTeams[$team2Index];
                                    $visitingTeam   = $teams[$team1Index];
                                }
                                break;

                            default:
                                Precondition::isTrue(false, "$poolType is not yet supported");
                        }
                        Assertion::isTrue(isset($homeTeam), "Major bug, homeTeam did not get set - See Dave");
                        Assertion::isTrue(isset($visitingTeam), "Major bug, visitingTeam did not get set - See Dave");

                        // Create the game
                        $game = Game::create($flight, $pool, $gameTime, $homeTeam, $visitingTeam);
                        $anyGamesAddedForThisDate = true;

                        // Create the familyGame to help find game overlaps for a family
                        $coach = Coach::lookupByTeam($homeTeam);
                        $family = null;
                        if (Family::findByPhone($season, $coach->phone1, $family)) {
                            FamilyGame::create($family, $game, true);
                        }

                        $family = null;
                        $coach = Coach::lookupByTeam($visitingTeam);
                        if (Family::findByPhone($season, $coach->phone1, $family)) {
                            FamilyGame::create($family, $game, true);
                        }

                        // Adjust the game number based on pool type.  ODD pools play an extra game each round
                        if ($poolType == TeamPolygon::ROUND_ROBIN_ODD and $gameNumber % count($teams) == 0) {
                            $gameNumber += 1;
                        }
                    }

                    if (!$anyGamesAddedForThisDate and $divisionFieldsIndex >= count($divisionFields)) {
                        // Appears games have been canceled for this game day.
                        // Reset the gameNumber and try again with next gameDate
                        $gameNumber     -= 1;
                        $gameDateIndex  += 1;
                        continue;
                    }

                    $teamPolygon->shift();
                    $shiftCount     += 1;
                    $gameDateIndex  += 1;
                }
            }
        }
    }

    /**
     *  Get pool sizes based on number of teams and division
     *
     * @param string    $divisionName
     * @param int       $numberOfTeams
     * @param int[]     $crossPoolSettings - Array poolIndex1 => poolIndex2 related to $poolSizes return attribute
     *
     * @return array $poolSizes
     */
    public function getPoolSizes($divisionName, $numberOfTeams, &$crossPoolSettings)
    {
        $poolSizes          = [];

        switch ($divisionName) {
            case 'U5':
            case 'U6':
            case 'U7':
            case 'U8':
                return array(1000);

            default:
                switch ($numberOfTeams) {
                    case 1:
                    case 2:
                    case 3:
                    case 4:
                    case 5:
                    case 6:
                    case 7:
                    case 8:
                        $poolSizes[] = $numberOfTeams;
                        break;
                    case 9:
                        $poolSizes          = array(5, 4);  // Cross pool play w/ second game???
                        $crossPoolSettings  = array(1);
                        break;
                    case 10:
                        $poolSizes          = array(5, 5);  // Cross pool play
                        $crossPoolSettings  = array(1);
                        break;
                    case 11:
                        $poolSizes          = array(5, 6);  // Cross pool play
                        $crossPoolSettings  = array(1);
                        break;
                    case 12:
                        $poolSizes = array(6, 6);
                        break;
                    case 13:
                        $poolSizes = array(7, 6);  // Second game for pool 1
                        break;
                    case 14:
                        $poolSizes = array(8, 6);
                        break;
                    case 15:
                        $poolSizes = array(8, 7);  // Second game for second pool
                        break;
                    case 16:
                        $poolSizes = array(8, 8);
                        break;
                    case 17:
                        $poolSizes          = array(6, 5, 6); // Cross pool for pools 2 and 3
                        $crossPoolSettings  = array(null, 2);
                        break;
                    case 18:
                        $poolSizes = array(6, 6, 6);
                        break;
                    case 19:
                        $poolSizes = array(7, 6, 6); // Second game for pool 1
                        break;
                    case 20:
                        $poolSizes = array(8, 6, 6);
                        break;
                    case 21:
                        $poolSizes = array(8, 6, 7); // Second game for pool 3
                        break;
                    case 22:
                        $poolSizes          = array(6, 6, 5, 5);    // Cross pool play for pools 3 and 4
                        $crossPoolSettings  = array(null, null, 3); // Pool 2 plays pool 3 and vice versa
                        break;
                    case 23:
                        $poolSizes          = array(6, 6, 5, 6);    // Cross pool play for pools 3 and 4 w/ second game
                        $crossPoolSettings  = array(null, null, 3);
                        break;
                    case 24:
                        $poolSizes = array(6, 6, 6, 6);
                        break;
                    case 25:
                        $poolSizes = array(7, 6, 6, 6); // Second game for pool 1
                        break;
                    case 26:
                        $poolSizes = array(8, 6, 6, 6);
                        break;
                    case 27:
                        $poolSizes = array(8, 6, 6, 7); // Second game for pool 4
                        break;
                    case 28:
                        $poolSizes          = array(6, 6, 5, 5, 6); // Cross pool play for pools 3 and 4 w/ second game
                        $crossPoolSettings  = array(null, null, 3);
                        break;
                    case 29:
                        $poolSizes          = array(6, 6, 5, 6, 6); // Cross pool play for pools 3 and 4 w/ second game
                        $crossPoolSettings  = array(null, null, 3);
                        break;
                    case 30:
                        $poolSizes = array(6, 6, 6, 6, 6);
                        break;
                    case 31:
                        $poolSizes = array(7, 6, 6, 6, 6); // Second game for pool 1
                        break;
                    case 32:
                        $poolSizes = array(8, 6, 6, 6, 6);
                        break;

                    default:
                        Assertion::isTrue(false, "$numberOfTeams is too large.  Current code only support 32 teams per division");
                }
        }

        $computedTeamCount = 0;
        foreach ($poolSizes as $poolSize) {
            $computedTeamCount += $poolSize;
        }
        Assertion::isTrue($numberOfTeams == $computedTeamCount, "Error: computed teams in pools only adds up to $computedTeamCount and should add up to $numberOfTeams");

        return $poolSizes;
    }

    /**
     *  Delete the schedule
     */
    public function delete()
    {
        $teams = Team::lookupByDivision($this->division);

        $pools = Pool::lookupBySchedule($this);
        foreach ($pools as $pool) {

            // Remove team from pool before deleting pool
            foreach ($teams as $team) {
                if (isset($team->pool) and $team->pool->id == $pool->id) {
                    $team->pool = null;
                }
            }

            $pool->delete();
        }

        $this->scheduleOrm->delete();
    }
}