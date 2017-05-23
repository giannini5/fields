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
 * @property int    $include5th6thGame
 * @property int    $include3rd4thGame
 * @property int    $includeSemiFinalGames
 * @property int    $includeChampionshipGame
 */
class FlightOrm extends PersistenceModel
{
    const FIELD_ID                          = 'id';
    const FIELD_SCHEDULE_ID                 = 'scheduleId';
    const FIELD_NAME                        = 'name';
    const FIELD_INCLUDE_5TH_6TH_GAME        = 'include5th6thGame';
    const FIELD_INCLUDE_3RD_4TH_GAME        = 'include3rd4thGame';
    const FIELD_INCLUDE_SEMI_FINAL_GAMES    = 'includeSemiFinalGames';
    const FIELD_INCLUDE_CHAMPIONSHIP_GAME   = 'includeChampionshipGame';

    protected static $fields = [
        self::FIELD_ID                          => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_SCHEDULE_ID                 => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_NAME                        => [FV::STRING, [FV::NO_CONSTRAINTS]],
        self::FIELD_INCLUDE_5TH_6TH_GAME        => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_INCLUDE_3RD_4TH_GAME        => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_INCLUDE_SEMI_FINAL_GAMES    => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_INCLUDE_CHAMPIONSHIP_GAME   => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
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
     * @param int       $include5th6thGame;
     * @param int       $include3rd4thGame;
     * @param int       $includeSemiFinalGames;
     * @param int       $includeChampionshipGame;
     *
     * @return FlightOrm
     * @throws DuplicateEntryException
     */
    public static function create(
        $scheduleId,
        $name,
        $include5th6thGame,
        $include3rd4thGame,
        $includeSemiFinalGames,
        $includeChampionshipGame)
    {
        $result = self::getPersistenceDriver()->create(
            [
                self::FIELD_SCHEDULE_ID                 => $scheduleId,
                self::FIELD_NAME                        => $name,
                self::FIELD_INCLUDE_5TH_6TH_GAME        => $include5th6thGame,
                self::FIELD_INCLUDE_3RD_4TH_GAME        => $include3rd4thGame,
                self::FIELD_INCLUDE_SEMI_FINAL_GAMES    => $includeSemiFinalGames,
                self::FIELD_INCLUDE_CHAMPIONSHIP_GAME   => $includeChampionshipGame,

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