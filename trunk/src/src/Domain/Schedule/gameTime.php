<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Orm\Schedule\GameTimeOrm;
use DAG\Framework\Exception\Precondition;

/**
 * @property int        $id
 * @property GameDate   $gameDate
 * @property Division   $division
 * @property Field      $field
 * @property string     $startTime
 */
class GameTime extends Domain
{
    /** @var GameTimeOrm */
    private $gameTimeOrm;

    /** @var GameDate */
    private $gameDate;

    /** @var Division */
    private $division;

    /** @var Field */
    private $field;

    /**
     * @param GameTimeOrm   $gameTimeOrm
     * @param GameDate      $gameDate (defaults to null)
     * @param Division      $division (defaults to null)
     * @param Field         $field (defaults to null)
     */
    protected function __construct(GameTimeOrm $gameTimeOrm, $gameDate = null, $division = null, $field = null)
    {
        $this->gameTimeOrm  = $gameTimeOrm;
        $this->gameDate     = isset($gameDate) ? $gameDate : GameDate::lookupById($gameTimeOrm->gameDateId);
        $this->division     = isset($division) ? $division : Division::lookupById($gameTimeOrm->divisionId);
        $this->field        = isset($field) ? $field : Field::lookupById($gameTimeOrm->fieldId);
    }

    /**
     * @param GameDate  $gameDate
     * @param Division  $division
     * @param Field     $field
     * @param string    $startTime
     *
     * @return GameTime
     */
    public static function create(
        $gameDate,
        $division,
        $field,
        $startTime)
    {
        $gameTimeOrm = GameTimeOrm::create($gameDate->id, $division->id, $field->id, $startTime);
        return new static($gameTimeOrm, $gameDate, $division, $field);
    }

    /**
     * @param int $gameTimeId
     *
     * @return GameTime
     */
    public static function lookupById($gameTimeId)
    {
        $gameTimeOrm = GameTimeOrm::loadById($gameTimeId);
        return new static($gameTimeOrm);
    }

    /**
     * @param GameDate      $gameDate
     *
     * @return array        $gameTimes
     */
    public static function lookupByGameDate($gameDate)
    {
        $gameTimes = [];

        $gameTimeOrms = GameTimeOrm::loadByGameDateId($gameDate->id);
        foreach ($gameTimeOrms as $gameTimeOrm) {
            $gameTimes[] = new static($gameTimeOrm, $gameDate);
        }

        return $gameTimes;
    }

    /**
     * @param GameDate      $gameDate
     * @param Division      $division
     *
     * @return array        $gameTimes
     */
    public static function lookupByDivision($gameDate, $division)
    {
        $gameTimes = [];

        $gameTimeOrms = GameTimeOrm::loadByDivisionId($gameDate->id, $division->id);
        foreach ($gameTimeOrms as $gameTimeOrm) {
            $gameTimes[] = new static($gameTimeOrm, $gameDate, $division);
        }

        return $gameTimes;
    }

    /**
     * @param GameDate      $gameDate
     * @param Field         $field
     *
     * @return array        $gameTimes
     */
    public static function lookupByField($gameDate, $field)
    {
        $gameTimes = [];

        $gameTimeOrms = GameTimeOrm::loadByFieldId($gameDate->id, $field->id);
        foreach ($gameTimeOrms as $gameTimeOrm) {
            $gameTimes[] = new static($gameTimeOrm, $gameDate, null, $field);
        }

        return $gameTimes;
    }

    /**
     * @param $propertyName
     * @return int|string
     */
    public function __get($propertyName)
    {
        switch ($propertyName) {
            case "id":
                return $this->gameTimeOrm->id;
                break;

            case "startTime":
                return $this->gameTimeOrm->startTime;
                break;

            case "gameDate":
                return $this->gameDate;
                break;

            case "division":
                return $this->division;
                break;

            case "field":
                return $this->field;
                break;

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
                break;
        }
    }

    /**
     *  Delete the gameTime
     *  TODO: Change to cascading delete
     */
    public function delete()
    {
        $this->gameTimeOrm->delete();
    }
}