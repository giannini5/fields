<?php

namespace DAG\Orm\Fields;

use DAG\Framework\Exception\Precondition;
use DAG\Framework\Orm\FieldValidator as FV;
use DAG\Framework\Orm\PersistenceConfig as PC;
use DAG\Framework\Orm\PersistenceModel;

/**
 * @property int    $id
 * @property string $creationDate
 * @property int    $userId
 * @property int    $userType
 * @property int    $teamId
 */
class SessionOrm extends PersistenceModel
{
    const FIELD_ID            = 'id';
    const FIELD_CREATION_DATE = 'creationDate';
    const FIELD_USER_ID       = 'userId';
    const FIELD_USER_TYPE     = 'userType';
    const FIELD_TEAM_ID       = 'teamId';

    // User Types
    const COACH                         = 0;
    const MANAGER                       = 1;
    const PRACTICE_FIELD_COORDINATOR    = 2;
    const SCHEDULE_COORDINATOR          = 3;
    const SCORING_COORDINATOR           = 4;
    const REFEREE_COORDINATOR           = 5;

    // User Type validator array
    const USER_TYPES = [
        self::COACH,
        self::MANAGER,
        self::PRACTICE_FIELD_COORDINATOR,
        self::SCHEDULE_COORDINATOR,
        self::SCORING_COORDINATOR,
        self::REFEREE_COORDINATOR,
    ];

    protected static $fields = [
        self::FIELD_ID            => [FV::INT,      [FV::NO_CONSTRAINTS], null],
        self::FIELD_CREATION_DATE => [FV::STRING,   [FV::NO_CONSTRAINTS], FV::CURRENT_TIME],
        self::FIELD_USER_ID       => [FV::INT,      [FV::NO_CONSTRAINTS]],
        self::FIELD_USER_TYPE     => [FV::INT,      [FV::ENUM, self::USER_TYPES]],
        self::FIELD_TEAM_ID       => [FV::INT,      [FV::NO_CONSTRAINTS]],
    ];

    protected static $config = [
        PC::PERSISTENCE_DRIVER => PC::DRIVER_MYSQL,
        PC::SCHEMA             => 'fields_rw',
        PC::TABLE              => 'session',
        PC::AUTO_INC_FIELD     => self::FIELD_ID,
        PC::PRIMARY_KEYS       => [self::FIELD_USER_ID, self::FIELD_USER_TYPE, self::FIELD_TEAM_ID],
    ];

    /**
     * Create a Season
     *
     * @param $userId
     * @param $userType
     * @param $teamId
     *
     * @return SessionOrm
     * @throws \DAG\Framework\Orm\DuplicateEntryException
     */
    public static function create($userId, $userType, $teamId)
    {
        Precondition::arrayKeyExists(self::USER_TYPES, $userType, "USER_TYPES");

        $result = self::getPersistenceDriver()->create(
            array_filter(
                [
                    self::FIELD_USER_ID     => $userId,
                    self::FIELD_USER_TYPE   => $userType,
                    self::FIELD_TEAM_ID     => $teamId,
                ],
                function ($item) {
                    return $item !== null;
                }
            )
        );

        return new static($result);
    }

    /**
     * Load a SessionOrm by id
     *
     * @param int $id
     *
     * @return SessionOrm
     */
    public static function loadById($id)
    {
        $result = self::getPersistenceDriver()->getOne([self::FIELD_ID => $id]);

        return new static($result);
    }

    /**
     * Load a SessionOrm by unique key elements
     *
     * @param int   $userId
     * @param int   $userType
     * @param int   $teamId
     *
     * @return SessionOrm
     */
    public static function loadByUserTypeAndTeam($userId, $userType, $teamId)
    {
        Precondition::arrayKeyExists(self::USER_TYPES, $userType, "USER_TYPES");

        $result = self::getPersistenceDriver()->getOne([
            self::FIELD_USER_ID     => $userId,
            self::FIELD_USER_TYPE   => $userType,
            self::FIELD_TEAM_ID     => $teamId,
            ]);

        return new static($result);
    }
}