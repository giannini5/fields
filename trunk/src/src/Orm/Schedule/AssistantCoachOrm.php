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
 * @property string $phone1
 * @property string $phone2
 */
class AssistantCoachOrm extends PersistenceModel
{
    const FIELD_ID        = 'id';
    const FIELD_TEAM_ID   = 'teamId';
    const FIELD_FAMILY_ID = 'familyId';
    const FIELD_NAME      = 'name';
    const FIELD_EMAIL     = 'email';
    const FIELD_PHONE1    = 'phone1';
    const FIELD_PHONE2    = 'phone2';

    protected static $fields = [
        self::FIELD_ID        => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_TEAM_ID   => [FV::INT,    [FV::NO_CONSTRAINTS]],
        self::FIELD_FAMILY_ID => [FV::INT,    [FV::NO_CONSTRAINTS], null],
        self::FIELD_NAME      => [FV::STRING, [FV::NO_CONSTRAINTS]],
        self::FIELD_EMAIL     => [FV::STRING, [FV::NO_CONSTRAINTS]],
        self::FIELD_PHONE1    => [FV::STRING, [FV::NO_CONSTRAINTS]],
        self::FIELD_PHONE2    => [FV::STRING, [FV::NO_CONSTRAINTS]],
    ];

    protected static $config = [
        PC::PERSISTENCE_DRIVER => PC::DRIVER_MYSQL,
        PC::SCHEMA             => 'schedule_rw',
        PC::TABLE              => 'assistantCoach',
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
     * @param string    $phone1
     * @param string    $phone2
     *
     * @return AssistantCoachOrm
     * @throws DuplicateEntryException
     */
    public static function create(
        $teamId,
        $familyId,
        $name,
        $email,
        $phone1,
        $phone2)
    {
        $result = self::getPersistenceDriver()->create(
                [
                    self::FIELD_TEAM_ID     => $teamId,
                    self::FIELD_FAMILY_ID   => $familyId,
                    self::FIELD_NAME        => $name,
                    self::FIELD_EMAIL       => $email,
                    self::FIELD_PHONE1      => $phone1,
                    self::FIELD_PHONE2      => $phone2,
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
     * @return AssistantCoachOrm
     */
    public static function loadById($id)
    {
        $result = self::getPersistenceDriver()->getOne([self::FIELD_ID => $id]);

        return new self($result);
    }

    /**
     * Load AssistantCoachOrm by teamId and email
     *
     * @param int       $teamId
     * @param string    $name
     *
     * @return AssistantCoachOrm
     */
    public static function loadByTeamIdAndName($teamId, $name)
    {
        $result = self::getPersistenceDriver()->getOne(
            [
                self::FIELD_TEAM_ID => $teamId,
                self::FIELD_NAME    => $name,
            ]);

        return new static($result);
    }

    /**
     * Load AssistantCoachOrms by teamId
     *
     * @param int       $teamId
     *
     * @return AssistantCoachOrm
     */
    public static function loadByTeamId($teamId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_TEAM_ID => $teamId,
            ]);

        $assistantCoachOrms = [];
        foreach ($results as $result) {
            $assistantCoachOrms[] = new static($result);
        }

        return $assistantCoachOrms;
    }

    /**
     * Load a AssistantCoachOrms by familyId
     *
     * @param int $familyId
     *
     * @return array []   AssistantCoachOrms
     */
    public static function loadByFamilyId($familyId)
    {
        $results = self::getPersistenceDriver()->getMany(
            [
                self::FIELD_FAMILY_ID => $familyId,
            ]);

        $assistantCoachOrms = [];
        foreach ($results as $result) {
            $assistantCoachOrms[] = new static($result);
        }

        return $assistantCoachOrms;
    }

    /**
     * Load all assistant coaches for the specified season
     *
     * @param int $seasonId
     *
     * @return array
     */
    public static function loadBySeasonId($seasonId)
    {
        $assistantCoachOrms = [];

        $divisionOrms = DivisionOrm::loadBySeasonId($seasonId);
        foreach ($divisionOrms as $divisionOrm) {
            $teamOrms = TeamOrm::loadByDivisionId($divisionOrm->id);

            foreach ($teamOrms as $team) {
                $orms = self::loadByTeamId($team->id);
                $assistantCoachOrms = array_merge($assistantCoachOrms, $orms);
            }
        }

        return $assistantCoachOrms;
    }
}