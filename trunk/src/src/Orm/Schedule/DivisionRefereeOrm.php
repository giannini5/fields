<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Orm\FieldValidator as FV;
use DAG\Framework\Orm\PersistenceConfig as PC;
use DAG\Framework\Orm\PersistenceModel;
use DAG\Framework\Orm\DuplicateEntryException;
use DAG\Framework\Orm\NoResultsException;


/**
 * @property int    $id
 * @property int    $divisionId
 * @property int    $refereeId
 * @property int    $isCenter
 * @property int    $isAssistant
 * @property int    $isMentor
 */
class DivisionRefereeOrm extends PersistenceModel
{
    const FIELD_ID              = 'id';
    const FIELD_DIVISION_ID     = 'divisionId';
    const FIELD_REFEREE_ID      = 'refereeId';
    const FIELD_IS_CENTER       = 'isCenter';
    const FIELD_IS_ASSISTANT    = 'isAssistant';
    const FIELD_IS_MENTOR       = 'isMentor';

    protected static $fields = [
        self::FIELD_ID              => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_DIVISION_ID     => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_REFEREE_ID      => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_IS_CENTER       => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_IS_ASSISTANT    => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_IS_MENTOR       => [FV::INT,    [FV::NO_CONSTRAINTS]],
    ];

    protected static $config = [
        PC::PERSISTENCE_DRIVER  => PC::DRIVER_MYSQL,
        PC::SCHEMA              => 'schedule_rw',
        PC::TABLE               => 'divisionReferee',
        PC::AUTO_INC_FIELD      => self::FIELD_ID,
        PC::PRIMARY_KEYS        => [self::FIELD_ID],
    ];

    /**
     * Constructor that Creates a DivisionReferee
     * On return, the object exists in all persistent storage locations specified in the configuration.
     *
     * @param int $divisionId
     * @param int $refereeId
     * @param int $isCenter
     * @param int $isAssistant
     * @param int $isMentor
     *
     * @return DivisionRefereeOrm
     * @throws DuplicateEntryException
     */
    public static function create(
        $divisionId,
        $refereeId,
        $isCenter,
        $isAssistant,
        $isMentor
    )
    {
        $result = self::getPersistenceDriver()->create(
            array_filter(
                [
                    self::FIELD_DIVISION_ID     => $divisionId,
                    self::FIELD_REFEREE_ID      => $refereeId,
                    self::FIELD_IS_CENTER       => $isCenter,
                    self::FIELD_IS_ASSISTANT    => $isAssistant,
                    self::FIELD_IS_MENTOR       => $isMentor,
                ],
                function ($item) {
                    return $item !== null;
                }
            )
        );

        return new static($result);
    }

    /**
     * Constructs a DivisionReferee from the primary key attributes
     *
     * @param int $id
     *
     * @return DivisionRefereeOrm
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
     * Load a DivisionRefereeOrms by divisionId
     *
     * @param int $divisionId
     *
     * @return DivisionRefereeOrm[]
     */
    public static function loadByDivisionId($divisionId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [self::FIELD_DIVISION_ID => $divisionId]);

        $divisionRefereeOrms = [];
        foreach ($results as $result) {
            $divisionRefereeOrms[] = new static($result);
        }

        return $divisionRefereeOrms;
    }

    /**
     * Load a DivisionRefereeOrms by refereeId
     *
     * @param int $refereeId
     *
     * @return DivisionRefereeOrm[]
     */
    public static function loadByRefereeId($refereeId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [self::FIELD_REFEREE_ID => $refereeId]);

        $divisionRefereeOrms = [];
        foreach ($results as $result) {
            $divisionRefereeOrms[] = new static($result);
        }

        return $divisionRefereeOrms;
    }

    /**
     * Constructs a DivisionReferee from the divisionId, refereeId
     *
     * @param int $divisionId
     * @param int $refereeId
     *
     * @return DivisionRefereeOrm
     * @throws NoResultsException
     */
    public static function loadByDivisionIdAndReferee($divisionId, $refereeId)
    {
        $result = self::getPersistenceDriver()->getOne(
            [
                self::FIELD_DIVISION_ID => $divisionId,
                self::FIELD_REFEREE_ID  => $refereeId,
            ]
        );

        return new static($result);
    }
}