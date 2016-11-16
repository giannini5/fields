<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Orm\Schedule\FieldOrm;
use DAG\Framework\Exception\Precondition;


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
     *
     * @return Field
     */
    public static function create(
        $facility,
        $name,
        $enabled = 1)
    {
        $fieldOrm = FieldOrm::create($facility->id, $name, $enabled);
        return new static($fieldOrm, $facility);
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
     *  Delete the field
     *  TODO: Change to cascading delete
     */
    public function delete()
    {
        $this->fieldOrm->delete();
    }
}