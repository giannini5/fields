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
 * @property int    $number
 * @property int    $goals
 * @property int    $quartersSub
 * @property int    $quartersKeep
 * @property int    $yellowCards
 * @property int    $redCards
 */
class PlayerOrm extends PersistenceModel
{
    const FIELD_ID              = 'id';
    const FIELD_TEAM_ID         = 'teamId';
    const FIELD_FAMILY_ID       = 'familyId';
    const FIELD_NAME            = 'name';
    const FIELD_EMAIL           = 'email';
    const FIELD_PHONE           = 'phone';
    const FIELD_NUMBER          = 'number';
    const FIELD_GOALS           = 'goals';
    const FIELD_QUARTERS_SUB    = 'quartersSub';
    const FIELD_QUARTERS_KEEP   = 'quartersKeep';
    const FIELD_YELLOW_CARDS    = 'yellowCards';
    const FIELD_RED_CARDS       = 'redCards';

    protected static $fields = [
        self::FIELD_ID              => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_TEAM_ID         => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_FAMILY_ID       => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_NAME            => [FV::STRING, [FV::NO_CONSTRAINTS]],
        self::FIELD_EMAIL           => [FV::STRING, [FV::NO_CONSTRAINTS]],
        self::FIELD_PHONE           => [FV::STRING, [FV::NO_CONSTRAINTS]],
        self::FIELD_NUMBER          => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_GOALS           => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_QUARTERS_SUB    => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_QUARTERS_KEEP   => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_YELLOW_CARDS    => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
        self::FIELD_RED_CARDS       => [FV::INT,    [FV::NO_CONSTRAINTS], 0],
    ];

    protected static $config = [
        PC::PERSISTENCE_DRIVER => PC::DRIVER_MYSQL,
        PC::SCHEMA             => 'schedule_rw',
        PC::TABLE              => 'player',
        PC::AUTO_INC_FIELD     => self::FIELD_ID,
        PC::PRIMARY_KEYS       => [self::FIELD_ID],
    ];

    const ORDER_BY_NAME     = 'orderByName';
    const ORDER_BY_NUMBER   = 'orderByNumber';

    /**
     * Creates a company
     *
     * @param int       $teamId
     * @param int       $familyId
     * @param string    $name
     * @param string    $email
     * @param string    $phone
     * @param int       $number - defaults to null
     *
     * @return PlayerOrm
     * @throws DuplicateEntryException
     */
    public static function create(
        $teamId,
        $familyId,
        $name,
        $email,
        $phone,
        $number = null)
    {
        $result = self::getPersistenceDriver()->create(
            array_filter(
                [
                    self::FIELD_TEAM_ID     => $teamId,
                    self::FIELD_FAMILY_ID   => $familyId,
                    self::FIELD_NAME        => $name,
                    self::FIELD_EMAIL       => $email,
                    self::FIELD_PHONE       => $phone,
                    self::FIELD_NUMBER      => $number,
                ],
                function ($item) {
                    return $item !== null;
                }
            )
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
     * @param string    $orderBy (PlayerOrm::ORDER_BY_NAME or PlayerOrm::ORDER_BY_NUMBER)
     *
     * @return PlayerOrm[]
     */
    public static function loadByTeamId($teamId, $orderBy = self::ORDER_BY_NAME)
    {
        $orderBySQL = $orderBy == self::ORDER_BY_NAME ?
            "order by name"
            : "order by ifnull(number, 9999), name";

        $results = self::getPersistenceDriver()->getManyFromCustomMySqlQuery(
            [
                self::FIELD_TEAM_ID => $teamId,
            ],
            $orderBySQL);

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
     * @return PlayerOrm[]
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