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
 * @property string $gender
 * @property int    $gameDurationMinutes
 * @property string $displayOrder
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
     * @param string    $gender
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
        $gameDurationMinutes,
        $displayOrder,
        $ignore = false)
    {
        try {
            $divisionOrm = DivisionOrm::create($season->id, $name, $gender, $gameDurationMinutes, $displayOrder);
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
     * @return array Divisions (sorted by gender, displayOrder)
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
        if ($a->gender != $b->gender) {
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
            case "gameDurationMinutes":
            case "displayOrder":
                return $this->divisionOrm->{$propertyName};

            case "season":
                return $this->{$propertyName};

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
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