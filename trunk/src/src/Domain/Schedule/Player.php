<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Orm\Schedule\PlayerOrm;
use DAG\Framework\Exception\Precondition;


/**
 * @property int        $id
 * @property Team       $team
 * @property Family     $family
 * @property string     $name
 * @property string     $email
 * @property string     $phone
 */
class Player extends Domain
{
    /** @var PlayerOrm */
    private $playerOrm;

    /** @var Team */
    private $team;

    /** @var Family */
    private $family;

    /**
     * @param PlayerOrm $playerOrm
     * @param Team      $team (defaults to null)
     * @param Family    $family (defaults to null)
     */
    protected function __construct(PlayerOrm $playerOrm, $team = null, $family = null)
    {
        $this->playerOrm    = $playerOrm;
        $this->team         = isset($team) ? $team : Team::lookupById($playerOrm->teamId);
        $this->family       = (!isset($family) and isset($playerOrm->familyId)) ? Family::lookupById($playerOrm->familyId) : $family;
    }

    /**
     * @param Team      $team
     * @param Family    $family
     * @param string    $name
     * @param string    $email
     * @param string    $phone
     *
     * @return Player
     */
    public static function create(
        $team,
        $family,
        $name,
        $email,
        $phone)
    {
        $familyId = isset($family) ? $family->id : null;
        $playerOrm = PlayerOrm::create($team->id, $familyId, $name, $email, $phone);
        return new static($playerOrm, $team, $family);
    }

    /**
     * @param int $playerId
     *
     * @return Player
     */
    public static function lookupById($playerId)
    {
        $playerOrm = PlayerOrm::loadById($playerId);
        return new static($playerOrm);
    }

    /**
     * @param Team      $team
     *
     * @return Player
     */
    public static function lookupByTeam($team)
    {
        $players = [];

        $playerOrms = PlayerOrm::loadByTeamId($team->id);
        foreach ($playerOrms as $playerOrm) {
            $players[] = new static($playerOrm, $team);
        }

        return $players;
    }

    /**
     * @param Family      $family
     *
     * @return Player
     */
    public static function lookupByFamily($family)
    {
        $players = [];

        $playerOrms = PlayerOrm::loadByFamilyId($family->id);
        foreach ($playerOrms as $playerOrm) {
            $players[] = new static($playerOrm, null, $family);
        }

        return $players;
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
            case "phone":
                return $this->playerOrm->{$propertyName};

            case "team":
            case "family":
                return $this->{$propertyName};

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
        }
    }

    /**
     *  Delete the player
     *  TODO: Change to cascading delete
     */
    public function delete()
    {
        $this->playerOrm->delete();
    }
}