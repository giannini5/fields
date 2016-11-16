<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Orm\FieldValidator as FV;
use DAG\Framework\Orm\PersistenceConfig as PC;
use DAG\Framework\Orm\PersistenceModel;
use DAG\Framework\Orm\DuplicateEntryException;

/**
 * @property int    $id
 * @property int    $divisionId
 * @property int    $poolId
 * @property string $name
 */
class TeamOrm extends PersistenceModel
{
    const FIELD_ID          = 'id';
    const FIELD_DIVISION_ID = 'divisionId';
    const FIELD_POOL_ID     = 'poolId';
    const FIELD_NAME        = 'name';

    protected static $fields = [
        self::FIELD_ID          => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_DIVISION_ID => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_POOL_ID     => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_NAME        => [FV::STRING, [FV::NO_CONSTRAINTS]],
    ];

    protected static $config = [
        PC::PERSISTENCE_DRIVER => PC::DRIVER_MYSQL,
        PC::SCHEMA             => 'schedule_rw',
        PC::TABLE              => 'team',
        PC::AUTO_INC_FIELD     => self::FIELD_ID,
        PC::PRIMARY_KEYS       => [self::FIELD_ID],
    ];

    /**
     * Create a TeamOrm
     *
     * @param int       $divisionId
     * @param int       $poolId
     * @param string    $name
     *
     * @return TeamOrm
     * @throws DuplicateEntryException
     */
    public static function create(
        $divisionId,
        $poolId,
        $name)
    {
        $result = self::getPersistenceDriver()->create(
            [
                self::FIELD_DIVISION_ID => $divisionId,
                self::FIELD_POOL_ID     => $poolId,
                self::FIELD_NAME        => $name,
            ],
            function ($item) {
                return $item !== null;
            }
        );

        return new static($result);
    }

    /**
     * Load a TeamOrm by id
     *
     * @param int $id
     *
     * @return TeamOrm
     */
    public static function loadById($id)
    {
        $result = self::getPersistenceDriver()->getOne([self::FIELD_ID => $id]);

        return new static($result);
    }

    /**
     * Load a TeamOrm by divisionId, name
     *
     * @param int       $divisionId
     * @param string    $name
     *
     * @return TeamOrm
     */
    public static function loadByDivisionIdAndName($divisionId, $name)
    {
        $result = self::getPersistenceDriver()->getOne(
            [
                self::FIELD_DIVISION_ID => $divisionId,
                self::FIELD_NAME        => $name,
            ]);

        return new static($result);
    }

    /**
     * Load a TeamOrms by divisionId
     *
     * @param int $divisionId
     *
     * @return array []   TeamOrms
     */
    public static function loadByDivisionId($divisionId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_DIVISION_ID  => $divisionId
            ]);

        $teamOrms = [];
        foreach ($results as $result) {
            $teamOrms[] = new static($result);
        }

        return $teamOrms;
    }

    /**
     * Load a TeamOrms by poolId
     *
     * @param int $poolId
     *
     * @return array []   TeamOrms
     */
    public static function loadByPoolId($poolId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_POOL_ID  => $poolId
            ]);

        $teamOrms = [];
        foreach ($results as $result) {
            $teamOrms[] = new static($result);
        }

        return $teamOrms;
    }
}