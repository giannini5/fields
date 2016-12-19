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
 * @property int    $gamesPerTeam
 */
class ScheduleOrm extends PersistenceModel
{
    const FIELD_ID              = 'id';
    const FIELD_DIVISION_ID     = 'divisionId';
    const FIELD_NAME            = 'name';
    const FIELD_GAMES_PER_TEAM  = 'gamesPerTeam';

    protected static $fields = [
        self::FIELD_ID              => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_DIVISION_ID     => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_NAME            => [FV::STRING, [FV::NO_CONSTRAINTS]],
        self::FIELD_GAMES_PER_TEAM  => [FV::INT,    [FV::POSITIVE]],
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
     * @param int       $divisionId
     * @param string    $name
     * @param int       $gamesPerTeam
     *
     * @return ScheduleOrm
     * @throws DuplicateEntryException
     */
    public static function create(
        $divisionId,
        $name,
        $gamesPerTeam)
    {
        $result = self::getPersistenceDriver()->create(
            [
                self::FIELD_DIVISION_ID     => $divisionId,
                self::FIELD_NAME            => $name,
                self::FIELD_GAMES_PER_TEAM  => $gamesPerTeam,
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
     * Load a ScheduleOrm by divisionId, name
     *
     * @param int       $divisionId
     * @param string    $name
     *
     * @return ScheduleOrm
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
     * Load a ScheduleOrms by divisionId
     *
     * @param int $divisionId
     *
     * @return ScheduleOrm[]
     */
    public static function loadByDivisionId($divisionId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_DIVISION_ID => $divisionId
            ]);

        $scheduleOrms = [];
        foreach ($results as $result) {
            $scheduleOrms[] = new static($result);
        }

        return $scheduleOrms;
    }
}