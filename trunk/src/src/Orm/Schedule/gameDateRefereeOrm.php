<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Orm\FieldValidator as FV;
use DAG\Framework\Orm\PersistenceConfig as PC;
use DAG\Framework\Orm\PersistenceModel;
use DAG\Framework\Orm\DuplicateEntryException;
use DAG\Framework\Orm\NoResultsException;


/**
 * @property int    $id
 * @property int    $gameDateId
 * @property int    $refereeId
 * @property int    $isCenter
 * @property int    $isAssistant
 * @property int    $isMentor
 */
class GameDateRefereeOrm extends PersistenceModel
{
    const FIELD_ID              = 'id';
    const FIELD_GameDate_ID     = 'gameDateId';
    const FIELD_REFEREE_ID      = 'refereeId';

    protected static $fields = [
        self::FIELD_ID              => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_GameDate_ID     => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_REFEREE_ID      => [FV::INT,    [FV::NO_CONSTRAINTS]],
    ];

    protected static $config = [
        PC::PERSISTENCE_DRIVER  => PC::DRIVER_MYSQL,
        PC::SCHEMA              => 'schedule_rw',
        PC::TABLE               => 'gameDateReferee',
        PC::AUTO_INC_FIELD      => self::FIELD_ID,
        PC::PRIMARY_KEYS        => [self::FIELD_ID],
    ];

    /**
     * Constructor that Creates a GameDateReferee
     * On return, the object exists in all persistent storage locations specified in the configuration.
     *
     * @param int $gameDateId
     * @param int $refereeId
     *
     * @return GameDateRefereeOrm
     * @throws DuplicateEntryException
     */
    public static function create(
        $gameDateId,
        $refereeId)
    {
        $result = self::getPersistenceDriver()->create(
            array_filter(
                [
                    self::FIELD_GameDate_ID     => $gameDateId,
                    self::FIELD_REFEREE_ID      => $refereeId,
                ],
                function ($item) {
                    return $item !== null;
                }
            )
        );

        return new static($result);
    }

    /**
     * Constructs a GameDateReferee from the primary key attributes
     *
     * @param int $id
     *
     * @return GameDateRefereeOrm
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
     * Load a GameDateRefereeOrms by gameDateId
     *
     * @param int $gameDateId
     *
     * @return GameDateRefereeOrm[]
     */
    public static function loadByGameDateId($gameDateId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [self::FIELD_GameDate_ID => $gameDateId]);

        $gameDateRefereeOrms = [];
        foreach ($results as $result) {
            $gameDateRefereeOrms[] = new static($result);
        }

        return $gameDateRefereeOrms;
    }

    /**
     * Load a GameDateRefereeOrms by refereeId
     *
     * @param int $refereeId
     *
     * @return GameDateRefereeOrm[]
     */
    public static function loadByRefereeId($refereeId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [self::FIELD_REFEREE_ID => $refereeId]);

        $gameDateRefereeOrms = [];
        foreach ($results as $result) {
            $gameDateRefereeOrms[] = new static($result);
        }

        return $gameDateRefereeOrms;
    }

    /**
     * Constructs a GameDateReferee from the gameDateId, refereeId
     *
     * @param int $gameDateId
     * @param int $refereeId
     *
     * @return GameDateRefereeOrm
     * @throws NoResultsException
     */
    public static function loadByGameDateIdAndReferee($gameDateId, $refereeId)
    {
        $result = self::getPersistenceDriver()->getOne(
            [
                self::FIELD_GameDate_ID => $gameDateId,
                self::FIELD_REFEREE_ID  => $refereeId,
            ]
        );

        return new static($result);
    }
}