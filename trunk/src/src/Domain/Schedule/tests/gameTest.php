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
    protected $schedule;
    protected $gameTime;
    protected $homeTeam;
    protected $visitingTeam;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->schedule     = Schedule::lookupById($this->defaultScheduleOrm->id);
        $this->gameTime     = GameTime::lookupById($this->defaultGameTimeOrm->id);
        $this->homeTeam     = Team::lookupById($this->defaultTeamOrm->id);
        $this->visitingTeam = Team::lookupById($this->defaultVisitingTeamOrm->id);

        // Clear out default game and create a new one for testing
        $game = Game::lookupById($this->defaultGameOrm->id);
        $game->delete();

        $this->gamesToCleanup[] = Game::create(
            $this->schedule,
            $this->gameTime,
            $this->homeTeam,
            $this->visitingTeam);
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

    public function test_lookupBySchedule()
    {
        $games = Game::lookupBySchedule($this->schedule);
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

    public function validateGame($game)
    {
        $this->assertTrue($game->id > 0);
        $this->assertEquals($this->schedule,        $game->schedule);
        $this->assertEquals($this->gameTime,        $game->gameTime);
        $this->assertEquals($this->homeTeam,        $game->homeTeam);
        $this->assertEquals($this->visitingTeam,    $game->visitingTeam);
    }
}