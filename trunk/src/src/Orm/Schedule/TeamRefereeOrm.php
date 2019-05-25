<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Orm\FieldValidator as FV;
use DAG\Framework\Orm\PersistenceConfig as PC;
use DAG\Framework\Orm\PersistenceModel;
use DAG\Framework\Orm\DuplicateEntryException;
use DAG\Framework\Orm\NoResultsException;


/**
 * @property int    $id
 * @property int    $teamId
 * @property int    $refereeId
 */
class TeamRefereeOrm extends PersistenceModel
{
    const FIELD_ID          = 'id';
    const FIELD_TEAM_ID     = 'teamId';
    const FIELD_REFEREE_ID  = 'refereeId';

    protected static $fields = [
        self::FIELD_ID          => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_TEAM_ID     => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_REFEREE_ID  => [FV::INT,    [FV::NO_CONSTRAINTS]],
    ];

    protected static $config = [
        PC::PERSISTENCE_DRIVER  => PC::DRIVER_MYSQL,
        PC::SCHEMA              => 'schedule_rw',
        PC::TABLE               => 'teamReferee',
        PC::AUTO_INC_FIELD      => self::FIELD_ID,
        PC::PRIMARY_KEYS        => [self::FIELD_ID],
    ];

    /**
     * Constructor that Creates a TeamReferee
     * On return, the object exists in all persistent storage locations specified in the configuration.
     *
     * @param int $teamId
     * @param int $refereeId
     *
     * @return TeamRefereeOrm
     * @throws DuplicateEntryException
     */
    public static function create(
        $teamId,
        $refereeId
    )
    {
        $result = self::getPersistenceDriver()->create(
            array_filter(
                [
                    self::FIELD_TEAM_ID     => $teamId,
                    self::FIELD_REFEREE_ID  => $refereeId,
                ],
                function ($item) {
                    return $item !== null;
                }
            )
        );

        return new static($result);
    }

    /**
     * Constructs a TeamReferee from the primary key attributes
     *
     * @param int $id
     *
     * @return TeamRefereeOrm
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
     * Load a TeamRefereeOrms by teamId
     *
     * @param int $teamId
     *
     * @return TeamRefereeOrm[]
     */
    public static function loadByTeamId($teamId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [self::FIELD_TEAM_ID => $teamId]);

        $teamRefereeOrms = [];
        foreach ($results as $result) {
            $teamRefereeOrms[] = new static($result);
        }

        return $teamRefereeOrms;
    }

    /**
     * Load a TeamRefereeOrms by refereeId
     *
     * @param int $refereeId
     *
     * @return TeamRefereeOrm[]
     */
    public static function loadByRefereeId($refereeId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [self::FIELD_REFEREE_ID => $refereeId]);

        $teamRefereeOrms = [];
        foreach ($results as $result) {
            $teamRefereeOrms[] = new static($result);
        }

        return $teamRefereeOrms;
    }

    /**
     * Constructs a TeamReferee from the teamId, refereeId
     *
     * @param int       $teamId
     * @param int       $refereeId
     *
     * @return TeamRefereeOrm
     * @throws NoResultsException
     */
    public static function loadByTeamIdAndReferee($teamId, $refereeId)
    {
        $result = self::getPersistenceDriver()->getOne(
            [
                self::FIELD_TEAM_ID     => $teamId,
                self::FIELD_REFEREE_ID  => $refereeId,
            ]
        );

        return new static($result);
    }
}