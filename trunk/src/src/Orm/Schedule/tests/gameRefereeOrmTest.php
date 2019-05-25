<?php

namespace DAG\Orm\Schedule;

require_once 'helper.php';


/**
 * @testSuite test GameReferee ORM
 */
class GameRefereeOrmTest extends ORM_TestHelper
{
    protected function setUp()
    {
        $this->primeDatabase();
    }

    protected function tearDown()
    {
        $this->clearDatabase();
    }

    public function test_create()
    {
        $this->verifyExpectedAttributes($this->defaultGameRefereeOrm, $this->defaultGameOrm->id, $this->defaultRefereeOrm->id);
    }

    public function test_loadById()
    {
        $gameRefereeOrm = GameRefereeOrm::loadById($this->defaultGameRefereeOrm->id);
        $this->verifyExpectedAttributes($gameRefereeOrm, $this->defaultGameOrm->id, $this->defaultRefereeOrm->id);
    }

    public function test_loadByGameReferee()
    {
        $gameRefereeOrm = GameRefereeOrm::loadByGameIdAndReferee($this->defaultGameOrm->id, $this->defaultRefereeOrm->id);
        $this->verifyExpectedAttributes($gameRefereeOrm, $this->defaultGameOrm->id, $this->defaultRefereeOrm->id);
    }

    public function test_loadByGame()
    {
        $gameRefereeOrms = GameRefereeOrm::loadByGameId($this->defaultGameOrm->id);
        $this->assertEquals(1, count($gameRefereeOrms));
        $this->verifyExpectedAttributes($gameRefereeOrms[0], $this->defaultGameOrm->id, $this->defaultRefereeOrm->id);
    }

    public function test_loadByReferee()
    {
        $gameRefereeOrms = GameRefereeOrm::loadByRefereeId($this->defaultRefereeOrm->id);
        $this->assertEquals(1, count($gameRefereeOrms));
        $this->verifyExpectedAttributes($gameRefereeOrms[0], $this->defaultGameOrm->id, $this->defaultRefereeOrm->id);
    }

    private function verifyExpectedAttributes($gameRefereeOrm, $gameOrmId, $refereeOrmId)
    {
        $this->assertTrue($gameRefereeOrm->id > 0);
        $this->assertEquals($gameOrmId,                  $gameRefereeOrm->gameId);
        $this->assertEquals($refereeOrmId,               $gameRefereeOrm->refereeId);
        $this->assertEquals(GameRefereeOrm::CENTER_ROLE, $gameRefereeOrm->role);
    }
}