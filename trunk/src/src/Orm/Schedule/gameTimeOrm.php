<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Orm\FieldValidator as FV;
use DAG\Framework\Orm\PersistenceConfig as PC;
use DAG\Framework\Orm\PersistenceModel;
use DAG\Framework\Orm\DuplicateEntryException;

/**
 * @property int    $id
 * @property int    $gameDateId
 * @property int    $divisionId
 * @property int    $fieldId
 * @property string $startTime
 */
class GameTimeOrm extends PersistenceModel
{
    const FIELD_ID              = 'id';
    const FIELD_GAME_DATE_ID    = 'gameDateId';
    const FIELD_DIVISION_ID     = 'divisionId';
    const FIELD_FIELD_ID        = 'fieldId';
    const FIELD_START_TIME      = 'startTime';

    protected static $fields = [
        self::FIELD_ID              => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_GAME_DATE_ID    => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_DIVISION_ID     => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_FIELD_ID        => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_START_TIME      => [FV::STRING, [FV::NO_CONSTRAINTS]],
    ];

    protected static $config = [
        PC::PERSISTENCE_DRIVER => PC::DRIVER_MYSQL,
        PC::SCHEMA             => 'schedule_rw',
        PC::TABLE              => 'gameTime',
        PC::AUTO_INC_FIELD     => self::FIELD_ID,
        PC::PRIMARY_KEYS       => [self::FIELD_ID],
    ];

    /**
     * Create a GameTimeOrm
     *
     * @param int       $gameDateId
     * @param int       $divisionId
     * @param int       $fieldId
     * @param string    $startTime
     *
     * @return GameTimeOrm
     * @throws DuplicateEntryException
     */
    public static function create(
        $gameDateId,
        $divisionId,
        $fieldId,
        $startTime)
    {
        $result = self::getPersistenceDriver()->create(
            [
                self::FIELD_GAME_DATE_ID    => $gameDateId,
                self::FIELD_DIVISION_ID     => $divisionId,
                self::FIELD_FIELD_ID        => $fieldId,
                self::FIELD_START_TIME      => $startTime,
            ],
            function ($item) {
                return $item !== null;
            }
        );

        return new static($result);
    }

    /**
     * Load a GameTimeOrm by id
     *
     * @param int $id
     *
     * @return GameTimeOrm
     */
    public static function loadById($id)
    {
        $result = self::getPersistenceDriver()->getOne([self::FIELD_ID => $id]);

        return new static($result);
    }

    /**
     * Load GameTimeOrms by gameDateId
     *
     * @param int       $gameDateId
     *
     * @return [] GameTimeOrms
     */
    public static function loadByGameDateId($gameDateId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_GAME_DATE_ID    => $gameDateId,
            ]);

        $gameTimeOrms = [];
        foreach ($results as $result) {
            $gameTimeOrms[] = new static($result);
        }

        return $gameTimeOrms;
    }

    /**
     * Load a GameTimeOrms by divisionId
     *
     * @param int $gameDateId
     * @param int $divisionId
     *
     * @return array []   GameTimeOrms
     */
    public static function loadByDivisionId($gameDateId, $divisionId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_GAME_DATE_ID    => $gameDateId,
                self::FIELD_DIVISION_ID     => $divisionId,
            ]);

        $gameTimeOrms = [];
        foreach ($results as $result) {
            $gameTimeOrms[] = new static($result);
        }

        return $gameTimeOrms;
    }

    /**
     * Load a GameTimeOrms by fieldId
     *
     * @param int $gameDateId
     * @param int $fieldId
     *
     * @return array []   GameTimeOrms
     */
    public static function loadByFieldId($gameDateId, $fieldId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_GAME_DATE_ID    => $gameDateId,
                self::FIELD_FIELD_ID        => $fieldId,
            ]);

        $gameTimeOrms = [];
        foreach ($results as $result) {
            $gameTimeOrms[] = new static($result);
        }

        return $gameTimeOrms;
    }
}