<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Orm\FieldValidator as FV;
use DAG\Framework\Orm\PersistenceConfig as PC;
use DAG\Framework\Orm\PersistenceModel;
use DAG\Framework\Orm\DuplicateEntryException;


/**
 * @property int    $id
 * @property int    $teamId
 * @property int    $familyId
 * @property string $name
 * @property string $email
 * @property string $phone
 */
class PlayerOrm extends PersistenceModel
{
    const FIELD_ID        = 'id';
    const FIELD_TEAM_ID   = 'teamId';
    const FIELD_FAMILY_ID = 'familyId';
    const FIELD_NAME      = 'name';
    const FIELD_EMAIL     = 'email';
    const FIELD_PHONE     = 'phone';

    protected static $fields = [
        self::FIELD_ID        => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_TEAM_ID   => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_FAMILY_ID => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_NAME      => [FV::STRING, [FV::NO_CONSTRAINTS]],
        self::FIELD_EMAIL     => [FV::STRING, [FV::NO_CONSTRAINTS]],
        self::FIELD_PHONE     => [FV::STRING, [FV::NO_CONSTRAINTS]],
    ];

    protected static $config = [
        PC::PERSISTENCE_DRIVER => PC::DRIVER_MYSQL,
        PC::SCHEMA             => 'schedule_rw',
        PC::TABLE              => 'player',
        PC::AUTO_INC_FIELD     => self::FIELD_ID,
        PC::PRIMARY_KEYS       => [self::FIELD_ID],
    ];

    /**
     * Creates a company
     *
     * @param int       $teamId
     * @param int       $familyId
     * @param string    $name
     * @param string    $email
     * @param string    $phone
     *
     * @return PlayerOrm
     * @throws DuplicateEntryException
     */
    public static function create(
        $teamId,
        $familyId,
        $name,
        $email,
        $phone)
    {
        $result = self::getPersistenceDriver()->create(
            [
                self::FIELD_TEAM_ID     => $teamId,
                self::FIELD_FAMILY_ID   => $familyId,
                self::FIELD_NAME        => $name,
                self::FIELD_EMAIL       => $email,
                self::FIELD_PHONE       => $phone,
            ],
            function ($item) {
                return $item !== null;
            }
        );

        return new self($result);
    }

    /**
     * Load by id
     *
     * @param int $id
     *
     * @return PlayerOrm
     */
    public static function loadById($id)
    {
        $result = self::getPersistenceDriver()->getOne([self::FIELD_ID => $id]);

        return new self($result);
    }

    /**
     * Load by name
     *
     * @param int       $teamId
     * @param string    $name
     *
     * @return PlayerOrm
     */
    public static function loadByName($teamId, $name)
    {
        $result = self::getPersistenceDriver()->getOne(
            [self::FIELD_TEAM_ID    => $teamId,
             self::FIELD_NAME       => $name,]);

        return new self($result);
    }

    /**
     * Load PlayerOrms by teamId
     *
     * @param int       $teamId
     *
     * @return PlayerOrm
     */
    public static function loadByTeamId($teamId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_TEAM_ID => $teamId,
            ]);

        $playerOrms = [];
        foreach ($results as $result) {
            $playerOrms[] = new static($result);
        }

        return $playerOrms;
    }

    /**
     * Load a PlayerOrms by familyId
     *
     * @param int $familyId
     *
     * @return array []   PlayerOrms
     */
    public static function loadByFamilyId($familyId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_FAMILY_ID => $familyId,
            ]);

        $playerOrms = [];
        foreach ($results as $result) {
            $playerOrms[] = new static($result);
        }

        return $playerOrms;
    }
}