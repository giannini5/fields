<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Framework\Orm\DuplicateEntryException;
use DAG\Framework\Orm\NoResultsException;
use DAG\Orm\Schedule\RefereeCrewOrm;
use DAG\Framework\Exception\Precondition;


/**
 * @property int        $id
 * @property Referee    $centerReferee
 * @property Referee    $assistantReferee1
 * @property Referee    $assistantReferee2
 * @property Division   $division
 * @property Team       $team
 * @property string     $name
 */
class RefereeCrew extends Domain
{
    /** @var RefereeCrewOrm */
    private $refereeCrewOrm;

    /** @var Referee */
    private $centerReferee;

    /** @var Referee */
    private $assistantReferee1;

    /** @var Referee */
    private $assistantReferee2;

    /** @var Division */
    private $division;

    /** @var Team */
    private $team;

    /**
     * @param RefereeCrewOrm   $refereeCrewOrm
     * @param Division          $division (defaults to null)
     * @param Team              $team (defaults to null)
     */
    protected function __construct(RefereeCrewOrm $refereeCrewOrm, $division = null, $team = null)
    {
        $this->refereeCrewOrm      = $refereeCrewOrm;
        $this->division             = isset($division) ? $division : Division::lookupById($refereeCrewOrm->divisionId);
        $this->centerReferee        = Referee::lookupById($refereeCrewOrm->centerRefereeId);
        $this->assistantReferee1    = Referee::lookupById($refereeCrewOrm->assistantReferee1Id);
        $this->assistantReferee2    = Referee::lookupById($refereeCrewOrm->assistantReferee2Id);
        $this->team                 = $refereeCrewOrm->teamId == 0 ? null
            : (isset($team) ? $team : Team::lookupById($refereeCrewOrm->teamId));
    }

    /**
     * @param Referee   $centerReferee
     * @param Referee   $assistantReferee1
     * @param Referee   $assistantReferee2
     * @param Division  $division
     * @param Team      $team
     *
     * @return RefereeCrew
     * @throws DuplicateEntryException
     * @throws \Exception
     */
    public static function create(
        $centerReferee,
        $assistantReferee1,
        $assistantReferee2,
        $division,
        $team)
    {
        $refereeCrewOrm = RefereeCrewOrm::create($centerReferee->id, $assistantReferee1->id,
            $assistantReferee2->id, $division->id, isset($team) ? $team->id : 0);
        return new static($refereeCrewOrm, $division, $team);
    }

    /**
     * @param int $refereeCrewId
     *
     * @return RefereeCrew
     */
    public static function lookupById($refereeCrewId)
    {
        $refereeCrewOrm = RefereeCrewOrm::loadById($refereeCrewId);
        return new static($refereeCrewOrm);
    }

    /**
     * @param Division $division
     *
     * @return RefereeCrew[]
     */
    public static function lookupByDivision($division)
    {
        $refereeCrews = [];

        $refereeCrewOrms = RefereeCrewOrm::loadByDivisionId($division->id);
        foreach ($refereeCrewOrms as $refereeCrewOrm){
            $refereeCrews[] = new static($refereeCrewOrm, $division);
        }

        return $refereeCrews;
    }

    /**
     * @param Team $team
     *
     * @return RefereeCrew[]
     */
    public static function lookupByTeam($team)
    {
        $refereeCrews = [];

        $refereeCrewOrms = RefereeCrewOrm::loadByTeamId($team->id);
        foreach ($refereeCrewOrms as $refereeCrewOrm){
            $refereeCrews[] = new static($refereeCrewOrm, null, $team);
        }

        return $refereeCrews;
    }

    /**
     * @param Referee       $referee1
     * @param Referee       $referee2
     * @param Referee       $referee3
     * @param Division      $division
     * @param Team          $team
     * @param RefereeCrew  $refereeCrew
     *
     * @return bool
     */
    public static function findByRefereesDivisionAndTeam($referee1, $referee2, $referee3, $division, $team, &$refereeCrew)
    {
        if (self::findByAttributes($referee1, $referee2, $referee3, $division, $team, $refereeCrew)) {
            return true;
        }

        if (self::findByAttributes($referee1, $referee3, $referee2, $division, $team, $refereeCrew)) {
            return true;
        }

        if (self::findByAttributes($referee2, $referee1, $referee3, $division, $team, $refereeCrew)) {
            return true;
        }

        if (self::findByAttributes($referee2, $referee3, $referee1, $division, $team, $refereeCrew)) {
            return true;
        }

        if (self::findByAttributes($referee3, $referee1, $referee2, $division, $team, $refereeCrew)) {
            return true;
        }

        if (self::findByAttributes($referee3, $referee2, $referee1, $division, $team, $refereeCrew)) {
            return true;
        }

        return false;
    }

    /**
     * @param Referee       $referee1
     * @param Referee       $referee2
     * @param Referee       $referee3
     * @param Division      $division
     * @param Team          $team
     * @param RefereeCrew  $refereeCrew
     *
     * @return bool
     */
    private static function findByAttributes($referee1, $referee2, $referee3, $division, $team, &$refereeCrew)
    {
        try {
            $refereeCrewOrm = RefereeCrewOrm::loadByDivisionCenterAssistantsTeam(
                $referee1->id, $referee2->id, $referee3->id, $division->id, isset($team) ? $team->id : 0);
            $refereeCrew = new static($refereeCrewOrm, $division, $team);
            return true;
        } catch (NoResultsException $e) {
            return false;
        }
    }

    /**
     * @param $propertyName
     * @return int|string
     */
    public function __get($propertyName)
    {
        switch ($propertyName) {
            case "id":
                return $this->refereeCrewOrm->id;

            case "team":
            case "division":
            case "centerReferee":
            case "assistantReferee1":
            case "assistantReferee2":
                return $this->{$propertyName};

            case "name":
                return isset($this->team) ? $this->team->nameId : "ERROR";

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
                return false;
        }
    }

    /**
     * @param $propertyName
     * @return bool
     */
    public function __isset($propertyName)
    {
        switch ($propertyName) {
            case "id":
                return true;

            case "team":
            case "division":
            case "centerReferee":
            case "assistantReferee1":
            case "assistantReferee2":
                return isset($this->{$propertyName});

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
                return false;
        }
    }

    /**
     * @param Division[] $divisions
     */
    public static function generateRefereeCrews($divisions)
    {
        // Delete existing crews
        foreach ($divisions as $division) {
            $refereeCrews = RefereeCrew::lookupByDivision($division);
            foreach ($refereeCrews as $refereeCrew) {
                $refereeCrew->delete();
            }
        }

        // Get division referees mapped to division and referee
        $divisionRefereesByDivision = [];
        foreach ($divisions as $division) {
            $divisionReferees = DivisionReferee::lookupByDivision($division);
            foreach ($divisionReferees as $divisionReferee) {
                $divisionRefereesByDivision[$division->id][$divisionReferee->referee->id] = $divisionReferee;
            }
        }

        // Generate new crews for all teams in a division
        foreach ($divisions as $division) {
            self::generateRefereeCrewsForDivision($divisions, $divisionRefereesByDivision, $division);
        }
    }

    /**
     * @param Division[]    $divisions
     * @param []            $divisionRefereesByDivision ([divisionId][refereeId] => divisionReferee)
     * @param Division      $division
     */
    public static function generateRefereeCrewsForDivision($divisions, $divisionRefereesByDivision, $division)
    {
        // Get teams for division
        $teams = Team::lookupByDivision($division);

        // Get team referees for division and generate referee crew for team
        foreach ($teams as $team) {
            $teamReferees = TeamReferee::lookupByTeam($team);
            self::generateRefereeCrewsForTeam($team, $divisions, $divisionRefereesByDivision, $teamReferees);
        }
    }

    /**
     * @param Team          $team
     * @param Division[]    $divisions
     * @param []            $divisionRefereesByDivision ([divisionId][refereeId] => divisionReferee)
     * @param TeamReferee[] $teamReferees
     */
    public static function generateRefereeCrewsForTeam($team, $divisions, $divisionRefereesByDivision, $teamReferees)
    {
        // For each division, create a referee crew for team if team has a center and two ARs
        foreach ($divisions as $division) {
            self::generateRefereeCrewsForTeamAndDivision($team, $division, $divisionRefereesByDivision, $teamReferees);
        }
    }

    /**
     * @param Team          $team
     * @param Division      $division
     * @param []            $divisionRefereesByDivision ([divisionId][refereeId] => divisionReferee)
     * @param TeamReferee[] $teamReferees
     * @param int[]         $excludeCenterRefereeIds
     */
    public static function generateRefereeCrewsForTeamAndDivision($team, $division, $divisionRefereesByDivision, $teamReferees, $excludeCenterRefereeIds = [])
    {
        // Return if the number of team referees is less than required for a crew
        if (count($teamReferees) < 3) {
            return;
        }

        $centerReferee      = null;
        $assistantReferee1  = null;
        $assistantReferee2  = null;

        // Find a center
        foreach ($teamReferees as $teamReferee) {
            if (isset($divisionRefereesByDivision[$division->id][$teamReferee->referee->id])) {
                /** @var DivisionReferee $divisionReferee */
                $divisionReferee = $divisionRefereesByDivision[$division->id][$teamReferee->referee->id];
                if ($divisionReferee->isCenter and !in_array($divisionReferee->referee->id, $excludeCenterRefereeIds)) {
                    $centerReferee = $divisionReferee->referee;
                }
            }
        }
        if (!isset($centerReferee)) {
            return;
        }

        // Find assistant 1
        foreach ($teamReferees as $teamReferee) {
            if (isset($divisionRefereesByDivision[$division->id][$teamReferee->referee->id])
                and $teamReferee->referee->id != $centerReferee->id) {
                /** @var DivisionReferee $divisionReferee */
                $divisionReferee = $divisionRefereesByDivision[$division->id][$teamReferee->referee->id];
                if ($divisionReferee->isAssistant) {
                    $assistantReferee1 = $divisionReferee->referee;
                }
            }
        }
        if (!isset($assistantReferee1)) {
            return;
        }

        // Find assistant 2
        foreach ($teamReferees as $teamReferee) {
            if (isset($divisionRefereesByDivision[$division->id][$teamReferee->referee->id])
                and $teamReferee->referee->id != $centerReferee->id
                and $teamReferee->referee->id != $assistantReferee1->id) {
                /** @var DivisionReferee $divisionReferee */
                $divisionReferee = $divisionRefereesByDivision[$division->id][$teamReferee->referee->id];
                if ($divisionReferee->isAssistant) {
                    $assistantReferee2 = $divisionReferee->referee;
                }
            }
        }
        if (!isset($assistantReferee2)) {
            // Try again with a different center referee before giving up
            $excludeCenterRefereeIds[] = $centerReferee->id;
            self::generateRefereeCrewsForTeamAndDivision($team, $division, $divisionRefereesByDivision, $teamReferees, $excludeCenterRefereeIds);
            return;
        }

        // Generate crew if it does not already exist
        // TODO: might need to check for existence first - should be okay now since all crews are first deleted
        RefereeCrew::create($centerReferee, $assistantReferee1, $assistantReferee2, $division, $team);
    }

    /**
     *  Delete the refereeCrew
     */
    public function delete()
    {
        $this->refereeCrewOrm->delete();
    }
}