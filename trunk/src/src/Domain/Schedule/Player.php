<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Framework\Orm\DuplicateEntryException;
use DAG\Orm\Schedule\PlayerOrm;
use DAG\Framework\Exception\Precondition;
use DAG\Services\MySql\DuplicateKeyException;


/**
 * @property int        $id
 * @property Team       $team
 * @property Family     $family
 * @property string     $name
 * @property string     $firstName
 * @property string     $email
 * @property string     $phone
 * @property mixed      $number
 * @property int        $goals
 * @property int        $quartersSub
 * @property int        $quartersKeep
 * @property int        $quartersInjured
 * @property int        $quartersAbsent
 * @property int        $yellowCards
 * @property int        $redCards
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
     * @param bool      $ignore defaults to false, if true then duplicate entry ignored and existing player returned
     *
     * @return Player
     *
     * @throws DuplicateEntryException
     * @throws \Exception
     */
    public static function create(
        $team,
        $family,
        $name,
        $email,
        $phone,
        $ignore = false)
    {
        try {
            $familyId = isset($family) ? $family->id : null;
            $playerOrm = PlayerOrm::create($team->id, $familyId, $name, $email, $phone);
            return new static($playerOrm, $team, $family);
        } catch (DuplicateEntryException $e) {
            if ($ignore) {
                return static::lookupByName($team, $name);
            } else {
                throw $e;
            }
        }

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
     * @param string    $name
     *
     * @return Player
     */
    public static function lookupByName($team, $name)
    {
        $playerOrm = PlayerOrm::loadByName($team->id, $name);
        return new static($playerOrm);
    }

    /**
     * @param Team      $team
     * @param string    $orderBy (PlayerOrm::ORDER_BY_NAME or PlayerOrm::ORDER_BY_NUMBER)
     *
     * @return Player[]
     */
    public static function lookupByTeam($team, $orderBy = PlayerOrm::ORDER_BY_NAME)
    {
        $players = [];

        $playerOrms = PlayerOrm::loadByTeamId($team->id, $orderBy);
        foreach ($playerOrms as $playerOrm) {
            $players[] = new static($playerOrm, $team);
        }

        return $players;
    }

    /**
     * @param Family      $family
     *
     * @return Player[]
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
     * Set the player's name.  Names must be unique for all players on a team.  Append
     * (<number>) is appended to no-unique names where <number> is incremented for each
     * non-unique name found.
     *
     * @param string    $name
     * @param int       $attempt Number of attempts to set a unique name
     */
    public function setName($name, $attempt = null)
    {
        $updatedName = $name;
        if (isset($attempt)) {
            $updatedName .= " ($attempt)";
            $attempt += 1;
        } else {
            $attempt = 2;
        }

        try {
            $this->name = $updatedName;
        } catch (DuplicateKeyException $e) {
            $this->setName($name, $attempt);
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
            case "name":
            case "email":
            case "phone":
            case "goals":
            case "quartersSub":
            case "quartersKeep":
            case "quartersInjured":
            case "quartersAbsent":
            case "yellowCards":
            case "redCards":
                return $this->playerOrm->{$propertyName};

            case "number":
                return isset($this->playerOrm->number) ? $this->playerOrm->number : '';

            case "team":
            case "family":
                return $this->{$propertyName};

            case "firstName":
                $words = explode(",", $this->name);
                if (count($words) > 1) {
                    return $words[1];
                }
                return $this->name;

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
        }
    }

    /**
     * @param $propertyName
     * @param $value
     */
    public function __set($propertyName, $value)
    {
        switch ($propertyName) {
            case "team":
                $this->playerOrm->teamId    = $value->id;
                $this->playerOrm->save();
                $this->team                 = $value;
                break;

            case "name":
            case "goals":
            case "quartersSub":
            case "quartersKeep":
            case "quartersInjured":
            case "quartersAbsent":
            case "yellowCards":
            case "redCards":
                $this->playerOrm->{$propertyName} = $value;
                $this->playerOrm->save();
                break;

            case "number":
                Precondition::isTrue(is_numeric($value), "Player numbers must be digits");
                $this->playerOrm->number = $value;
                $this->playerOrm->save();
                break;

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