<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Orm\FieldValidator as FV;
use DAG\Framework\Orm\PersistenceConfig as PC;
use DAG\Framework\Orm\PersistenceModel;
use DAG\Framework\Orm\DuplicateEntryException;
use DAG\Framework\Orm\NoResultsException;


/**
 * @property int    $id
 * @property int    $centerRefereeId
 * @property int    $assistantRefereeId1
 * @property int    $assistantRefereeId2
 * @property int    $divisionId
 * @property int    $teamId
 */
class RefereeCrewOrm extends PersistenceModel
{
    const FIELD_ID                      = 'id';
    const FIELD_CENTER_REFEREE_ID       = 'centerRefereeId';
    const FIELD_ASSISTANT_REFEREE_ID_1  = 'assistantReferee1Id';
    const FIELD_ASSISTANT_REFEREE_ID_2  = 'assistantReferee2Id';
    const FIELD_DIVISION_ID             = 'divisionId';
    const FIELD_TEAM_ID                 = 'teamId';

    protected static $fields = [
        self::FIELD_ID                      => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_CENTER_REFEREE_ID       => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_ASSISTANT_REFEREE_ID_1  => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_ASSISTANT_REFEREE_ID_2  => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_DIVISION_ID             => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_TEAM_ID                 => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
    ];

    protected static $config = [
        PC::PERSISTENCE_DRIVER  => PC::DRIVER_MYSQL,
        PC::SCHEMA              => 'schedule_rw',
        PC::TABLE               => 'refereeCrew',
        PC::AUTO_INC_FIELD      => self::FIELD_ID,
        PC::PRIMARY_KEYS        => [self::FIELD_ID],
    ];

    /**
     * Constructor that Creates a RefereeCrew
     * On return, the object exists in all persistent storage locations specified in the configuration.
     *
     * @param int $centerRefereeId
     * @param int $assistantRefereeId1
     * @param int $assistantRefereeId2
     * @param int $divisionId
     * @param int $teamId
     *
     * @return RefereeCrewOrm
     * @throws DuplicateEntryException
     */
    public static function create(
        $centerRefereeId,
        $assistantRefereeId1,
        $assistantRefereeId2,
        $divisionId,
        $teamId
    )
    {
        $result = self::getPersistenceDriver()->create(
            array_filter(
                [
                    self::FIELD_CENTER_REFEREE_ID       => $centerRefereeId,
                    self::FIELD_ASSISTANT_REFEREE_ID_1  => $assistantRefereeId1,
                    self::FIELD_ASSISTANT_REFEREE_ID_2  => $assistantRefereeId2,
                    self::FIELD_DIVISION_ID             => $divisionId,
                    self::FIELD_TEAM_ID                 => $teamId,
                ],
                function ($item) {
                    return $item !== null;
                }
            )
        );

        return new static($result);
    }

    /**
     * Constructs a RefereeCrew from the primary key attributes
     *
     * @param int $id
     *
     * @return RefereeCrewOrm
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
     * Load a RefereeCrewOrms by teamId
     *
     * @param int $teamId
     *
     * @return RefereeCrewOrm[]
     */
    public static function loadByTeamId($teamId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [self::FIELD_TEAM_ID => $teamId]);

        $refereeCrewOrms = [];
        foreach ($results as $result) {
            $refereeCrewOrms[] = new static($result);
        }

        return $refereeCrewOrms;
    }

    /**
     * Load a RefereeCrewOrms by divisionId
     *
     * @param int $divisionId
     *
     * @return RefereeCrewOrm[]
     */
    public static function loadByDivisionId($divisionId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [self::FIELD_DIVISION_ID => $divisionId]);

        $refereeCrewOrms = [];
        foreach ($results as $result) {
            $refereeCrewOrms[] = new static($result);
        }

        return $refereeCrewOrms;
    }

    /**
     * Constructs a RefereeCrew from the centerRefereeId, assistantRefereeId1, assistantRefereeId2, divisionId, teamId
     *
     * @param int $centerRefereeId
     * @param int $assistantRefereeId1
     * @param int $assistantRefereeId2
     * @param int $divisionId
     * @param int $teamId
     *
     * @return RefereeCrewOrm
     * @throws NoResultsException
     */
    public static function loadByDivisionCenterAssistantsTeam(
        $centerRefereeId,
        $assistantRefereeId1,
        $assistantRefereeId2,
        $divisionId,
        $teamId
    ) {
        $result = self::getPersistenceDriver()->getOne(
            array_filter(
                [
                    self::FIELD_CENTER_REFEREE_ID       => $centerRefereeId,
                    self::FIELD_ASSISTANT_REFEREE_ID_1  => $assistantRefereeId1,
                    self::FIELD_ASSISTANT_REFEREE_ID_2  => $assistantRefereeId2,
                    self::FIELD_DIVISION_ID             => $divisionId,
                    self::FIELD_TEAM_ID                 => $teamId,
                ],
                function ($item) {
                    return $item !== null;
                }
            )
        );

        return new static($result);
    }
}