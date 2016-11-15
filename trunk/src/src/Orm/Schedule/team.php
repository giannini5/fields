<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Orm\Schedule\TeamOrm;
use DAG\Framework\Exception\Precondition;

/**
 * @property int        $id
 * @property Division   $division
 * @property Pool       $pool
 * @property string     $name
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
        $this->pool     = isset($pool) and isset($teamOrm->poolId) ? $pool : Pool::lookupById($teamOrm->poolId);
    }

    /**
     * @param Division  $division
     * @param Pool      $pool
     * @param string    $name
     *
     * @return Team
     */
    public static function create(
        $division,
        $pool,
        $name)
    {
        $poolId = isset($pool) ? $pool->id : null;
        $teamOrm = TeamOrm::create($division->id, $poolId, $name);
        return new static($teamOrm, $division, $pool);
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
     * @param $propertyName
     * @return int|string
     */
    public function __get($propertyName)
    {
        switch ($propertyName) {
            case "id":
                return $this->teamOrm->id;
                break;

            case "name":
                return $this->teamOrm->name;
                break;

            case "division":
                return $this->division;
                break;

            case "pool":
                return $this->pool;
                break;

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
                break;
        }
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