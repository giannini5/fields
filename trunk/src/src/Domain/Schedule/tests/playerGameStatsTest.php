<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\ORM_TestHelper;
use DAG\Orm\Schedule\PlayerGameStatsOrm;

require_once dirname(dirname(dirname(__DIR__))) . '/Orm/Schedule/tests/helper.php';


/**
 * @testSuite test PlayerGameStats
 */
class PlayerGameStatsTest extends ORM_TestHelper
{
    /** @var  PlayerGameStats */
    private $playerGameStats;

    /** @var array */
    private $expectedPlayerGameStatsOrm = [];

    protected function setUp()
    {
        $this->primeDatabase();

        $this->expectedPlayerGameStatsOrm =
            [
                PlayerGameStatsOrm::FIELD_GAME_ID                   => $this->defaultGameOrm->id,
                PlayerGameStatsOrm::FIELD_TEAM_ID                   => $this->defaultTeamOrm->id,
                PlayerGameStatsOrm::FIELD_PLAYER_ID                 => $this->defaultPlayerOrm->id,
                PlayerGameStatsOrm::FIELD_GOALS                     => 0,
                PlayerGameStatsOrm::FIELD_SUBSTITUTION_QUARTER_1    => 0,
                PlayerGameStatsOrm::FIELD_SUBSTITUTION_QUARTER_2    => 0,
                PlayerGameStatsOrm::FIELD_SUBSTITUTION_QUARTER_3    => 0,
                PlayerGameStatsOrm::FIELD_SUBSTITUTION_QUARTER_4    => 0,
                PlayerGameStatsOrm::FIELD_KEEPER_QUARTER_1          => 0,
                PlayerGameStatsOrm::FIELD_KEEPER_QUARTER_2          => 0,
                PlayerGameStatsOrm::FIELD_KEEPER_QUARTER_3          => 0,
                PlayerGameStatsOrm::FIELD_KEEPER_QUARTER_4          => 0,
                PlayerGameStatsOrm::FIELD_YELLOW_CARDS              => 0,
                PlayerGameStatsOrm::FIELD_RED_CARD                  => 0,
            ];

        $game   = Game::lookupById($this->defaultGameOrm->id);
        $team   = Team::lookupById($this->defaultTeamOrm->id);
        $player = Player::lookupById($this->defaultPlayerOrm->id);

        $this->playerGameStats = PlayerGameStats::findOrCreate(
            $game,
            $team,
            $player);
    }

    protected function tearDown()
    {
        if (isset($this->playerGameStats)) {
            $this->playerGameStats->delete();
        }

        $this->clearDatabase();
    }

    public function test_findWithCreate()
    {
        $this->verifyExpectedAttributes($this->playerGameStats);
    }

    public function test_find()
    {
        $game   = Game::lookupById($this->defaultGameOrm->id);
        $team   = Team::lookupById($this->defaultTeamOrm->id);
        $player = Player::lookupById($this->defaultPlayerOrm->id);

        $playerGameStats = PlayerGameStats::findOrCreate(
            $game,
            $team,
            $player);
        $this->verifyExpectedAttributes($playerGameStats);
    }

    public function test_set()
    {
        $this->playerGameStats->goals                = 15;
        $this->playerGameStats->substitutionQuarter1 = true;
        $this->playerGameStats->substitutionQuarter2 = true;
        $this->playerGameStats->substitutionQuarter3 = true;
        $this->playerGameStats->substitutionQuarter4 = true;
        $this->playerGameStats->keeperQuarter1       = true;
        $this->playerGameStats->keeperQuarter2       = true;
        $this->playerGameStats->keeperQuarter3       = true;
        $this->playerGameStats->keeperQuarter4       = true;
        $this->playerGameStats->yellowCards          = 2;
        $this->playerGameStats->redCard              = true;

        $game   = Game::lookupById($this->defaultGameOrm->id);
        $team   = Team::lookupById($this->defaultTeamOrm->id);
        $player = Player::lookupById($this->defaultPlayerOrm->id);

        $playerGameStats = PlayerGameStats::findOrCreate(
            $game,
            $team,
            $player);

        $this->expectedPlayerGameStatsOrm =
            [
                PlayerGameStatsOrm::FIELD_GAME_ID                   => $this->defaultGameOrm->id,
                PlayerGameStatsOrm::FIELD_TEAM_ID                   => $this->defaultTeamOrm->id,
                PlayerGameStatsOrm::FIELD_PLAYER_ID                 => $this->defaultPlayerOrm->id,
                PlayerGameStatsOrm::FIELD_GOALS                     => 15,
                PlayerGameStatsOrm::FIELD_SUBSTITUTION_QUARTER_1    => 1,
                PlayerGameStatsOrm::FIELD_SUBSTITUTION_QUARTER_2    => 1,
                PlayerGameStatsOrm::FIELD_SUBSTITUTION_QUARTER_3    => 1,
                PlayerGameStatsOrm::FIELD_SUBSTITUTION_QUARTER_4    => 1,
                PlayerGameStatsOrm::FIELD_KEEPER_QUARTER_1          => 1,
                PlayerGameStatsOrm::FIELD_KEEPER_QUARTER_2          => 1,
                PlayerGameStatsOrm::FIELD_KEEPER_QUARTER_3          => 1,
                PlayerGameStatsOrm::FIELD_KEEPER_QUARTER_4          => 1,
                PlayerGameStatsOrm::FIELD_YELLOW_CARDS              => 2,
                PlayerGameStatsOrm::FIELD_RED_CARD                  => 1,
            ];

        $this->verifyExpectedAttributes($playerGameStats);
    }

    /**
     * @param PlayerGameStats $playerGameStats
     */
    private function verifyExpectedAttributes($playerGameStats)
    {
        $this->assertEquals($this->expectedPlayerGameStatsOrm[PlayerGameStatsOrm::FIELD_GAME_ID],   $playerGameStats->game->id);
        $this->assertEquals($this->expectedPlayerGameStatsOrm[PlayerGameStatsOrm::FIELD_TEAM_ID],   $playerGameStats->team->id);
        $this->assertEquals($this->expectedPlayerGameStatsOrm[PlayerGameStatsOrm::FIELD_PLAYER_ID], $playerGameStats->player->id);
        $this->assertEquals($this->expectedPlayerGameStatsOrm[PlayerGameStatsOrm::FIELD_SUBSTITUTION_QUARTER_1], $playerGameStats->substitutionQuarter1 ? 1 : 0);
        $this->assertEquals($this->expectedPlayerGameStatsOrm[PlayerGameStatsOrm::FIELD_SUBSTITUTION_QUARTER_2], $playerGameStats->substitutionQuarter2 ? 1 : 0);
        $this->assertEquals($this->expectedPlayerGameStatsOrm[PlayerGameStatsOrm::FIELD_SUBSTITUTION_QUARTER_3], $playerGameStats->substitutionQuarter3 ? 1 : 0);
        $this->assertEquals($this->expectedPlayerGameStatsOrm[PlayerGameStatsOrm::FIELD_SUBSTITUTION_QUARTER_4], $playerGameStats->substitutionQuarter4 ? 1 : 0);
        $this->assertEquals($this->expectedPlayerGameStatsOrm[PlayerGameStatsOrm::FIELD_KEEPER_QUARTER_1], $playerGameStats->keeperQuarter1 ? 1 : 0);
        $this->assertEquals($this->expectedPlayerGameStatsOrm[PlayerGameStatsOrm::FIELD_KEEPER_QUARTER_2], $playerGameStats->keeperQuarter2 ? 1 : 0);
        $this->assertEquals($this->expectedPlayerGameStatsOrm[PlayerGameStatsOrm::FIELD_KEEPER_QUARTER_3], $playerGameStats->keeperQuarter3 ? 1 : 0);
        $this->assertEquals($this->expectedPlayerGameStatsOrm[PlayerGameStatsOrm::FIELD_KEEPER_QUARTER_4], $playerGameStats->keeperQuarter4 ? 1 : 0);
        $this->assertEquals($this->expectedPlayerGameStatsOrm[PlayerGameStatsOrm::FIELD_YELLOW_CARDS],     $playerGameStats->yellowCards);
        $this->assertEquals($this->expectedPlayerGameStatsOrm[PlayerGameStatsOrm::FIELD_RED_CARD],         $playerGameStats->redCard ? 1 : 0);
    }
}