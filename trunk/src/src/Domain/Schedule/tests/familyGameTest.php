<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\ORM_TestHelper;

require_once dirname(dirname(dirname(__DIR__))) . '/Orm/Schedule/tests/helper.php';



/**
 * @testSuite test FamilyGame
 */
class FamilyGameTest extends ORM_TestHelper
{
    public $family;
    public $game;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->family   = Family::lookupById($this->defaultFamilyOrm->id);
        $this->game     = Game::lookupById($this->defaultGameOrm->id);
    }

    protected function tearDown()
    {
        $this->clearDatabase();
    }

    public function test_lookupById()
    {
        $familyGame = FamilyGame::lookupById($this->defaultFamilyGameOrm->id);
        $this->validateFamilyGame($familyGame, $this->family, $this->game);
    }

    public function test_lookupByFamily()
    {
        $familyGames = FamilyGame::lookupByFamily($this->family);
        $this->assertEquals(1, count($familyGames));
        $this->validateFamilyGame($familyGames[0], $this->family, $this->game);
    }

    public function test_lookupByGame()
    {
        $familyGames = FamilyGame::lookupByGame($this->game);
        $this->assertEquals(1, count($familyGames));
        $this->validateFamilyGame($familyGames[0], $this->family, $this->game);
    }

    public function test_lookupByFamilyAndGame()
    {
        $familyGame = FamilyGame::lookupByFamilyAndGame($this->family, $this->game);
        $this->validateFamilyGame($familyGame, $this->family, $this->game);
    }

    public function validateFamilyGame($familyGame, $family, $game)
    {
        $this->assertTrue($familyGame->id > 0);
        $this->assertEquals($family,    $familyGame->family);
        $this->assertEquals($game,      $familyGame->game);
    }
}