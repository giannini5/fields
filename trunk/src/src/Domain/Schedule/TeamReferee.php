<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Framework\Orm\DuplicateEntryException;
use DAG\Framework\Orm\NoResultsException;
use DAG\Orm\Schedule\TeamRefereeOrm;
use DAG\Framework\Exception\Precondition;


/**
 * @property int        $id
 * @property Team       $team
 * @property Referee    $referee
 */
class TeamReferee extends Domain
{
    /** @var TeamRefereeOrm */
    private $teamRefereeOrm;

    /** @var Team */
    private $team;

    /** @var Referee */
    private $referee;

    /**
     * @param TeamRefereeOrm    $teamRefereeOrm
     * @param Team              $team (defaults to null)
     * @param Referee           $referee (defaults to null)
     */
    protected function __construct(TeamRefereeOrm $teamRefereeOrm, $team = null, $referee = null)
    {
        $this->teamRefereeOrm = $teamRefereeOrm;
        $this->team = isset($team) ? $team : Team::lookupById($teamRefereeOrm->teamId);
        $this->referee    = isset($referee) ? $referee : Referee::lookupById($teamRefereeOrm->refereeId);
    }

    /**
     * @param Team      $team
     * @param Referee   $referee
     * @param bool      $ignoreIfAlreadyExists
     *
     * @return TeamReferee
     * @throws DuplicateEntryException
     * @throws \Exception
     */
    public static function create(
        $team,
        $referee,
        $ignoreIfAlreadyExists = false)
    {
        try {
            $teamRefereeOrm = TeamRefereeOrm::create($team->id, $referee->id);
            return new static($teamRefereeOrm, $team, $referee);
        } catch (DuplicateEntryException $e) {
            if ($ignoreIfAlreadyExists) {
                $teamRefereeOrm = TeamRefereeOrm::loadByTeamIdAndReferee($team->id, $referee->id);
                return new static($teamRefereeOrm, $team, $referee);
            } else {
                throw $e;
            }
        }
    }

    /**
     * @param int $teamRefereeId
     *
     * @return TeamReferee
     */
    public static function lookupById($teamRefereeId)
    {
        $teamRefereeOrm = TeamRefereeOrm::loadById($teamRefereeId);
        return new static($teamRefereeOrm);
    }

    /**
     * @param Team      $team
     * @param Referee   $referee
     *
     * @return TeamReferee
     */
    public static function lookupByTeamAndReferee($team, $referee)
    {
        $teamRefereeOrm = TeamRefereeOrm::loadByTeamIdAndReferee($team->id, $referee->id);
        return new static($teamRefereeOrm, $team, $referee);
    }

    /**
     * @param Team          $team
     * @param Referee       $referee
     * @param TeamReferee   $teamReferee
     *
     * @return bool
     */
    public static function findByTeamAndReferee($team, $referee, &$teamReferee)
    {
        try {
            $teamRefereeOrm = TeamRefereeOrm::loadByTeamIdAndReferee($team->id, $referee->id);
            $teamReferee = new static($teamRefereeOrm, $team, $referee);
            return true;
        } catch (NoResultsException $e) {
            return false;
        }
    }

    /**
     * @param Team $team
     *
     * @return TeamReferee[]
     */
    public static function lookupByTeam($team)
    {
        $teamReferees = [];

        $teamRefereeOrms = TeamRefereeOrm::loadByTeamId($team->id);
        foreach ($teamRefereeOrms as $teamRefereeOrm){
            $teamReferees[] = new static($teamRefereeOrm, $team);
        }

        return $teamReferees;
    }

    /**
     * @param Referee $referee
     *
     * @return TeamReferee[]
     */
    public static function lookupByReferee($referee)
    {
        $teamReferees = [];

        $teamRefereeOrms = TeamRefereeOrm::loadByRefereeId($referee->id);
        foreach ($teamRefereeOrms as $teamRefereeOrm){
            $teamReferees[] = new static($teamRefereeOrm, null, $referee);
        }

        return $teamReferees;
    }

    /**
     * @param $propertyName
     * @return int|string
     */
    public function __get($propertyName)
    {
        switch ($propertyName) {
            case "id":
                return $this->teamRefereeOrm->id;

            case "team":
            case "referee":
                return $this->{$propertyName};

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
                return false;
        }
    }

    /**
     *  Delete the teamReferee
     */
    public function delete()
    {
        $this->teamRefereeOrm->delete();
    }
}