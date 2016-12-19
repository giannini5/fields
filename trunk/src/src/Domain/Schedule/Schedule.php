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
 * @property int        $gamesPerTeam
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
     * @param int       $gamesPerTeam
     *
     * @return Schedule
     */
    public static function create(
        $division,
        $name,
        $gamesPerTeam)
    {
        $scheduleOrm = ScheduleOrm::create($division->id, $name, $gamesPerTeam);
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
     *
     * @return Schedule[]
     */
    public static function lookupByDivision($division)
    {
        $schedules = [];

        $scheduleOrms = ScheduleOrm::loadByDivisionId($division->id);
        foreach ($scheduleOrms as $scheduleOrm) {
            $schedules[] = new static($scheduleOrm, $division);
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
            case "gamesPerTeam":
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
            case "gamesPerTeam":
                $this->scheduleOrm->{$propertyName} = $value;
                $this->scheduleOrm->save();
                break;

            default:
                Precondition::isTrue(false, "Set not allowed for property: $propertyName");

        }
    }

    /**
     * Populate Pools
     */
    public function populatePools()
    {
        $teams          = Team::lookupByDivision($this->division);
        $numberOfTeams  = count($teams);
        $poolSizes      = $this->getPoolSizes($this->division->name, $numberOfTeams);
        $numberOfPools  = count($poolSizes);

        $teamIndex = 0;
        for ($index = 0; $index < $numberOfPools; $index++) {
            $poolName   = sprintf("Pool %s", chr(ord('A') + $index));
            $pool       = Pool::create($this, $poolName);

            $teamsInPool = $poolSizes[$index];
            while ($teamsInPool > 0 and $teamIndex <= $numberOfTeams) {
                $team       = $teams[$teamIndex++];
                $team->pool = $pool;
                $teamsInPool -= 1;
            }
        }
    }

    /**
     * Populate Games
     */
    public function populateGames()
    {
        // TODO add gamesDates to schedule - gameDateSchedule table
        // TODO alternate boys/girls game times by week
        // TODO only use Sunday's if absolutely necessary
        // TODO add logic to avoid coaching overlaps
        // TODO add logic to use specific fields based on picture day
        // TODO much, much more
        $divisionFields     = DivisionField::lookupByDivision($this->division);
        $pools              = Pool::lookupBySchedule($this);
        $gender             = $this->division->gender;
        $triedSundays       = false;
        $triedOtherGender   = false;

        foreach ($pools as $pool) {
            $gameDates      = GameDate::lookupBySeason($this->division->season, GameDate::SATURDAYS_ONLY);
            $gameDateIndex  = 0;
            $teams          = Team::lookupByPool($pool);
            $teamPolygon    = new TeamPolygon($teams);

            for ($gameNumber = 1; $gameNumber <= $this->gamesPerTeam; $gameNumber++) {
                if ($gameDateIndex >= count($gameDates) and !$triedSundays) {
                    $triedSundays   = true;
                    $gameDates      = GameDate::lookupBySeason($this->division->season, GameDate::SUNDAYS_ONLY);
                    $gameDateIndex  = 0;
                }
                Assertion::isTrue($gameDateIndex < count($gameDates), "Ran out of games dates - need to add more dates or write code to allow more than one game per day");                $gameDate       = $gameDates[$gameDateIndex];

                $teamPairings   = $teamPolygon->getTeamPairings();

                foreach ($teamPairings as $team1Index => $team2Index) {
                    // Find next available gameTime for division/field
                    $gameTime = null;
                    foreach ($divisionFields as $divisionField) {
                        $gameTimes = GameTime::lookupByGameDateAndFieldAndGender($gameDate, $divisionField->field, $gender, true);
                        if (count($gameTimes) > 0) {
                            $gameTime = $gameTimes[0];
                            break;
                        }
                    }

                    Assertion::isTrue(isset($gameTime), "Drat, no more games times found for $gameDate->day");
                    Game::create($pool, $gameTime, $teams[$team1Index], $teams[$team2Index]);
                }

                $teamPolygon->shift();
                $gameDateIndex += 1;
            }
        }
    }

    /**
     *  Get pool sizes based on number of teams and division
     *
     * @param string    $divisionName
     * @param int       $numberOfTeams
     *
     * @return array $poolSizes
     */
    public function getPoolSizes($divisionName, $numberOfTeams)
    {
        $poolSizes = [];
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
                        $poolSizes = array(5, 4);
                        break;
                    case 10:
                        $poolSizes = array(5, 5);
                        break;
                    case 11:
                        $poolSizes = array(6, 5);
                        break;
                    case 12:
                        $poolsSize = array(6, 6);
                        break;
                    case 13:
                        $poolSizes = array(7, 6);
                        break;
                    case 14:
                        $poolSizes = array(7, 7);
                        break;
                    case 15:
                        $poolSizes = array(8, 7);
                        break;
                    case 16:
                        $poolSizes = array(8, 8);
                        break;
                    case 17:
                        $poolSizes = array(5, 4, 4, 4);
                        break;
                    case 18:
                        $poolSizes = array(5, 4, 5, 4);
                        break;
                    case 19:
                        $poolSizes = array(5, 5, 5, 4);
                        break;
                    case 20:
                        $poolSizes = array(5, 5, 5, 5);
                        break;
                    case 21:
                        $poolSizes = array(6, 5, 5, 5);
                        break;
                    case 22:
                        $poolSizes = array(6, 5, 6, 5);
                        break;
                    case 23:
                        $poolSizes = array(6, 6, 6, 5);
                        break;
                    case 24:
                        $poolSizes = array(6, 6, 6, 6);
                        break;
                    case 25:
                        $poolSizes = array(7, 6, 6, 6);
                        break;
                    case 26:
                        $poolSizes = array(7, 6, 7, 6);
                        break;
                    case 27:
                        $poolSizes = array(7, 7, 7, 6);
                        break;
                    case 28:
                        $poolSizes = array(7, 7, 7, 7);
                        break;
                    case 29:
                        $poolSizes = array(8, 7, 7, 7);
                        break;
                    case 30:
                        $poolSizes = array(8, 8, 7, 7);
                        break;
                    case 31:
                        $poolSizes = array(8, 8, 8, 7);
                        break;
                    case 32:
                        $poolSizes = array(8, 8, 8, 8);
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
                if ($team->pool == $pool) {
                    $team->pool = null;
                }
            }

            $pool->delete();
        }

        $this->scheduleOrm->delete();
    }
}