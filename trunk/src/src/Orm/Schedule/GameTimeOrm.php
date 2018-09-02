<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Orm\FieldValidator as FV;
use DAG\Framework\Orm\PersistenceConfig as PC;
use DAG\Framework\Orm\PersistenceModel;
use DAG\Framework\Orm\DuplicateEntryException;


/**
 * @property int    $id
 * @property int    $gameDateId
 * @property int    $fieldId
 * @property string $startTime
 * @property string $genderPreference
 * @property int    $gameId
 * @property int    locked
 */
class GameTimeOrm extends PersistenceModel
{
    const FIELD_ID                  = 'id';
    const FIELD_GAME_DATE_ID        = 'gameDateId';
    const FIELD_FIELD_ID            = 'fieldId';
    const FIELD_START_TIME          = 'startTime';
    const FIELD_ACTUAL_START_TIME   = 'actualStartTime';
    const FIELD_GENDER_PREFERENCE   = 'genderPreference';
    const FIELD_GAME_ID             = 'gameId';
    const FIELD_LOCKED              = 'locked';

    const BOYS  = 'Boys';
    const GIRLS = 'Girls';

    protected static $fields = [
        self::FIELD_ID                  => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_GAME_DATE_ID        => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_FIELD_ID            => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_START_TIME          => [FV::STRING, [FV::NO_CONSTRAINTS]],
        self::FIELD_ACTUAL_START_TIME   => [FV::STRING, [FV::NO_CONSTRAINTS], null],
        self::FIELD_GENDER_PREFERENCE   => [FV::STRING, [FV::NO_CONSTRAINTS]],
        self::FIELD_GAME_ID             => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_LOCKED              => [FV::INT,    [FV::NO_CONSTRAINTS]],
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
     * @param int       $fieldId
     * @param string    $startTime
     * @param string    $genderPreference
     * @param int       $gameId
     * @param string    $actualStartTime
     * @param int       $locked
     *
     * @return GameTimeOrm
     * @throws DuplicateEntryException
     */
    public static function create(
        $gameDateId,
        $fieldId,
        $startTime,
        $genderPreference,
        $gameId = null,
        $actualStartTime = null,
        $locked = 0)
    {
        $result = self::getPersistenceDriver()->create(
            array_filter(
                [
                    self::FIELD_GAME_DATE_ID        => $gameDateId,
                    self::FIELD_FIELD_ID            => $fieldId,
                    self::FIELD_START_TIME          => $startTime,
                    self::FIELD_GENDER_PREFERENCE   => $genderPreference,
                    self::FIELD_GAME_ID             => $gameId,
                    self::FIELD_ACTUAL_START_TIME   => $actualStartTime,
                    self::FIELD_LOCKED              => $locked,
                ],
                function ($item) {
                    return $item !== null;
                }
            )
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
     * Load a GameTimeOrm by gameId
     *
     * @param int $gameId
     *
     * @return GameTimeOrm
     */
    public static function loadByGameId($gameId)
    {
        $result = self::getPersistenceDriver()->getOne([self::FIELD_GAME_ID => $gameId]);

        return new static($result);
    }

    /**
     * Load GameTimeOrms by gameDateId
     *
     * @param int       $gameDateId
     *
     * @return GameTimeOrm[]
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
     * Load a GameTimeOrms by gameId and fieldId
     *
     * @param int $gameDateId
     * @param int $fieldId
     *
     * @return GameTimeOrm[]
     */
    public static function loadByGameDateIdAndFieldId($gameDateId, $fieldId)
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

    /**
     * Load a GameTimeOrms by gameId and fieldId and gender
     *
     * @param int       $gameDateId
     * @param string    $gender
     * @param int       $fieldId
     *
     * @return GameTimeOrm[]
     */
    public static function loadByGameDateIdAndFieldIdAndGender($gameDateId, $fieldId, $gender)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_GAME_DATE_ID        => $gameDateId,
                self::FIELD_FIELD_ID            => $fieldId,
                self::FIELD_GENDER_PREFERENCE   => $gender,
            ]);

        $gameTimeOrms = [];
        foreach ($results as $result) {
            $gameTimeOrms[] = new static($result);
        }

        return $gameTimeOrms;
    }

    /**
     * Load a GameTimeOrm by gameId and fieldId and startTime
     *
     * @param int       $gameDateId
     * @param int       $fieldId
     * @param string    $startTime
     *
     * @return GameTimeOrm
     */
    public static function loadByGameDateIdAndFieldIdAndStartTime($gameDateId, $fieldId, $startTime)
    {
        $result = self::getPersistenceDriver()->getOne(
            [
                self::FIELD_GAME_DATE_ID    => $gameDateId,
                self::FIELD_FIELD_ID        => $fieldId,
                self::FIELD_START_TIME      => $startTime,
            ]);

        return new static($result);
    }

    /**
     * Load a GameTimeOrms by fieldId
     *
     * @param int $fieldId
     *
     * @return array []   GameTimeOrms
     */
    public static function loadByFieldId($fieldId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_FIELD_ID        => $fieldId,
            ]);

        $gameTimeOrms = [];
        foreach ($results as $result) {
            $gameTimeOrms[] = new static($result);
        }

        return $gameTimeOrms;
    }

    /**
     * Get unique set of startTimes for the field and set of divisions playing on the field
     *
     * @param string    $fieldIds - comma separated list of fieldIds
     *
     * @return string[] $startTimes
     */
    public static function getUniqueStartTimes($fieldIds)
    {
        $query = "
            select
                distinct ifnull(t.actualStartTime, t.startTime) as startTime
            from
                field as f
                join gameTime as t on
                    t.fieldId = f.id
            where
                f.id in ($fieldIds)";

        $results = self::getPersistenceDriver()->rawQuery($query);

        return $results;
    }
}