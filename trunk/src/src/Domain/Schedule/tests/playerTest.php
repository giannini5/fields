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
    private $expectedDefaults = array(
        'name'              => 'TEST Domain player name',
        'email'             => 'TEST Domain player email',
        'phone'             => 'TEST Domain player phone',
        'number'            => '',
        'goals'             => 0,
        'quartersSub'       => 0,
        'quartersKeep'      => 0,
        'quartersInjured'   => 0,
        'quartersAbsent'    => 0,
        'yellowCards'       => 0,
        'redCards'          => 0,
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
        // $player = Player::lookupById($this->defaultPlayerOrm->id);
        // $player->delete();

        $this->playersToCleanup[] = Player::create(
            $this->team,
            $this->family,
            $this->expectedDefaults['name'],
            $this->expectedDefaults['email'],
            $this->expectedDefaults['phone']);
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
        $this->validatePlayer($player, $this->team, $this->family, $this->expectedDefaults);
    }

    public function test_createWithNoFamily()
    {
        $this->playersToCleanup[0]->delete();
        $this->playersToCleanup = [];

        $this->expectedDefaults['name'] = 'Player with no family';
        $player = Player::create(
            $this->team,
            null,
            $this->expectedDefaults['name'],
            $this->expectedDefaults['email'],
            $this->expectedDefaults['phone']);
        $this->playersToCleanup[] = $player;

        $this->validatePlayer($player, $this->team, null, $this->expectedDefaults);
    }

    public function test_lookupById()
    {
        $player = Player::lookupById($this->playersToCleanup[0]->id);
        $this->validatePlayer($player, $this->team, $this->family, $this->expectedDefaults);
    }

    public function test_lookupByName()
    {
        $player = Player::lookupByName($this->playersToCleanup[0]->team, $this->playersToCleanup[0]->name);
        $this->validatePlayer($player, $this->team, $this->family, $this->expectedDefaults);
    }

    public function test_lookupByTeam()
    {
        $players = Player::lookupByTeam($this->team);
        $this->assertEquals(2, count($players));
        $this->validatePlayer($players[1], $this->team, $this->family, $this->expectedDefaults);
    }

    public function test_lookupByFamily()
    {
        $players = Player::lookupByFamily($this->family);
        $this->assertEquals(2, count($players));
        $this->validatePlayer($players[1], $this->team, $this->family, $this->expectedDefaults);
    }

    public function test_setNumber()
    {
        $player = Player::lookupById($this->playersToCleanup[0]->id);
        $this->validatePlayer($player, $this->team, $this->family, $this->expectedDefaults);

        $player->number = 25;
        $this->expectedDefaults['number'] = 25;
        $this->validatePlayer($player, $this->team, $this->family, $this->expectedDefaults);

        $this->setExpectedException("PreconditionException");
        $player->number = 'invalid';
    }

    public function test_setStats()
    {
        $player = Player::lookupById($this->playersToCleanup[0]->id);
        $this->validatePlayer($player, $this->team, $this->family, $this->expectedDefaults);

        $player->goals           = 10;
        $player->quartersSub     = 11;
        $player->quartersKeep    = 12;
        $player->quartersInjured = 15;
        $player->quartersAbsent  = 16;
        $player->yellowCards     = 13;
        $player->redCards        = 14;

        $this->expectedDefaults['goals']            = 10;
        $this->expectedDefaults['quartersSub']      = 11;
        $this->expectedDefaults['quartersKeep']     = 12;
        $this->expectedDefaults['yellowCards']      = 13;
        $this->expectedDefaults['redCards']         = 14;
        $this->expectedDefaults['quartersInjured']  = 15;
        $this->expectedDefaults['quartersAbsent']   = 16;

        $this->validatePlayer($player, $this->team, $this->family, $this->expectedDefaults);
    }

    public function test_setName()
    {
        $player1 = Player::lookupById($this->playersToCleanup[0]->id);
        $player2 = Player::lookupById($this->defaultPlayerOrm->id);

        $player1->name = "David Giannini";
        $player2->setName("David Giannini");

        $this->assertEquals('David Giannini', $player1->name);
        $this->assertEquals('David Giannini (2)', $player2->name);
    }

    public function validatePlayer($player, $team, $family, $expectedDefaults)
    {
        $this->assertTrue($player->id > 0);
        $this->assertEquals($expectedDefaults['name'],              $player->name);
        $this->assertEquals($expectedDefaults['email'],             $player->email);
        $this->assertEquals($expectedDefaults['phone'],             $player->phone);
        $this->assertEquals($expectedDefaults['number'],            $player->number);
        $this->assertEquals($expectedDefaults['goals'],             $player->goals);
        $this->assertEquals($expectedDefaults['quartersSub'],       $player->quartersSub);
        $this->assertEquals($expectedDefaults['quartersKeep'],      $player->quartersKeep);
        $this->assertEquals($expectedDefaults['quartersInjured'],   $player->quartersInjured);
        $this->assertEquals($expectedDefaults['quartersAbsent'],    $player->quartersAbsent);
        $this->assertEquals($expectedDefaults['yellowCards'],       $player->yellowCards);
        $this->assertEquals($expectedDefaults['redCards'],          $player->redCards);
        $this->assertEquals($team,                                  $player->team);
        $this->assertEquals($family,                                $player->family);
    }
}