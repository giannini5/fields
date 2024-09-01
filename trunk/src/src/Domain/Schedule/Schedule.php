<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Orm\Schedule\GameOrm;
use DAG\Orm\Schedule\GameRefereeOrm;
use DAG\Orm\Schedule\GameTimeOrm;
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
 * @property string     $displayNotes
 * @property int        $published
 */
class Schedule extends Domain
{
    static $FLIGHT_NAMES = [1 => 'Anacapa',
                            2 => 'Santa Cruz',
                            3 => 'Santa Rosa',
                            4 => 'San Miguel',
                            5 => 'Catalina',
                            6 => 'San Nicholas'];

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
     * @param string    $displayNotes defaults to ''
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
        $published  = 0,
        $displayNotes = ''
    ) {
        self::verifyDate($startDate, $division->season, 'StartDate');
        self::verifyDate($endDate, $division->season, 'EndDate');
        self::verifyTime($startTime, $division->season, 'StartTime');
        self::verifyTime($endTime, $division->season, 'EndTime');
        self::verifyDaysOfWeek($daysOfWeek, $division->season);

        $scheduleOrm = ScheduleOrm::create($division->id, $name, $scheduleType, $gamesPerTeam, $startDate, $endDate, $startTime, $endTime, $daysOfWeek, $published, $displayNotes );
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
            case "displayNotes":
                return $this->scheduleOrm->{$propertyName};

            case "division":
                return $this->{$propertyName};

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
                return false;
        }
    }

    /**
     * @param $propertyName
     * @param $value
     */
    public function __set($propertyName, $value)
    {
        switch ($propertyName) {
            case "name":
            case "scheduleType":
            case "gamesPerTeam":
            case "published":
            case "displayNotes":
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
     * @return bool - true if schedule is published, false otherwise
     */
    public function isPublished()
    {
        return $this->published == 1;
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
        date_default_timezone_set('America/Los_Angeles');
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
     * Populate Pools with teams
     */
    public function populatePools()
    {
        switch($this->scheduleType) {
            case ScheduleOrm::SCHEDULE_TYPE_LEAGUE:
                $this->populateLeaguePools();
                break;
            case ScheduleOrm::SCHEDULE_TYPE_TOURNAMENT:
                $this->populateTournamentPools();
                break;
            case ScheduleOrm::SCHEDULE_TYPE_BRACKET:
                $this->populateBracketPools();
                break;
            default:
                Assertion::isTrue(false, "Unrecognized schedule type: $this->scheduleType");
        }
    }

    /**
     * Populate League Pools with teams
     */
    public function populateLeaguePools()
    {
        Precondition::isTrue($this->scheduleType == ScheduleOrm::SCHEDULE_TYPE_LEAGUE, "Invalid scheduleType: $this->scheduleType");

        $teams              = Team::lookupByDivision($this->division);
        $numberOfTeams      = count($teams);
        $crossPoolSettings  = [];
        $poolSizes          = $this->getPoolSizes($this->division->name, $numberOfTeams, $crossPoolSettings);
        $numberOfPools      = count($poolSizes);
        $pools              = [];

        // Create Flights and Pools and add teams to pools (2 pools per flight)
        $teamIndex                  = 0;
        $flightIndex                = 0;
        $flightPoolIndex            = 0;
        $flight                     = null;
        $include5th6thPlaceGame     = 0;
        $includeQuarterFinalGames   = 0;
        $includeSemiFinalGames      = 0;
        $includeChampionShipGame    = 0;
        for ($index = 0; $index < $numberOfPools; $index++) {
            // Create Flight (or re-use previousFlight)
            if ($index % 2 == 0) {
                $flightIndex        += 1;
                $flightPoolIndex    = 0;
                $flightName         = "$flightIndex";
                $flight             = Flight::create(
                    $this,
                    $flightName,
                    $include5th6thPlaceGame,
                    $includeQuarterFinalGames,
                    $includeSemiFinalGames,
                    $includeChampionShipGame);
            }

            // Create Pool
            $poolName           = sprintf("Pool %s", chr(ord('A') + $flightPoolIndex));
            $pool               = Pool::create($flight, $this, $poolName);
            $pools[]            = $pool;
            $flightPoolIndex    += 1;

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
     * Populate Tournament Pools with teams
     */
    public function populateTournamentPools()
    {
        Precondition::isTrue($this->scheduleType == ScheduleOrm::SCHEDULE_TYPE_TOURNAMENT, "Invalid scheduleType: $this->scheduleType");

        $teams          = Team::lookupByDivision($this->division);
        $numberOfTeams  = count($teams);
        $flightData     = $this->getTournamentPoolSizes($numberOfTeams);
        $pools          = [];
        $teamIndex      = 0;

        // Create Flights and Pools and add teams to pools
        foreach ($flightData as $flightIndex => $poolData) {
            $include5th6thPlaceGame     = ($numberOfTeams > 3 and $poolData[0] == 3) ? 1 : 0;
            $include3rd4thPlaceGame     = ($numberOfTeams > 4 and ($poolData[0] == 3 or $poolData[0] == 4)) ? 1 : 0;
            $includeSemiFinalGames      = $poolData[0] == 3 ? 1 : 0;
            $includeChampionShipGame    = ($numberOfTeams > 4 and ($poolData[0] == 3 or $poolData[0] == 4)) ? 1 : 0;
            $flightName                 = $this->getFlightName($flightIndex);
            $flight = Flight::create(
                $this,
                $flightName,
                $include5th6thPlaceGame,
                $include3rd4thPlaceGame,
                $includeSemiFinalGames,
                $includeChampionShipGame);

            $poolIndex = 0;
            foreach ($poolData as $numberOfPoolTeams) {
                $poolName   = sprintf("Pool %s", chr(ord('A') + $poolIndex++));
                $pool       = Pool::create($flight, $this, $poolName);
                $pools[]    = $pool;

                // Add Teams to pool
                while ($numberOfPoolTeams > 0 and $teamIndex <= $numberOfTeams) {
                    $team               = $teams[$teamIndex++];
                    $team->pool         = $pool;
                    $numberOfPoolTeams -= 1;
                }
            }
        }
    }

    /**
     * Populate Bracket Pools with teams
     */
    public function populateBracketPools()
    {
        Precondition::isTrue($this->scheduleType == ScheduleOrm::SCHEDULE_TYPE_BRACKET, "Invalid scheduleType: $this->scheduleType");

        $teams          = Team::lookupByDivision($this->division);
        $numberOfTeams  = count($teams);
        $flightData     = $this->getBracketPoolSizes($numberOfTeams);
        $pools          = [];
        $teamIndex      = 0;

        // Create Flights and Pools and add teams to pools
        foreach ($flightData as $flightIndex => $poolData) {
            $include5th6thPlaceGame     = 0;
            $include3rd4thPlaceGame     = 0;
            $includeSemiFinalGames      = 1;
            $includeChampionShipGame    = 1;
            $flightName                 = $this->getFlightName($flightIndex);
            $flight = Flight::create(
                $this,
                $flightName,
                $include5th6thPlaceGame,
                $include3rd4thPlaceGame,
                $includeSemiFinalGames,
                $includeChampionShipGame);

            $poolIndex = 0;
            foreach ($poolData as $numberOfPoolTeams) {
                $poolName   = sprintf("Pool %s", chr(ord('A') + $poolIndex++));
                $pool       = Pool::create($flight, $this, $poolName);
                $pools[]    = $pool;

                // Add Teams to pool
                while ($numberOfPoolTeams > 0 and $teamIndex <= $numberOfTeams) {
                    $team               = $teams[$teamIndex++];
                    $team->pool         = $pool;
                    $numberOfPoolTeams -= 1;
                }
            }
        }
    }

    /**
     * @param int   $flightIndex
     *
     * @return string
     */
    private function getFlightName($flightIndex)
    {
        Assertion::isTrue(isset(self::$FLIGHT_NAMES[$flightIndex]), "Invalid flight index: $flightIndex");
        return self::$FLIGHT_NAMES[$flightIndex];
    }

    /**
     * Populate Games
     */
    public function populateGames()
    {
        switch($this->scheduleType) {
            case ScheduleOrm::SCHEDULE_TYPE_LEAGUE:
                $this->populateLeagueGames();
                break;
            case ScheduleOrm::SCHEDULE_TYPE_TOURNAMENT:
                $this->populateTournamentGames();
                break;
            case ScheduleOrm::SCHEDULE_TYPE_BRACKET:
                $this->populateBracketGames();
                break;
            default:
                Assertion::isTrue(false, "Unrecognized schedule type: $this->scheduleType");
        }
    }

    /**
     * Populate League Games
     */
    public function populateLeagueGames()
    {
        Precondition::isTrue($this->scheduleType == ScheduleOrm::SCHEDULE_TYPE_LEAGUE, "Invalid scheduleType: $this->scheduleType");

        // TODO add logic for non-matching team count in cross-pool play (second game per day for one team)
        $divisionFields         = DivisionField::lookupByDivision($this->division);
        $processedPoolIds       = [];
        $season                 = $this->division->season;
        $flights                = Flight::lookupBySchedule($this);
        $homeGameTrackerByPool  = [];

        foreach ($flights as $flight) {
            if ($flight->scheduleGames != 1) {
                // Skip game scheduling for this flight
                continue;
            }

            $pools = Pool::lookupByFlight($flight);
            foreach ($pools as $pool) {
                // With cross-pool play, games may have already been created for this pool.  Check and skip if so.
                if (in_array($pool->id, $processedPoolIds)) {
                    continue;
                }
                $processedPoolIds[] = $pool->id;

                $gameDates                          = GameDate::lookupBySeason($this->division->season,
                    GameDate::SATURDAYS_ONLY, $this);
                $triedSundays                       = false;
                $gameDateIndex                      = 0;
                $teams                              = Team::lookupByPool($pool);
                $homeGameTracker                    = new HomeGameTracker($teams);
                $homeGameTrackerByPool[$pool->id]   = $homeGameTracker;
                $poolType                           = TeamPolygon::ROUND_ROBIN_EVEN;
                $crossPoolTeams                     = null;
                $shiftCount                         = 0;

                if ($pool->gamesAgainstPool->id != $pool->id) {
                    $poolType           = TeamPolygon::CROSS_POOL_EVEN;
                    $crossPoolTeams     = Team::lookupByPool($pool->gamesAgainstPool);
                    $processedPoolIds[] = $pool->gamesAgainstPool->id;
                    $homeGameTracker->addTeams($crossPoolTeams);
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
                            if (!$anyGamesAddedForThisDate and $divisionFieldsIndex >= count($divisionFields) and $triedOppositeGender) {
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
                        if ($pool->gamesAgainstPool->id != $pool->id) {
                            list($homeTeam, $visitingTeam) = $homeGameTracker->getHomeVisitorTeams($teams[$team1Index], $crossPoolTeams[$team2Index]);
                        } else {
                            list($homeTeam, $visitingTeam) = $homeGameTracker->getHomeVisitorTeams($teams[$team1Index], $teams[$team2Index]);
                        }
                        Assertion::isTrue(isset($homeTeam), "Major bug, homeTeam did not get set - See Dave");
                        Assertion::isTrue(isset($visitingTeam), "Major bug, visitingTeam did not get set - See Dave");

                        // Create the game
                        $this->createGame($season, $flight, $pool, $gameTime, $homeTeam, $visitingTeam);
                        $anyGamesAddedForThisDate = true;

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

        // Attempt to even out home and visiting games
        foreach ($homeGameTrackerByPool as $poolId => $homeGameTracker) {
            $homeGameTracker->evenOutHomeGames($this);
        }
    }

    /**
     * Populate Tournament Games
     */
    public function populateTournamentGames()
    {
        Precondition::isTrue($this->scheduleType == ScheduleOrm::SCHEDULE_TYPE_TOURNAMENT, "Invalid scheduleType: $this->scheduleType");

        // Get all fields
        // Get all gameDates
        // Get all available gameTimes by gameDate
        // For each flight
            // For each pool
                // Number of games = number of teams in pool - 1
                // Schedule each round of games at same time
                    // Exception for ODD number teams, last game in pairing is scheduled at later time
                // Try for 3 hours between games and then make second pass with 1.5 hour between games if no slots found

        $season                 = $this->division->season;
        $flights                = Flight::lookupBySchedule($this);
        $divisionFields         = DivisionField::lookupByDivision($this->division);
        $fields                 = [];
        $gameDates              = GameDate::lookupBySeason($this->division->season, GameDate::ALL_DAYS, $this);
        $gender                 = $this->division->gender;
        $gameTimesByDayAndTime  = [];
        $timeBetweenGames       = 60 * 60 * 3; // three hours in seconds
        $lastGameTime           = null;

        foreach ($divisionFields as $divisionField) {
            $fields[] = $divisionField->field;
        }
        foreach ($gameDates as $gameDate) {
            // $gameTimes = GameTime::lookupByGameDateAndGenderAndFields($gameDate, $gender, $fields);
            $gameTimes = GameTime::lookupByGameDateAndFields($gameDate, $fields);
            foreach($gameTimes as $gameTime) {
                $gameTimesByDayAndTime[$gameDate->day][$gameTime->startTime][] = $gameTime;
            }
        }

        foreach ($flights as $flight) {
            if ($flight->scheduleGames != 1) {
                // Skip game scheduling for this flight
                continue;
            }

            $pools = Pool::lookupByFlight($flight);
            foreach ($pools as $pool) {
                Assertion::isTrue($pool->gamesAgainstPool->id == $pool->id, "Cross pool play not supported for tournament play");

                $gameDateIndex          = 0;
                $teams                  = Team::lookupByPool($pool);
                $homeGameTracker        = new HomeGameTracker($teams);
                $poolType               = count($teams) % 2 != 0 ? TeamPolygon::ROUND_ROBIN_ODD_TOURNAMENT : TeamPolygon::ROUND_ROBIN_EVEN;
                $teamPolygon            = new TeamPolygon($teams, $poolType);
                $shiftCount             = 0;
                $gamesPerDay            = 2;
                $currentGamesPerDay     = 0;
                $firstGameTime          = null;
                $triedOppositeGender    = false;
                $gameCount              = 0;
                $maxIterations          = $poolType == TeamPolygon::ROUND_ROBIN_ODD_TOURNAMENT ? count($teams) + 1 : count($teams);

                // At most two games on Saturday
                // Remaining games on Sunday
                // Medal round on Sunday
                $minStartTime       = '05:00:00';
                $teamIdsWithGame    = [];
                /** @var GameTime $lastGameTime */
                $gameTimesByTime    = [];

                for ($gameNumber = 1; $gameNumber < $maxIterations; $gameNumber++) {
                    Assertion::isTrue($gameDateIndex < count($gameDates), "Ran out of games dates - need to add more dates or write code to allow more than two games per day");

                    $currentGamesPerDay += 1;
                    $teamPairings       = $teamPolygon->getTeamPairings();

                    if ($gameNumber == 1 or $poolType != TeamPolygon::ROUND_ROBIN_ODD_TOURNAMENT) {
                        $gameDate           = $gameDates[$gameDateIndex];
                        $gameTimesByTime    = $gameTimesByDayAndTime[$gameDate->day];
                        ksort($gameTimesByTime);
                        $lastGameTime       = null;
                        $teamIdsWithGame    = [];
                    }

                    foreach ($teamPairings as $team1Index => $team2Index) {
                        // HACK to deal with ODD POOL Sizes of 3 or 5
                        if ($poolType == TeamPolygon::ROUND_ROBIN_ODD_TOURNAMENT and $gameCount == count($teams)) {
                            // Switch to next day for next set of games
                            $gameDateIndex          += 1;
                            $currentGamesPerDay     = 0;
                            $minStartTime           = '05:00:00';
                            /** @var GameTime $firstGameTime */
                            $firstGameTime          = null;

                            $gameDate           = $gameDates[$gameDateIndex];
                            $gameTimesByTime    = $gameTimesByDayAndTime[$gameDate->day];
                            ksort($gameTimesByTime);
                            /** @var GameTime $lastGameTime */
                            $lastGameTime       = null;
                            $teamIdsWithGame    = [];
                        }

                        // Advance startTime if one of the teams has played already (odd pool problem where bye teams play late)
                        if (in_array($teams[$team1Index]->id, $teamIdsWithGame)
                            or in_array($teams[$team2Index]->id, $teamIdsWithGame)) {
                            $minStartTime = date("H:i:s", strtotime($lastGameTime->startTime) + $timeBetweenGames);
                        }

                        // Find next available gameTime
                        $gameTime = $this->findGameTime($gameTimesByTime, $minStartTime, $gender);

                        // If no gameTime found then try again with a start Time that is 1.5 hours after the first game of the day
                        if (!isset($gameTime) and isset($firstGameTime)) {
                            $minStartTime = date("H:i:s", strtotime($firstGameTime->startTime) + 60*60*1.5);
                            $gameTime = $this->findGameTime($gameTimesByTime, $minStartTime, $gender);
                        }

                        // If still no gameTime found then try with different gender
                        if (!isset($gameTime) and !$triedOppositeGender) {
                            $triedOppositeGender    = true;
                            $oppositeGender         = $gender == GameTimeOrm::BOYS ? GameTimeOrm::GIRLS : GameTimeOrm::BOYS;
                            $minStartTime           = '05:00:00';
                            $gameTime               = $this->findGameTime($gameTimesByTime, $minStartTime, $oppositeGender);
                        }

                        Assertion::isTrue(isset($gameTime), "No available gameTime found for $gender: " . $pool->fullName);

                        $lastGameTime   = $gameTime;
                        $firstGameTime  = isset($firstGameTime) ? $firstGameTime : $gameTime;


                        // Set the home team and visiting team
                        list($homeTeam, $visitingTeam) = $homeGameTracker->getHomeVisitorTeams($teams[$team1Index], $teams[$team2Index]);
                        Assertion::isTrue(isset($homeTeam), "Major bug, homeTeam did not get set - See Dave, $team1Index, $team2Index");
                        Assertion::isTrue(isset($visitingTeam), "Major bug, visitingTeam did not get set - See Dave, $team1Index, $team2Index");

                        // Create the game
                        $game = Game::create($flight, $pool, $gameTime, $homeTeam, $visitingTeam);
                        $teamIdsWithGame[$homeTeam->id]     = $homeTeam->id;
                        $teamIdsWithGame[$visitingTeam->id] = $visitingTeam->id;
                        $gameCount                          += 1;

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
                    }

                    // Set start time for next game to be 3 hours from last game
                    $minStartTime = date("H:i:s", strtotime($lastGameTime->startTime) + $timeBetweenGames);

                    $teamPolygon->shift();
                    $shiftCount += 1;
                    if ($currentGamesPerDay >= $gamesPerDay and $poolType != TeamPolygon::ROUND_ROBIN_ODD_TOURNAMENT) {
                        $gameDateIndex          += 1;
                        $currentGamesPerDay     = 0;
                        $minStartTime           = '05:00:00';
                        $firstGameTime          = null;
                        $triedOppositeGender    = false;
                    }
                }
            }

            // Prepare for medal round game assignments
            //   - Set gameDay to last day of play
            //   - Set minStartTime to beginning of day if no games have been assigned; otherwise
            //     set to last game that was assigned
            if (isset($gameDate) and $gameDate->day == $gameDates[count($gameDates) - 1]->day) {
                $minStartTime       = date("H:i:s", strtotime($lastGameTime->startTime) + 60*60*2.75);
            } else {
                $minStartTime       = '05:00:00';
                $gameDate           = $gameDates[count($gameDates) - 1];  // Final day
            }
            $gameTimesByTime    = $gameTimesByDayAndTime[$gameDate->day];
            ksort($gameTimesByTime);

            // 5th/6th game if any
            if ($flight->include5th6thGame) {
                // Reset minStartTime to beginning of day
                $gameTime           = $this->findGameTime($gameTimesByTime, $minStartTime, $gender);
                Assertion::isTrue(isset($gameTime), 'Unable to find game time for 5th/6th place game');
                Game::create($flight, null, $gameTime, null, null, GameOrm::TITLE_5TH_6TH);
            }

            // Semi-final game if any
            $semiFinalGames = [];
            if ($flight->includeSemiFinalGames) {
                $gameTime = null;
                for ($i = 1; $i <= 2; $i++) {
                    $gameTime = $this->findGameTime($gameTimesByTime, $minStartTime, $gender);
                    Assertion::isTrue(isset($gameTime), 'Unable to find game time for Semi-Final Game $i');
                    $semiFinalGames[] = Game::create($flight, null, $gameTime, null, null, GameOrm::TITLE_SEMI_FINAL);
                }
                $minStartTime = date("H:i:s", strtotime($gameTime->startTime) + 60*60*2.75);
            }

            // Get Semi-final Game Ids
            $semiFinal1GameId = count($semiFinalGames) == 2 ? $semiFinalGames[0]->id : 0;
            $semiFinal2GameId = count($semiFinalGames) == 2 ? $semiFinalGames[1]->id : 0;

            // 3rd/4th game if any
            if ($flight->include3rd4thGame) {
                $gameTime = $this->findGameTime($gameTimesByTime, $minStartTime, $gender, true);
                Assertion::isTrue(isset($gameTime), 'Unable to find game time for 3rd/4th Game');
                Game::create($flight, null, $gameTime, null, null, GameOrm::TITLE_3RD_4TH, 0,
                    $semiFinal1GameId, $semiFinal2GameId, 0);
            }

            // Championship game if any
            if ($flight->includeChampionshipGame) {
                $gameTime = $this->findGameTime($gameTimesByTime, $minStartTime, $gender, true);
                Assertion::isTrue(isset($gameTime), 'Unable to find game time for Championship Game');
                Game::create($flight, null, $gameTime, null, null, GameOrm::TITLE_CHAMPIONSHIP, 0,
                    $semiFinal1GameId, $semiFinal2GameId, 1);
            }
        }
    }

    /**
     * Populate Bracket Games
     */
    public function populateBracketGames()
    {
        Precondition::isTrue($this->scheduleType == ScheduleOrm::SCHEDULE_TYPE_BRACKET, "Invalid scheduleType: $this->scheduleType");

        // Each flight contains pools of size 4 to 12.  Bracket play crowns a champion in each pool.  No cross-pool play
        // Create games for entire bracket and populate teams in PLAYOFF game (if necessary) and QUARTER_FINAL games
        // SEMI_FINAL and CHAMPIONSHIP games are populated with teams as scores are entered for earlier games

        $flights = Flight::lookupBySchedule($this);
        foreach ($flights as $flight) {
            if ($flight->scheduleGames != 1) {
                // Skip game scheduling for this flight
                continue;
            }

            $pools = Pool::lookupByFlight($flight);
            foreach ($pools as $pool) {
                Assertion::isTrue($pool->gamesAgainstPool->id == $pool->id, "Cross pool play not supported for tournament play");

                $teams          = Team::lookupByPool($pool);
                $numberOfTeams  = count($teams);

                // Schedule Saturday games (reduce 4 teams for Sunday)
                //     - Pre-game to reduce pool count
                //          6  teams then 2 games (top 2 teams get a bye)
                //          7  teams then 3 games (top 1 team gets a bye)
                //          8  teams then 4 games
                //          9  teams then 1 game  (top 7 teams get a bye), then 4 games
                //         10  teams then 2 games (top 6 teams get a bye), then 4 games
                //         11  teams then 3 games (top 5 teams get a bye), then 4 games
                //         12  teams then 4 games (top 4 teams get a bye), then 4 games
                Assertion::isTrue($numberOfTeams >= 6 and $numberOfTeams <= 12,
                    "Invalid pool size, only 6 to 12 teams supported at this time.  $numberOfTeams entered.");

                // Schedule pre-tournament game (if any)
                $playoff1Teams  = [];
                $playoff2Teams  = [];
                $playoff3Teams  = [];
                $playoff4Teams  = [];
                $q1Teams        = [];
                $q2Teams        = [];
                $q3Teams        = [];
                $q4Teams        = [];
                $s1Teams        = [];
                $s2Teams        = [];
                switch ($numberOfTeams) {
                    case 6:
                        // Not Q1 or Q3 - Top two seeds get a buy to the semis

                        $q2Teams[]  = $teams[3]; // Seat 4 plays 5
                        $q2Teams[]  = $teams[4];

                        $q4Teams[]  = $teams[2]; // Seat 3 plays 6
                        $q4Teams[]  = $teams[5];

                        $s1Teams[]  = $teams[0];
                        $s2Teams[]  = $teams[1];
                        break;

                    case 7:
                        // No Q1 - Top seed gets a buy to the semis

                        $q2Teams[] = $teams[3]; // Seat 4 plays 5
                        $q2Teams[] = $teams[4];

                        $q3Teams[] = $teams[1]; // Seat 2 plays 7
                        $q3Teams[] = $teams[6];

                        $q4Teams[] = $teams[2]; // Seat 3 plays 6
                        $q4Teams[] = $teams[5];

                        $s1Teams[]  = $teams[0];
                        break;

                    case 8:
                        $q1Teams[] = $teams[0]; // Seat 1 plays 8
                        $q1Teams[] = $teams[7];

                        $q2Teams[] = $teams[3]; // Seat 4 plays 5
                        $q2Teams[] = $teams[4];

                        $q3Teams[] = $teams[1]; // Seat 2 plays 7
                        $q3Teams[] = $teams[6];

                        $q4Teams[] = $teams[2]; // Seat 3 plays 6
                        $q4Teams[] = $teams[5];
                        break;

                    case 9:
                        $playoff1Teams[] = $teams[7]; // Seat 8 plays 9
                        $playoff1Teams[] = $teams[8];

                        $q1Teams[] = $teams[0]; // Seat 1 plays winner of $playoffTeams

                        $q2Teams[] = $teams[3]; // Seat 4 plays 5
                        $q2Teams[] = $teams[4];

                        $q3Teams[] = $teams[1]; // Seat 2 plays 7
                        $q3Teams[] = $teams[6];

                        $q4Teams[] = $teams[2]; // Seat 3 plays 6
                        $q4Teams[] = $teams[5];
                        break;

                    case 10:
                        $playoff1Teams[] = $teams[7]; // Seat 8 plays 9
                        $playoff1Teams[] = $teams[8];
                        $playoff2Teams[] = $teams[6]; // Seat 7 plays 10
                        $playoff2Teams[] = $teams[9];

                        $q1Teams[] = $teams[0]; // Seat 1 plays winner of $playoffTeams game 1

                        $q2Teams[] = $teams[3]; // Seat 4 plays 5
                        $q2Teams[] = $teams[4];

                        $q3Teams[] = $teams[1]; // Seat 2 plays winner of $playoffTeams game 2

                        $q4Teams[] = $teams[2]; // Seat 3 plays 6
                        $q4Teams[] = $teams[5];
                        break;

                    case 11:
                        $playoff1Teams[] = $teams[7]; // Seat 8 plays 9
                        $playoff1Teams[] = $teams[8];
                        $playoff2Teams[] = $teams[6]; // Seat 7 plays 10
                        $playoff2Teams[] = $teams[9];
                        $playoff3Teams[] = $teams[5]; // Seat 6 plays 11
                        $playoff3Teams[] = $teams[10];

                        $q1Teams[] = $teams[0]; // Seat 1 plays winner of $playoffTeams game 1

                        $q2Teams[] = $teams[3]; // Seat 4 plays 5
                        $q2Teams[] = $teams[4];

                        $q3Teams[] = $teams[1]; // Seat 2 plays winner of $playoffTeams game 2

                        $q4Teams[] = $teams[2]; // Seat 3 plays winner of $playoffTeams game 3
                        break;

                    case 12:
                        $playoff1Teams[] = $teams[7]; // Seat 8 plays 9
                        $playoff1Teams[] = $teams[8];
                        $playoff2Teams[] = $teams[6]; // Seat 7 plays 10
                        $playoff2Teams[] = $teams[9];
                        $playoff3Teams[] = $teams[5]; // Seat 6 plays 11
                        $playoff3Teams[] = $teams[10];
                        $playoff4Teams[] = $teams[4]; // Seat 5 plays 12
                        $playoff4Teams[] = $teams[11];

                        $q1Teams[] = $teams[0]; // Seat 1 plays winner of $playoffTeams game 1

                        $q2Teams[] = $teams[3]; // Seat 4 plays winner of $playoffTeams game 4

                        $q3Teams[] = $teams[1]; // Seat 2 plays winner of $playoffTeams game 2

                        $q4Teams[] = $teams[2]; // Seat 3 plays winner of $playoffTeams game 3
                        break;

                    default:
                        Assertion::isTrue(false, "Invalid poolSize: $numberOfTeams");
                }

                // Only two games dates used (first and last) when scheduling bracket play
                // Scheduler must move games across day boundaries to use more dates
                $gameDates = GameDate::lookupBySeason($this->division->season, GameDate::ALL_DAYS, $this);
                Assertion::isTrue(count($gameDates) >= 2, "At least 2 gamesDates required at this time");

                // Get the fields allowed for the division
                $divisionFields = DivisionField::lookupByDivision($this->division);
                $fields         = [];
                foreach ($divisionFields as $divisionField) {
                    $fields[] = $divisionField->field;
                }

                // Get the game Times for each gameDate
                $gameTimesByDayAndTime = [];
                foreach ($gameDates as $gameDate) {
                    $gameTimes = GameTime::lookupByGameDateAndFields($gameDate, $fields);
                    foreach($gameTimes as $gameTime) {
                        $gameTimesByDayAndTime[$gameDate->day][$gameTime->startTime][] = $gameTime;
                    }
                }

                // Create Play-in games
                $gameTimesByTime = $gameTimesByDayAndTime[$gameDates[0]->day];
                ksort($gameTimesByTime);

                $minStartTime           = '05:00:00';
                $minutesBetweenGames    = $this->division->minutesBetweenGames;

                $p1Game = $this->createBracketGame($pool, $gameTimesByTime, $playoff1Teams, GameOrm::TITLE_PLAYOFF, $minStartTime);
                $p2Game = $this->createBracketGame($pool, $gameTimesByTime, $playoff2Teams, GameOrm::TITLE_PLAYOFF, $minStartTime);
                $p3Game = $this->createBracketGame($pool, $gameTimesByTime, $playoff3Teams, GameOrm::TITLE_PLAYOFF, $minStartTime);
                $p4Game = $this->createBracketGame($pool, $gameTimesByTime, $playoff4Teams, GameOrm::TITLE_PLAYOFF, $minStartTime);

                // Create Quarter Final Games (in reverse order to give more time from playoff games
                if (count($teams) == 9) {
                    Assertion::isTrue(isset($p1Game), "Playoff game 1 not found for pool of size 9");
                    $minStartTime = date("H:i:s", strtotime($p1Game->gameTime->startTime) + $minutesBetweenGames);
                } else if (count($teams) == 10) {
                    Assertion::isTrue(isset($p2Game), "Playoff game 2 not found for pool of size 10");
                    $minStartTime = date("H:i:s", strtotime($p2Game->gameTime->startTime) + $minutesBetweenGames);
                } else if (count($teams) == 11) {
                    Assertion::isTrue(isset($p3Game), "Playoff game 3 not found for pool of size 11");
                    $minStartTime = date("H:i:s", strtotime($p3Game->gameTime->startTime) + $minutesBetweenGames);
                } else if (count($teams) == 12) {
                    Assertion::isTrue(isset($p4Game), "Playoff game 4 not found for pool of size 12");
                    $minStartTime = date("H:i:s", strtotime($p4Game->gameTime->startTime) + $minutesBetweenGames);
                }

                $q1Game = null;
                if ($numberOfTeams >= 8) {
                    $q1Game = $this->createBracketGame($pool, $gameTimesByTime, $q1Teams, GameOrm::TITLE_QUARTER_FINAL, $minStartTime, null, $p1Game);
                }
                $q2Game = $this->createBracketGame($pool, $gameTimesByTime, $q2Teams, GameOrm::TITLE_QUARTER_FINAL, $minStartTime, null, $p4Game);

                $q3Game = null;
                if ($numberOfTeams >= 7) {
                    $q3Game = $this->createBracketGame($pool, $gameTimesByTime, $q3Teams, GameOrm::TITLE_QUARTER_FINAL, $minStartTime, null, $p2Game);
                }
                $q4Game = $this->createBracketGame($pool, $gameTimesByTime, $q4Teams, GameOrm::TITLE_QUARTER_FINAL, $minStartTime, null, $p3Game);

                // Create Semi-Final Games - use the last game date only for games, scheduler can manually move around
                // across game days to fix
                $minStartTime       = '05:00:00';
                $gameTimesByTime    = $gameTimesByDayAndTime[$gameDates[count($gameDates) - 1]->day];
                ksort($gameTimesByTime);

                $s1Game = $this->createBracketGame($pool, $gameTimesByTime, $s1Teams, GameOrm::TITLE_SEMI_FINAL, $minStartTime, $q1Game, $q2Game);
                $s2Game = $this->createBracketGame($pool, $gameTimesByTime, $s2Teams, GameOrm::TITLE_SEMI_FINAL, $minStartTime, $q3Game, $q4Game);

                // Create 3rd/4th and Championship Game
                $minStartTime = date("H:i:s", strtotime($s2Game->gameTime->startTime) + $minutesBetweenGames);
                $this->createBracketGame($pool, $gameTimesByTime, [], GameOrm::TITLE_3RD_4TH, $minStartTime, $s1Game, $s2Game, 0);
                $this->createBracketGame($pool, $gameTimesByTime, [], GameOrm::TITLE_CHAMPIONSHIP, $minStartTime, $s1Game, $s2Game);
            }
        }
    }

    /**
     * Create the game and the family game to help us later find coaching overlaps
     *
     * @param $season
     * @param $flight
     * @param $pool
     * @param $gameTime
     * @param $homeTeam
     * @param $visitingTeam
     * @param $title                - optional
     * @param $locked               - optional
     * @param $playInHomeGameId     - optional
     * @param $playInVisitingGameId - optional
     * @param $playInByWin          - optional
     *
     * @return Game
     * @throws
     */
    public function createGame($season, $flight, $pool, $gameTime, $homeTeam, $visitingTeam,
                                $title='', $locked=0, $playInHomeGameId=0, $playInVisitingGameId=0, $playInByWin=0)
    {
        // Create the game
        $game = Game::create($flight, $pool, $gameTime, $homeTeam, $visitingTeam,
            $title, $locked, $playInHomeGameId, $playInVisitingGameId, $playInByWin);

        // Create the familyGame to help find game overlaps for a family
        if ($homeTeam)
        {
            $this->createFamilyGame($season, $homeTeam, $game);
        }
        if ($visitingTeam)
        {
            $this->createFamilyGame($season, $visitingTeam, $game);
        }

        return $game;
    }

    /**
     * Create FamilyGame to help find coaching overlaps
     *
     * @param $season
     * @param $team
     * @param $game
     * @throws \DAG\Framework\Orm\DuplicateEntryException
     */
    private function createFamilyGame($season, $team, $game)
    {
        $coach = Coach::lookupByTeam($team);
        $family = null;
        if (Family::findByPhone($season, $coach->phone1, $family)) {
            FamilyGame::create($family, $game, true);
        } else if (Family::findByPhone($season, $coach->phone2, $family)) {
            FamilyGame::create($family, $game, true);
        }
    }

    /**
     * Create a bracket game
     *
     * @param Pool          $pool
     * @param array         $gameTimesByTime
     * @param Team[]        $teams
     * @param string        $gameTitle
     * @param string        $minStartTime
     * @param Game | null   $homePlayInGame
     * @param Game | null   $visitingPlayInGame
     * @param int           $playInByWin
     *
     * @return Game | null
     *
     * @throws
     */
    private function createBracketGame($pool, $gameTimesByTime, $teams, $gameTitle, $minStartTime, $homePlayInGame = null, $visitingPlayInGame = null, $playInByWin = 1)
    {
        Precondition::isTrue($this->scheduleType == ScheduleOrm::SCHEDULE_TYPE_BRACKET, "Invalid scheduleType: $this->scheduleType");

        // Skip creation for playoff game with no teams
        if (($gameTitle == GameOrm::TITLE_PLAYOFF)
            and count($teams) == 0) {
            return null;
        }

        // Find first available gameTime that is greater than minStartTime
        $gender     = $this->division->gender;
        $gameTime   = $this->findGameTime($gameTimesByTime, $minStartTime, $gender);
        Assertion::isTrue(isset($gameTime), "No available gameTime found for $gender: " . $pool->fullName);

        // Create the game
        $playInHomeGameId       = isset($homePlayInGame) ? $homePlayInGame->id : 0;
        $playInVisitingGameId   = isset($visitingPlayInGame) ? $visitingPlayInGame->id : 0;
        $homeTeam               = isset($teams[0]) ? $teams[0] : null;
        $visitingTeam           = isset($teams[1]) ? $teams[1] : null;
        $season                 = $pool->flight->schedule->division->season;
        $flight                 = $pool->flight;

        $game = $this->createGame($season, $flight, $pool, $gameTime, $homeTeam, $visitingTeam,
            $gameTitle, 0, $playInHomeGameId, $playInVisitingGameId, $playInByWin);

        return $game;
    }

    /**
     * Find an open gameTime that has a startTime that is greater than specified minStartTime
     *
     * @param array     $gameTimesByTime
     * @param string    $minStartTime
     * @param string    $genderPreference
     * @param bool      $tryEarlierTime
     *
     * @return GameTime | null
     */
    private function findGameTime($gameTimesByTime, $minStartTime, $genderPreference, $tryEarlierTime = false)
    {
        // Try to find based on gender preference
        foreach ($gameTimesByTime as $time => $gameTimes) {
            if ($time >= $minStartTime) {
                foreach ($gameTimes as $gameTime) {
                    if (!isset($gameTime->game)) {
                        if ($gameTime->genderPreference == $genderPreference) {
                            return $gameTime;
                        }
                    }
                }
            }
        }

        // Unable to find based on gender preference so try to find regardless of gender
        foreach ($gameTimesByTime as $time => $gameTimes) {
            if ($time >= $minStartTime) {
                foreach ($gameTimes as $gameTime) {
                    if (!isset($gameTime->game)) {
                        return $gameTime;
                    }
                }
            }
        }

        // Unable to find regardless of gender so find first available time in reverse order
        if ($tryEarlierTime) {
            $gameTimesByTimeReversed = array_reverse($gameTimesByTime);
            foreach ($gameTimesByTimeReversed as $time => $gameTimes) {
                foreach ($gameTimes as $gameTime) {
                    if (!isset($gameTime->game)) {
                        return $gameTime;
                    }
                }
            }
        }

        return null;
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
            case '5U':
            case '6U':
            case '7U':
            case '8U':
            case '10U':
            case '12U':
            case '14U':
            case '5U-2013':
            case '6U-2012':
            case '7U-2011':
            case '8U-2010':
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
                        $poolSizes[] = $numberOfTeams;
                        break;
                    case 8:
                        $poolSizes          = array(4, 4);
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
                        $poolSizes          = array(5, 5, 7); // Cross pool for pools 1 and 2
                        $crossPoolSettings  = array(1);
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
                    case 36:
                        $poolSizes = array(6, 6, 6, 6, 6, 6);
                        break;
                    case 37:
                        $poolSizes = array(6, 6, 6, 6, 6, 7);
                        break;
                    case 42:
                        $poolSizes = array(6, 6, 6, 6, 6, 6, 6);
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
     *  Get pool sizes based on number of teams and division
     *
     * @param int       $numberOfTeams
     *
     * @return array    $flightData
     */
    public function getTournamentPoolSizes($numberOfTeams)
    {
        $flightData          = [];

        switch ($numberOfTeams) {
            case 4:
                $flightData = [1 => [4]];  // pool play only
                break;
            case 5:
                $flightData = [1 => [5]];  // pool play only
                break;
            case 6:
                $flightData = [1 => [3, 3]];  // 5th/6th place game, semi, 3rd/4th place, championship
                break;
            case 8:
                $flightData = [1 => [4, 4]];  // 3rd/4th place, championship
                break;
            case 10:
                $flightData = [1 => [5, 5]];
                break;
            case 11:
                $flightData = [1 => [3, 3], 2 => [5]];
                break;
            case 12:
                $flightData = [1 => [3, 3], 2 => [3, 3]];
                break;
            case 13:
                $flightData = [1 => [4, 4], 2 => [5]];
                break;
            case 14:
                $flightData = [1 => [3, 3], 2 => [4, 4]];
                break;
            case 15:
                $flightData = [1 => [5], 2 => [5], 3 => [5]];
                break;
            case 16:
                $flightData = [1 => [4, 4], 2 => [4, 4]];
                break;
            case 17:
                $flightData = [1 => [3, 3], 2 => [3, 3], 3 => [5]];
                break;
            case 18:
                $flightData = [1 => [4, 4], 2 => [5], 3 => [5]];
                break;
            case 19:
                $flightData = [1 => [5], 2 => [4, 4], 3 => [3, 3]];
                break;
            case 20:
                $flightData = [1 => [4, 4], 2 => [3, 3], 3 => [3, 3]];
                break;
            case 21:
                $flightData = [1 => [4, 4], 2 => [4, 4], 3 => [5]];
                break;
            case 22:
                $flightData = [1 => [3, 3], 2 => [4, 4], 3 => [4, 4]];
                break;
            case 23:
                $flightData = [1 => [3, 3], 2 => [3, 3], 3 => [3, 3], 4 => [5]];
                break;
            case 24:
                $flightData = [1 => [4, 4], 2 => [4, 4], 3 => [4, 4]];
                break;
            case 25:
                $flightData = [1 => [4, 4], 2 => [3, 3], 3 => [3, 3], 4 => [5]];
                break;
            case 26:
                $flightData = [1 => [4, 4], 2 => [4, 4], 3 => [5], 4 => [5]];
                break;
            case 27:
                $flightData = [1 => [4, 4], 2 => [4, 4], 3 => [3, 3], 4 => [5]];
                break;
            case 28:
                $flightData = [1 => [4, 4], 2 => [4, 4], 3 => [3, 3], 4 => [3, 3]];
                break;
            case 29:
                $flightData = [1 => [4, 4], 2 => [4, 4], 3 => [4, 4], 4 => [5]];
                break;
            case 30:
                $flightData = [1 => [4, 4], 2 => [4, 4], 3 => [4, 4], 4 => [3, 3]];
                break;
            case 31:
                $flightData = [1 => [4, 4], 2 => [4, 4], 3 => [5], 4 => [5], 5 => [5]];
                break;
            case 32:
                $flightData = [1 => [4, 4], 2 => [4, 4], 3 => [4, 4], 4 => [4, 4]];
                break;
            case 36:
                $flightData = [1 => [4, 4], 2 => [4, 4], 3 => [4, 4], 4 => [3, 3], 5 => [3, 3]];
                break;
            case 42:
                $flightData = [1 => [4, 4], 2 => [4, 4], 3 => [4, 4], 4 => [4, 4], 5 => [5], 6 => [5]];
                break;

            default:
                Assertion::isTrue(false, "$numberOfTeams is not yet supported for tournament play");
        }

        $computedTeamCount = 0;
        foreach ($flightData as $flight => $poolSizes) {
            foreach ($poolSizes as $poolSize) {
                $computedTeamCount += $poolSize;
            }
        }
        Assertion::isTrue($numberOfTeams == $computedTeamCount, "Error: computed teams in pools only adds up to $computedTeamCount and should add up to $numberOfTeams");

        return $flightData;
    }

    /**
     *  Get pool sizes based on number of teams and division
     *
     * @param int       $numberOfTeams
     *
     * @return array    $flightData
     */
    public function getBracketPoolSizes($numberOfTeams)
    {
        $flightData          = [];

        switch ($numberOfTeams) {
            case 4:
                $flightData = [1 => [4]];  // semi-final, championship, 3rd/4th
                break;
            case 6:
                $flightData = [1 => [6]];  // top two teams get a bye, Pre-round before simi-final, etc.
                break;
            case 7:
                $flightData = [1 => [7]];  // top team gets a bye, Pre-round before simi-final, etc.
                break;
            case 8:
                $flightData = [1 => [8]];  // Simi-final, etc.
                break;
            case 9:
                $flightData = [1 => [9]];  // top three teams get a bye, Pre-round before simi-final, etc.
                break;
            case 10:
                $flightData = [1 => [10]];
                break;
            case 11:
                $flightData = [1 => [7], 2 => [4]];
                break;
            case 12:
                $flightData = [1 => [6], 2 => [6]];
                break;
            case 13:
                $flightData = [1 => [7], 2 => [6]];
                break;
            case 14:
                $flightData = [1 => [8], 2 => [6]];
                break;
            case 15:
                $flightData = [1 => [8], 2 => [7]];
                break;
            case 16:
                $flightData = [1 => [8], 2 => [8]];  // Pre-round
                break;
            case 17:
                $flightData = [1 => [9], 2 => [8]];
                break;
            case 18:
                $flightData = [1 => [9], 2 => [9]];
                break;
            case 19:
                $flightData = [1 => [10], 2 => [9]];
                break;
            case 20:
                $flightData = [1 => [10], 2 => [10]];
                break;
            case 21:
                $flightData = [1 => [8], 2 => [7], 3 => [6]];
                break;
            case 22:
                $flightData = [1 => [8], 2 => [8], 3 => [6]];
                break;
            case 23:
                $flightData = [1 => [8], 2 => [8], 3 => [7]];
                break;
            case 24:
                $flightData = [1 => [8], 2 => [8], 3 => [8]];
                break;
            case 25:
                $flightData = [1 => [9], 2 => [8], 3 => [8]];
                break;
            case 26:
                $flightData = [1 => [9], 2 => [9], 3 => [8]];
                break;
            case 27:
                $flightData = [1 => [9], 2 => [9], 3 => [9]];
                break;
            case 28:
                $flightData = [1 => [10], 2 => [10], 3 => [8]];
                break;
            case 29:
                $flightData = [1 => [10], 2 => [10], 3 => [9]];
                break;
            case 30:
                $flightData = [1 => [10], 2 => [10], 3 => [10]];
                break;
            case 31:
                $flightData = [1 => [8], 2 => [8], 3 => [8], 4 => [7]];
                break;
            case 32:
                $flightData = [1 => [8], 2 => [8], 3 => [8], 4 => [8]];
                break;
            case 36:
                $flightData = [1 => [8], 2 => [8], 3 => [10], 4 => [10]];
                break;
            case 42:
                $flightData = [1 => [9], 2 => [9], 3 => [8], 4 => [8], 5 => [8]];
                break;

            default:
                Assertion::isTrue(false, "$numberOfTeams is not yet supported for bracket play");
        }

        $computedTeamCount = 0;
        foreach ($flightData as $flight => $poolSizes) {
            foreach ($poolSizes as $poolSize) {
                $computedTeamCount += $poolSize;
            }
        }
        Assertion::isTrue($numberOfTeams == $computedTeamCount, "Error: computed teams in pools only adds up to $computedTeamCount and should add up to $numberOfTeams");

        return $flightData;
    }

    /**
     * Populate game day referees
     *
     * @param GameDate  $gameDate
     * @param string    $refereeType - All, Team or Non-Team
     */
    public function populateGameDayReferees($gameDate, $refereeType = Referee::ALL_REFEREES)
    {
        // Get games ordered by time
        $games = Game::lookupByScheduleDay($this, $gameDate, true);

        switch ($refereeType) {
            case Referee::TEAM_REFEREE:
                $this->assignTeamRefereesToGames($games, $gameDate);
                break;

            case Referee::NON_TEAM_REFEREE:
            case Referee::ALL_REFEREES:
                $this->assignRefereesToGames($refereeType, $games, $gameDate);
                break;

            default:
                Precondition::isTrue(false, "Unrecognized refereeType: $refereeType");
        }
    }

    /**
     * Clear game day referees
     *
     * @param GameDate  $gameDate
     */
    public function clearGameDayReferees($gameDate)
    {
        $games = Game::lookupByScheduleDay($this, $gameDate);
        foreach ($games as $game) {
            $gameReferees = GameReferee::lookupByGame($game);
            foreach ($gameReferees as $gameReferee) {
                $gameReferee->delete();
            }
            $game->refereeCrew = null;
        }

    }

    /**
     * @param Game[]    $games       - Games that need referee assignments
     * @param GameDate  $gameDate    - Date of games to be assigned
     */
    private function assignTeamRefereesToGames($games, $gameDate)
    {
        $teams = Team::lookupByDivision($this->division);

        // TODO: For each team, get team referees and
        // TODO:     Get games for teams ordered by game time
        // TODO:     Assign team referees to game before team's game (or right after if coach or first game of day) just one game
        foreach ($teams as $team) {
            $teamReferees = TeamReferee::lookupByTeam($team);
            $gamesForTeam = Game::lookupByTeamAndDay($team, $gameDate);

            foreach ($teamReferees as $teamReferee) {
                $this->assignRefereeToAGame($teamReferee->referee, $games, $gameDate, $gamesForTeam);
            }
        }
    }

    /**
     * @param string    $refereeType - Type of Referee (ALL, TEAM, NON-TEAM)
     * @param Game[]    $games       - Games that need referee assignments
     * @param GameDate  $gameDate    - Date of games to be assigned
     */
    private function assignRefereesToGames($refereeType, $games, $gameDate)
    {
        $referees = Referee::lookupByDivisionAndType($this->division, $refereeType);

        foreach ($referees as $referee) {
            $this->assignRefereeToAGame($referee, $games, $gameDate, []);
        }
    }

    /**
     * Find first game that qualifies (if any), assign referee and return
     *
     * @param Referee  $referee      - Referee
     * @param Game[]   $games        - Games that need referee assignments
     * @param GameDate $gameDate     - Date of games to be assigned
     * @param Game[]   $gamesForTeam - Games associated with referee's team
     */
    private function assignRefereeToAGame($referee, $games, $gameDate, $gamesForTeam)
    {
        // Return if referee cannot ref division
        /** @var DivisionReferee $divisionReferee */
        $divisionReferee = null;
        if (!DivisionReferee::findByDivisionAndReferee($this->division, $referee, $divisionReferee)) {
            return;
        }

        // TODO Return if referee cannot ref that day

        // Return if referee cannot ref any more games
        $gameReferees = GameReferee::lookupByReferee($referee);
        if (count($gameReferees) >= $referee->maxGamesPerDay) {
            return;
        }

        foreach ($games as $game) {
            // Add local variables because these are expensive to compute
            $isAssistantReferee1Assigned    = $game->isAssistantReferee1Assigned;
            $isAssistantReferee2Assigned    = $game->isAssistantReferee2Assigned;
            $isCenterRefereeAssigned        = $game->isCenterRefereeAssigned;

            // Skip games that do not need any referees
            if ($isAssistantReferee1Assigned and $isAssistantReferee2Assigned and $isCenterRefereeAssigned) {
                continue;
            }

            // Skip game if referee not qualified for open assignment for game
            if (($isCenterRefereeAssigned or !$divisionReferee->isCenter) and
                (($isAssistantReferee1Assigned and $isAssistantReferee2Assigned) or !$divisionReferee->isAssistant)) {
                continue;
            }

            // Skip game if referee already assigned a game "around" that time
            if (GameReferee::isAlreadyAssignedGame($game, $gameReferees)) {
                continue;
            }

            // Skip game if referee's team already playing a game "around" that time
            if ($game->anyOverlap($gamesForTeam, $overlappingGame, 0, 0, true, true)) {
                continue;
            }

            // Skip game if referee is also a coach and has a game "around" that time
            $games = $this->getGamesAsCoach($referee, $gameDate);
            if ($game->anyOverlap($games, $overlappingGame, 0, 0, true, true)) {
                continue;
            }

            // TODO Skip game if referee is with a team in the same pool

            // TODO Skip game if referee already has games and this is not a back-to-back game

            // Assign referee to game and return
            $this->assignRefereeToGame($divisionReferee, $game, !$isCenterRefereeAssigned, !$isAssistantReferee1Assigned, !$isAssistantReferee2Assigned);
            return;
        }
    }

    /**
     * @param Referee   $referee
     * @param GameDate  $gameDate
     * @return Game[]   Games referee has as a coach
     */
    private function getGamesAsCoach($referee, $gameDate)
    {
        $games   = [];
        $coaches = Coach::lookupByReferee($referee);

        foreach ($coaches as $coach) {
            $coachGames = Game::lookupByTeamAndDay($coach->team, $gameDate);
            $games = array_merge($games, $coachGames);
        }

        return $games;
    }

    /**
     * @param DivisionReferee   $divisionReferee
     * @param Game              $game
     * @param bool              $isCenterNeeded
     * @param bool              $isAssistant1Needed
     * @param bool              $isAssistant2Needed
     */
    private function assignRefereeToGame($divisionReferee, $game, $isCenterNeeded, $isAssistant1Needed, $isAssistant2Needed)
    {
        if ($isCenterNeeded and $divisionReferee->isCenter) {
            GameReferee::create($game, $divisionReferee->referee, GameRefereeOrm::CENTER_ROLE);
        } elseif ($isAssistant1Needed and $divisionReferee->isAssistant) {
            GameReferee::create($game, $divisionReferee->referee, GameRefereeOrm::ASSISTANT_ROLE_1);
        } elseif ($isAssistant2Needed and $divisionReferee->isAssistant) {
            GameReferee::create($game, $divisionReferee->referee, GameRefereeOrm::ASSISTANT_ROLE_2);
        }
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

        $flights = Flight::lookupBySchedule($this);
        foreach ($flights as $flight) {
            $flight->delete();
        }

        $this->scheduleOrm->delete();
    }
}
