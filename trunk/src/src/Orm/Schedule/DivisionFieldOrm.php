<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Orm\FieldValidator as FV;
use DAG\Framework\Orm\PersistenceConfig as PC;
use DAG\Framework\Orm\PersistenceModel;
use DAG\Framework\Orm\DuplicateEntryException;
use DAG\Framework\Orm\NoResultsException;


/**
 * @property int    $id
 * @property int    $divisionId
 * @property int    $fieldId
 */
class DivisionFieldOrm extends PersistenceModel
{
    const FIELD_ID          = 'id';
    const FIELD_DIVISION_ID = 'divisionId';
    const FIELD_FIELD_ID    = 'fieldId';

    protected static $fields = [
        self::FIELD_ID          => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_DIVISION_ID => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_FIELD_ID    => [FV::INT,    [FV::NO_CONSTRAINTS]],
    ];

    protected static $config = [
        PC::PERSISTENCE_DRIVER  => PC::DRIVER_MYSQL,
        PC::SCHEMA              => 'schedule_rw',
        PC::TABLE               => 'divisionField',
        PC::AUTO_INC_FIELD      => self::FIELD_ID,
        PC::PRIMARY_KEYS        => [self::FIELD_ID],
    ];

    /**
     * Constructor that Creates a DivisionField
     * On return, the object exists in all persistent storage locations specified in the configuration.
     *
     * @param int $divisionId
     * @param int $fieldId
     *
     * @return DivisionFieldOrm
     * @throws DuplicateEntryException
     */
    public static function create(
        $divisionId,
        $fieldId
    )
    {
        $result = self::getPersistenceDriver()->create(
            [
                self::FIELD_DIVISION_ID     => $divisionId,
                self::FIELD_FIELD_ID        => $fieldId,
            ],
            function ($item) {
                return $item !== null;
            }
        );

        return new static($result);
    }

    /**
     * Constructs a DivisionField from the primary key attributes
     *
     * @param int $id
     *
     * @return DivisionFieldOrm
     * @throws NoResultsException
     */
    public static function loadById($id)
    {
        $result = self::getPersistenceDriver()->getOne(
            [
                self::FIELD_ID => $id,
            ]
        );

        return new static($result);
    }

    /**
     * Load a DivisionFieldOrms by divisionId
     *
     * @param int $divisionId
     *
     * @return array []   divisionFieldOrms
     */
    public static function loadByDivisionId($divisionId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [self::FIELD_DIVISION_ID => $divisionId]);

        $divisionFieldOrms = [];
        foreach ($results as $result) {
            $divisionFieldOrms[] = new static($result);
        }

        return $divisionFieldOrms;
    }

    /**
     * Load a DivisionFieldOrms by fieldId
     *
     * @param int $fieldId
     *
     * @return DivisionFieldOrm[]
     */
    public static function loadByFieldId($fieldId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [self::FIELD_FIELD_ID => $fieldId]);

        $divisionFieldOrms = [];
        foreach ($results as $result) {
            $divisionFieldOrms[] = new static($result);
        }

        return $divisionFieldOrms;
    }

    /**
     * Constructs a DivisionField from the divisionId, fieldId
     *
     * @param int       $divisionId
     * @param int       $fieldId
     *
     * @return DivisionFieldOrm
     * @throws NoResultsException
     */
    public static function loadByDivisionIdAndField($divisionId, $fieldId)
    {
        $result = self::getPersistenceDriver()->getOne(
            [
                self::FIELD_DIVISION_ID => $divisionId,
                self::FIELD_FIELD_ID    => $fieldId,
            ]
        );

        return new static($result);
    }
}