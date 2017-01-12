<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\ORM_TestHelper;

require_once dirname(dirname(dirname(__DIR__))) . '/Orm/Schedule/tests/helper.php';



/**
 * @testSuite test Game
 */
class GameTest extends ORM_TestHelper
{
    protected $gamesToCleanup = array();
    protected $pool;
    protected $gameTime;
    protected $homeTeam;
    protected $visitingTeam;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->pool         = Pool::lookupById($this->defaultPoolOrm->id);
        $this->gameTime     = GameTime::lookupById($this->defaultGameTimeOrm->id);
        $this->homeTeam     = Team::lookupById($this->defaultTeamOrm->id);
        $this->visitingTeam = Team::lookupById($this->defaultVisitingTeamOrm->id);

        // Clear out default game and create a new one for testing
        $game = Game::lookupById($this->defaultGameOrm->id);
        $game->delete();

        $this->gamesToCleanup[] = Game::create(
            $this->pool,
            $this->gameTime,
            $this->homeTeam,
            $this->visitingTeam);

        // Update again since it changes when Game is deleted/created
        $this->gameTime     = GameTime::lookupById($this->defaultGameTimeOrm->id);
    }

    protected function tearDown()
    {
        foreach ($this->gamesToCleanup as $entity) {
            $entity->delete();
        }

        $this->clearDatabase();
    }

    public function test_create()
    {
        $game = $this->gamesToCleanup[0];
        $this->validateGame($game);
    }

    public function test_lookupById()
    {
        $game = Game::lookupById($this->gamesToCleanup[0]->id);
        $this->validateGame($game);
    }

    public function test_lookupByPool()
    {
        $games = Game::lookupByPool($this->pool);
        $this->assertTrue(count($games) == 1);
        $this->validateGame($games[0]);
    }

    public function test_lookupByGameTime()
    {
        $game = Game::lookupByGameTime($this->gameTime);
        $this->validateGame($game);
    }

    public function test_lookupByTeam()
    {
        $games = Game::lookupByTeam($this->homeTeam);
        $this->assertTrue(count($games) == 1);
        $this->validateGame($games[0]);

        $games = Game::lookupByTeam($this->visitingTeam);
        $this->assertTrue(count($games) == 1);
        $this->validateGame($games[0]);
    }

    public function test_lookupByDivisionDay()
    {
        $division   = Division::lookupById($this->defaultDivisionOrm->id);
        $day        = $this->defaultGameDateOrm->day;

        $games = Game::lookupByDivisionDay($division, $day);
        $this->assertTrue(count($games) == 1);
        $this->validateGame($games[0]);
    }

    public function validateGame($game)
    {
        $this->assertTrue($game->id > 0);
        $this->assertEquals($this->pool,            $game->pool);
        $this->assertEquals($this->gameTime,        $game->gameTime);
        $this->assertEquals($this->homeTeam,        $game->homeTeam);
        $this->assertEquals($this->visitingTeam,    $game->visitingTeam);
    }
}