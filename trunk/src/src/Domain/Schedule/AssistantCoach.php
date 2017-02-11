<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Framework\Orm\DuplicateEntryException;
use DAG\Orm\Schedule\AssistantCoachOrm;
use DAG\Framework\Exception\Precondition;


/**
 * @property int        $id
 * @property Team       $team
 * @property Family     $family
 * @property string     $name
 * @property string     $lastName
 * @property string     $email
 * @property string     $phone1
 * @property string     $phone2
 */
class AssistantCoach extends Domain
{
    /** @var AssistantCoachOrm */
    private $assistantCoachOrm;

    /** @var Team */
    private $team;

    /** @var Family */
    private $family;

    /**
     * @param AssistantCoachOrm $assistantCoachOrm
     * @param Team              $team (defaults to null)
     * @param Family            $family (defaults to null)
     */
    protected function __construct(AssistantCoachOrm $assistantCoachOrm, $team = null, $family = null)
    {
        $this->assistantCoachOrm    = $assistantCoachOrm;
        $this->team                 = isset($team) ? $team : Team::lookupById($assistantCoachOrm->teamId);
        $this->family               = (!isset($family) and isset($assistantCoachOrm->familyId)) ? Family::lookupById($assistantCoachOrm->familyId) : $family;
    }

    /**
     * @param Team      $team
     * @param Family    $family
     * @param string    $name
     * @param string    $email
     * @param string    $phone1
     * @param string    $phone2
     * @param bool      $ignore - true if DuplicateEntryException should be ignored and use lookup instead
     *
     * @return AssistantCoach
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
            $assistantCoachOrm = AssistantCoachOrm::create($team->id, $familyId, $name, $email, $phone1, $phone2);
            return new static($assistantCoachOrm, $team, $family);
        } catch (DuplicateEntryException $e) {
            if ($ignore) {
                return AssistantCoach::lookupByTeamAndName($team, $name);
            } else {
                throw $e;
            }

        }
    }

    /**
     * @param int $assistantCoachId
     *
     * @return AssistantCoach
     */
    public static function lookupById($assistantCoachId)
    {
        $assistantCoachOrm = AssistantCoachOrm::loadById($assistantCoachId);
        return new static($assistantCoachOrm);
    }

    /**
     * @param Team      $team
     * @param string    $name
     *
     * @return AssistantCoach
     */
    public static function lookupByTeamAndName($team, $name)
    {
        $assistantCoachOrm = AssistantCoachOrm::loadByTeamIdAndName($team->id, $name);
        return new static($assistantCoachOrm, $team);
    }

    /**
     * @param Team      $team
     *
     * @return array AssistantCoaches
     */
    public static function lookupByTeam($team)
    {
        $assistantCoaches = [];

        $assistantCoachOrms = AssistantCoachOrm::loadByTeamId($team->id);
        foreach ($assistantCoachOrms as $assistantCoachOrm) {
            $assistantCoaches[] = new static($assistantCoachOrm, $team);
        }

        return $assistantCoaches;
    }

    /**
     * @param Family      $family
     *
     * @return AssistantCoach[]
     */
    public static function lookupByFamily($family)
    {
        $assistantCoaches = [];

        $assistantCoachOrms = AssistantCoachOrm::loadByFamilyId($family->id);
        foreach ($assistantCoachOrms as $assistantCoachOrm) {
            $assistantCoaches[] = new static($assistantCoachOrm, null, $family);
        }

        return $assistantCoaches;
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
                return $this->assistantCoachOrm->{$propertyName};

            case "lastName":
                $nameParts = explode(" ", $this->assistantCoachOrm->name);
                switch (count($nameParts)) {
                    case 0:
                    case 1:
                        return $this->assistantCoachOrm->name;
                    default:
                        array_shift($nameParts);
                        return implode(" ", $nameParts);
                }
                break;

            case "shortName":
                $nameParts = explode(" ", $this->assistantCoachOrm->name);
                switch (count($nameParts)) {
                    case 0:
                    case 1:
                        return $this->assistantCoachOrm->name;
                    default:
                        $firstName  = array_shift($nameParts);
                        $lastName   = implode(" ", $nameParts);
                        $lastName   .= ", " . $firstName[0];
                        return $lastName;
                }
                break;

            case "team":
            case "family":
                return $this->{$propertyName};

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
        }
    }

    /**
     *  Delete the assistantCoach
     *  TODO: Change to cascading delete
     */
    public function delete()
    {
        $this->assistantCoachOrm->delete();
    }
}