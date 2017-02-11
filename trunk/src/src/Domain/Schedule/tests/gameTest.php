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
    protected $flight;
    protected $pool;
    protected $gameTime;
    protected $homeTeam;
    protected $visitingTeam;
    protected $title;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->flight       = Flight::lookupById($this->defaultFlightOrm->id);
        $this->pool         = Pool::lookupById($this->defaultPoolOrm->id);
        $this->gameTime     = GameTime::lookupById($this->defaultGameTimeOrm->id);
        $this->homeTeam     = Team::lookupById($this->defaultTeamOrm->id);
        $this->visitingTeam = Team::lookupById($this->defaultVisitingTeamOrm->id);
        $this->title        = 'Championship';

        // Clear out default game and create a new one for testing
        $game = Game::lookupById($this->defaultGameOrm->id);
        $game->delete();

        $this->gamesToCleanup[] = Game::create(
            $this->flight,
            $this->pool,
            $this->gameTime,
            $this->homeTeam,
            $this->visitingTeam,
            $this->title);

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

    public function test_lookupByFlight()
    {
        $games = Game::lookupByFlight($this->flight);
        $this->assertTrue(count($games) == 1);
        $this->validateGame($games[0]);
    }

    public function test_lookupByFlightAndTitle()
    {
        $games = Game::lookupByFlightAndTitle($this->flight, $this->title);
        $this->assertTrue(count($games) == 1);
        $this->validateGame($games[0]);
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

    public function test_set()
    {
        $game = Game::lookupById($this->gamesToCleanup[0]->id);
        $game->title = 'Hello World';

        $game = Game::lookupById($this->gamesToCleanup[0]->id);
        $this->assertEquals('Hello World', $game->title);
    }

    public function test_isset()
    {
        $game = Game::lookupById($this->gamesToCleanup[0]->id);
        $this->assertTrue(isset($game->id));
        $this->assertTrue(isset($game->title));
        $this->assertTrue(isset($game->homeTeam));
        $this->assertTrue(isset($game->visitingTeam));
        $this->assertTrue(isset($game->gameTime));
        $this->assertTrue(isset($game->flight));
        $this->assertTrue(isset($game->pool));
    }

    public function validateGame($game)
    {
        $this->assertTrue($game->id > 0);
        $this->assertEquals($this->pool,            $game->pool);
        $this->assertEquals($this->gameTime,        $game->gameTime);
        $this->assertEquals($this->homeTeam,        $game->homeTeam);
        $this->assertEquals($this->visitingTeam,    $game->visitingTeam);
        $this->assertEquals($this->title,           $game->title);
    }
}