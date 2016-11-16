<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Orm\FieldValidator as FV;
use DAG\Framework\Orm\PersistenceConfig as PC;
use DAG\Framework\Orm\PersistenceModel;


/**
 * @property int    $id
 * @property int    $seasonId
 * @property string $name
 * @property string $address1
 * @property string $address2
 * @property string $city
 * @property string $state
 * @property string $postalCode
 * @property string $country
 * @property string $contactName
 * @property string $contactEmail
 * @property string $contactPhone
 * @property string $image
 * @property bool   $enabled
 */
class FacilityOrm extends PersistenceModel
{
    const FIELD_ID            = 'id';
    const FIELD_SEASON_ID     = 'seasonId';
    const FIELD_NAME          = 'name';
    const FIELD_ADDRESS1      = 'address1';
    const FIELD_ADDRESS2      = 'address2';
    const FIELD_CITY          = 'city';
    const FIELD_STATE         = 'state';
    const FIELD_POSTAL_CODE   = 'postalCode';
    const FIELD_COUNTRY       = 'country';
    const FIELD_CONTACT_NAME  = 'contactName';
    const FIELD_CONTACT_EMAIL = 'contactEmail';
    const FIELD_CONTACT_PHONE = 'contactPhone';
    const FIELD_IMAGE         = 'image';
    const FIELD_ENABLED       = 'enabled';

    protected static $fields = [
        self::FIELD_ID            => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_SEASON_ID     => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_NAME          => [FV::STRING, [FV::NO_CONSTRAINTS]],
        self::FIELD_ADDRESS1      => [FV::STRING, [FV::NO_CONSTRAINTS], ''],
        self::FIELD_ADDRESS2      => [FV::STRING, [FV::NO_CONSTRAINTS], ''],
        self::FIELD_CITY          => [FV::STRING, [FV::NO_CONSTRAINTS], ''],
        self::FIELD_STATE         => [FV::STRING, [FV::NO_CONSTRAINTS], ''],
        self::FIELD_POSTAL_CODE   => [FV::STRING, [FV::NO_CONSTRAINTS], ''],
        self::FIELD_COUNTRY       => [FV::STRING, [FV::NO_CONSTRAINTS], ''],
        self::FIELD_CONTACT_NAME  => [FV::STRING, [FV::NO_CONSTRAINTS], ''],
        self::FIELD_CONTACT_EMAIL => [FV::STRING, [FV::NO_CONSTRAINTS], ''],
        self::FIELD_CONTACT_PHONE => [FV::STRING, [FV::NO_CONSTRAINTS], ''],
        self::FIELD_IMAGE         => [FV::STRING, [FV::NO_CONSTRAINTS], ''],
        self::FIELD_ENABLED       => [FV::INT,    [FV::NO_CONSTRAINTS], '1'],
    ];

    protected static $config = [
        PC::PERSISTENCE_DRIVER => PC::DRIVER_MYSQL,
        PC::SCHEMA             => 'schedule_rw',
        PC::TABLE              => 'facility',
        PC::AUTO_INC_FIELD     => self::FIELD_ID,
        PC::PRIMARY_KEYS       => [self::FIELD_SEASON_ID, self::FIELD_NAME],
    ];

    /**
     * Create a Facility
     *
     * @param int    $seasonId
     * @param string $name
     * @param string $address1
     * @param string $address2
     * @param string $city
     * @param string $state
     * @param string $postalCode
     * @param string $country
     * @param string $contactName
     * @param string $contactEmail
     * @param string $contactPhone
     * @param string $image
     * @param int    $enabled
     *
     * @return FacilityOrm
     * @throws \DAG\Framework\Orm\DuplicateEntryException
     */
    public static function create(
        $seasonId,
        $name,
        $address1       = null,
        $address2       = null,
        $city           = null,
        $state          = null,
        $postalCode     = null,
        $country        = null,
        $contactName    = null,
        $contactEmail   = null,
        $contactPhone   = null,
        $image          = null,
        $enabled        = 1)
    {
        $result = self::getPersistenceDriver()->create(
            array_filter(
                [
                    self::FIELD_SEASON_ID       => $seasonId,
                    self::FIELD_NAME            => $name,
                    self::FIELD_ADDRESS1        => $address1,
                    self::FIELD_ADDRESS2        => $address2,
                    self::FIELD_CITY            => $city,
                    self::FIELD_STATE           => $state,
                    self::FIELD_POSTAL_CODE     => $postalCode,
                    self::FIELD_COUNTRY         => $country,
                    self::FIELD_CONTACT_NAME    => $contactName,
                    self::FIELD_CONTACT_EMAIL   => $contactEmail,
                    self::FIELD_CONTACT_PHONE   => $contactPhone,
                    self::FIELD_IMAGE           => $image,
                    self::FIELD_ENABLED         => $enabled,
                ],
                function ($item) {
                    return $item !== null;
                }
            )
        );

        return new static($result);
    }

    /**
     * Load a FacilityOrm by id
     *
     * @param int $id
     *
     * @return FacilityOrm
     */
    public static function loadById($id)
    {
        $result = self::getPersistenceDriver()->getOne([self::FIELD_ID => $id]);

        return new static($result);
    }

    /**
     * Load a FacilityOrm by seasonId, name
     *
     * @param int       $seasonId
     * @param string    $name
     *
     * @return FacilityOrm
     */
    public static function loadBySeasonIdAndName($seasonId, $name)
    {
        $result = self::getPersistenceDriver()->getOne(
            [
                self::FIELD_SEASON_ID  => $seasonId,
                self::FIELD_NAME       => $name
            ]);

        return new static($result);
    }

    /**
     * Load a FacilityOrms by seasonId
     *
     * @param int $seasonId
     *
     * @return array []   FacilityOrms
     */
    public static function loadBySeasonId($seasonId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_SEASON_ID  => $seasonId
            ]);

        $facilityOrms = [];
        foreach ($results as $result) {
            $facilityOrms[] = new static($result);
        }

        return $facilityOrms;
    }
}