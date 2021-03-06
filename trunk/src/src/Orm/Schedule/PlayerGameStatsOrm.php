<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Orm\FieldValidator as FV;
use DAG\Framework\Orm\NoResultsException;
use DAG\Framework\Orm\PersistenceConfig as PC;
use DAG\Framework\Orm\PersistenceModel;
use DAG\Framework\Orm\DuplicateEntryException;


/**
 * @property int    $gameId
 * @property int    $teamId
 * @property int    $playerId
 * @property int    $goals
 * @property int    $substitutionQuarter1
 * @property int    $substitutionQuarter2
 * @property int    $substitutionQuarter3
 * @property int    $substitutionQuarter4
 * @property int    $keeperQuarter1
 * @property int    $keeperQuarter2
 * @property int    $keeperQuarter3
 * @property int    $keeperQuarter4
 * @property int    $injuredQuarter1
 * @property int    $injuredQuarter2
 * @property int    $injuredQuarter3
 * @property int    $injuredQuarter4
 * @property int    $absentQuarter1
 * @property int    $absentQuarter2
 * @property int    $absentQuarter3
 * @property int    $absentQuarter4
 * @property int    $yellowCards
 * @property int    $redCard
 */
class PlayerGameStatsOrm extends PersistenceModel
{
    const FIELD_GAME_ID                 = 'gameId';
    const FIELD_TEAM_ID                 = 'teamId';
    const FIELD_PLAYER_ID               = 'playerId';
    const FIELD_GOALS                   = 'goals';
    const FIELD_SUBSTITUTION_QUARTER_1  = 'substitutionQuarter1';
    const FIELD_SUBSTITUTION_QUARTER_2  = 'substitutionQuarter2';
    const FIELD_SUBSTITUTION_QUARTER_3  = 'substitutionQuarter3';
    const FIELD_SUBSTITUTION_QUARTER_4  = 'substitutionQuarter4';
    const FIELD_KEEPER_QUARTER_1        = 'keeperQuarter1';
    const FIELD_KEEPER_QUARTER_2        = 'keeperQuarter2';
    const FIELD_KEEPER_QUARTER_3        = 'keeperQuarter3';
    const FIELD_KEEPER_QUARTER_4        = 'keeperQuarter4';
    const FIELD_INJURED_QUARTER_1       = 'injuredQuarter1';
    const FIELD_INJURED_QUARTER_2       = 'injuredQuarter2';
    const FIELD_INJURED_QUARTER_3       = 'injuredQuarter3';
    const FIELD_INJURED_QUARTER_4       = 'injuredQuarter4';
    const FIELD_ABSENT_QUARTER_1        = 'absentQuarter1';
    const FIELD_ABSENT_QUARTER_2        = 'absentQuarter2';
    const FIELD_ABSENT_QUARTER_3        = 'absentQuarter3';
    const FIELD_ABSENT_QUARTER_4        = 'absentQuarter4';
    const FIELD_YELLOW_CARDS            = 'yellowCards';
    const FIELD_RED_CARD                = 'redCard';

    protected static $fields = [
        self::FIELD_GAME_ID                 => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_TEAM_ID                 => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_PLAYER_ID               => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_GOALS                   => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_SUBSTITUTION_QUARTER_1  => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_SUBSTITUTION_QUARTER_3  => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_SUBSTITUTION_QUARTER_2  => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_SUBSTITUTION_QUARTER_4  => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_KEEPER_QUARTER_1        => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_KEEPER_QUARTER_2        => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_KEEPER_QUARTER_3        => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_KEEPER_QUARTER_4        => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_INJURED_QUARTER_1       => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_INJURED_QUARTER_2       => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_INJURED_QUARTER_3       => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_INJURED_QUARTER_4       => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_ABSENT_QUARTER_1        => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_ABSENT_QUARTER_2        => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_ABSENT_QUARTER_3        => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_ABSENT_QUARTER_4        => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_YELLOW_CARDS            => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_RED_CARD                => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
    ];

    protected static $config = [
        PC::PERSISTENCE_DRIVER => PC::DRIVER_MYSQL,
        PC::SCHEMA             => 'schedule_rw',
        PC::TABLE              => 'playerGameStats',
        PC::PRIMARY_KEYS       => [self::FIELD_GAME_ID, self::FIELD_TEAM_ID, self::FIELD_PLAYER_ID],
    ];

    /**
     * Create a GameOrm
     *
     * @param int       $gameId
     * @param int       $teamId
     * @param int       $playerId
     * @param int       $goals
     * @param int       $substitutionQuarter1
     * @param int       $substitutionQuarter2
     * @param int       $substitutionQuarter3
     * @param int       $substitutionQuarter4
     * @param int       $keeperQuarter1
     * @param int       $keeperQuarter2
     * @param int       $keeperQuarter3
     * @param int       $keeperQuarter4
     * @param int       $injuredQuarter1
     * @param int       $injuredQuarter2
     * @param int       $injuredQuarter3
     * @param int       $injuredQuarter4
     * @param int       $absentQuarter1
     * @param int       $absentQuarter2
     * @param int       $absentQuarter3
     * @param int       $absentQuarter4
     * @param int       $yellowCards
     * @param int       $redCard
     *
     * @return PlayerGameStatsOrm
     * @throws DuplicateEntryException
     */
    public static function create(
        $gameId,
        $teamId,
        $playerId,
        $goals = 0,
        $substitutionQuarter1 = 0,
        $substitutionQuarter2 = 0,
        $substitutionQuarter3 = 0,
        $substitutionQuarter4 = 0,
        $keeperQuarter1 = 0,
        $keeperQuarter2 = 0,
        $keeperQuarter3 = 0,
        $keeperQuarter4 = 0,
        $injuredQuarter1 = 0,
        $injuredQuarter2 = 0,
        $injuredQuarter3 = 0,
        $injuredQuarter4 = 0,
        $absentQuarter1 = 0,
        $absentQuarter2 = 0,
        $absentQuarter3 = 0,
        $absentQuarter4 = 0,
        $yellowCards    = 0,
        $redCard        = 0
    ) {
        // Create the PlayerGameStatsOrm
        $result = self::getPersistenceDriver()->create(
            array_filter(
                [
                    self::FIELD_GAME_ID                 => $gameId,
                    self::FIELD_TEAM_ID                 => $teamId,
                    self::FIELD_PLAYER_ID               => $playerId,
                    self::FIELD_GOALS                   => $goals,
                    self::FIELD_SUBSTITUTION_QUARTER_1  => $substitutionQuarter1,
                    self::FIELD_SUBSTITUTION_QUARTER_2  => $substitutionQuarter2,
                    self::FIELD_SUBSTITUTION_QUARTER_3  => $substitutionQuarter3,
                    self::FIELD_SUBSTITUTION_QUARTER_4  => $substitutionQuarter4,
                    self::FIELD_KEEPER_QUARTER_1        => $keeperQuarter1,
                    self::FIELD_KEEPER_QUARTER_2        => $keeperQuarter2,
                    self::FIELD_KEEPER_QUARTER_3        => $keeperQuarter3,
                    self::FIELD_KEEPER_QUARTER_4        => $keeperQuarter4,
                    self::FIELD_INJURED_QUARTER_1       => $injuredQuarter1,
                    self::FIELD_INJURED_QUARTER_2       => $injuredQuarter2,
                    self::FIELD_INJURED_QUARTER_3       => $injuredQuarter3,
                    self::FIELD_INJURED_QUARTER_4       => $injuredQuarter4,
                    self::FIELD_ABSENT_QUARTER_1        => $absentQuarter1,
                    self::FIELD_ABSENT_QUARTER_2        => $absentQuarter2,
                    self::FIELD_ABSENT_QUARTER_3        => $absentQuarter3,
                    self::FIELD_ABSENT_QUARTER_4        => $absentQuarter4,
                    self::FIELD_YELLOW_CARDS            => $yellowCards,
                    self::FIELD_RED_CARD                => $redCard,
                ],
                function ($item) {
                    return $item !== null;
                }
            )
        );

        $playerGameStatsOrm = new static($result);

        return $playerGameStatsOrm;
    }

    /**
     * Create a GameOrm
     *
     * @param int       $gameId
     * @param int       $teamId
     * @param int       $playerId
     *
     * @return PlayerGameStatsOrm
     * @throws DuplicateEntryException
     */
    public static function findOrCreate(
        $gameId,
        $teamId,
        $playerId
    ) {
        $playerGameStatsOrm = null;

        try {
            $playerGameStatsOrm = static::loadByPk($gameId, $teamId, $playerId);
        } catch (NoResultsException $e) {
            $playerGameStatsOrm = static::create($gameId, $teamId, $playerId);
        }

        return $playerGameStatsOrm;
    }

    /**
     * Load a GameOrm by Pk
     *
     * @param int $gameId
     * @param int $teamId
     * @param int $playerId
     *
     * @return PlayerGameStatsOrm
     * @throws NoResultsException
     */
    public static function loadByPk($gameId, $teamId, $playerId)
    {
        $result = self::getPersistenceDriver()->getOne(
            [
                self::FIELD_GAME_ID    => $gameId,
                self::FIELD_TEAM_ID    => $teamId,
                self::FIELD_PLAYER_ID  => $playerId,
            ]);

        return new static($result);
    }

    /**
     * Load a GameOrm by game
     *
     * @param int $gameId
     *
     * @return PlayerGameStatsOrm[]
     * @throws NoResultsException
     */
    public static function loadByGameId($gameId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_GAME_ID    => $gameId,
            ]);

        $playerGameStatsOrms = [];
        foreach ($results as $result) {
            $playerGameStatsOrms[] = new static($result);
        }

        return $playerGameStatsOrms;
    }
}