<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\ORM_TestHelper;

require_once dirname(dirname(dirname(__DIR__))) . '/Orm/Schedule/tests/helper.php';



/**
 * @testSuite test Player
 */
class PlayerTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected static $expectedDefaults = array(
        'name'          => 'TEST Domain player name',
        'email'         => 'TEST Domain player email',
        'phone'         => 'TEST Domain player phone',
        'number'        => '',
    );

    protected $playersToCleanup = array();
    protected $team;
    protected $family;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->team     = Team::lookupById($this->defaultTeamOrm->id);
        $this->family   = Family::lookupById($this->defaultFamilyOrm->id);

        // Clear out default player and create a new one for testing
        $player = Player::lookupById($this->defaultPlayerOrm->id);
        $player->delete();

        $this->playersToCleanup[] = Player::create(
            $this->team,
            $this->family,
            self::$expectedDefaults['name'],
            self::$expectedDefaults['email'],
            self::$expectedDefaults['phone']);
    }

    protected function tearDown()
    {
        foreach ($this->playersToCleanup as $entity) {
            $entity->delete();
        }

        $this->clearDatabase();
    }

    public function test_create()
    {
        $player = $this->playersToCleanup[0];
        $this->validatePlayer($player, $this->team, $this->family, self::$expectedDefaults);
    }

    public function test_createWithNoFamily()
    {
        $this->playersToCleanup[0]->delete();
        $this->playersToCleanup = [];

        self::$expectedDefaults['name'] = 'Player with no family';
        $player = Player::create(
            $this->team,
            null,
            self::$expectedDefaults['name'],
            self::$expectedDefaults['email'],
            self::$expectedDefaults['phone']);
        $this->playersToCleanup[] = $player;

        $this->validatePlayer($player, $this->team, null, self::$expectedDefaults);
    }

    public function test_lookupById()
    {
        $player = Player::lookupById($this->playersToCleanup[0]->id);
        $this->validatePlayer($player, $this->team, $this->family, self::$expectedDefaults);
    }

    public function test_lookupByName()
    {
        $player = Player::lookupByName($this->playersToCleanup[0]->team, $this->playersToCleanup[0]->name);
        $this->validatePlayer($player, $this->team, $this->family, self::$expectedDefaults);
    }

    public function test_lookupByTeam()
    {
        $players = Player::lookupByTeam($this->team);
        $this->assertTrue(count($players) == 1);
        $this->validatePlayer($players[0], $this->team, $this->family, self::$expectedDefaults);
    }

    public function test_lookupByFamily()
    {
        $players = Player::lookupByFamily($this->family);
        $this->assertTrue(count($players) == 1);
        $this->validatePlayer($players[0], $this->team, $this->family, self::$expectedDefaults);
    }

    public function test_setNumber()
    {
        $player = Player::lookupById($this->playersToCleanup[0]->id);
        $this->validatePlayer($player, $this->team, $this->family, self::$expectedDefaults);

        $player->number = 25;
        self::$expectedDefaults['number'] = 25;
        $this->validatePlayer($player, $this->team, $this->family, self::$expectedDefaults);

        $this->setExpectedException("PreconditionException");
        $player->number = 'invalid';
    }

    public function validatePlayer($player, $team, $family, $expectedDefaults)
    {
        $this->assertTrue($player->id > 0);
        $this->assertEquals($expectedDefaults['name'],      $player->name);
        $this->assertEquals($expectedDefaults['email'],     $player->email);
        $this->assertEquals($expectedDefaults['phone'],     $player->phone);
        $this->assertEquals($expectedDefaults['number'],    $player->number);
        $this->assertEquals($team,                          $player->team);
        $this->assertEquals($family,                        $player->family);
    }
}