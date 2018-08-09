<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Framework\Exception\Precondition;
use DAG\Orm\Schedule\PlayerGameStatsOrm;


/**
 * @property Game   $game
 * @property Team   $team
 * @property Player $player
 * @property int    $goals
 * @property bool   $substitutionQuarter1
 * @property bool   $substitutionQuarter2
 * @property bool   $substitutionQuarter3
 * @property bool   $substitutionQuarter4
 * @property bool   $keeperQuarter1
 * @property bool   $keeperQuarter2
 * @property bool   $keeperQuarter3
 * @property bool   $keeperQuarter4
 * @property bool   $injuredQuarter1
 * @property bool   $injuredQuarter2
 * @property bool   $injuredQuarter3
 * @property bool   $injuredQuarter4
 * @property bool   $absentQuarter1
 * @property bool   $absentQuarter2
 * @property bool   $absentQuarter3
 * @property bool   $absentQuarter4
 * @property int    $yellowCards
 * @property bool   $redCard
 */
class PlayerGameStats extends Domain
{
    /** @var PlayerGameStatsOrm */
    private $playerGameStatsOrm;

    /** @var Game */
    private $game;

    /** @var Team */
    private $team;

    /** @var Player */
    private $player;

    /**
     * @param PlayerGameStatsOrm    $playerGameStatsOrm
     * @param Game                  $game (defaults to null)
     * @param Team                  $team (defaults to null)
     * @param Player                $player (defaults to null)
     */
    protected function __construct(
        PlayerGameStatsOrm $playerGameStatsOrm,
        $game = null,
        $team = null,
        $player = null)
    {
        $this->playerGameStatsOrm   = $playerGameStatsOrm;
        $this->game                 = isset($game) ? $game : Game::lookupById($playerGameStatsOrm->gameId);
        $this->team                 = isset($team) ? $team : Team::lookupById($playerGameStatsOrm->teamId);
        $this->player               = isset($player) ? $player : Player::lookupById($playerGameStatsOrm->playerId);
    }

    /**
     * @param Game      $game
     * @param Team      $team
     * @param Player    $player
     *
     * @return PlayerGameStats
     */
    public static function findOrCreate(
        $game,
        $team,
        $player
    ) {
        $playerGameStatsOrm = PlayerGameStatsOrm::findOrCreate($game->id, $team->id, $player->id);
        $playerGameStats    = new static($playerGameStatsOrm, $game, $team, $player);

        return $playerGameStats;
    }

    /**
     * @param Game      $game
     *
     * @return PlayerGameStats[]
     */
    public static function lookup($game)
    {
        $playerGameStats = [];

        $playerGameStatsOrms = PlayerGameStatsOrm::loadByGameId($game->id);
        foreach ($playerGameStatsOrms as $playerGameStatsOrm) {
            $playerGameStats[] = new static($playerGameStatsOrm);
        }

        return $playerGameStats;
    }

    /**
     * @param $propertyName
     * @return mixed
     */
    public function __get($propertyName)
    {
        switch ($propertyName) {
            case "goals":
                return $this->playerGameStatsOrm->{$propertyName};

            case "substitutionQuarter1":
            case "substitutionQuarter2":
            case "substitutionQuarter3":
            case "substitutionQuarter4":
            case "keeperQuarter1":
            case "keeperQuarter2":
            case "keeperQuarter3":
            case "keeperQuarter4":
            case "injuredQuarter1":
            case "injuredQuarter2":
            case "injuredQuarter3":
            case "injuredQuarter4":
            case "absentQuarter1":
            case "absentQuarter2":
            case "absentQuarter3":
            case "absentQuarter4":
            case "redCard":
                return $this->playerGameStatsOrm->{$propertyName} == 1;

            case "yellowCards":
                return $this->playerGameStatsOrm->{$propertyName};

            case "game":
            case "team":
            case "player":
                return $this->{$propertyName};

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
            case "goals":
                $this->playerGameStatsOrm->{$propertyName} = $value;
                $this->playerGameStatsOrm->save();
                break;

            case "substitutionQuarter1":
            case "substitutionQuarter2":
            case "substitutionQuarter3":
            case "substitutionQuarter4":
            case "keeperQuarter1":
            case "keeperQuarter2":
            case "keeperQuarter3":
            case "keeperQuarter4":
            case "injuredQuarter1":
            case "injuredQuarter2":
            case "injuredQuarter3":
            case "injuredQuarter4":
            case "absentQuarter1":
            case "absentQuarter2":
            case "absentQuarter3":
            case "absentQuarter4":
            case "redCard":
                $this->playerGameStatsOrm->{$propertyName} = $value ? 1 : 0;
                $this->playerGameStatsOrm->save();
                break;

            case "yellowCards":
                $this->playerGameStatsOrm->{$propertyName} = $value;
                $this->playerGameStatsOrm->save();
                break;

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
        }
    }

    /**
     *  Delete the game
     */
    public function delete()
    {
        $this->playerGameStatsOrm->delete();
    }
}