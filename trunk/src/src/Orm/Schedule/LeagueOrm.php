<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Orm\FieldValidator as FV;
use DAG\Framework\Orm\PersistenceConfig as PC;
use DAG\Framework\Orm\PersistenceModel;

/**
 * @property int    $id
 * @property string $name
 */
class LeagueOrm extends PersistenceModel
{
    const FIELD_ID   = 'id';
    const FIELD_NAME = 'name';

    protected static $fields = [
        self::FIELD_ID   => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_NAME => [FV::STRING, [FV::NO_CONSTRAINTS], ''],
    ];

    protected static $config = [
        PC::PERSISTENCE_DRIVER => PC::DRIVER_MYSQL,
        PC::SCHEMA             => 'schedule_rw',
        PC::TABLE              => 'league',
        PC::AUTO_INC_FIELD     => self::FIELD_ID,
        PC::PRIMARY_KEYS       => [self::FIELD_NAME],
    ];

    /**
     * Create a League
     *
     * @param $name
     *
     * @return LeagueOrm
     * @throws \DAG\Framework\Orm\DuplicateEntryException
     */
    public static function create($name)
    {
        $result = self::getPersistenceDriver()->create(
            array_filter(
                [
                    self::FIELD_NAME     => $name,
                ],
                function ($item) {
                    return $item !== null;
                }
            )
        );

        return new static($result);
    }

    /**
     * Load a League by id
     *
     * @param int $id
     *
     * @return LeagueOrm
     */
    public static function loadById($id)
    {
        $result = self::getPersistenceDriver()->getOne([self::FIELD_ID => $id]);
        return new static($result);
    }

    /**
     * Load a League by name
     *
     * @param string $name
     *
     * @return LeagueOrm
     */
    public static function loadByName($name)
    {
        $result = self::getPersistenceDriver()->getOne([self::FIELD_NAME => $name]);
        return new static($result);
    }

}