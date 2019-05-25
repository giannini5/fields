<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\GameRefereeOrm;
use DAG\Orm\Schedule\ORM_TestHelper;

require_once dirname(dirname(dirname(__DIR__))) . '/Orm/Schedule/tests/helper.php';



/**
 * @testSuite test GameReferee
 */
class GameRefereeTest extends ORM_TestHelper
{
    public $game;
    public $referee;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->game     = Game::lookupById($this->defaultGameOrm->id);
        $this->referee  = Referee::lookupById($this->defaultRefereeOrm->id);
    }

    protected function tearDown()
    {
        $this->clearDatabase();
    }

    public function test_lookupById()
    {
        $gameReferee = GameReferee::lookupById($this->defaultGameRefereeOrm->id);
        $this->validateGameReferee($gameReferee, $this->game, $this->referee);
    }

    public function test_lookupByGame()
    {
        $gameReferees = GameReferee::lookupByGame($this->game);
        $this->assertEquals(1, count($gameReferees));
        $this->validateGameReferee($gameReferees[0], $this->game, $this->referee);
    }

    public function test_lookupByReferee()
    {
        $gameReferees = GameReferee::lookupByReferee($this->referee);
        $this->assertEquals(1, count($gameReferees));
        $this->validateGameReferee($gameReferees[0], $this->game, $this->referee);
    }

    public function test_lookupByGameAndReferee()
    {
        $gameReferee = GameReferee::lookupByGameAndReferee($this->game, $this->referee);
        $this->validateGameReferee($gameReferee, $this->game, $this->referee);
    }

    public function test_findByGameAndReferee()
    {
        $gameReferee = null;
        $result = GameReferee::findByGameAndReferee($this->game, $this->referee, $gameReferee);
        $this->assertTrue($result, "findByGameAndReferee returned false");
        $this->validateGameReferee($gameReferee, $this->game, $this->referee);
    }

    public function validateGameReferee($gameReferee, $game, $referee)
    {
        $this->assertTrue($gameReferee->id > 0);
        $this->assertEquals($game,                          $gameReferee->game);
        $this->assertEquals($referee,                       $gameReferee->referee);
        $this->assertEquals(GameRefereeOrm::CENTER_ROLE,    $gameReferee->role);
    }
}