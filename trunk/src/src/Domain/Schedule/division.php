<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Framework\Orm\DuplicateEntryException;
use DAG\Orm\Schedule\DivisionOrm;
use DAG\Framework\Exception\Precondition;
use DAG\Services\MySql\DuplicateKeyException;

/**
 * @property int    $id
 * @property Season $season
 * @property string $name
 */
class Division extends Domain
{
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
        $ignore = false)
    {
        try {
            $divisionOrm = DivisionOrm::create($season->id, $name);
            return new static($divisionOrm, $season);
        } catch (DuplicateEntryException $e) {
            if ($ignore) {
                return static::lookupByName($season, $name);
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
     *
     * @return Division
     */
    public static function lookupByName($season, $name)
    {
        Precondition::isNonEmpty($name, 'name should not be empty');
        $divisionOrm = DivisionOrm::loadBySeasonIdAndName($season->id, $name);
        return new static($divisionOrm, $season);
    }

    /**
     * @param Season $season
     *
     * @return array Divisions
     */
    public static function lookupBySeason($season)
    {
        $divisions = [];

        $divisionOrms = DivisionOrm::loadBySeasonId($season->id);
        foreach ($divisionOrms as $divisionOrm) {
            $divisions[] = new static($divisionOrm, $season);
        }

        return $divisions;
    }

    /**
     * @param $propertyName
     * @return int|string
     */
    public function __get($propertyName)
    {
        switch ($propertyName) {
            case "id":
                return $this->divisionOrm->id;
                break;

            case "name":
                return $this->divisionOrm->name;
                break;

            case "season":
                return $this->season;
                break;

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
                break;
        }
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