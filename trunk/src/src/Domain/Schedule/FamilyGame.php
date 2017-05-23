<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Framework\Orm\DuplicateEntryException;
use DAG\Orm\Schedule\FamilyGameOrm;
use DAG\Framework\Exception\Precondition;


/**
 * @property int    $id
 * @property Family $family
 * @property Game   $game
 */
class FamilyGame extends Domain
{
    // Reasons two games are not swappable
    const SAME_GAMES                = 'sameGames';
    const PUBLISHED_SCHEDULE        = 'publishedSchedule';
    const TEAM_MATCH                = 'teamMatch';
    const GAMES_OVERLAP             = 'gamesOverlap';
    const HOME_COACH_IN_FAMILY      = 'homeCoachInFamily';
    const VISITING_COACH_IN_FAMILY  = 'visitingCoachInFamily';
    const GAMES_SWAPPABLE           = 'gamesSwappable';

    /** @var FamilyGameOrm */
    private $familyGameOrm;

    /** @var Family */
    private $family;

    /** @var Game */
    private $game;

    /**
     * @param FamilyGameOrm $familyGameOrm
     * @param Family        $family (defaults to null)
     * @param Game          $game (defaults to null)
     */
    protected function __construct(FamilyGameOrm $familyGameOrm, $family = null, $game = null)
    {
        $this->familyGameOrm    = $familyGameOrm;
        $this->family           = isset($family) ? $family : Family::lookupById($familyGameOrm->familyId);
        $this->game             = isset($game) ? $game : Game::lookupById($familyGameOrm->gameId);
    }

    /**
     * @param Family    $family (defaults to null)
     * @param Game      $game (defaults to null)
     * @param bool      $ignoreIfAlreadyExists
     *
     * @return FamilyGame
     * @throws DuplicateEntryException
     * @throws \Exception
     */
    public static function create(
        $family,
        $game,
        $ignoreIfAlreadyExists = false)
    {
        try {
            $familyGameOrm = FamilyGameOrm::create($family->id, $game->id);
            return new static($familyGameOrm, $family, $game);
        } catch (DuplicateEntryException $e) {
            if ($ignoreIfAlreadyExists) {
                $familyGameOrm = FamilyGameOrm::loadByFamilyIdAndGameId($family->id, $game->id);
                return new static($familyGameOrm, $family, $game);
            } else {
                throw $e;
            }
        }
    }

    /**
     * @param int $familyGameId
     *
     * @return FamilyGame
     */
    public static function lookupById($familyGameId)
    {
        $familyGameOrm = FamilyGameOrm::loadById($familyGameId);
        return new static($familyGameOrm);
    }

    /**
     * @param Family    $family
     * @param Game      $game
     *
     * @return FamilyGame
     */
    public static function lookupByFamilyAndGame($family, $game)
    {
        $familyGameOrm = FamilyGameOrm::loadByFamilyIdAndGameId($family->id, $game->id);
        return new static($familyGameOrm, $family, $game);
    }

    /**
     * @param Family $family
     *
     * @return FamilyGame[]
     */
    public static function lookupByFamily($family)
    {
        $familyGames = [];

        $familyGameOrms = FamilyGameOrm::loadByFamilyId($family->id);
        foreach ($familyGameOrms as $familyGameOrm){
            $familyGames[] = new static($familyGameOrm, $family);
        }

        return $familyGames;
    }

    /**
     * @param Game $game
     *
     * @return FamilyGame[]
     */
    public static function lookupByGame($game)
    {
        $familyGames = [];

        $familyGameOrms = FamilyGameOrm::loadByGameId($game->id);
        foreach ($familyGameOrms as $familyGameOrm){
            $familyGames[] = new static($familyGameOrm, null, $game);
        }

        return $familyGames;
    }

    /**
     * Attempt to fix overlapping games by moving game to a different time on the same day, swapping
     * with another game where the team coaches are not part of a family.
     *
     * @param $family
     *
     * @return bool true if overlaps resolved; false otherwise
     */
    public static function fixOverlaps($family)
    {
        $gamesByDay = self::getGamesByDayForFamily($family);

        // Attempt to fix games that overlap for the family
        $gameOverlaps   = 0;
        $gamesFixed     = 0;
        foreach ($gamesByDay as $day => $games) {

            // Fix just one game per day.  Continuing to iterate over games after
            // fixing one game appears to have problems (PHP bug?).
            foreach ($games as $game) {
                $overlappingGame = null;
                if ($game->anyOverlap($games, $overlappingGame)) {
                    $gameOverlaps   += 1;
                    if (self::findOverlappingGameFix($family, $day, $game, $overlappingGame)) {
                        $gamesFixed += 1;
                        break;
                    }
                }
            }
        }

        // var_dump($gameOverlaps);
        // var_dump($gamesFixed);
        return $gameOverlaps == $gamesFixed;
    }

    /**
     * @param Family    $family
     * @param string    $day    - if null get games across all days; otherwise just get games for specified day
     *
     * @return array    day => Game
     */
    public static function getGamesByDayForFamily($family, $day = null)
    {
        // Get the family games
        $familyGames = FamilyGame::lookupByFamily($family);

        // Sort by time
        $gamesByTime = [];
        foreach ($familyGames as $familyGame) {
            $gamesByTime[$familyGame->game->gameTime->startTime][] = $familyGame->game;
        }
        ksort($gamesByTime);

        // Sort by day
        $gamesByDay = [];
        foreach ($gamesByTime as $games) {
            foreach ($games as $game) {
                if (!isset($day) or $day == $game->gameTime->gameDate->day) {
                    $gamesByDay[$game->gameTime->gameDate->day][] = $game;
                }
            }
        }
        ksort($gamesByDay);

        return $gamesByDay;
    }

    /**
     * Return the Game that family is playing where coach is not coaching more than one
     * team.  i.e. coach is not in a family.  $game is returned if $opverlappingGame is
     * locked.
     *
     * @param Game  $game
     * @param Game  $overlappingGame
     *
     * @return Game Game to fix
     */
    public static function selectGameToFix($game, $overlappingGame)
    {
        if ($overlappingGame->isLocked()) {
            return $game;
        }

        $coach = Coach::lookupByTeam($game->homeTeam);
        if (!isset($coach->family)) {
            return $game;
        }

        $coach = Coach::lookupByTeam($game->visitingTeam);
        if (!isset($coach->family)) {
            return $game;
        }

        $coach = Coach::lookupByTeam($overlappingGame->homeTeam);
        if (!isset($coach->family)) {
            return $overlappingGame;
        }

        $coach = Coach::lookupByTeam($overlappingGame->visitingTeam);
        if (!isset($coach->family)) {
            return $overlappingGame;
        }

        // Both games are coached by coaches that coach multiple team.  Return the original game
        return $game;
    }

    /**
     * Attempt to fix overlapping games by finding another game to swap with one of the two games.
     *
     * @param Family            $family
     * @param string            $day
     * @param Game              $game
     * @param Game              $overlappingGame
     *
     * @return bool true if fix found; false otherwise
     */
    public static function findOverlappingGameFix($family, $day, $game, $overlappingGame)
    {
        $skippedGames   = [];

        // Skip if game is in a schedule that has been published or the game has been locked
        if ($game->pool->schedule->published == 1
            or $game->isLocked()) {
            return false;
        }

        // Pick the game to fix
        $gameToFix = self::selectGameToFix($game, $overlappingGame);

        // Get division for game being fixed (moved to different time/field)
        $division = $gameToFix->pool->schedule->division;

        // Get games on given day for division
        $divisionGames = Game::lookupByDivisionDay($division, $day);

        // Search for game to swap
        // Find game that does not overlap where the coaches are not part of families
        foreach ($divisionGames as $divisionGame) {
            $result = self::areGamesSwappable($family, $gameToFix, $divisionGame);
            if ($result != self::GAMES_SWAPPABLE) {
                if ($result == self::HOME_COACH_IN_FAMILY) {
                    $coach = Coach::lookupByTeam($divisionGame->homeTeam);
                    $skippedGames[$coach->family->id] = $divisionGame;
                } else if ($result == self::VISITING_COACH_IN_FAMILY) {
                    $coach = Coach::lookupByTeam($divisionGame->visitingTeam);
                    $skippedGames[$coach->family->id] = $divisionGame;
                }
                continue;
            }

            // Swap game times to fix overlap
            self::swapGameTimes($gameToFix, $divisionGame);
            return true;
        }

        foreach ($skippedGames as $skippedFamilyId => $skippedGame) {
            $skippedFamily      = Family::lookupById($skippedFamilyId);
            $skippedFamilyGames = self::getGamesByDayForFamily($skippedFamily, $day);

            self::swapGameTimes($gameToFix, $skippedGame);

            $overlappingGame = null;
            if (!$skippedGame->anyOverlap($skippedFamilyGames, $overlappingGame)) {
                // Found a fix
                return true;
            }

            // Fix not found, swap back
            self::swapGameTimes($gameToFix, $skippedGame);
        }

        // No fix found
        return false;
    }

    /**
     * @param Family    $family
     * @param Game      $game1
     * @param Game      $game2
     * @return string
     */
    public static function areGamesSwappable($family, $game1, $game2)
    {
        // Skip if games are same
        if ($game2->id == $game1->id) {
            return self::SAME_GAMES;
        }

        // Skip if game is in a schedule that has been published
        if ($game2->pool->schedule->published == 1) {
            return self::PUBLISHED_SCHEDULE;
        }

        // Skip if there is a team match in games
        if ($game1->homeTeam->id     == $game2->homeTeam->id or
            $game1->homeTeam->id     == $game2->visitingTeam->id or
            $game1->visitingTeam->id == $game2->homeTeam->id or
            $game1->visitingTeam->id == $game2->visitingTeam->id) {
            return self::TEAM_MATCH;
        }

        // Skip if there is an overlap
        $overlappingGame = null;
        if ($game1->anyOverlap(array($game2), $overlappingGame)) {
            return self::GAMES_OVERLAP;
        }

        // Skip if (for now) if coach is in a different family
        $coach = Coach::lookupByTeam($game2->homeTeam);
        if (isset($coach->family)) {
            if ($coach->family->id != $family->id) {
                $skippedGames[$coach->family->id] = $game2;
            }
            return self::HOME_COACH_IN_FAMILY;
        }

        $coach = Coach::lookupByTeam($game2->visitingTeam);
        if (isset($coach->family)) {
            if ($coach->family->id != $family->id) {
                $skippedGames[$coach->family->id] = $game2;
            }
            return self::VISITING_COACH_IN_FAMILY;
        }

        return self::GAMES_SWAPPABLE;
    }

    /**
     * @param Game $game1
     * @param Game $game2
     */
    public static function swapGameTimes($game1, $game2)
    {
        Precondition::isTrue($game1->gameTime->gameDate->day == $game2->gameTime->gameDate->day, "Not allowed to swap games on different days");

        $game1GameTime      = $game1->gameTime;
        $game2GameTime      = $game2->gameTime;
        $game1->gameTime    = $game2GameTime;
        $game2->gameTime    = $game1GameTime;

        $game1GameTime->game = null;
        $game2GameTime->game = null;
        $game1GameTime->game = $game2;
        $game2GameTime->game = $game1;

    }

    /**
     * @param $propertyName
     * @return int|string
     */
    public function __get($propertyName)
    {
        switch ($propertyName) {
            case "id":
                return $this->familyGameOrm->id;

            case "family":
            case "game":
                return $this->{$propertyName};

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
        }
    }

    /**
     *  Delete the divisionField
     */
    public function delete()
    {
        $this->familyGameOrm->delete();
    }
}