<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Framework\Orm\DuplicateEntryException;
use DAG\Framework\Orm\NoResultsException;
use DAG\Orm\Schedule\GameRefereeOrm;
use DAG\Framework\Exception\Precondition;


/**
 * @property int        $id
 * @property Game       $game
 * @property Referee    $referee
 * @property string     $role
 */
class GameReferee extends Domain
{
    const TITLE_ROW     = 'title';
    const CENTER_ROW    = 'C';
    const AR1_ROW       = 'AR1';
    const AR2_ROW       = 'AR2';
    const MENTOR_ROW    = 'M';
    const LIST_ROW      = 'L';

    const CENTER_ROLE      = GameRefereeOrm::CENTER_ROLE;
    const ASSISTANT_1_ROLE = GameRefereeOrm::ASSISTANT_ROLE_1;
    const ASSISTANT_2_ROLE = GameRefereeOrm::ASSISTANT_ROLE_2;
    const STANDBY_ROLE     = GameRefereeOrm::STANDBY_ROLE;
    const MENTOR_ROLE      = GameRefereeOrm::MENTOR_ROLE;

    /** @var GameRefereeOrm */
    private $gameRefereeOrm;

    /** @var Game */
    private $game;

    /** @var Referee */
    private $referee;

    /**
     * @param GameRefereeOrm    $gameRefereeOrm
     * @param Game              $game (defaults to null)
     * @param Referee           $referee (defaults to null)
     */
    protected function __construct(GameRefereeOrm $gameRefereeOrm, $game = null, $referee = null)
    {
        $this->gameRefereeOrm = $gameRefereeOrm;
        $this->game           = isset($game) ? $game : Game::lookupById($gameRefereeOrm->gameId);
        $this->referee        = isset($referee) ? $referee : Referee::lookupById($gameRefereeOrm->refereeId);
    }

    /**
     * @param Game      $game
     * @param Referee   $referee
     * @param string    $role - See GameRefereeORM for roles
     * @param bool      $ignoreIfAlreadyExists
     *
     * @return GameReferee
     * @throws DuplicateEntryException
     * @throws \Exception
     */
    public static function create(
        $game,
        $referee,
        $role,
        $ignoreIfAlreadyExists = false)
    {
        try {
            $gameRefereeOrm = GameRefereeOrm::create($game->id, $referee->id, $role);
            return new static($gameRefereeOrm, $game, $referee);
        } catch (DuplicateEntryException $e) {
            if ($ignoreIfAlreadyExists) {
                $gameRefereeOrm = GameRefereeOrm::loadByGameIdAndReferee($game->id, $referee->id);
                return new static($gameRefereeOrm, $game, $referee);
            } else {
                throw $e;
            }
        }
    }

    /**
     * @param int $gameRefereeId
     *
     * @return GameReferee
     */
    public static function lookupById($gameRefereeId)
    {
        $gameRefereeOrm = GameRefereeOrm::loadById($gameRefereeId);
        return new static($gameRefereeOrm);
    }

    /**
     * @param Game      $game
     * @param Referee   $referee
     *
     * @return GameReferee
     */
    public static function lookupByGameAndReferee($game, $referee)
    {
        $gameRefereeOrm = GameRefereeOrm::loadByGameIdAndReferee($game->id, $referee->id);
        return new static($gameRefereeOrm, $game, $referee);
    }

    /**
     * @param Game          $game
     * @param Referee       $referee
     * @param GameReferee   $gameReferee
     *
     * @return bool
     */
    public static function findByGameAndReferee($game, $referee, &$gameReferee)
    {
        try {
            $gameRefereeOrm = GameRefereeOrm::loadByGameIdAndReferee($game->id, $referee->id);
            $gameReferee    = new static($gameRefereeOrm, $game, $referee);
            return true;
        } catch (NoResultsException $e) {
            return false;
        }
    }

    /**
     * @param Game $game
     *
     * @return GameReferee[]
     */
    public static function lookupByGame($game)
    {
        $gameReferees = [];

        $gameRefereeOrms = GameRefereeOrm::loadByGameId($game->id);
        foreach ($gameRefereeOrms as $gameRefereeOrm){
            $gameReferees[] = new static($gameRefereeOrm, $game);
        }

        return $gameReferees;
    }

    /**
     * @param Referee $referee
     *
     * @return GameReferee[]
     */
    public static function lookupByReferee($referee)
    {
        $gameReferees = [];

        $gameRefereeOrms = GameRefereeOrm::loadByRefereeId($referee->id);
        foreach ($gameRefereeOrms as $gameRefereeOrm){
            $gameReferees[] = new static($gameRefereeOrm, null, $referee);
        }

        return $gameReferees;
    }

    /**
     * @param string    $rowType
     * @return string   Role - see const values in this class
     * @throws \Exception
     */
    public static function getRoleFromRowType($rowType)
    {
        switch ($rowType) {
            case self::CENTER_ROW:
                return self::CENTER_ROLE;
            case self::AR1_ROW:
                return self::ASSISTANT_1_ROLE;
            case self::AR2_ROW:
                return self::ASSISTANT_2_ROLE;
            case self::MENTOR_ROW:
                return self::MENTOR_ROLE;
            default:
                throw new \Exception("RowType: $rowType cannot be converted to a referee role");
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
            case "role":
                return $this->gameRefereeOrm->{$propertyName};

            case "game":
            case "referee":
                return $this->{$propertyName};

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
                return false;
        }
    }

    /**
     * @param string $propertyName
     * @param string $value
     */
    public function __set($propertyName, $value)
    {
        switch ($propertyName) {
            case "role":
                $this->gameRefereeOrm->{$propertyName} = $value;
                $this->gameRefereeOrm->save();
                break;

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
        }
    }

    /**
     * @param Game          $game
     * @param GameReferee[] $gameReferees
     * @return bool         True if already assigned game around time of game; false otherwise
     */
    static public function isAlreadyAssignedGame($game, $gameReferees)
    {
        $games = [];
        foreach ($gameReferees as $gameReferee) {
            $games[] = $gameReferee->game;
        }

        return $game->anyOverlap($games, $overlappingGame, 0, 0, true, true);
    }

    /**
     *  Delete the gameReferee
     */
    public function delete()
    {
        $this->gameRefereeOrm->delete();
    }
}