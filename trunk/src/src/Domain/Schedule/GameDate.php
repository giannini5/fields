<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Framework\Exception\Assertion;
use DAG\Orm\Schedule\GameDateOrm;
use DAG\Framework\Exception\Precondition;


/**
 * @property int    $id
 * @property Season $season
 * @property string $day
 */
class GameDate extends Domain
{
    const ALL_DAYS          = 'all';
    const SATURDAYS_ONLY    = 'saturdays';
    const SUNDAYS_ONLY      = 'sundays';

    /** @var GameDateOrm */
    private $gameDateOrm;

    /** @var Season */
    private $season;

    /**
     * @param GameDateOrm   $gameDateOrm
     * @param Season        $season (defaults to null)
     */
    protected function __construct(GameDateOrm $gameDateOrm, $season = null)
    {
        $this->gameDateOrm = $gameDateOrm;
        $this->season = isset($season) ? $season : Season::lookupById($gameDateOrm->seasonId);
    }

    /**
     * @param Season $season
     * @param string $day
     *
     * @return GameDate
     */
    public static function create(
        $season,
        $day)
    {
        $gameDateOrm = GameDateOrm::create($season->id, $day);
        return new static($gameDateOrm, $season);
    }

    /**
     * @param int $gameDateId
     *
     * @return GameDate
     */
    public static function lookupById($gameDateId)
    {
        $gameDateOrm = GameDateOrm::loadById($gameDateId);
        return new static($gameDateOrm);
    }

    /**
     * @param Season $season
     * @param string $day
     *
     * @return GameDate
     */
    public static function lookupByDay($season, $day)
    {
        Precondition::isNonEmpty($day, 'day should not be empty');
        $gameDateOrm = GameDateOrm::loadBySeasonIdAndDay($season->id, $day);
        return new static($gameDateOrm, $season);
    }

    /**
     * @param Season    $season
     * @param string    $dayFilter defaults to ALL_DAYS
     * @param Schedule  $limitBySchedule defaults to null
     *
     * @return array GameDates
     */
    public static function lookupBySeason($season, $dayFilter = self::ALL_DAYS, $limitBySchedule = null)
    {
        $gameDates = [];

        $gameDateOrms = GameDateOrm::loadBySeasonId($season->id);
        foreach ($gameDateOrms as $gameDateOrm) {
            switch ($dayFilter) {
                case self::ALL_DAYS:
                    $gameDates[] = new static($gameDateOrm, $season);
                    break;

                case self::SATURDAYS_ONLY:
                    if ($gameDateOrm->isSaturday()) {
                        $gameDates[] = new static($gameDateOrm, $season);
                    }
                    break;

                case self::SUNDAYS_ONLY:
                    if ($gameDateOrm->isSunday()) {
                        $gameDates[] = new static($gameDateOrm, $season);
                    }
                    break;

                default:
                    Assertion::isTrue(false, "Unrecognized dayFilter, '$dayFilter'");
            }
        }

        // Remove games dates that are not within the schedule's start/end date if requested
        if (isset($limitBySchedule)) {
            $scheduleGameDates = [];
            foreach ($gameDates as $gameDate) {
                if ($gameDate->day >= $limitBySchedule->startDate and $gameDate->day <= $limitBySchedule->endDate) {
                    $scheduleGameDates[] = $gameDate;
                }
            }

            $gameDates = $scheduleGameDates;
        }

        return $gameDates;
    }

    /**
     * @param $propertyName
     * @return int|string
     */
    public function __get($propertyName)
    {
        switch ($propertyName) {
            case "id":
            case "day":
                return $this->gameDateOrm->{$propertyName};

            case "season":
                return $this->{$propertyName};

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
        }
    }

    /**
     * Check to see if game date is a Sunday
     *
     * @return bool
     */
    public function isSunday()
    {
        return $this->gameDateOrm->isSunday();
    }

    /**
     *  Delete the gameDate
     */
    public function delete()
    {
        $gameTimes = GameTime::lookupByGameDate($this);
        foreach ($gameTimes as $gameTime) {
            $gameTime->delete();
        }

        $this->gameDateOrm->delete();
    }
}