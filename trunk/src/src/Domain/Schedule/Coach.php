<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Framework\Orm\DuplicateEntryException;
use DAG\Framework\Orm\NoResultsException;
use DAG\Orm\Schedule\CoachOrm;
use DAG\Framework\Exception\Precondition;


/**
 * @property int        $id
 * @property Team       $team
 * @property Family     $family
 * @property string     $name
 * @property string     $email
 * @property string     $phone1
 * @property string     $phone2
 */
class Coach extends Domain
{
    /** @var CoachOrm */
    private $coachOrm;

    /** @var Team */
    private $team;

    /** @var Family */
    private $family;

    /**
     * @param CoachOrm  $coachOrm
     * @param Team      $team (defaults to null)
     * @param Family    $family (defaults to null)
     */
    protected function __construct(CoachOrm $coachOrm, $team = null, $family = null)
    {
        $this->coachOrm  = $coachOrm;
        $this->team = isset($team) ? $team : Team::lookupById($coachOrm->teamId);
        $this->family     = (!isset($family) and isset($coachOrm->familyId)) ? Family::lookupById($coachOrm->familyId) : $family;
    }

    /**
     * @param Team      $team
     * @param Family    $family
     * @param string    $name
     * @param string    $email
     * @param string    $phone1
     * @param string    $phone2
     * @param bool      $ignore - true if DuplicateEntryException should be ignored and lookup instead
     *
     * @return Coach
     * @throws DuplicateEntryException
     * @throws \Exception
     */
    public static function create(
        $team,
        $family,
        $name,
        $email,
        $phone1,
        $phone2,
        $ignore = false)
    {
        try {
            $familyId = isset($family) ? $family->id : null;
            $coachOrm = CoachOrm::create($team->id, $familyId, $name, $email, $phone1, $phone2);
            return new static($coachOrm, $team, $family);
        } catch (DuplicateEntryException $e) {
            if ($ignore) {
                return Coach::lookupByTeam($team);
            } else {
                throw $e;
            }

        }
    }

    /**
     * @param int $coachId
     *
     * @return Coach
     */
    public static function lookupById($coachId)
    {
        $coachOrm = CoachOrm::loadById($coachId);
        return new static($coachOrm);
    }

    /**
     * @param Team      $team
     *
     * @return Coach
     */
    public static function lookupByTeam($team)
    {
        $coachOrm = CoachOrm::loadByTeamId($team->id);
        return new static($coachOrm, $team);
    }

    /**
     * @param Team      $team
     * @param Coach     $coach - Output parameter of coach if Coach found
     *
     * @return bool true if coach found, false otherwise
     */
    public static function findCoachForTeam($team, &$coach)
    {
        try {
            $coachOrm = CoachOrm::loadByTeamId($team->id);
            $coach = new static($coachOrm, $team);
            return true;
        } catch (NoResultsException $e) {
            return false;
        }
    }

    /**
     * @param Family      $family
     *
     * @return Coach
     */
    public static function lookupByFamily($family)
    {
        $coaches = [];

        $coachOrms = CoachOrm::loadByFamilyId($family->id);
        foreach ($coachOrms as $coachOrm) {
            $coaches[] = new static($coachOrm, null, $family);
        }

        return $coaches;
    }

    /**
     * @param $propertyName
     * @return int|string
     */
    public function __get($propertyName)
    {
        switch ($propertyName) {
            case "id":
            case "name":
            case "email":
            case "phone1":
            case "phone2":
                return $this->coachOrm->{$propertyName};

            case "team":
            case "family":
                return $this->{$propertyName};

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
        }
    }

    /**
     *  Delete the coach
     *  TODO: Change to cascading delete
     */
    public function delete()
    {
        $this->coachOrm->delete();
    }
}