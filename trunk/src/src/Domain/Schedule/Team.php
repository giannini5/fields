<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Framework\Orm\DuplicateEntryException;
use DAG\Orm\Schedule\TeamOrm;
use DAG\Framework\Exception\Precondition;


/**
 * @property int        $id
 * @property Division   $division
 * @property Pool       $pool
 * @property string     $name
 * @property string     $nameId
 * @property string     $region
 * @property string     $city
 */
class Team extends Domain
{
    /** @var TeamOrm */
    private $teamOrm;

    /** @var Division */
    private $division;

    /** @var Pool */
    private $pool;

    /**
     * @param TeamOrm   $teamOrm
     * @param Division  $division (defaults to null)
     * @param Pool      $pool (defaults to null)
     */
    protected function __construct(TeamOrm $teamOrm, $division = null, $pool = null)
    {
        $this->teamOrm  = $teamOrm;
        $this->division = isset($division) ? $division : Division::lookupById($teamOrm->divisionId);
        $this->pool     = (!isset($pool) and isset($teamOrm->poolId)) ? Pool::lookupById($teamOrm->poolId) : $pool;
    }

    /**
     * @param Division  $division
     * @param Pool      $pool
     * @param string    $name
     * @param string    $nameId
     * @param string    $region
     * @param string    $city
     *
     * @param bool      $ignore - defaults to false and duplicates raise an exception
     *
     * @return Team
     *
     * @throws DuplicateEntryException
     * @throws \Exception
     */
    public static function create(
        $division,
        $pool,
        $name,
        $nameId,
        $region,
        $city,
        $ignore = false)
    {
        $poolId = isset($pool) ? $pool->id : null;

        try {
            $teamOrm = TeamOrm::create($division->id, $poolId, $name, $nameId, $region, $city);
            return new static($teamOrm, $division, $pool);
        } catch (DuplicateEntryException $e) {
            if ($ignore) {
                return static::lookupByName($division, $name);
            } else {
                throw $e;
            }
        }
    }

    /**
     * @param int $teamId
     *
     * @return Team
     */
    public static function lookupById($teamId)
    {
        $teamOrm = TeamOrm::loadById($teamId);
        return new static($teamOrm);
    }

    /**
     * @param Division  $division
     * @param string    $name
     *
     * @return Team
     */
    public static function lookupByName($division, $name)
    {
        Precondition::isNonEmpty($name, 'name should not be empty');
        $teamOrm = TeamOrm::loadByDivisionIdAndName($division->id, $name);
        return new static($teamOrm, $division);
    }

    /**
     * @param Division  $division
     *
     * @return array Teams
     */
    public static function lookupByDivision($division)
    {
        $teams = [];

        $teamOrms = TeamOrm::loadByDivisionId($division->id);
        foreach ($teamOrms as $teamOrm) {
            $teams[] = new static($teamOrm, $division);
        }

        return $teams;
    }

    /**
     * @param Pool  $pool
     *
     * @return array Teams
     */
    public static function lookupByPool($pool)
    {
        $teams = [];

        $teamOrms = TeamOrm::loadByPoolId($pool->id);
        foreach ($teamOrms as $teamOrm) {
            $teams[] = new static($teamOrm, null, $pool);
        }

        usort($teams, "static::compare");

        return $teams;
    }

    /**
     * @param Division $a
     * @param Division $b
     * @return int - -1, 0, 1 based on how $a name compares with $b
     */
    public static function compare($a, $b)
    {
        return strcmp($a->nameId, $b->nameId);
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
            case "nameId":
            case "region":
            case "city":
                return $this->teamOrm->{$propertyName};

            case "division":
            case "pool":
                return $this->{$propertyName};

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
        }
    }

    /**
     * @param $propertyName
     * @param $value
     */
    public function __set($propertyName, $value)
    {
        switch ($propertyName) {
            case "pool":
                $this->pool = $value;

                if (isset($value)) {
                    $this->teamOrm->poolId = $this->pool->id;
                } else {
                    $this->teamOrm->poolId = null;
                }

                $this->teamOrm->save();
                break;

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
        }
    }

    /**
     * @param $propertyName
     * @return int|string
     */
    public function __isset($propertyName)
    {
        switch ($propertyName) {
            case "id":
            case "name":
            case "nameId":
            case "region":
            case "city":
            case "division":
                return true;

            case "pool":
                return isset($this->teamOrm->poolId);

            default:
                Precondition::isTrue(false, "Unrecognized isset property: $propertyName");
        }
    }

    /**
     * @param Game[]    $games List of games to evaluate when computing points.  If empty then lookup games for team
     *
     * @return int  Number of points for team
     */
    public function getPoints($games = null)
    {
        $points = 0;

        if (!isset($games)) {
            $games = Game::lookupByTeam($this);
        }

        foreach ($games as $game) {
            // Exclude title games
            if ($game->title == '') {
                if ($game->homeTeam->id == $this->id) {
                    $points += $game->computeGamePoints(true);
                } else if ($game->visitingTeam->id == $this->id) {
                    $points += $game->computeGamePoints(false);
                }
            }
        }

        return $points;
    }

    /**
     *  Delete the team
     *  TODO: Change to cascading delete
     */
    public function delete()
    {
        $this->teamOrm->delete();
    }
}