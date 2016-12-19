<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Framework\Orm\DuplicateEntryException;
use DAG\Framework\Orm\NoResultsException;
use DAG\Orm\Schedule\FieldOrm;
use DAG\Framework\Exception\Precondition;


class GameExistsForGameTime extends \DAG_Exception
{
    /**
     * @param Field     $field
     * @param GameTime  $gameTime
     */
    public function __construct($field, $gameTime)
    {
        $facilityName = $field->facility->name;
        $fieldName = $facilityName . ": " . $field->name;
        parent::__construct("Game is already set for $fieldName at time $gameTime->startTime");
    }

}


/**
 * @property int        $id
 * @property Facility   $facility
 * @property string     $name
 * @property int        $enabled
 */
class Field extends Domain
{
    /** @var FieldOrm */
    private $fieldOrm;

    /** @var Facility */
    private $facility;

    /**
     * @param FieldOrm  $fieldOrm
     * @param Facility  $facility (defaults to null)
     */
    protected function __construct(FieldOrm $fieldOrm, $facility = null)
    {
        $this->fieldOrm = $fieldOrm;
        $this->facility = isset($facility) ? $facility : Facility::lookupById($fieldOrm->facilityId);
    }

    /**
     * @param Facility  $facility
     * @param string    $name
     * @param int       $enabled (defaults to 1)
     * @param bool      $ignoreIfAlreadyExists
     *
     * @return Field
     * @throws DuplicateEntryException
     */
    public static function create(
        $facility,
        $name,
        $enabled = 1,
        $ignoreIfAlreadyExists = false)
    {
        try {
            $fieldOrm = FieldOrm::create($facility->id, $name, $enabled);
            return new static($fieldOrm, $facility);
        } catch (DuplicateEntryException $e) {
            if ($ignoreIfAlreadyExists) {
                $fieldOrm = FieldOrm::loadByFacilityIdAndName($facility->id, $name);
                return new static($fieldOrm, $facility);
            } else {
                throw $e;
            }
        }
    }

    /**
     * @param int $fieldId
     *
     * @return Field
     */
    public static function lookupById($fieldId)
    {
        $fieldOrm = FieldOrm::loadById($fieldId);
        return new static($fieldOrm);
    }

    /**
     * @param Facility $facility
     * @param string $name
     *
     * @return Field
     */
    public static function lookupByName($facility, $name)
    {
        Precondition::isNonEmpty($name, 'name should not be empty');
        $fieldOrm = FieldOrm::loadByFacilityIdAndName($facility->id, $name);
        return new static($fieldOrm, $facility);
    }

    /**
     * @param Facility $facility
     *
     * @return Field[]
     */
    public static function lookupByFacility($facility)
    {
        $fields = [];

        $fieldOrms = FieldOrm::loadByFacilityId($facility->id);
        foreach ($fieldOrms as $fieldOrm){
            $fields[] = new static($fieldOrm, $facility);
        }

        usort($fields, "static::compare");

        return $fields;
    }

    /**
     * @param Field $a
     * @param Field $b
     * @return int - -1, 0, 1 based on how $a name compares with $b
     */
    public static function compare($a, $b)
    {
        if (strlen($a->name) != strlen($b->name)) {
            return strlen($a->name) - strlen($b->name);
        }

        return strcmp($a->name, $b->name);
    }

    /**
     * Return true if field found; false otherwise.
     *
     * @param Facility  $facility
     * @param string    $name
     * @param Field     $field - output parameter
     *
     * @return bool
     */
    public static function findByName($facility, $name, &$field)
    {
        Precondition::isNonEmpty($name, 'name should not be empty');

        try {
            $fieldOrm = FieldOrm::loadByFacilityIdAndName($facility->id, $name);
            $field = new static($fieldOrm, $facility);
            return true;
        } catch (NoResultsException $e) {
            return false;
        }
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
            case "enabled":
                return $this->fieldOrm->{$propertyName};

            case "facility":
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
            case "name":
            case "enabled":
                $this->fieldOrm->{$propertyName} = $value;
                $this->fieldOrm->save();
                break;

            default:
                Precondition::isTrue(false, "Set not allowed for property: $propertyName");
        }
    }

    /**
     * @return \DateInterval - represents game duration minutes for the field
     */
    public function getGameDurationInMinutesInterval()
    {
        $divisionFields = DivisionField::lookupByField($this);
        $gameDurationMinutes = 0;
        foreach ($divisionFields as $divisionField) {
            if ($divisionField->division->gameDurationMinutes > $gameDurationMinutes) {
                $gameDurationMinutes = $divisionField->division->gameDurationMinutes;
            }
        }

        return new \DateInterval("PT" . $gameDurationMinutes . "M");
    }

    /**
     * Delete GameTimes provided no games are scheduled during the time
     *
     * @param bool $errorIfGameSet - Throw GameExistsForGameTime if there is an attempt
     *                               to delete a time with an assigned game.
     *
     * @throws GameExistsForGameTime
     */
    public function deleteGameTimes($errorIfGameSet = true)
    {
        $gameTimes = GameTime::lookupByField($this);

        // Check to see if games have already been set
        if ($errorIfGameSet) {
            foreach ($gameTimes as $gameTime) {
                if (isset($gameTime->game)) {
                    throw new GameExistsForGameTime($this, $gameTime);
                }
            }
        }

        // Delete existing GameTimes
        foreach ($gameTimes as $gameTime) {
            $gameTime->delete();
        }
    }

    /**
     *  Delete the field - cascading delete
     */
    public function delete()
    {
        // Delete division fields
        $divisionFields = DivisionField::lookupByField($this);
        foreach ($divisionFields as $divisionField) {
            $divisionField->delete();
        }

        // Delete game times
        $gameTimes = GameTime::lookupByField($this);
        foreach ($gameTimes as $gameTime) {
            $gameTime->delete();
        }

        $this->fieldOrm->delete();
    }
}