<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Orm\FieldValidator as FV;
use DAG\Framework\Orm\PersistenceConfig as PC;
use DAG\Framework\Orm\PersistenceModel;
use DAG\Framework\Orm\DuplicateEntryException;

/**
 * @property int    $id
 * @property int    $scheduleId
 * @property int    $gameTimeId
 * @property int    $homeTeamId
 * @property int    $visitingTeamId
 */
class GameOrm extends PersistenceModel
{
    const FIELD_ID                  = 'id';
    const FIELD_SCHEDULE_ID         = 'scheduleId';
    const FIELD_GAME_TIME_ID        = 'gameTimeId';
    const FIELD_HOME_TEAM_ID        = 'homeTeamId';
    const FIELD_VISITING_TEAM_ID    = 'visitingTeamId';

    protected static $fields = [
        self::FIELD_ID                  => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_SCHEDULE_ID         => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_GAME_TIME_ID        => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_HOME_TEAM_ID        => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_VISITING_TEAM_ID    => [FV::INT,    [FV::NO_CONSTRAINTS]],
    ];

    protected static $config = [
        PC::PERSISTENCE_DRIVER => PC::DRIVER_MYSQL,
        PC::SCHEMA             => 'schedule_rw',
        PC::TABLE              => 'game',
        PC::AUTO_INC_FIELD     => self::FIELD_ID,
        PC::PRIMARY_KEYS       => [self::FIELD_ID],
    ];

    /**
     * Create a GameOrm
     *
     * @param int       $scheduleId
     * @param int       $gameTimeId
     * @param int       $homeTeamId
     * @param int       $visitingTeamId
     *
     * @return GameOrm
     * @throws DuplicateEntryException
     */
    public static function create(
        $scheduleId,
        $gameTimeId,
        $homeTeamId,
        $visitingTeamId)
    {
        $result = self::getPersistenceDriver()->create(
            [
                self::FIELD_SCHEDULE_ID         => $scheduleId,
                self::FIELD_GAME_TIME_ID        => $gameTimeId,
                self::FIELD_HOME_TEAM_ID        => $homeTeamId,
                self::FIELD_VISITING_TEAM_ID    => $visitingTeamId,
            ],
            function ($item) {
                return $item !== null;
            }
        );

        return new static($result);
    }

    /**
     * Load a GameOrm by id
     *
     * @param int $id
     *
     * @return GameOrm
     */
    public static function loadById($id)
    {
        $result = self::getPersistenceDriver()->getOne([self::FIELD_ID => $id]);

        return new static($result);
    }

    /**
     * Load a GameOrm by gameTimeId
     *
     * @param int $gameTimeId
     *
     * @return GameOrm
     */
    public static function loadByGameTimeId($gameTimeId)
    {
        $result = self::getPersistenceDriver()->getOne([self::FIELD_GAME_TIME_ID => $gameTimeId]);

        return new static($result);
    }

    /**
     * Get GameOrms for a scheduleId
     *
     * @param $scheduleId
     *
     * @return array [] GameOrm
     */
    public static function loadByScheduleId($scheduleId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_SCHEDULE_ID => $scheduleId
            ]);

        $gameOrms = [];
        foreach ($results as $result) {
            $gameOrms[] = new static($result);
        }

        return $gameOrms;
    }

    /**
     * Load a GameOrms by teamId
     *
     * @param int $teamId
     *
     * @return array []   GameOrms
     */
    public static function loadByTeamId($teamId)
    {
        $results = self::getPersistenceDriver()->getManyFromCustomMySqlQuery(
            [],
            "where " . self::FIELD_HOME_TEAM_ID . " = $teamId or " . self::FIELD_VISITING_TEAM_ID . " = $teamId");

        $gameOrms = [];
        foreach ($results as $result) {
            $gameOrms[] = new static($result);
        }

        return $gameOrms;
    }
}