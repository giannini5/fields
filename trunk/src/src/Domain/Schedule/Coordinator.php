<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Orm\Schedule\ScheduleCoordinatorOrm;
use DAG\Framework\Exception\Precondition;


/**
 * @property int    $id
 * @property League $league
 * @property string $email
 * @property string $name
 * @property string $password
 */
class Coordinator extends Domain
{
    const COACH_USER_TYPE                       = 0;
    const MANAGER_USER_TYPE                     = 1;
    const PRACTICE_FIELD_COORDINATOR_USER_TYPE  = 2;
    const SCHEDULE_COORDINATOR_USER_TYPE        = 3;
    const SCORING_COORDINATOR_USER_TYPE         = 4;
    const REFEREE_COORDINATOR_USER_TYPE         = 5;
    const REFEREE_USER_TYPE                     = 6;

    /** @var ScheduleCoordinatorOrm TODO: change to CoordinatorOrm */
    private $coordinatorOrm;

    /** @var League */
    private $league;

    /**
     * @param ScheduleCoordinatorOrm    $coordinatorOrm
     * @param League                    $league (defaults to null)
     */
    protected function __construct($coordinatorOrm, $league = null)
    {
        $this->coordinatorOrm = $coordinatorOrm;
        $this->league = isset($league) ? $league : League::lookupById($coordinatorOrm->leagueId);
    }

    /**
     * @param League $league
     * @param string $email
     * @param string $name
     * @param string $password
     *
     * @return Coordinator
     */
    public static function create(
        $league,
        $email,
        $name,
        $password)
    {
        $coordinatorOrm = ScheduleCoordinatorOrm::create($league->id, $email, $name, $password);
        return new static($coordinatorOrm, $league);
    }

    /**
     * @param int $coordinatorId
     *
     * @return Coordinator
     */
    public static function lookupById($coordinatorId)
    {
        $coordinatorOrm = ScheduleCoordinatorOrm::loadById($coordinatorId);
        return new static($coordinatorOrm);
    }

    /**
     * @param League $league
     * @param string $email
     *
     * @return Coordinator
     */
    public static function lookupByEmail($league, $email)
    {
        Precondition::isNonEmpty($email, 'email should not be empty');
        $coordinatorOrm = ScheduleCoordinatorOrm::loadByLeagueIdAndEmail($league->id, $email);
        return new static($coordinatorOrm, $league);
    }

    /**
     * @param League $league
     *
     * @return Coordinator[]
     */
    public static function lookupByLeague($league)
    {
        $coordinators = [];

        $coordinatorOrms = ScheduleCoordinatorOrm::loadByLeagueId($league->id);
        foreach ($coordinatorOrms as $coordinatorOrm) {
            $coordinators[] = new static($coordinatorOrm, $league);
        }

        return $coordinators;
    }

    /**
     * @param $propertyName
     * @return int|string
     */
    public function __get($propertyName)
    {
        switch ($propertyName) {
            case "id":
            case "email":
            case "name":
            case "password":
                return $this->coordinatorOrm->{$propertyName};

            case "league":
                return $this->{$propertyName};

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
                return false;
        }
    }

    /**
     * @param $propertyName
     * @param $value
     */
    public function __set($propertyName, $value)
    {
        try {
            switch ($propertyName) {
                case "name":
                case "email":
                case "password":
                    if ($this->coordinatorOrm->{$propertyName} != $value) {
                        $this->coordinatorOrm->{$propertyName} = $value;
                        $this->coordinatorOrm->save();
                    }
                    break;

                default:
                    Precondition::isTrue(false, "Unrecognized property: $propertyName");
                    break;
            }
        } catch (\Exception $e) {
            var_dump($e);
        }
    }

    /**
     *  Delete the coordinator
     */
    public function delete()
    {
        $this->coordinatorOrm->delete();
    }
}