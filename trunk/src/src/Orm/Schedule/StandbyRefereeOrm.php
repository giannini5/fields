<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Orm\FieldValidator as FV;
use DAG\Framework\Orm\PersistenceConfig as PC;
use DAG\Framework\Orm\PersistenceModel;
use DAG\Framework\Orm\DuplicateEntryException;
use DAG\Framework\Orm\NoResultsException;


/**
 * @property int    $id
 * @property int    $facilityId
 * @property int    $gameDateId
 * @property string $divisionName
 * @property string $startTime
 * @property int    $refereeId
 * @property string $role
 * @property int    $refereeCrewId
 */
class StandbyRefereeOrm extends PersistenceModel
{
    const FIELD_ID              = 'id';
    const FIELD_FACILITY_ID     = 'facilityId';
    const FIELD_GAME_DATE_ID    = 'gameDateId';
    const FIELD_DIVISION_NAME   = 'divisionName';
    const FIELD_START_TIME      = 'startTime';
    const FIELD_REFEREE_ID      = 'refereeId';
    const FIELD_ROLE            = 'role';
    const FIELD_REFEREE_CREW_ID = 'refereeCrewId';

    protected static $fields = [
        self::FIELD_ID              => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_FACILITY_ID     => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_GAME_DATE_ID    => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_DIVISION_NAME   => [FV::STRING, [FV::NO_CONSTRAINTS]],
        self::FIELD_START_TIME      => [FV::STRING, [FV::NO_CONSTRAINTS]],
        self::FIELD_REFEREE_ID      => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_ROLE            => [FV::STRING, [FV::ENUM, [GameRefereeOrm::CENTER_ROLE, GameRefereeOrm::ASSISTANT_ROLE_1, GameRefereeOrm::ASSISTANT_ROLE_2, GameRefereeOrm::MENTOR_ROLE]]],
        self::FIELD_REFEREE_CREW_ID => [FV::INT,    [FV::NO_CONSTRAINTS], null],
    ];

    protected static $config = [
        PC::PERSISTENCE_DRIVER  => PC::DRIVER_MYSQL,
        PC::SCHEMA              => 'schedule_rw',
        PC::TABLE               => 'standbyReferee',
        PC::AUTO_INC_FIELD      => self::FIELD_ID,
        PC::PRIMARY_KEYS        => [self::FIELD_ID],
    ];

    /**
     * Constructor that Creates a StandbyReferee
     * On return, the object exists in all persistent storage locations specified in the configuration.
     *
     * @param int       $facilityId
     * @param int       $gameDateId
     * @param string    $divisionName
     * @param string    $startTime
     * @param int       $refereeId
     * @param string    $role
     * @param int       $refereeCrewId
     *
     * @return StandbyRefereeOrm
     * @throws DuplicateEntryException
     */
    public static function create(
        $facilityId,
        $gameDateId,
        $divisionName,
        $startTime,
        $refereeId,
        $role,
        $refereeCrewId = null
    )
    {
        $result = self::getPersistenceDriver()->create(
            array_filter(
                [
                    self::FIELD_FACILITY_ID     => $facilityId,
                    self::FIELD_GAME_DATE_ID    => $gameDateId,
                    self::FIELD_DIVISION_NAME   => $divisionName,
                    self::FIELD_START_TIME      => $startTime,
                    self::FIELD_REFEREE_ID      => $refereeId,
                    self::FIELD_ROLE            => $role,
                    self::FIELD_REFEREE_CREW_ID => $refereeCrewId,
                ],
                function ($item) {
                    return $item !== null;
                }
            )
        );

        return new static($result);
    }

    /**
     * Constructs a StandbyReferee from the primary key attributes
     *
     * @param int $id
     *
     * @return StandbyRefereeOrm
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
     * Load a StandbyRefereeOrms by gameId
     *
     * @param int       $facilityId
     * @param int       $gameDateId
     * @param string    $divisionName
     * @param int       $startTime
     *
     * @return StandbyRefereeOrm[]
     */
    public static function loadByFacilityIdGameDateIdDivisionNameStartTime($facilityId, $gameDateId, $divisionName, $startTime)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_FACILITY_ID     => $facilityId,
                self::FIELD_GAME_DATE_ID    => $gameDateId,
                self::FIELD_DIVISION_NAME   => $divisionName,
                self::FIELD_START_TIME      => $startTime,
            ]);

        $standbyRefereeOrms = [];
        foreach ($results as $result) {
            $standbyRefereeOrms[] = new static($result);
        }

        return $standbyRefereeOrms;
    }

    /**
     * Load a StandbyRefereeOrms by gameId
     *
     * @param int       $facilityId
     * @param int       $gameDateId
     * @param string    $divisionName
     * @param int       $startTime
     * @param int       $refereeId
     *
     * @return StandbyRefereeOrm
     * @throws NoResultsException
     */
    public static function loadByFacilityIdGameDateIdDivisionNameStartTimeRefereeId($facilityId, $gameDateId, $divisionName, $startTime, $refereeId)
    {
        $result = self::getPersistenceDriver()->getOne(
            [
                self::FIELD_FACILITY_ID     => $facilityId,
                self::FIELD_GAME_DATE_ID    => $gameDateId,
                self::FIELD_DIVISION_NAME   => $divisionName,
                self::FIELD_START_TIME      => $startTime,
                self::FIELD_REFEREE_ID      => $refereeId,
            ]);

        return new static($result);
    }

    /**
     * Load a StandbyRefereeOrms by startTime and refereeId
     *
     * @param int $facilityId
     * @param int $gameDateId
     * @param int $refereeId
     *
     * @return StandbyRefereeOrm[]
     */
    public static function loadByGameDateIdRefereeId($facilityId, $gameDateId, $refereeId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_FACILITY_ID     => $facilityId,
                self::FIELD_GAME_DATE_ID    => $gameDateId,
                self::FIELD_REFEREE_ID      => $refereeId,
            ]);

        $standbyRefereeOrms = [];
        foreach ($results as $result) {
            $standbyRefereeOrms[] = new static($result);
        }

        return $standbyRefereeOrms;
    }

    /**
     * Load a StandbyRefereeOrms by facilityId
     *
     * @param int $facilityId
     *
     * @return StandbyRefereeOrm[]
     */
    public static function loadByFacilityId($facilityId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_FACILITY_ID     => $facilityId,
            ]);

        $standbyRefereeOrms = [];
        foreach ($results as $result) {
            $standbyRefereeOrms[] = new static($result);
        }

        return $standbyRefereeOrms;
    }
}