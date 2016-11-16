<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Orm\Schedule\GameDateOrm;
use DAG\Framework\Exception\Precondition;


/**
 * @property int    $id
 * @property Season $season
 * @property string $day
 */
class GameDate extends Domain
{
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
     * @param Season $season
     *
     * @return array GameDates
     */
    public static function lookupBySeason($season)
    {
        $gameDates = [];

        $gameDateOrms = GameDateOrm::loadBySeasonId($season->id);
        foreach ($gameDateOrms as $gameDateOrm) {
            $gameDates[] = new static($gameDateOrm, $season);
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
     *  Delete the gameDate
     *  TODO: Change to cascading delete
     */
    public function delete()
    {
        $this->gameDateOrm->delete();
    }
}