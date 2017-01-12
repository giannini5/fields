<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Orm\FieldValidator as FV;
use DAG\Framework\Orm\PersistenceConfig as PC;
use DAG\Framework\Orm\PersistenceModel;
use DAG\Framework\Orm\DuplicateEntryException;

/**
 * @property int    $id
 * @property int    $scheduleId
 * @property string $name
 * @property int    $gamesAgainstPoolId
 */
class PoolOrm extends PersistenceModel
{
    const FIELD_ID                      = 'id';
    const FIELD_SCHEDULE_ID             = 'scheduleId';
    const FIELD_NAME                    = 'name';
    const FIELD_GAMES_AGAINST_POOL_ID   = 'gamesAgainstPoolId';

    protected static $fields = [
        self::FIELD_ID                      => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_SCHEDULE_ID             => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_NAME                    => [FV::STRING, [FV::NO_CONSTRAINTS]],
        self::FIELD_GAMES_AGAINST_POOL_ID   => [FV::INT,    [FV::NO_CONSTRAINTS], null],
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
     * @param int       $scheduleId
     * @param string    $name
     * @param int       $gamesAgainstPoolId
     *
     * @return PoolOrm
     * @throws DuplicateEntryException
     */
    public static function create(
        $scheduleId,
        $name,
        $gamesAgainstPoolId = null)
    {
        $result = self::getPersistenceDriver()->create(
            [
                self::FIELD_SCHEDULE_ID             => $scheduleId,
                self::FIELD_NAME                    => $name,
                self::FIELD_GAMES_AGAINST_POOL_ID   => $gamesAgainstPoolId,
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
     * @param string    $name
     *
     * @return PoolOrm
     */
    public static function loadByDivisionIdAndName($name)
    {
        $result = self::getPersistenceDriver()->getOne(
            [
                self::FIELD_NAME        => $name,
            ]);

        return new static($result);
    }

    /**
     * Load a PoolOrm by scheduleId, name
     *
     * @param int       $scheduleId
     * @param string    $name
     *
     * @return PoolOrm
     */
    public static function loadByScheduleIdAndName($scheduleId, $name)
    {
        $result = self::getPersistenceDriver()->getOne(
            [
                self::FIELD_SCHEDULE_ID => $scheduleId,
                self::FIELD_NAME        => $name,
            ]);

        return new static($result);
    }

    /**
     * Load a PoolOrms by scheduleId
     *
     * @param int $scheduleId
     *
     * @return PoolOrm[]
     */
    public static function loadByScheduleId($scheduleId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_SCHEDULE_ID => $scheduleId
            ]);

        $poolOrms = [];
        foreach ($results as $result) {
            $poolOrms[] = new static($result);
        }

        return $poolOrms;
    }
}