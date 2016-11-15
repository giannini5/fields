<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Orm\FieldValidator as FV;
use DAG\Framework\Orm\PersistenceConfig as PC;
use DAG\Framework\Orm\PersistenceModel;
use DAG\Framework\Orm\DuplicateEntryException;


/**
 * @property int    $id
 * @property int    $divisionId
 * @property string $name
 */
class PoolOrm extends PersistenceModel
{
    const FIELD_ID          = 'id';
    const FIELD_DIVISION_ID = 'divisionId';
    const FIELD_NAME        = 'name';

    protected static $fields = [
        self::FIELD_ID          => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_DIVISION_ID => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_NAME        => [FV::STRING, [FV::NO_CONSTRAINTS]],
    ];

    protected static $config = [
        PC::PERSISTENCE_DRIVER => PC::DRIVER_MYSQL,
        PC::SCHEMA             => 'schedule_rw',
        PC::TABLE              => 'pool',
        PC::AUTO_INC_FIELD     => self::FIELD_ID,
        PC::PRIMARY_KEYS       => [self::FIELD_ID],
    ];

    /**
     * Create a PoolOrm
     *
     * @param int       $divisionId
     * @param string    $name
     *
     * @return PoolOrm
     * @throws DuplicateEntryException
     */
    public static function create(
        $divisionId,
        $name)
    {
        $result = self::getPersistenceDriver()->create(
            [
                self::FIELD_DIVISION_ID => $divisionId,
                self::FIELD_NAME        => $name,
            ],
            function ($item) {
                return $item !== null;
            }
        );

        return new static($result);
    }

    /**
     * Load a PoolOrm by id
     *
     * @param int $id
     *
     * @return PoolOrm
     */
    public static function loadById($id)
    {
        $result = self::getPersistenceDriver()->getOne([self::FIELD_ID => $id]);

        return new static($result);
    }

    /**
     * Load a PoolOrm by divisionId, name
     *
     * @param int       $divisionId
     * @param string    $name
     *
     * @return PoolOrm
     */
    public static function loadByDivisionIdAndName($divisionId, $name)
    {
        $result = self::getPersistenceDriver()->getOne(
            [
                self::FIELD_DIVISION_ID => $divisionId,
                self::FIELD_NAME        => $name,
            ]);

        return new static($result);
    }

    /**
     * Load a PoolOrms by divisionId
     *
     * @param int $divisionId
     *
     * @return array []   PoolOrms
     */
    public static function loadByDivisionId($divisionId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_DIVISION_ID  => $divisionId
            ]);

        $poolOrms = [];
        foreach ($results as $result) {
            $poolOrms[] = new static($result);
        }

        return $poolOrms;
    }
}