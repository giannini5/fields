<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Orm\Schedule\PoolOrm;
use DAG\Framework\Exception\Precondition;

/**
 * @property int        $id
 * @property Division   $division
 * @property string     $name
 */
class Pool extends Domain
{
    /** @var PoolOrm */
    private $poolOrm;

    /** @var Division */
    private $division;

    /**
     * @param PoolOrm   $poolOrm
     * @param Division  $division (defaults to null)
     */
    protected function __construct(PoolOrm $poolOrm, $division = null)
    {
        $this->poolOrm = $poolOrm;
        $this->division = isset($division) ? $division : Division::lookupById($poolOrm->divisionId);
    }

    /**
     * @param Division  $division
     * @param string    $name
     *
     * @return Pool
     */
    public static function create(
        $division,
        $name)
    {
        $poolOrm = PoolOrm::create($division->id, $name);
        return new static($poolOrm, $division);
    }

    /**
     * @param int $poolId
     *
     * @return Pool
     */
    public static function lookupById($poolId)
    {
        $poolOrm = PoolOrm::loadById($poolId);
        return new static($poolOrm);
    }

    /**
     * @param Division  $division
     * @param string    $name
     *
     * @return Pool
     */
    public static function lookupByName($division, $name)
    {
        Precondition::isNonEmpty($name, 'name should not be empty');
        $poolOrm = PoolOrm::loadByDivisionIdAndName($division->id, $name);
        return new static($poolOrm, $division);
    }

    /**
     * @param $propertyName
     * @return int|string
     */
    public function __get($propertyName)
    {
        switch ($propertyName) {
            case "id":
                return $this->poolOrm->id;
                break;

            case "name":
                return $this->poolOrm->name;
                break;

            case "division":
                return $this->division;
                break;

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
                break;
        }
    }

    /**
     *  Delete the pool
     *  TODO: Change to cascading delete
     */
    public function delete()
    {
        $this->poolOrm->delete();
    }
}