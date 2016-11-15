<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Orm\FieldValidator as FV;
use DAG\Framework\Orm\PersistenceConfig as PC;
use DAG\Framework\Orm\PersistenceModel;
use DAG\Framework\Orm\DuplicateEntryException;


/**
 * @property int    $id
 * @property int    $seasonId
 * @property string $name
 */
class DivisionOrm extends PersistenceModel
{
    const FIELD_ID          = 'id';
    const FIELD_SEASON_ID   = 'seasonId';
    const FIELD_NAME        = 'name';

    protected static $fields = [
        self::FIELD_ID          => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_SEASON_ID   => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_NAME        => [FV::STRING, [FV::NO_CONSTRAINTS]],
    ];

    protected static $config = [
        PC::PERSISTENCE_DRIVER => PC::DRIVER_MYSQL,
        PC::SCHEMA             => 'schedule_rw',
        PC::TABLE              => 'division',
        PC::AUTO_INC_FIELD     => self::FIELD_ID,
        PC::PRIMARY_KEYS       => [self::FIELD_ID],
    ];

    /**
     * Create a DivisionOrm
     *
     * @param int       $seasonId
     * @param string    $name
     *
     * @return DivisionOrm
     * @throws DuplicateEntryException
     */
    public static function create(
        $seasonId,
        $name)
    {
        $result = self::getPersistenceDriver()->create(
            [
                self::FIELD_SEASON_ID   => $seasonId,
                self::FIELD_NAME        => $name,
            ],
            function ($item) {
                return $item !== null;
            }
        );

        return new static($result);
    }

    /**
     * Load a DivisionOrm by id
     *
     * @param int $id
     *
     * @return DivisionOrm
     */
    public static function loadById($id)
    {
        $result = self::getPersistenceDriver()->getOne([self::FIELD_ID => $id]);

        return new static($result);
    }

    /**
     * Load a DivisionOrm by seasonId, name
     *
     * @param int       $seasonId
     * @param string    $name
     *
     * @return DivisionOrm
     */
    public static function loadBySeasonIdAndName($seasonId, $name)
    {
        $result = self::getPersistenceDriver()->getOne(
            [
                self::FIELD_SEASON_ID   => $seasonId,
                self::FIELD_NAME        => $name,
            ]);

        return new static($result);
    }

    /**
     * Load a DivisionOrms by seasonId
     *
     * @param int $seasonId
     *
     * @return array []   DivisionOrms
     */
    public static function loadBySeasonId($seasonId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_SEASON_ID  => $seasonId
            ]);

        $divisionOrms = [];
        foreach ($results as $result) {
            $divisionOrms[] = new static($result);
        }

        return $divisionOrms;
    }
}