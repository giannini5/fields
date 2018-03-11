<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Orm\FieldValidator as FV;
use DAG\Framework\Orm\PersistenceConfig as PC;
use DAG\Framework\Orm\PersistenceModel;
use DAG\Framework\Orm\DuplicateEntryException;

/**
 * @property int    $id
 * @property int    $seasonId
 * @property string $name
 * @property string $gender
 * @property int    $maxPlayersPerTeam
 * @property int    $gameDurationMinutes
 * @property int    $minutesBetweenGames
 * @property int    $scoringTracked
 * @property int    $displayOrder
 * @property int    $combineLeagueSchedules
 */
class DivisionOrm extends PersistenceModel
{
    const FIELD_ID                          = 'id';
    const FIELD_SEASON_ID                   = 'seasonId';
    const FIELD_NAME                        = 'name';
    const FIELD_GENDER                      = 'gender';
    const FIELD_MAX_PLAYERS_PER_TEAM        = 'maxPlayersPerTeam';
    const FIELD_GAME_DURATION_MINUTES       = 'gameDurationMinutes';
    const FIELD_MINUTES_BETWEEN_GAMES       = 'minutesBetweenGames';
    const FIELD_SCORING_TRACKED             = 'scoringTracked';
    const FIELD_DISPLAY_ORDER               = 'displayOrder';
    const FIELD_COMBINE_LEAGUE_SCHEDULES    = 'combineLeagueSchedules';

    protected static $fields = [
        self::FIELD_ID                          => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_SEASON_ID                   => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_NAME                        => [FV::STRING, [FV::NO_CONSTRAINTS]],
        self::FIELD_GENDER                      => [FV::STRING, [FV::NO_CONSTRAINTS]],
        self::FIELD_MAX_PLAYERS_PER_TEAM        => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_GAME_DURATION_MINUTES       => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_MINUTES_BETWEEN_GAMES       => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_SCORING_TRACKED             => [FV::INT,    [FV::NO_CONSTRAINTS], 1],
        self::FIELD_DISPLAY_ORDER               => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_COMBINE_LEAGUE_SCHEDULES    => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
    ];

    protected static $config = [
        PC::PERSISTENCE_DRIVER => PC::DRIVER_MYSQL,
        PC::SCHEMA             => 'schedule_rw',
        PC::TABLE              => 'division',
        PC::AUTO_INC_FIELD     => self::FIELD_ID,
        PC::PRIMARY_KEYS       => [self::FIELD_ID],
    ];

    /**
     * Create a DivisionOrm
     *
     * @param int       $seasonId
     * @param string    $name
     * @param string    $gender
     * @param int       $maxPlayersPerTeam
     * @param int       $gameDurationMinutes
     * @param int       $minutesBetweenGames
     * @param int       $displayOrder
     * @param int       $scoringTracked
     * @param int       $combineLeagueSchedules
     *
     * @return DivisionOrm
     * @throws DuplicateEntryException
     */
    public static function create(
        $seasonId,
        $name,
        $gender,
        $maxPlayersPerTeam,
        $gameDurationMinutes,
        $minutesBetweenGames,
        $displayOrder,
        $scoringTracked = 1,
        $combineLeagueSchedules = 0)
    {
        $result = self::getPersistenceDriver()->create(
            array_filter(
                [
                    self::FIELD_SEASON_ID                   => $seasonId,
                    self::FIELD_NAME                        => $name,
                    self::FIELD_GENDER                      => $gender,
                    self::FIELD_MAX_PLAYERS_PER_TEAM        => $maxPlayersPerTeam,
                    self::FIELD_GAME_DURATION_MINUTES       => $gameDurationMinutes,
                    self::FIELD_MINUTES_BETWEEN_GAMES       => $minutesBetweenGames,
                    self::FIELD_DISPLAY_ORDER               => $displayOrder,
                    self::FIELD_SCORING_TRACKED             => $scoringTracked,
                    self::FIELD_COMBINE_LEAGUE_SCHEDULES    => $combineLeagueSchedules,
                ],
                function ($item) {
                    return $item !== null;
                }
            )
        );

        return new static($result);
    }

    /**
     * Load a DivisionOrm by id
     *
     * @param int $id
     *
     * @return DivisionOrm
     */
    public static function loadById($id)
    {
        $result = self::getPersistenceDriver()->getOne([self::FIELD_ID => $id]);

        return new static($result);
    }

    /**
     * Load a DivisionOrm by seasonId, name
     *
     * @param int       $seasonId
     * @param string    $name
     * @param string    $gender
     *
     * @return DivisionOrm
     */
    public static function loadBySeasonIdAndNameAndGender($seasonId, $name, $gender)
    {
        $result = self::getPersistenceDriver()->getOne(
            [
                self::FIELD_SEASON_ID   => $seasonId,
                self::FIELD_NAME        => $name,
                self::FIELD_GENDER      => $gender,
            ]);

        return new static($result);
    }

    /**
     * Load a DivisionOrm by seasonId, name
     *
     * @param int       $seasonId
     * @param string    $name
     *
     * @return DivisionOrm[]
     */
    public static function loadBySeasonIdAndName($seasonId, $name)
    {
        $divisions = [];

        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_SEASON_ID   => $seasonId,
                self::FIELD_NAME        => $name,
            ]);

        foreach ($results as $result) {
            $divisions[] = new static($result);
        }

        return $divisions;
    }

    /**
     * Load a DivisionOrms by seasonId
     *
     * @param int $seasonId
     *
     * @return array []   DivisionOrms
     */
    public static function loadBySeasonId($seasonId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_SEASON_ID  => $seasonId
            ]);

        $divisionOrms = [];
        foreach ($results as $result) {
            $divisionOrms[] = new static($result);
        }

        return $divisionOrms;
    }
}