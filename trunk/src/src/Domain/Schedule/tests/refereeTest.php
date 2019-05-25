<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\ORM_TestHelper;
use DAG\Orm\Schedule\RefereeOrm;

require_once dirname(dirname(dirname(__DIR__))) . '/Orm/Schedule/tests/helper.php';



/**
 * @testSuite test Referee
 */
class RefereeTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected static $expectedDefaults = array(
        'name'                  => 'TEST Domain referee name',
        'email'                 => 'email@domain',
        'phone'                 => '12223334444',
        'badgeId'               => RefereeOrm::INTERMEDIATE,
        'maxGamesPerDay'        => 3,
        'specialInstructions'   => "Time for breakfast",
    );

    protected $refereesToCleanup = [];
    protected $season;
    protected $family;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->season = Season::lookupById($this->defaultSeasonOrm->id);
        $this->family = Family::lookupById($this->defaultFamilyOrm->id);

        $this->refereesToCleanup[] = Referee::create(
            $this->season,
            $this->family,
            self::$expectedDefaults['name'],
            self::$expectedDefaults['email'],
            self::$expectedDefaults['phone'],
            self::$expectedDefaults['badgeId'],
            self::$expectedDefaults['maxGamesPerDay'],
            self::$expectedDefaults['specialInstructions']);
    }

    protected function tearDown()
    {
        foreach ($this->refereesToCleanup as $entity) {
            $entity->delete();
        }

        $this->clearDatabase();
    }

    public function test_create()
    {
        $referee = $this->refereesToCleanup[0];
        $this->validateReferee($referee, self::$expectedDefaults);
    }

    public function test_lookupById()
    {
        $referee = Referee::lookupById($this->refereesToCleanup[0]->id);
        $this->validateReferee($referee, self::$expectedDefaults);
    }

    public function test_lookupByEmailAndName()
    {
        $referee = Referee::lookupByEmailAndName($this->season, self::$expectedDefaults['email'], self::$expectedDefaults['name']);
        $this->validateReferee($referee, self::$expectedDefaults);
    }

    public function test_lookupByEmail()
    {
        $referees = Referee::lookupByEmail($this->season, self::$expectedDefaults['email']);
        $this->assertEquals(1, count($referees));
        $this->validateReferee($referees[0], self::$expectedDefaults);
    }

    public function test_lookupByName()
    {
        $referees = Referee::lookupByName($this->season, self::$expectedDefaults['name']);
        $this->assertEquals(1, count($referees));
        $this->validateReferee($referees[0], self::$expectedDefaults);
    }

    public function test_findByEmailNameTrue()
    {
        $referee = null;
        $result = Referee::findByEmailAndName($this->season, self::$expectedDefaults['email'], self::$expectedDefaults['name'], $referee);
        $this->assertTrue($result);
        $this->validateReferee($referee, self::$expectedDefaults);
    }

    public function test_findByEmailFalse()
    {
        $referee = null;
        $result = Referee::findByEmailAndName($this->season, 'Not my email', 'Not my Referee Name', $referee);
        $this->assertFalse($result);
    }

    public function test_lookupBySeason()
    {
        $referees = Referee::lookupBySeason($this->season);
        $this->assertEquals(5, count($referees)); // default, center, assistant1, assistant2 from priming
    }

    public function test_lookupByFamily()
    {
        $referees = Referee::lookupByFamily($this->family);
        $this->assertEquals(5, count($referees)); // default, center, assistant1, assistant2 from priming
    }

    public function test_lookupByDivision()
    {
        $division = Division::lookupById($this->defaultDivisionOrm->id);
        $referees = Referee::lookupByDivisionAndType($division, Referee::ALL_REFEREES);
        $this->assertEquals(1, count($referees));
    }

    public function test_lookupByDivisionAndTeam()
    {
        $division = Division::lookupById($this->defaultDivisionOrm->id);
        $referees = Referee::lookupByDivisionAndType($division, Referee::TEAM_REFEREE);
        $this->assertEquals(1, count($referees));
    }

    public function test_lookupByDivisionAndNonTeam()
    {
        $division = Division::lookupById($this->defaultDivisionOrm->id);
        $referees = Referee::lookupByDivisionAndType($division, Referee::NON_TEAM_REFEREE);
        $this->assertEquals(0, count($referees));
    }

    public function test_set()
    {
        $referee = $this->refereesToCleanup[0];

        $expectedDefaults = [];
        $referee->name                  = $expectedDefaults['name']                 = 'dave';
        $referee->email                 = $expectedDefaults['email']                = 'dave1';
        $referee->phone                 = $expectedDefaults['phone']                = 'dave3';
        $referee->badgeId               = $expectedDefaults['badgeId']              = RefereeOrm::ADVANCED;
        $referee->maxGamesPerDay        = $expectedDefaults['maxGamesPerDay']       = 4;
        $referee->specialInstructions   = $expectedDefaults['specialInstructions']  = '';

        $this->validateReferee($referee, $expectedDefaults);
    }

    public function test_setFamilyToNull()
    {
        $referee            = $this->refereesToCleanup[0];
        $referee->family    = null;
        $this->assertFalse(isset($referee->family));
    }

    public function test_isset()
    {
        $referee = $this->refereesToCleanup[0];

        $this->assertTrue(isset($referee->name));
        $this->assertTrue(isset($referee->email));
        $this->assertTrue(isset($referee->phone));
        $this->assertTrue(isset($referee->badgeId));
        $this->assertTrue(isset($referee->maxGamesPerDay));
        $this->assertTrue(isset($referee->specialInstructions));
        $this->assertTrue(isset($referee->family));
        $this->assertTrue(isset($referee->season));
    }

    public function validateReferee($referee, $expectedDefaults)
    {
        $this->assertTrue($referee->id > 0);
        $this->assertEquals($this->season,                              $referee->season);
        $this->assertEquals($this->family,                              $referee->family);
        $this->assertEquals($expectedDefaults['name'],                  $referee->name);
        $this->assertEquals($expectedDefaults['email'],                 $referee->email);
        $this->assertEquals($expectedDefaults['phone'],                 $referee->phone);
        $this->assertEquals($expectedDefaults['badgeId'],               $referee->badgeId);
        $this->assertEquals($expectedDefaults['maxGamesPerDay'],        $referee->maxGamesPerDay);
        $this->assertEquals($expectedDefaults['specialInstructions'],   $referee->specialInstructions);
        $this->assertEquals(RefereeOrm::$badgeLevels[$expectedDefaults['badgeId']], $referee->badge);
    }
}