<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Orm\FieldValidator as FV;
use DAG\Framework\Orm\PersistenceConfig as PC;
use DAG\Framework\Orm\PersistenceModel;
use DAG\Framework\Orm\DuplicateEntryException;

/**
 * @property int    $id
 * @property int    $seasonId
 * @property string $phone
 */
class FamilyOrm extends PersistenceModel
{
    const FIELD_ID            = 'id';
    const FIELD_SEASON_ID     = 'seasonId';
    const FIELD_PHONE         = 'phone';

    protected static $fields = [
        self::FIELD_ID            => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_SEASON_ID     => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_PHONE         => [FV::STRING, [FV::NO_CONSTRAINTS]],
    ];

    protected static $config = [
        PC::PERSISTENCE_DRIVER => PC::DRIVER_MYSQL,
        PC::SCHEMA             => 'schedule_rw',
        PC::TABLE              => 'family',
        PC::AUTO_INC_FIELD     => self::FIELD_ID,
        PC::PRIMARY_KEYS       => [self::FIELD_ID],
    ];

    /**
     * Create a FamilyOrm
     *
     * @param int       $seasonId
     * @param string    $phone
     *
     * @return FamilyOrm
     * @throws DuplicateEntryException
     */
    public static function create(
        $seasonId,
        $phone)
    {
        $result = self::getPersistenceDriver()->create(
            [
                self::FIELD_SEASON_ID       => $seasonId,
                self::FIELD_PHONE           => $phone,
            ],
            function ($item) {
                return $item !== null;
            }
        );

        return new static($result);
    }

    /**
     * Load a FamilyOrm by id
     *
     * @param int $id
     *
     * @return FamilyOrm
     */
    public static function loadById($id)
    {
        $result = self::getPersistenceDriver()->getOne([self::FIELD_ID => $id]);

        return new static($result);
    }

    /**
     * Load a FamilyOrm by seasonId, phone
     *
     * @param int       $seasonId
     * @param string    $phone
     *
     * @return FamilyOrm
     */
    public static function loadBySeasonIdAndPhone($seasonId, $phone)
    {
        $result = self::getPersistenceDriver()->getOne(
            [
                self::FIELD_SEASON_ID   => $seasonId,
                self::FIELD_PHONE       => $phone,
            ]);

        return new static($result);
    }

    /**
     * Load a FamilyOrms by seasonId
     *
     * @param int $seasonId
     *
     * @return array []   FamilyOrms
     */
    public static function loadBySeasonId($seasonId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_SEASON_ID  => $seasonId
            ]);

        $familyOrms = [];
        foreach ($results as $result) {
            $familyOrms[] = new static($result);
        }

        return $familyOrms;
    }
}