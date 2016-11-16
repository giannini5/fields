<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Orm\FieldValidator as FV;
use DAG\Framework\Orm\PersistenceConfig as PC;
use DAG\Framework\Orm\PersistenceModel;
use DAG\Framework\Orm\DuplicateEntryException;

/**
 * @property int    $id
 * @property int    $poolId
 * @property string $name
 */
class ScheduleOrm extends PersistenceModel
{
    const FIELD_ID      = 'id';
    const FIELD_POOL_ID = 'poolId';
    const FIELD_NAME    = 'name';

    protected static $fields = [
        self::FIELD_ID      => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_POOL_ID => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_NAME    => [FV::STRING, [FV::NO_CONSTRAINTS]],
    ];

    protected static $config = [
        PC::PERSISTENCE_DRIVER => PC::DRIVER_MYSQL,
        PC::SCHEMA             => 'schedule_rw',
        PC::TABLE              => 'schedule',
        PC::AUTO_INC_FIELD     => self::FIELD_ID,
        PC::PRIMARY_KEYS       => [self::FIELD_ID],
    ];

    /**
     * Create a ScheduleOrm
     *
     * @param int       $poolId
     * @param string    $name
     *
     * @return ScheduleOrm
     * @throws DuplicateEntryException
     */
    public static function create(
        $poolId,
        $name)
    {
        $result = self::getPersistenceDriver()->create(
            [
                self::FIELD_POOL_ID => $poolId,
                self::FIELD_NAME    => $name,
            ],
            function ($item) {
                return $item !== null;
            }
        );

        return new static($result);
    }

    /**
     * Load a ScheduleOrm by id
     *
     * @param int $id
     *
     * @return ScheduleOrm
     */
    public static function loadById($id)
    {
        $result = self::getPersistenceDriver()->getOne([self::FIELD_ID => $id]);

        return new static($result);
    }

    /**
     * Load a ScheduleOrm by poolId, name
     *
     * @param int       $poolId
     * @param string    $name
     *
     * @return ScheduleOrm
     */
    public static function loadByPoolIdAndName($poolId, $name)
    {
        $result = self::getPersistenceDriver()->getOne(
            [
                self::FIELD_POOL_ID => $poolId,
                self::FIELD_NAME    => $name,
            ]);

        return new static($result);
    }

    /**
     * Load a ScheduleOrms by poolId
     *
     * @param int $poolId
     *
     * @return array []   ScheduleOrms
     */
    public static function loadByPoolId($poolId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_POOL_ID  => $poolId
            ]);

        $scheduleOrms = [];
        foreach ($results as $result) {
            $scheduleOrms[] = new static($result);
        }

        return $scheduleOrms;
    }
}