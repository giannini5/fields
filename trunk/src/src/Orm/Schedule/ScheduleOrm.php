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
 * @property string $startDate
 * @property string $endDate
 * @property string $startTime
 * @property string $endTime
 * @property string $daysOfWeek
 * @property string $displayNotes
 * @property int    $published
 */
class ScheduleOrm extends PersistenceModel
{
    const FIELD_ID                      = 'id';
    const FIELD_DIVISION_ID             = 'divisionId';
    const FIELD_NAME                    = 'name';
    const FIELD_SCHEDULE_TYPE           = 'scheduleType';
    const FIELD_GAMES_PER_TEAM          = 'gamesPerTeam';
    const FIELD_START_DATE              = 'startDate';
    const FIELD_END_DATE                = 'endDate';
    const FIELD_START_TIME              = 'startTime';
    const FIELD_END_TIME                = 'endTime';
    const FIELD_DAYS_OF_WEEK            = 'daysOfWeek';
    const FIELD_DISPLAY_NOTES           = 'displayNotes';
    const FIELD_PUBLISHED               = 'published';

    const SCHEDULE_TYPE_LEAGUE      = 'L';
    const SCHEDULE_TYPE_TOURNAMENT  = 'T';
    const SCHEDULE_TYPE_BRACKET     = 'B';

    public static $scheduleTypes = [
        self::SCHEDULE_TYPE_LEAGUE      => 'League',
        self::SCHEDULE_TYPE_TOURNAMENT  => 'Tournament',
        self::SCHEDULE_TYPE_BRACKET     => 'Bracket',
    ];

    protected static $fields = [
        self::FIELD_ID                      => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_DIVISION_ID             => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_NAME                    => [FV::STRING, [FV::NO_CONSTRAINTS]],
        self::FIELD_SCHEDULE_TYPE           => [FV::STRING, [FV::ENUM, [self::SCHEDULE_TYPE_LEAGUE, self::SCHEDULE_TYPE_TOURNAMENT, self::SCHEDULE_TYPE_BRACKET]]],
        self::FIELD_GAMES_PER_TEAM          => [FV::INT,    [FV::POSITIVE]],
        self::FIELD_START_DATE              => [FV::DATE,   [FV::NO_CONSTRAINTS], ''],
        self::FIELD_END_DATE                => [FV::DATE,   [FV::NO_CONSTRAINTS], ''],
        self::FIELD_START_TIME              => [FV::TIME,   [FV::NO_CONSTRAINTS], ''],
        self::FIELD_END_TIME                => [FV::TIME,   [FV::NO_CONSTRAINTS], ''],
        self::FIELD_DAYS_OF_WEEK            => [FV::STRING, [FV::NO_CONSTRAINTS], '0000011'],
        self::FIELD_DISPLAY_NOTES           => [FV::STRING, [FV::NO_CONSTRAINTS], ''],
        self::FIELD_PUBLISHED               => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
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
     * @param string    $scheduleType
     * @param int       $gamesPerTeam
     * @param string    $startDate
     * @param string    $endDate
     * @param string    $startTime
     * @param string    $endTime
     * @param string    $daysOfWeek
     * @param int       $published
     * @param string    $displayNotes
     *
     * @return ScheduleOrm
     * @throws DuplicateEntryException
     */
    public static function create(
        $divisionId,
        $name,
        $scheduleType,
        $gamesPerTeam,
        $startDate,
        $endDate,
        $startTime,
        $endTime,
        $daysOfWeek,
        $published,
        $displayNotes = '')
    {
        $result = self::getPersistenceDriver()->create(
            array_filter(
                [
                    self::FIELD_DIVISION_ID             => $divisionId,
                    self::FIELD_NAME                    => $name,
                    self::FIELD_SCHEDULE_TYPE           => $scheduleType,
                    self::FIELD_GAMES_PER_TEAM          => $gamesPerTeam,
                    self::FIELD_START_DATE              => $startDate,
                    self::FIELD_END_DATE                => $endDate,
                    self::FIELD_START_TIME              => $startTime,
                    self::FIELD_END_TIME                => $endTime,
                    self::FIELD_DAYS_OF_WEEK            => $daysOfWeek,
                    self::FIELD_DISPLAY_NOTES           => $displayNotes,
                    self::FIELD_PUBLISHED               => $published,
                ],
                function ($item) {
                    return $item !== null;
                }
            )
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