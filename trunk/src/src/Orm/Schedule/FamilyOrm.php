<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Exception\Precondition;
use DAG\Framework\Orm\FieldValidator as FV;
use DAG\Framework\Orm\NoResultsException;
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
    const FIELD_PHONE1        = 'phone1';
    const FIELD_PHONE2        = 'phone2';

    protected static $fields = [
        self::FIELD_ID            => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_SEASON_ID     => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_PHONE1        => [FV::STRING, [FV::NO_CONSTRAINTS]],
        self::FIELD_PHONE2        => [FV::STRING, [FV::NO_CONSTRAINTS], ''],
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
     * @param string    $phone1
     * @param string    $phone2
     *
     * @return FamilyOrm
     * @throws DuplicateEntryException
     */
    public static function create(
        $seasonId,
        $phone1,
        $phone2 = '')
    {
        $result = self::getPersistenceDriver()->create(
            [
                self::FIELD_SEASON_ID       => $seasonId,
                self::FIELD_PHONE1          => $phone1,
                self::FIELD_PHONE2          => $phone2,
            ],
            function ($item) {
                return $item !== null;
            }
        );

        return new static($result);
    }

    /**
     * Load or Create a FamilyOrm
     *
     * @param int       $seasonId
     * @param string    $phone1
     * @param string    $phone2
     *
     * @return FamilyOrm
     */
    public static function loadOrCreate(
        $seasonId,
        $phone1,
        $phone2)
    {
        Precondition::isTrue(!empty($phone1) or !empty($phone2), "FamilyOrm::loadOrCreate either phone1 or phone2 must be populated");

        // Do not store the same phone number more than once
        if ($phone1 == $phone2) {
            $phone2 = '';
        }

        // Try to find with phone1
        $familyOrm = null;
        if (self::findBySeasonIdAndPhone($seasonId, $phone1, $familyOrm)) {
            return $familyOrm;
        }

        // Try to find with phone2
        if (self::findBySeasonIdAndPhone($seasonId, $phone2, $familyOrm)) {
            return $familyOrm;
        }

        // Not found so create, make sure phone1 is always populated.  Optionally populate phone2
        $result = self::getPersistenceDriver()->create(
            [
                self::FIELD_SEASON_ID       => $seasonId,
                self::FIELD_PHONE1          => empty($phone1) ? $phone2 : $phone1,
                self::FIELD_PHONE2          => empty($phone1) ? '' : $phone2,
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
        try {
            $result = self::getPersistenceDriver()->getOne(
                [
                    self::FIELD_SEASON_ID   => $seasonId,
                    self::FIELD_PHONE1      => $phone,
                ]);

            return new static($result);
        } catch (NoResultsException $e) {
            $result = self::getPersistenceDriver()->getOne(
                [
                    self::FIELD_SEASON_ID   => $seasonId,
                    self::FIELD_PHONE2      => $phone,
                ]);

            return new static($result);
        }
    }

    /**
     * Find a FamilyOrm by seasonId, phone
     *
     * @param int       $seasonId
     * @param string    $phone
     * @param FamilyOrm $familyOrm output parameter
     *
     * @return bool
     */
    public static function findBySeasonIdAndPhone($seasonId, $phone, &$familyOrm)
    {
        try {
            $result = self::getPersistenceDriver()->getOne(
                [
                    self::FIELD_SEASON_ID   => $seasonId,
                    self::FIELD_PHONE1      => $phone,
                ]);

            $familyOrm = new static($result);

            return true;
        } catch (NoResultsException $e) {
            return false;
        }
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