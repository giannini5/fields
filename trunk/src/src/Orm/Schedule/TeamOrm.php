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
 * @property string $nameId
 * @property string $color
 * @property string $region
 * @property string $city
 * @property int    $volunteerPoints
 * @property int    $seed
 * @property int    $rank
 * @property string $thirdPartyId
 */
class TeamOrm extends PersistenceModel
{
    const FIELD_ID                  = 'id';
    const FIELD_DIVISION_ID         = 'divisionId';
    const FIELD_POOL_ID             = 'poolId';
    const FIELD_NAME                = 'name';
    const FIELD_NAME_ID             = 'nameId';
    const FIELD_COLOR               = 'color';
    const FIELD_REGION              = 'region';
    const FIELD_CITY                = 'city';
    const FIELD_VOLUNTEER_POINTS    = 'volunteerPoints';
    const FIELD_SEED                = 'seed';
    const FIELD_RANK                = 'rank';
    const FIELD_THIRD_PARTY_ID      = 'thirdPartyId';
    protected static $fields = [
        self::FIELD_ID                  => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_DIVISION_ID         => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_POOL_ID             => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_NAME                => [FV::STRING, [FV::NO_CONSTRAINTS], ''],
        self::FIELD_NAME_ID             => [FV::STRING, [FV::NO_CONSTRAINTS], ''],
        self::FIELD_COLOR               => [FV::STRING, [FV::NO_CONSTRAINTS], ''],
        self::FIELD_REGION              => [FV::STRING, [FV::NO_CONSTRAINTS], ''],
        self::FIELD_CITY                => [FV::STRING, [FV::NO_CONSTRAINTS], ''],
        self::FIELD_VOLUNTEER_POINTS    => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_SEED                => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_RANK                => [FV::INT,    [FV::NO_CONSTRAINTS], 1000],
        self::FIELD_THIRD_PARTY_ID      => [FV::STRING, [FV::NO_CONSTRAINTS], null],
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
     * @param string    $nameId     - Example: U10G-1A
     * @param string    $region     - Example: 122
     * @param string    $city
     * @param int       $volunteerPoints - defaults to 0
     * @param int       $seed - defaults to 0
     * @param int       $rank - defaults to 1000
     * @param string    $thirdPartyId - defaults to null
     *
     * @return TeamOrm
     * @throws DuplicateEntryException
     */
    public static function create(
        $divisionId,
        $poolId,
        $name,
        $nameId,
        $region,
        $city,
        $volunteerPoints = 0,
        $seed = 0,
        $color = '',
        $rank = 1000,
        $thirdPartyId = null
    ) {
        $result = self::getPersistenceDriver()->create(
            [
                self::FIELD_DIVISION_ID         => $divisionId,
                self::FIELD_POOL_ID             => $poolId,
                self::FIELD_NAME                => $name,
                self::FIELD_NAME_ID             => $nameId,
                self::FIELD_COLOR               => $color,
                self::FIELD_REGION              => $region,
                self::FIELD_CITY                => $city,
                self::FIELD_VOLUNTEER_POINTS    => $volunteerPoints,
                self::FIELD_SEED                => $seed,
                self::FIELD_RANK                => $rank,
                self::FIELD_THIRD_PARTY_ID      => $thirdPartyId,
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
     * Load a TeamOrm by thirdPartyId
     *
     * @param string $thirdPartyId
     *
     * @return TeamOrm
     */
    public static function loadByThirdPartyId($thirdPartyId)
    {
        $result = self::getPersistenceDriver()->getOne([self::FIELD_THIRD_PARTY_ID => $thirdPartyId]);
        return new static($result);
    }

    /**
     * Load a TeamOrm by divisionId, name
     *
     * @param int       $divisionId
     * @param string    $nameId
     *
     * @return TeamOrm
     */
    public static function loadByDivisionIdAndNameId($divisionId, $nameId)
    {
        $result = self::getPersistenceDriver()->getOne(
            [
                self::FIELD_DIVISION_ID => $divisionId,
                self::FIELD_NAME_ID     => $nameId,
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