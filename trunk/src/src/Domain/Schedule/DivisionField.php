<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Framework\Orm\DuplicateEntryException;
use DAG\Framework\Orm\NoResultsException;
use DAG\Orm\Schedule\DivisionFieldOrm;
use DAG\Framework\Exception\Precondition;


/**
 * @property int        $id
 * @property Division   $division
 * @property Field      $field
 */
class DivisionField extends Domain
{
    /** @var DivisionFieldOrm */
    private $divisionFieldOrm;

    /** @var Division */
    private $division;

    /** @var Field */
    private $field;

    /**
     * @param DivisionFieldOrm  $divisionFieldOrm
     * @param Division  $division (defaults to null)
     * @param Field     $field (defaults to null)
     */
    protected function __construct(DivisionFieldOrm $divisionFieldOrm, $division = null, $field = null)
    {
        $this->divisionFieldOrm = $divisionFieldOrm;
        $this->division = isset($division) ? $division : Division::lookupById($divisionFieldOrm->divisionId);
        $this->field    = isset($field) ? $field : Field::lookupById($divisionFieldOrm->fieldId);
    }

    /**
     * @param Division $division
     * @param Field $field
     * @param bool $ignoreIfAlreadyExists
     *
     * @return DivisionField
     * @throws DuplicateEntryException
     * @throws \Exception
     */
    public static function create(
        $division,
        $field,
        $ignoreIfAlreadyExists = false)
    {
        try {
            $divisionFieldOrm = DivisionFieldOrm::create($division->id, $field->id);
            return new static($divisionFieldOrm, $division, $field);
        } catch (DuplicateEntryException $e) {
            if ($ignoreIfAlreadyExists) {
                $divisionFieldOrm = DivisionFieldOrm::loadByDivisionIdAndField($division->id, $field->id);
                return new static($divisionFieldOrm, $division, $field);
            } else {
                throw $e;
            }
        }
    }

    /**
     * @param int $divisionFieldId
     *
     * @return DivisionField
     */
    public static function lookupById($divisionFieldId)
    {
        $divisionFieldOrm = DivisionFieldOrm::loadById($divisionFieldId);
        return new static($divisionFieldOrm);
    }

    /**
     * @param Division  $division
     * @param Field     $field
     *
     * @return DivisionField
     */
    public static function lookupByDivisionAndField($division, $field)
    {
        $divisionFieldOrm = DivisionFieldOrm::loadByDivisionIdAndField($division->id, $field->id);
        return new static($divisionFieldOrm, $division, $field);
    }

    /**
     * @param Division      $division
     * @param Field         $field
     * @param DivisionField $divisionField
     *
     * @return bool
     */
    public static function findByDivisionAndField($division, $field, &$divisionField)
    {
        try {
            $divisionFieldOrm = DivisionFieldOrm::loadByDivisionIdAndField($division->id, $field->id);
            $divisionField = new static($divisionFieldOrm, $division, $field);
            return true;
        } catch (NoResultsException $e) {
            return false;
        }
    }

    /**
     * @param Division $division
     * @param bool      $includeDisabledFields
     *
     * @return DivisionField[]
     */
    public static function lookupByDivision($division, $includeDisabledFields = false)
    {
        $divisionFields = [];

        $divisionFieldOrms = DivisionFieldOrm::loadByDivisionId($division->id);
        foreach ($divisionFieldOrms as $divisionFieldOrm){
            $divisionField = new static($divisionFieldOrm, $division);
            if ($includeDisabledFields or $divisionField->field->enabled == 1) {
                $divisionFields[] = new static($divisionFieldOrm, $division);
            }
        }

        return $divisionFields;
    }

    /**
     * @param Field $field
     *
     * @return DivisionField[]
     */
    public static function lookupByField($field)
    {
        $divisionFields = [];

        $divisionFieldOrms = DivisionFieldOrm::loadByFieldId($field->id);
        foreach ($divisionFieldOrms as $divisionFieldOrm){
            $divisionFields[] = new static($divisionFieldOrm, null, $field);
        }

        return $divisionFields;
    }

    /**
     * @param $propertyName
     * @return int|string
     */
    public function __get($propertyName)
    {
        switch ($propertyName) {
            case "id":
                return $this->divisionFieldOrm->id;

            case "division":
            case "field":
                return $this->{$propertyName};

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
                return false;
        }
    }

    /**
     *  Delete the divisionField
     */
    public function delete()
    {
        $this->divisionFieldOrm->delete();
    }
}