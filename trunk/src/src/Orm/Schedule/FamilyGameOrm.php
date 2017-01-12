<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Orm\FieldValidator as FV;
use DAG\Framework\Orm\PersistenceConfig as PC;
use DAG\Framework\Orm\PersistenceModel;
use DAG\Framework\Orm\DuplicateEntryException;
use DAG\Framework\Orm\NoResultsException;


/**
 * @property int    $id
 * @property int    $familyId
 * @property int    $gameId
 */
class FamilyGameOrm extends PersistenceModel
{
    const FIELD_ID          = 'id';
    const FIELD_FAMILY_ID   = 'familyId';
    const FIELD_GAME_ID     = 'gameId';

    protected static $fields = [
        self::FIELD_ID          => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_FAMILY_ID   => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_GAME_ID     => [FV::INT,    [FV::NO_CONSTRAINTS]],
    ];

    protected static $config = [
        PC::PERSISTENCE_DRIVER  => PC::DRIVER_MYSQL,
        PC::SCHEMA              => 'schedule_rw',
        PC::TABLE               => 'familyGame',
        PC::AUTO_INC_FIELD      => self::FIELD_ID,
        PC::PRIMARY_KEYS        => [self::FIELD_ID],
    ];

    /**
     * Constructor that Creates a FamilyGame
     * On return, the object exists in all persistent storage locations specified in the configuration.
     *
     * @param int $familyId
     * @param int $gameId
     *
     * @return FamilyGameOrm
     * @throws DuplicateEntryException
     */
    public static function create(
        $familyId,
        $gameId
    )
    {
        $result = self::getPersistenceDriver()->create(
            [
                self::FIELD_FAMILY_ID   => $familyId,
                self::FIELD_GAME_ID     => $gameId,
            ],
            function ($item) {
                return $item !== null;
            }
        );

        return new static($result);
    }

    /**
     * Constructs a FamilyGame from the primary key attributes
     *
     * @param int $id
     *
     * @return FamilyGameOrm
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
     * Load a FamilyGameOrms by familyId
     *
     * @param int $familyId
     *
     * @return FamilyGameOrm[]
     */
    public static function loadByFamilyId($familyId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [self::FIELD_FAMILY_ID => $familyId]);

        $familyGameOrms = [];
        foreach ($results as $result) {
            $familyGameOrms[] = new static($result);
        }

        return $familyGameOrms;
    }

    /**
     * Load a FamilyGameOrms by gameId
     *
     * @param int $gameId
     *
     * @return FamilyGameOrm[]
     */
    public static function loadByGameId($gameId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [self::FIELD_GAME_ID => $gameId]);

        $familyGameOrms = [];
        foreach ($results as $result) {
            $familyGameOrms[] = new static($result);
        }

        return $familyGameOrms;
    }

    /**
     * Constructs a FamilyGame from the familyId, gameId
     *
     * @param int   $familyId
     * @param int   $gameId
     *
     * @return FamilyGameOrm
     * @throws NoResultsException
     */
    public static function loadByFamilyIdAndGameId($familyId, $gameId)
    {
        $result = self::getPersistenceDriver()->getOne(
            [
                self::FIELD_FAMILY_ID   => $familyId,
                self::FIELD_GAME_ID     => $gameId,
            ]
        );

        return new static($result);
    }
}