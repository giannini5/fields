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
    protected $locked;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->flight       = Flight::lookupById($this->defaultFlightOrm->id);
        $this->pool         = Pool::lookupById($this->defaultPoolOrm->id);
        $this->gameTime     = GameTime::lookupById($this->defaultGameTimeOrm->id);
        $this->homeTeam     = Team::lookupById($this->defaultTeamOrm->id);
        $this->visitingTeam = Team::lookupById($this->defaultVisitingTeamOrm->id);
        $this->title        = 'Championship';
        $this->locked       = 1;

        // Clear out default game and create a new one for testing
        $game = Game::lookupById($this->defaultGameOrm->id);
        $game->delete();

        $this->gamesToCleanup[] = Game::create(
            $this->flight,
            $this->pool,
            $this->gameTime,
            $this->homeTeam,
            $this->visitingTeam,
            $this->title,
            $this->locked);

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

    public function test_findById()
    {
        $result = Game::findById($this->gamesToCleanup[0]->id, $game);
        $this->assertTrue($result, "Game with id " . $this->gamesToCleanup[0]->id . " not found");
        $this->validateGame($game);
    }

    public function test_notFindById()
    {
        $result = Game::findById(987654321, $game);
        $this->assertFalse($result, "Game with id 987654321 unexpectendly found");
    }

    public function test_findByPlayInGameIdWin()
    {
        // Setup
        $playInByWin    = 1;

        $this->gamesToCleanup[0]->playInByWin = $playInByWin;
        $this->gamesToCleanup[0]->setPlayInHomeGame($this->gamesToCleanup[0]);

        // Run Tests
        $game   = null;
        $result = Game::findByPlayInGame($this->gamesToCleanup[0], $playInByWin, $game);

        // Check Results
        $this->assertTrue($result, "Game with id " . $this->gamesToCleanup[0]->id . " not found");
        $this->assertTrue(isset($game));
    }

    public function test_findByPlayInGameIdNotWin()
    {
        // Setup
        $playInByWin    = 0;

        $this->gamesToCleanup[0]->playInByWin = $playInByWin;
        $this->gamesToCleanup[0]->setPlayInHomeGame($this->gamesToCleanup[0]);

        // Run Tests
        $game   = null;
        $result = Game::findByPlayInGame($this->gamesToCleanup[0], $playInByWin, $game);

        // Check results
        $this->assertTrue($result, "Game with id " . $this->gamesToCleanup[0]->id . " not found");
        $this->assertTrue(isset($game));
    }

    public function test_findByPlayInVisitingGameIdWin()
    {
        // Setup
        $playInByWin    = 1;

        $this->gamesToCleanup[0]->playInByWin = $playInByWin;
        $this->gamesToCleanup[0]->setPlayInVisitingGame($this->gamesToCleanup[0]);

        // Run Tests
        $game   = null;
        $result = Game::findByPlayInGame($this->gamesToCleanup[0], $playInByWin, $game);

        // Check Results
        $this->assertTrue($result, "Game with id " . $this->gamesToCleanup[0]->id . " not found");
        $this->assertTrue(isset($game));
    }

    public function test_findByPlayInVisitingGameIdNotWin()
    {
        // Setup
        $playInByWin    = 0;

        $this->gamesToCleanup[0]->playInByWin = $playInByWin;
        $this->gamesToCleanup[0]->setPlayInVisitingGame($this->gamesToCleanup[0]);

        // Run Tests
        $game   = null;
        $result = Game::findByPlayInGame($this->gamesToCleanup[0], $playInByWin, $game);

        // Check results
        $this->assertTrue($result, "Game with id " . $this->gamesToCleanup[0]->id . " not found");
        $this->assertTrue(isset($game));
    }

    public function test_notFindByPlayInGameId()
    {
        $result = Game::findByPlayInGame($this->gamesToCleanup[0], 0, $game);
        $this->assertFalse($result, "Game with id " . $this->gamesToCleanup[0]->id . " found");

        $result = Game::findByPlayInGame($this->gamesToCleanup[0], 1, $game);
        $this->assertFalse($result, "Game with id " . $this->gamesToCleanup[0]->id . " found");
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
        $game->title                    = 'Hello World';
        $game->locked                   = 0;
        $game->homeTeamScore            = 1;
        $game->visitingTeamScore        = 2;
        $game->homeTeamYellowCards      = 3;
        $game->visitingTeamYellowCards  = 4;
        $game->homeTeamRedCards         = 5;
        $game->visitingTeamRedCards     = 6;
        $game->notes                    = "Goodbye world";

        $game = Game::lookupById($this->gamesToCleanup[0]->id);
        $this->assertEquals('Hello World', $game->title);
        $this->assertEquals(0, $game->locked);
        $this->assertFalse($game->isLocked());
        $this->assertEquals(1, $game->homeTeamScore);
        $this->assertEquals(2, $game->visitingTeamScore);
        $this->assertEquals(3, $game->homeTeamYellowCards);
        $this->assertEquals(4, $game->visitingTeamYellowCards);
        $this->assertEquals(5, $game->homeTeamRedCards);
        $this->assertEquals(6, $game->visitingTeamRedCards);
        $this->assertEquals("Goodbye world", $game->notes);
    }

    public function test_isLocked()
    {
        $game = Game::lookupById($this->gamesToCleanup[0]->id);
        $this->assertTrue($game->isLocked());
    }

    public function test_isset()
    {
        $game = Game::lookupById($this->gamesToCleanup[0]->id);
        $this->assertTrue(isset($game->id), "id not set");
        $this->assertTrue(isset($game->title), "title not set");
        $this->assertTrue(isset($game->homeTeam), "homeTeam not set");
        $this->assertTrue(isset($game->visitingTeam));
        $this->assertTrue(isset($game->gameTime));
        $this->assertTrue(isset($game->flight));
        $this->assertTrue(isset($game->pool));
        $this->assertTrue(isset($game->locked));
        $this->assertFalse(isset($game->homeTeamScore), "homeTeamScore is set");
        $this->assertFalse(isset($game->visitingTeamScore), "visitingTeamScore is set");
        $this->assertTrue(isset($game->homeTeamYellowCards), "homeTeamYellowCards not set");
        $this->assertTrue(isset($game->visitingTeamYellowCards), "visitingTeamYellowCards not set");
        $this->assertTrue(isset($game->homeTeamRedCards), "homeTeamRedCards not set");
        $this->assertTrue(isset($game->visitingTeamRedCards), "visitingTeamRedCards not set");
        $this->assertTrue(isset($game->notes));
        $this->assertFalse(isset($game->playInHomeGameId), "playInHomeGameId is set");
        $this->assertFalse(isset($game->playInVisitingGameId), "playInVisitingGameId is set");
        $this->assertTrue(isset($game->playInByWin), "playInByWin is not set");
    }

    public function test_isForHomeTeam()
    {
        $game = Game::lookupById($this->gamesToCleanup[0]->id);
        $this->assertTrue($game->isForTeam($this->homeTeam));
    }

    public function test_isForVisitingTeam()
    {
        $game = Game::lookupById($this->gamesToCleanup[0]->id);
        $this->assertTrue($game->isForTeam($this->visitingTeam));
    }

    public function validateGame($game, $homeScore = NULL, $visitingScore = NULL, $homeYellows = 0, $visitingYellows = 0, $homeReds = 0, $visitingReds = 0, $notes = '')
    {
        $this->assertTrue($game->id > 0);
        $this->assertEquals($this->pool,            $game->pool);
        $this->assertEquals($this->gameTime,        $game->gameTime);
        $this->assertEquals($this->homeTeam,        $game->homeTeam);
        $this->assertEquals($this->visitingTeam,    $game->visitingTeam);
        $this->assertEquals($this->title,           $game->title);
        $this->assertEquals($this->locked,          $game->locked);
        $this->assertEquals($homeYellows,           $game->homeTeamYellowCards);
        $this->assertEquals($visitingYellows,       $game->visitingTeamYellowCards);
        $this->assertEquals($homeReds,              $game->homeTeamRedCards);
        $this->assertEquals($visitingReds,          $game->visitingTeamRedCards);
        $this->assertEquals($notes,                 $game->notes);
        $this->assertEquals(0,                      $game->playInHomeGameId);
        $this->assertEquals(0,                      $game->playInVisitingGameId);
        $this->assertEquals(0,                      $game->playInByWin);

        if (!isset($homeScore)) {
            $this->assertTrue(!isset($game->homeTeamScore));
        } else {
            $this->assertEquals($homeScore, $game->homeTeamScore);
        }

        if (!isset($visitingScore)) {
            $this->assertTrue(!isset($game->visitingTeamScore));
        } else {
            $this->assertEquals($visitingScore, $game->visitingTeamScore);
        }
    }
}