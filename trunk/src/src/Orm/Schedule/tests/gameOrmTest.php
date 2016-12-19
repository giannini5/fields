<?php

namespace DAG\Orm\Schedule;

require_once 'helper.php';


/**
 * @testSuite test Facility ORM
 */
class GameOrmTest extends ORM_TestHelper
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
        $this->verifyExpectedAttributes($this->defaultGameOrm);
    }

    public function test_loadById()
    {
        $gameOrm = GameOrm::loadById($this->defaultGameOrm->id);
        $this->verifyExpectedAttributes($gameOrm);
    }

    public function test_loadByGameTimeId()
    {
        $gameOrm = GameOrm::loadByGameTimeId($this->defaultGameTimeOrm->id);
        $this->verifyExpectedAttributes($gameOrm);
    }

    public function test_loadByPoolId()
    {
        $gameOrms = GameOrm::loadByPoolId($this->defaultPoolOrm->id);
        $this->assertEquals(1, count($gameOrms));
        $this->verifyExpectedAttributes($gameOrms[0]);
    }

    public function test_loadByTeamId()
    {
        $gameOrms = GameOrm::loadByTeamId($this->defaultTeamOrm->id);
        $this->assertEquals(1, count($gameOrms));
        $this->verifyExpectedAttributes($gameOrms[0]);

        $gameOrms = GameOrm::loadByTeamId($this->defaultVisitingTeamOrm->id);
        $this->assertEquals(1, count($gameOrms));
        $this->verifyExpectedAttributes($gameOrms[0]);
    }

    private function verifyExpectedAttributes($gameOrm)
    {
        $this->assertTrue($gameOrm->id > 0);
        $this->assertEquals($this->defaultPoolOrm->id,          $gameOrm->poolId);
        $this->assertEquals($this->defaultGameTimeOrm->id,      $gameOrm->gameTimeId);
        $this->assertEquals($this->defaultTeamOrm->id,          $gameOrm->homeTeamId);
        $this->assertEquals($this->defaultVisitingTeamOrm->id,  $gameOrm->visitingTeamId);
    }
}