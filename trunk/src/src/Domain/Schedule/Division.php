<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Framework\Orm\DuplicateEntryException;
use DAG\Framework\Orm\NoResultsException;
use DAG\Orm\Schedule\DivisionOrm;
use DAG\Framework\Exception\Precondition;
use DAG\Orm\Schedule\RefereeOrm;


/**
 * @property int    $id
 * @property Season $season
 * @property string $name
 * @property string $nameWithGender
 * @property string $gender
 * @property int    $maxPlayersPerTeam
 * @property int    $gameDurationMinutes
 * @property int    $minutesBetweenGames
 * @property int    $scoringTracked
 * @property bool   $isScoringTracked
 * @property string $displayOrder
 * @property int    $combineLeagueSchedules
 */
class Division extends Domain
{
    static $ALL     = 'All';
    static $BOYS    = 'Boys';
    static $GIRLS   = 'Girls';

    /** @var DivisionOrm */
    private $divisionOrm;

    /** @var Season */
    private $season;

    /**
     * @param DivisionOrm   $divisionOrm
     * @param Season        $season (defaults to null)
     */
    protected function __construct(DivisionOrm $divisionOrm, $season = null)
    {
        $this->divisionOrm = $divisionOrm;
        $this->season = isset($season) ? $season : Season::lookupById($divisionOrm->seasonId);
    }

    /**
     * @param Season    $season
     * @param string    $name
     * @param string    $gender
     * @param int       $maxPlayersPerTeam
     * @param int       $gameDurationMinutes
     * @param string    $displayOrder
     * @param bool      $ignore - defaults to false and duplicates raise an exception
     *
     * @return Division
     *
     * @throws DuplicateEntryException
     * @throws \Exception
     */
    public static function create(
        $season,
        $name,
        $gender,
        $maxPlayersPerTeam,
        $gameDurationMinutes,
        $displayOrder,
        $ignore = false)
    {
        try {
            $minutesBetweenGames = 180;
            $divisionOrm = DivisionOrm::create(
                $season->id,
                $name,
                $gender,
                $maxPlayersPerTeam,
                $gameDurationMinutes,
                $minutesBetweenGames,
                $displayOrder);
            return new static($divisionOrm, $season);
        } catch (DuplicateEntryException $e) {
            if ($ignore) {
                return static::lookupByNameAndGender($season, $name, $gender);
            } else {
                throw $e;
            }
        }
    }

    /**
     * @param int $divisionId
     *
     * @return Division
     */
    public static function lookupById($divisionId)
    {
        $divisionOrm = DivisionOrm::loadById($divisionId);
        return new static($divisionOrm);
    }

    /**
     * @param Season $season
     * @param string $name
     * @param string $gender
     *
     * @return Division
     */
    public static function lookupByNameAndGender($season, $name, $gender)
    {
        Precondition::isNonEmpty($name, 'name should not be empty');
        Precondition::isNonEmpty($gender, 'gender should not be empty');

        $divisionOrm = DivisionOrm::loadBySeasonIdAndNameAndGender($season->id, $name, $gender);
        return new static($divisionOrm, $season);
    }

    /**
     * @param Season        $season
     * @param string        $name
     * @param string        $gender
     * @param Division|null $division
     *
     * @return bool
     */
    public static function findByNameAndGender($season, $name, $gender, &$division)
    {
        Precondition::isNonEmpty($name, 'name should not be empty');
        Precondition::isNonEmpty($gender, 'gender should not be empty');

        $division = null;

        try {
            $divisionOrm = DivisionOrm::loadBySeasonIdAndNameAndGender($season->id, $name, $gender);
            $division = new static($divisionOrm, $season);
            return true;
        } catch (NoResultsException $e) {
            return false;
        }
    }

    /**
     * @param Season $season
     * @param string $name
     *
     * @return Division[]
     */
    public static function lookupByName($season, $name)
    {
        Precondition::isNonEmpty($name, 'name should not be empty');

        $divisions = [];

        $divisionOrms = DivisionOrm::loadBySeasonIdAndName($season->id, $name);
        foreach ($divisionOrms as $divisionOrm) {
            $divisions[] = new static($divisionOrm, $season);
        }

        return $divisions;
    }

    /**
     * @param Season $season
     *
     * @return Division[] - (sorted by gender, displayOrder)
     */
    public static function lookupBySeason($season)
    {
        $divisions = [];

        $divisionOrms = DivisionOrm::loadBySeasonId($season->id);
        foreach ($divisionOrms as $divisionOrm) {
            $divisions[] = new static($divisionOrm, $season);
        }

        usort($divisions, "static::compare");

        return $divisions;
    }

    /**
     * @param Division $a
     * @param Division $b
     * @return int - -1, 0, 1 based on how $a gender compares with $b
     */
    public static function compare($a, $b)
    {
        if ($a->displayOrder == $b->displayOrder) {
            return strcmp($a->gender, $b->gender);
        }

        return $a->displayOrder - $b->displayOrder;
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
            case "gender":
            case "maxPlayersPerTeam":
            case "gameDurationMinutes":
            case "minutesBetweenGames":
            case "scoringTracked":
            case "displayOrder":
            case "combineLeagueSchedules":
                return $this->divisionOrm->{$propertyName};

            case "isScoringTracked":
                return $this->divisionOrm->scoringTracked == 1;

            case "nameWithGender":
                return $this->divisionOrm->name . " " . $this->divisionOrm->gender;

            case "season":
                return $this->{$propertyName};

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
                return false;
        }
    }

    /**
     * @param string    $propertyName
     * @param mixed     $value
     */
    public function __set($propertyName, $value)
    {
        switch ($propertyName) {
            case "name":
            case "maxPlayersPerTeam":
            case "gameDurationMinutes":
            case "minutesBetweenGames":
            case "scoringTracked":
            case "displayOrder":
            case "combineLeagueSchedules":
                $this->divisionOrm->{$propertyName} = $value;
                $this->divisionOrm->save();
                break;

            default:
                Precondition::isTrue(false, "Set not supported for property: $propertyName");
        }
    }

    /**
     * Populate game day referees for schedules
     *
     * @param GameDate  $gameDate
     * @param string    $refereeType - See Schedule::$refereeTypes
     */
    public function populateGameDayReferees($gameDate, $refereeType)
    {
        $schedules = Schedule::lookupByDivision($this, true);

        foreach ($schedules as $schedule) {
            if ($gameDate->day >= $schedule->startDate and $gameDate->day <= $schedule->endDate) {
                $schedule->populateGameDayReferees($gameDate, $refereeType);
            }
        }
    }

    /**
     * Clear game day referees for schedules
     *
     * @param GameDate  $gameDate
     */
    public function clearGameDayReferees($gameDate)
    {
        $schedules = Schedule::lookupByDivision($this, true);

        foreach ($schedules as $schedule) {
            if ($gameDate->day >= $schedule->startDate and $gameDate->day <= $schedule->endDate) {
                $schedule->clearGameDayReferees($gameDate);
            }
        }
    }

    /**
     * @param string $badgeId - See RefereeOrm for badge identifiers
     * @return bool
     */
    public function canCenter($badgeId)
    {
        switch ($badgeId) {
            case RefereeOrm::UNKNOWN:
            case RefereeOrm::REGIONAL:
                return strpos($this->name, '10') !== false;
            case RefereeOrm::INTERMEDIATE:
                return strpos($this->name, '12') !== false;
            case RefereeOrm::ADVANCED:
                return strpos($this->name, '14') !== false;
            case RefereeOrm::NATIONAL:
                if (strpos($this->name, '16') !== false or
                    strpos($this->name, '18') !== false or
                    strpos($this->name, '19') !== false) {
                    return true;
                }
        }

        return false;
    }

    /**
     * @param string $badgeId - See RefereeOrm for badge identifiers
     * @return bool
     */
    public function canAR($badgeId)
    {
        switch ($badgeId) {
            case RefereeOrm::UNKNOWN:
            case RefereeOrm::REGIONAL:
                if (strpos($this->name, '10') !== false or
                    strpos($this->name, '12') !== false) {
                    return true;
                }
                break;
            case RefereeOrm::INTERMEDIATE:
                if (strpos($this->name, '12') !== false or
                    strpos($this->name, '14') !== false) {
                    return true;
                }
                break;
            case RefereeOrm::ADVANCED:
                if (strpos($this->name, '14') !== false or
                    strpos($this->name, '16') !== false) {
                    return true;
                }
                break;
            case RefereeOrm::NATIONAL:
                if (strpos($this->name, '16') !== false or
                    strpos($this->name, '18') !== false or
                    strpos($this->name, '19') !== false) {
                    return true;
                }
        }

        return false;
    }

    /**
     *  Delete the division
     *  TODO: Change to cascading delete
     */
    public function delete()
    {
        $this->divisionOrm->delete();
    }
}