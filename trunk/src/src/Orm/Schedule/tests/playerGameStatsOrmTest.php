<?php

namespace DAG\Orm\Schedule;

require_once 'helper.php';


/**
 * @testSuite test PlayerGameStatsOrm
 */
class PlayerGameStatsOrmTest extends ORM_TestHelper
{
    /** @var  PlayerGameStatsOrm */
    private $playerGameStatsOrm;

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
            ];
    }

    protected function tearDown()
    {
        if (isset($this->playerGameStatsOrm)) {
            $this->playerGameStatsOrm->delete();
        }

        $this->clearDatabase();
    }

    public function test_create()
    {
        $this->playerGameStatsOrm = PlayerGameStatsOrm::create(
            $this->defaultGameOrm->id,
            $this->defaultTeamOrm->id,
            $this->defaultPlayerOrm->id);
        $this->verifyExpectedAttributes($this->playerGameStatsOrm);
    }

    public function test_findWithCreate()
    {
        $this->playerGameStatsOrm = PlayerGameStatsOrm::findOrCreate(
            $this->defaultGameOrm->id,
            $this->defaultTeamOrm->id,
            $this->defaultPlayerOrm->id);
        $this->verifyExpectedAttributes($this->playerGameStatsOrm);
    }

    public function test_find()
    {
        $this->playerGameStatsOrm = PlayerGameStatsOrm::create(
            $this->defaultGameOrm->id,
            $this->defaultTeamOrm->id,
            $this->defaultPlayerOrm->id);

        $playerGameStatsOrm = PlayerGameStatsOrm::findOrCreate(
            $this->defaultGameOrm->id,
            $this->defaultTeamOrm->id,
            $this->defaultPlayerOrm->id);
        $this->verifyExpectedAttributes($playerGameStatsOrm);
    }

    public function test_set()
    {
        $this->playerGameStatsOrm = PlayerGameStatsOrm::create(
            $this->defaultGameOrm->id,
            $this->defaultTeamOrm->id,
            $this->defaultPlayerOrm->id);

        $this->playerGameStatsOrm->goals                = 15;
        $this->playerGameStatsOrm->substitutionQuarter1 = 1;
        $this->playerGameStatsOrm->substitutionQuarter2 = 1;
        $this->playerGameStatsOrm->substitutionQuarter3 = 1;
        $this->playerGameStatsOrm->substitutionQuarter4 = 1;
        $this->playerGameStatsOrm->keeperQuarter1       = 1;
        $this->playerGameStatsOrm->keeperQuarter2       = 1;
        $this->playerGameStatsOrm->keeperQuarter3       = 1;
        $this->playerGameStatsOrm->keeperQuarter4       = 1;
        $this->playerGameStatsOrm->save();

        $playerGameStatsOrm = PlayerGameStatsOrm::loadByPk(
            $this->defaultGameOrm->id,
            $this->defaultTeamOrm->id,
            $this->defaultPlayerOrm->id);

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
            ];

        $this->verifyExpectedAttributes($playerGameStatsOrm);
    }

    public function test_loadByPk()
    {
        $this->playerGameStatsOrm = PlayerGameStatsOrm::create(
            $this->defaultGameOrm->id,
            $this->defaultTeamOrm->id,
            $this->defaultPlayerOrm->id);

        $playerGameStatsOrm = PlayerGameStatsOrm::loadByPk(
            $this->defaultGameOrm->id,
            $this->defaultTeamOrm->id,
            $this->defaultPlayerOrm->id);
        $this->verifyExpectedAttributes($playerGameStatsOrm);
    }

    /**
     * @param PlayerGameStatsOrm $playerGameStatsOrm
     */
    private function verifyExpectedAttributes($playerGameStatsOrm)
    {
        $this->assertEquals($this->expectedPlayerGameStatsOrm[PlayerGameStatsOrm::FIELD_GAME_ID],   $playerGameStatsOrm->gameId);
        $this->assertEquals($this->expectedPlayerGameStatsOrm[PlayerGameStatsOrm::FIELD_TEAM_ID],   $playerGameStatsOrm->teamId);
        $this->assertEquals($this->expectedPlayerGameStatsOrm[PlayerGameStatsOrm::FIELD_PLAYER_ID], $playerGameStatsOrm->playerId);
        $this->assertEquals($this->expectedPlayerGameStatsOrm[PlayerGameStatsOrm::FIELD_SUBSTITUTION_QUARTER_1], $playerGameStatsOrm->substitutionQuarter1);
        $this->assertEquals($this->expectedPlayerGameStatsOrm[PlayerGameStatsOrm::FIELD_SUBSTITUTION_QUARTER_2], $playerGameStatsOrm->substitutionQuarter2);
        $this->assertEquals($this->expectedPlayerGameStatsOrm[PlayerGameStatsOrm::FIELD_SUBSTITUTION_QUARTER_3], $playerGameStatsOrm->substitutionQuarter3);
        $this->assertEquals($this->expectedPlayerGameStatsOrm[PlayerGameStatsOrm::FIELD_SUBSTITUTION_QUARTER_4], $playerGameStatsOrm->substitutionQuarter4);
        $this->assertEquals($this->expectedPlayerGameStatsOrm[PlayerGameStatsOrm::FIELD_KEEPER_QUARTER_1], $playerGameStatsOrm->keeperQuarter1);
        $this->assertEquals($this->expectedPlayerGameStatsOrm[PlayerGameStatsOrm::FIELD_KEEPER_QUARTER_2], $playerGameStatsOrm->keeperQuarter2);
        $this->assertEquals($this->expectedPlayerGameStatsOrm[PlayerGameStatsOrm::FIELD_KEEPER_QUARTER_3], $playerGameStatsOrm->keeperQuarter3);
        $this->assertEquals($this->expectedPlayerGameStatsOrm[PlayerGameStatsOrm::FIELD_KEEPER_QUARTER_4], $playerGameStatsOrm->keeperQuarter4);
    }
}