<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Orm\FieldValidator as FV;
use DAG\Framework\Orm\PersistenceConfig as PC;
use DAG\Framework\Orm\PersistenceModel;
use DAG\Framework\Orm\DuplicateEntryException;
use DAG\Framework\Orm\NoResultsException;


/**
 * @property int    $id
 * @property int    $gameId
 * @property int    $refereeId
 * @property string $role
 */
class GameRefereeOrm extends PersistenceModel
{
    const FIELD_ID          = 'id';
    const FIELD_GAME_ID     = 'gameId';
    const FIELD_REFEREE_ID  = 'refereeId';
    const FIELD_ROLE        = 'role';

    const CENTER_ROLE      = 'C';
    const ASSISTANT_ROLE_1 = 'A1';
    const ASSISTANT_ROLE_2 = 'A2';
    const STANDBY_ROLE     = 'S';
    const MENTOR_ROLE      = 'M';

    public static $roleNames = [
        self::CENTER_ROLE       => 'Center',
        self::ASSISTANT_ROLE_1  => 'Assistant_1',
        self::ASSISTANT_ROLE_2  => 'Assistant_2',
        self::STANDBY_ROLE      => 'Standby',
        self::MENTOR_ROLE       => 'Mentor',
    ];

    protected static $fields = [
        self::FIELD_ID          => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_GAME_ID     => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_REFEREE_ID  => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_ROLE        => [FV::STRING, [FV::ENUM, [self::CENTER_ROLE, self::ASSISTANT_ROLE_1, self::ASSISTANT_ROLE_2, self::STANDBY_ROLE, self::MENTOR_ROLE]]],
    ];

    protected static $config = [
        PC::PERSISTENCE_DRIVER  => PC::DRIVER_MYSQL,
        PC::SCHEMA              => 'schedule_rw',
        PC::TABLE               => 'gameReferee',
        PC::AUTO_INC_FIELD      => self::FIELD_ID,
        PC::PRIMARY_KEYS        => [self::FIELD_ID],
    ];

    /**
     * Constructor that Creates a GameReferee
     * On return, the object exists in all persistent storage locations specified in the configuration.
     *
     * @param int       $gameId
     * @param int       $refereeId
     * @param string    $role
     *
     * @return GameRefereeOrm
     * @throws DuplicateEntryException
     */
    public static function create(
        $gameId,
        $refereeId,
        $role
    )
    {
        $result = self::getPersistenceDriver()->create(
            array_filter(
                [
                    self::FIELD_GAME_ID     => $gameId,
                    self::FIELD_REFEREE_ID  => $refereeId,
                    self::FIELD_ROLE        => $role,
                ],
                function ($item) {
                    return $item !== null;
                }
            )
        );

        return new static($result);
    }

    /**
     * Constructs a GameReferee from the primary key attributes
     *
     * @param int $id
     *
     * @return GameRefereeOrm
     * @throws NoResultsException
     */
    public static function loadById($id)
    {
        $result = self::getPersistenceDriver()->getOne(
            [
                self::FIELD_ID => $id,
            ]
        );

        return new static($result);
    }

    /**
     * Load a GameRefereeOrms by gameId
     *
     * @param int $gameId
     *
     * @return GameRefereeOrm[]
     */
    public static function loadByGameId($gameId)
    {
        $results = self::getPersistenceDriver()->getMany([self::FIELD_GAME_ID => $gameId]);

        $gameRefereeOrms = [];
        foreach ($results as $result) {
            $gameRefereeOrms[] = new static($result);
        }

        return $gameRefereeOrms;
    }

    /**
     * Load a GameRefereeOrms by refereeId
     *
     * @param int $refereeId
     *
     * @return GameRefereeOrm[]
     */
    public static function loadByRefereeId($refereeId)
    {
        $results = self::getPersistenceDriver()->getMany([self::FIELD_REFEREE_ID => $refereeId]);

        $gameRefereeOrms = [];
        foreach ($results as $result) {
            $gameRefereeOrms[] = new static($result);
        }

        return $gameRefereeOrms;
    }

    /**
     * Constructs a GameReferee from the gameId, refereeId
     *
     * @param int $gameId
     * @param int $refereeId
     *
     * @return GameRefereeOrm
     * @throws NoResultsException
     */
    public static function loadByGameIdAndReferee($gameId, $refereeId)
    {
        $result = self::getPersistenceDriver()->getOne(
            [
                self::FIELD_GAME_ID     => $gameId,
                self::FIELD_REFEREE_ID  => $refereeId,
            ]
        );

        return new static($result);
    }
}