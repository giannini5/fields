<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Orm\FieldValidator as FV;
use DAG\Framework\Orm\PersistenceConfig as PC;
use DAG\Framework\Orm\PersistenceModel;

/**
 * @property int    $id
 * @property int    $leagueId
 * @property string $email
 * @property string $name
 * @property string $password
 */
class ScheduleCoordinatorOrm extends PersistenceModel
{
    const FIELD_ID           = 'id';
    const FIELD_LEAGUE_ID    = 'leagueId';
    const FIELD_EMAIL        = 'email';
    const FIELD_NAME         = 'name';
    const FIELD_PASSWORD     = 'password';

    protected static $fields = [
        self::FIELD_ID           => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_LEAGUE_ID    => [FV::INT,    [FV::NO_CONSTRAINTS], ''],
        self::FIELD_EMAIL        => [FV::STRING, [FV::NO_CONSTRAINTS], ''],
        self::FIELD_NAME         => [FV::STRING, [FV::NO_CONSTRAINTS], ''],
        self::FIELD_PASSWORD     => [FV::STRING, [FV::NO_CONSTRAINTS], ''],
    ];

    protected static $config = [
        PC::PERSISTENCE_DRIVER => PC::DRIVER_MYSQL,
        PC::SCHEMA             => 'schedule_rw',
        PC::TABLE              => 'scheduleCoordinator',
        PC::AUTO_INC_FIELD     => self::FIELD_ID,
        PC::PRIMARY_KEYS       => [self::FIELD_LEAGUE_ID, self::FIELD_EMAIL],
    ];

    /**
     * Create a ScheduleCoordinator
     *
     * @param $leagueId
     * @param $email
     * @param $name
     * @param $password
     *
     * @return ScheduleCoordinatorOrm
     * @throws \DAG\Framework\Orm\DuplicateEntryException
     */
    public static function create($leagueId, $email, $name, $password)
    {
        $result = self::getPersistenceDriver()->create(
            array_filter(
                [
                    self::FIELD_LEAGUE_ID    => $leagueId,
                    self::FIELD_EMAIL        => $email,
                    self::FIELD_NAME         => $name,
                    self::FIELD_PASSWORD     => $password,
                ],
                function ($item) {
                    return $item !== null;
                }
            )
        );

        return new static($result);
    }

    /**
     * Load a ScheduleCoordinatorOrm by id
     *
     * @param int $id
     *
     * @return ScheduleCoordinatorOrm
     */
    public static function loadById($id)
    {
        $result = self::getPersistenceDriver()->getOne([self::FIELD_ID => $id]);

        return new static($result);
    }

    /**
     * Load a ScheduleCoordinatorOrm by leagueId, email
     *
     * @param int       $leagueId
     * @param string    $email
     *
     * @return ScheduleCoordinatorOrm
     */
    public static function loadByLeagueIdAndEmail($leagueId, $email)
    {
        $result = self::getPersistenceDriver()->getOne(
            [self::FIELD_LEAGUE_ID  => $leagueId,
             self::FIELD_EMAIL      => $email]);

        return new static($result);
    }

    /**
     * Load a ScheduleCoordinatorOrms by leagueId
     *
     * @param int $leagueId
     *
     * @return array []   ScheduleCoordinatorOrms
     */
    public static function loadByLeagueId($leagueId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [self::FIELD_LEAGUE_ID  => $leagueId]);

        $scheduleCoordinatorOrms = [];
        foreach ($results as $result) {
            $scheduleCoordinatorOrms[] = new static($result);
        }

        return $scheduleCoordinatorOrms;
    }
}