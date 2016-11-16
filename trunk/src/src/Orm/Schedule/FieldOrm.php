<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Orm\FieldValidator as FV;
use DAG\Framework\Orm\PersistenceConfig as PC;
use DAG\Framework\Orm\PersistenceModel;
use DAG\Framework\Orm\DuplicateEntryException;
use DAG\Framework\Orm\NoResultsException;


/**
 * @property int    $id
 * @property int    $facilityId
 * @property string $name
 * @property int    $enabled
 */
class FieldOrm extends PersistenceModel
{
    const FIELD_ID          = 'id';
    const FIELD_FACILITY_ID = 'facilityId';
    const FIELD_NAME        = 'name';
    const FIELD_ENABLED     = 'enabled';

    protected static $fields = [
        self::FIELD_ID          => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_FACILITY_ID => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_NAME        => [FV::STRING, [FV::NO_CONSTRAINTS]],
        self::FIELD_ENABLED     => [FV::INT,    [FV::NO_CONSTRAINTS], 1],
    ];

    protected static $config = [
        PC::PERSISTENCE_DRIVER  => PC::DRIVER_MYSQL,
        PC::SCHEMA              => 'schedule_rw',
        PC::TABLE               => 'field',
        PC::AUTO_INC_FIELD      => self::FIELD_ID,
        PC::PRIMARY_KEYS        => [self::FIELD_ID],
    ];

    /**
     * Constructor that Creates a Field
     * On return, the object exists in all persistent storage locations specified in the configuration.
     *
     * @param int $facilityId
     * @param string $name
     * @param int $enabled
     *
     * @return FieldOrm
     * @throws DuplicateEntryException
     */
    public static function create(
        $facilityId,
        $name,
        $enabled = 1
    )
    {
        $result = self::getPersistenceDriver()->create(
            [
                self::FIELD_FACILITY_ID     => $facilityId,
                self::FIELD_NAME            => $name,
                self::FIELD_ENABLED         => $enabled,
            ],
            function ($item) {
                return $item !== null;
            }
        );

        return new static($result);
    }

    /**
     * Constructs a Field from the primary key attributes
     *
     * @param int $id
     *
     * @return FieldOrm
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
     * Load a FieldOrms by facilityId
     *
     * @param int $facilityId
     *
     * @return array []   fieldOrms
     */
    public static function loadByFacilityId($facilityId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [self::FIELD_FACILITY_ID  => $facilityId]);

        $fieldOrms = [];
        foreach ($results as $result) {
            $fieldOrms[] = new static($result);
        }

        return $fieldOrms;
    }

    /**
     * Constructs a Field from the facilityId and name
     *
     * @param int       $facilityId
     * @param string    $name
     *
     * @return FieldOrm
     * @throws NoResultsException
     */
    public static function loadByFacilityIdAndName($facilityId, $name)
    {
        $result = self::getPersistenceDriver()->getOne(
            [
                self::FIELD_FACILITY_ID => $facilityId,
                self::FIELD_NAME        => $name,
            ]
        );

        return new static($result);
    }
}