<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Orm\FieldValidator as FV;
use DAG\Framework\Orm\PersistenceConfig as PC;
use DAG\Framework\Orm\PersistenceModel;


/**
 * @property int    $id
 * @property int    $leagueId
 * @property string $name
 * @property string $startDate
 * @property string $endDate
 * @property string $startTime
 * @property string $endTime
 * @property string $daysOfWeek
 * @property bool   $enabled
 */
class SeasonOrm extends PersistenceModel
{
    const FIELD_ID           = 'id';
    const FIELD_LEAGUE_ID    = 'leagueId';
    const FIELD_NAME         = 'name';
    const FIELD_START_DATE   = 'startDate';
    const FIELD_END_DATE     = 'endDate';
    const FIELD_START_TIME   = 'startTime';
    const FIELD_END_TIME     = 'endTime';
    const FIELD_DAYS_OF_WEEK = 'daysOfWeek';
    const FIELD_ENABLED      = 'enabled';

    protected static $fields = [
        self::FIELD_ID           => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_LEAGUE_ID    => [FV::INT,    [FV::NO_CONSTRAINTS], ''],
        self::FIELD_NAME         => [FV::STRING, [FV::NO_CONSTRAINTS], ''],
        self::FIELD_START_DATE   => [FV::DATE,   [FV::NO_CONSTRAINTS], ''],
        self::FIELD_END_DATE     => [FV::DATE,   [FV::NO_CONSTRAINTS], ''],
        self::FIELD_START_TIME   => [FV::TIME,   [FV::NO_CONSTRAINTS], ''],
        self::FIELD_END_TIME     => [FV::TIME,   [FV::NO_CONSTRAINTS], ''],
        self::FIELD_DAYS_OF_WEEK => [FV::STRING, [FV::NO_CONSTRAINTS], '0000011'],
        self::FIELD_ENABLED      => [FV::INT,    [FV::NO_CONSTRAINTS], 1],
    ];

    protected static $config = [
        PC::PERSISTENCE_DRIVER => PC::DRIVER_MYSQL,
        PC::SCHEMA             => 'schedule_rw',
        PC::TABLE              => 'season',
        PC::AUTO_INC_FIELD     => self::FIELD_ID,
        PC::PRIMARY_KEYS       => [self::FIELD_LEAGUE_ID, self::FIELD_NAME],
    ];

    /**
     * Create a Season
     *
     * @param $leagueId
     * @param $name
     *
     * @param $startDate
     * @param $endDate
     * @param $startTime
     * @param $endTime
     * @param $daysOfWeek
     * @param $enabled
     *
     * @return SeasonOrm
     * @throws \DAG\Framework\Orm\DuplicateEntryException
     */
    public static function create($leagueId, $name, $startDate, $endDate, $startTime, $endTime, $daysOfWeek, $enabled)
    {
        $result = self::getPersistenceDriver()->create(
            array_filter(
                [
                    self::FIELD_LEAGUE_ID    => $leagueId,
                    self::FIELD_NAME         => $name,
                    self::FIELD_START_DATE   => $startDate,
                    self::FIELD_END_DATE     => $endDate,
                    self::FIELD_START_TIME   => $startTime,
                    self::FIELD_END_TIME     => $endTime,
                    self::FIELD_DAYS_OF_WEEK => $daysOfWeek,
                    self::FIELD_ENABLED      => $enabled,
                ],
                function ($item) {
                    return $item !== null;
                }
            )
        );

        return new static($result);
    }

    /**
     * Load a SeasonOrm by id
     *
     * @param int $id
     *
     * @return SeasonOrm
     */
    public static function loadById($id)
    {
        $result = self::getPersistenceDriver()->getOne([self::FIELD_ID => $id]);

        return new static($result);
    }

    /**
     * Load a SeasonOrm by leagueId, name
     *
     * @param int       $leagueId
     * @param string    $name
     *
     * @return SeasonOrm
     */
    public static function loadByLeagueIdAndName($leagueId, $name)
    {
        $result = self::getPersistenceDriver()->getOne(
            [self::FIELD_LEAGUE_ID  => $leagueId,
             self::FIELD_NAME       => $name]);

        return new static($result);
    }

    /**
     * Load a SeasonOrms by leagueId
     *
     * @param int $leagueId
     *
     * @return array []   SeasonOrms
     */
    public static function loadByLeagueId($leagueId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [self::FIELD_LEAGUE_ID  => $leagueId]);

        $seasonOrms = [];
        foreach ($results as $result) {
            $seasonOrms[] = new static($result);
        }

        return $seasonOrms;
    }
}