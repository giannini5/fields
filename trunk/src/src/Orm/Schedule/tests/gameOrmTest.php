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

    public function test_loadByScheduleId()
    {
        $gameOrms = GameOrm::loadByScheduleId($this->defaultFlightOrm->scheduleId);
        $this->assertEquals(1, count($gameOrms));
        $this->verifyExpectedAttributes($gameOrms[0], self::$defaultGameOrmAttributes);
    }

    public function test_loadByScheduleIdGameDateId()
    {
        $gameOrms = GameOrm::loadByScheduleIdGameDateId($this->defaultScheduleOrm->id, $this->defaultGameDateOrm->id);
        $this->assertEquals(1, count($gameOrms));
        $this->verifyExpectedAttributes($gameOrms[0], self::$defaultGameOrmAttributes);
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

    public function test_loadByGameDateIdTeamId()
    {
        $gameOrms = GameOrm::loadByGameDateIdTeamId($this->defaultGameDateOrm->id, $this->defaultTeamOrm->id);
        $this->assertEquals(1, count($gameOrms));
        $this->verifyExpectedAttributes($gameOrms[0], self::$defaultGameOrmAttributes);

        $gameOrms = GameOrm::loadByGameDateIdTeamId($this->defaultGameDateOrm->id, $this->defaultVisitingTeamOrm->id);
        $this->assertEquals(1, count($gameOrms));
        $this->verifyExpectedAttributes($gameOrms[0], self::$defaultGameOrmAttributes);
    }

    public function test_findByPlayInGameIdFalse()
    {
        $gameOrm    = null;
        $result     = GameOrm::findByPlayInGameId(1, 0, $gameOrm);

        $this->assertFalse($result);
        $this->assertTrue(!isset($gameOrm));
    }

    public function test_findByPlayInGameIdWinTrue()
    {
        // Create game with playByWin = 1 and find game
        $homeGameId     = 2;
        $visitingGameId = 3;
        $playInByWin    = 1;

        $this->defaultGameTimeOrm->gameId = null;
        $this->defaultGameTimeOrm->save();
        $this->defaultGameOrm->delete();

        $gameOrm = GameOrm::create(
            $this->defaultFlightOrm->scheduleId,
            $this->defaultFlightOrm->id,
            $this->defaultPoolOrm->id,
            $this->defaultGameTimeOrm->gameDateId,
            $this->defaultGameTimeOrm->id,
            $this->defaultTeamOrm->id,
            $this->defaultVisitingTeamOrm->id,
            self::$defaultGameOrmAttributes[self::TITLE],
            self::$defaultGameOrmAttributes[self::LOCKED],
            $homeGameId,
            $visitingGameId,
            $playInByWin
            );

        $foundGameOrm   = null;
        $result         = GameOrm::findByPlayInGameId($homeGameId, $playInByWin, $foundGameOrm);

        $this->assertTrue($result);
        $this->assertTrue(isset($foundGameOrm));
        $this->assertTrue($foundGameOrm->id == $gameOrm->id, "Invalid gameId found");
        $this->assertTrue($foundGameOrm->playInHomeGameId == $homeGameId, "Invalid home gameId");
        $this->assertTrue($foundGameOrm->playInVisitingGameId == $visitingGameId, "Invalid visiting gameId");
    }

    public function test_findByPlayInGameIdWinFalse()
    {
        // Create game with playByWin = 1 and find game
        $homeGameId     = 2;
        $visitingGameId = 3;
        $playInByWin    = 0;

        $this->defaultGameTimeOrm->gameId = null;
        $this->defaultGameTimeOrm->save();
        $this->defaultGameOrm->delete();

        $gameOrm = GameOrm::create(
            $this->defaultFlightOrm->scheduleId,
            $this->defaultFlightOrm->id,
            $this->defaultPoolOrm->id,
            $this->defaultGameTimeOrm->gameDateId,
            $this->defaultGameTimeOrm->id,
            $this->defaultTeamOrm->id,
            $this->defaultVisitingTeamOrm->id,
            self::$defaultGameOrmAttributes[self::TITLE],
            self::$defaultGameOrmAttributes[self::LOCKED],
            $homeGameId,
            $visitingGameId,
            $playInByWin
        );

        /** @var GameOrm $foundGameOrm */
        $foundGameOrm   = null;
        $result         = GameOrm::findByPlayInGameId($homeGameId, $playInByWin, $foundGameOrm);

        $this->assertTrue($result);
        $this->assertTrue(isset($foundGameOrm));
        $this->assertTrue($foundGameOrm->id == $gameOrm->id, "Invalid gameId found");
        $this->assertTrue($foundGameOrm->playInHomeGameId == $homeGameId, "Invalid home gameId");
        $this->assertTrue($foundGameOrm->playInVisitingGameId == $visitingGameId, "Invalid visiting gameId");
    }

    public function test_refereeCrewId()
    {
        $gameOrm = GameOrm::loadById($this->defaultGameOrm->id);
        $this->assertNull($gameOrm->refereeCrewId);

        $gameOrm->refereeCrewId = $this->defaultRefereeCrewOrm->id;
        $gameOrm->save();
        $this->assertEquals($this->defaultRefereeCrewOrm->id, $gameOrm->refereeCrewId);

        $gameOrm2 = GameOrm::loadById($this->defaultGameOrm->id);
        $this->assertEquals($this->defaultRefereeCrewOrm->id, $gameOrm2->refereeCrewId);
    }

    private function verifyExpectedAttributes($gameOrm, $gameOrmAttributes)
    {
        $this->assertTrue($gameOrm->id > 0);
        $this->assertEquals($this->defaultFlightOrm->scheduleId,                $gameOrm->scheduleId);
        $this->assertEquals($this->defaultFlightOrm->id,                        $gameOrm->flightId);
        $this->assertEquals($this->defaultPoolOrm->id,                          $gameOrm->poolId);
        $this->assertEquals($this->defaultGameDateOrm->id,                      $gameOrm->gameDateId);
        $this->assertEquals($this->defaultGameTimeOrm->id,                      $gameOrm->gameTimeId);
        $this->assertEquals($this->defaultTeamOrm->id,                          $gameOrm->homeTeamId);
        $this->assertEquals($this->defaultVisitingTeamOrm->id,                  $gameOrm->visitingTeamId);
        $this->assertEquals($gameOrmAttributes[self::TITLE],                    $gameOrm->title);
        $this->assertEquals($gameOrmAttributes[self::PLAY_IN_HOME_GAME_ID],     $gameOrm->playInHomeGameId);
        $this->assertEquals($gameOrmAttributes[self::PLAY_IN_VISITING_GAME_ID], $gameOrm->playInHomeGameId);
        $this->assertEquals($gameOrmAttributes[self::PLAY_IN_BY_WIN],           $gameOrm->playInByWin);
        $this->assertEquals($gameOrmAttributes[self::LOCKED],                   $gameOrm->locked);
    }
}