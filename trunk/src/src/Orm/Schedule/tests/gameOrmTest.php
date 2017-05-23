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
        $this->verifyExpectedAttributes($this->defaultGameOrm, self::$defaultGameOrmAttributes);
    }

    public function test_loadById()
    {
        $gameOrm = GameOrm::loadById($this->defaultGameOrm->id);
        $this->verifyExpectedAttributes($gameOrm, self::$defaultGameOrmAttributes);
    }

    public function test_loadByGameTimeId()
    {
        $gameOrm = GameOrm::loadByGameTimeId($this->defaultGameTimeOrm->id);
        $this->verifyExpectedAttributes($gameOrm, self::$defaultGameOrmAttributes);
    }

    public function test_loadByFlightId()
    {
        $gameOrms = GameOrm::loadByFlightId($this->defaultFlightOrm->id);
        $this->assertEquals(1, count($gameOrms));
        $this->verifyExpectedAttributes($gameOrms[0], self::$defaultGameOrmAttributes);
    }

    public function test_loadByFlightIdAndTitle()
    {
        $gameOrms = GameOrm::loadByFlightIdAndTitle($this->defaultFlightOrm->id, self::$defaultGameOrmAttributes[self::TITLE]);
        $this->assertEquals(1, count($gameOrms));
        $this->verifyExpectedAttributes($gameOrms[0], self::$defaultGameOrmAttributes);
    }

    public function test_loadByPoolId()
    {
        $gameOrms = GameOrm::loadByPoolId($this->defaultPoolOrm->id);
        $this->assertEquals(1, count($gameOrms));
        $this->verifyExpectedAttributes($gameOrms[0], self::$defaultGameOrmAttributes);
    }

    public function test_loadByTeamId()
    {
        $gameOrms = GameOrm::loadByTeamId($this->defaultTeamOrm->id);
        $this->assertEquals(1, count($gameOrms));
        $this->verifyExpectedAttributes($gameOrms[0], self::$defaultGameOrmAttributes);

        $gameOrms = GameOrm::loadByTeamId($this->defaultVisitingTeamOrm->id);
        $this->assertEquals(1, count($gameOrms));
        $this->verifyExpectedAttributes($gameOrms[0], self::$defaultGameOrmAttributes);
    }

    private function verifyExpectedAttributes($gameOrm, $gameOrmAttributes)
    {
        $this->assertTrue($gameOrm->id > 0);
        $this->assertEquals($this->defaultFlightOrm->id,        $gameOrm->flightId);
        $this->assertEquals($this->defaultPoolOrm->id,          $gameOrm->poolId);
        $this->assertEquals($this->defaultGameTimeOrm->id,      $gameOrm->gameTimeId);
        $this->assertEquals($this->defaultTeamOrm->id,          $gameOrm->homeTeamId);
        $this->assertEquals($this->defaultVisitingTeamOrm->id,  $gameOrm->visitingTeamId);
        $this->assertEquals($gameOrmAttributes[self::TITLE],    $gameOrm->title);
        $this->assertEquals($gameOrmAttributes[self::LOCKED],    $gameOrm->locked);
    }
}