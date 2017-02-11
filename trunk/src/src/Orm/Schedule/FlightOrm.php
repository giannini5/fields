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
 */
class FlightOrm extends PersistenceModel
{
    const FIELD_ID                      = 'id';
    const FIELD_SCHEDULE_ID             = 'scheduleId';
    const FIELD_NAME                    = 'name';

    protected static $fields = [
        self::FIELD_ID                      => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_SCHEDULE_ID             => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_NAME                    => [FV::STRING, [FV::NO_CONSTRAINTS]],
    ];

    protected static $config = [
        PC::PERSISTENCE_DRIVER => PC::DRIVER_MYSQL,
        PC::SCHEMA             => 'schedule_rw',
        PC::TABLE              => 'flight',
        PC::AUTO_INC_FIELD     => self::FIELD_ID,
        PC::PRIMARY_KEYS       => [self::FIELD_ID],
    ];

    /**
     * Create a FlightOrm
     *
     * @param int       $scheduleId
     * @param string    $name
     *
     * @return FlightOrm
     * @throws DuplicateEntryException
     */
    public static function create(
        $scheduleId,
        $name)
    {
        $result = self::getPersistenceDriver()->create(
            [
                self::FIELD_SCHEDULE_ID             => $scheduleId,
                self::FIELD_NAME                    => $name,
            ],
            function ($item) {
                return $item !== null;
            }
        );

        return new static($result);
    }

    /**
     * Load a FlightOrm by id
     *
     * @param int $id
     *
     * @return FlightOrm
     */
    public static function loadById($id)
    {
        $result = self::getPersistenceDriver()->getOne([self::FIELD_ID => $id]);

        return new static($result);
    }

    /**
     * Load a FlightOrm by scheduleId, name
     *
     * @param int       $scheduleId
     * @param string    $name
     *
     * @return FlightOrm
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
     * Load a FlightOrms by scheduleId
     *
     * @param int $scheduleId
     *
     * @return FlightOrm[]
     */
    public static function loadByScheduleId($scheduleId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_SCHEDULE_ID => $scheduleId
            ]);

        $flightOrms = [];
        foreach ($results as $result) {
            $flightOrms[] = new static($result);
        }

        return $flightOrms;
    }
}