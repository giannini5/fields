<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\AssistantCoachOrm;
use DAG\Orm\Schedule\CoachOrm;
use DAG\Orm\Schedule\ORM_TestHelper;
use DAG\Orm\Schedule\TeamOrm;

require_once dirname(dirname(dirname(__DIR__))) . '/Orm/Schedule/tests/helper.php';



/**
 * @testSuite test Family
 */
class FamilyTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected $expectedDefaults = [];
    protected $familysToCleanup = [];
    protected $season;
    protected $team2;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->season = Season::lookupById($this->defaultSeasonOrm->id);

        $this->expectedDefaults = array(
            'phone1'        => '14156896044',
            'phone2'        => '',
        );
        
        $this->familysToCleanup[] = Family::create(
            $this->season,
            $this->expectedDefaults['phone1']);

        $this->team2 = TeamOrm::create(
            $this->defaultDivisionOrm->id,
            null,
            'team2');
    }

    protected function tearDown()
    {
        foreach ($this->familysToCleanup as $entity) {
            $entity->delete();
        }

        $this->clearDatabase();
    }

    public function test_create()
    {
        $family = $this->familysToCleanup[0];
        $this->validateFamily($family, $this->season, $this->expectedDefaults);
    }

    public function test_createFromCoachesZero()
    {
        $families = Family::createFromCoaches($this->season);
        $this->assertEquals(2, count($families));
    }

    public function test_createSamePhone1()
    {
        CoachOrm::create(
            $this->team2->id,
            null,
            'Coach2',
            'Coach2Email',
            self::$defaultCoachOrmAttributes[self::PHONE1],
            self::$defaultCoachOrmAttributes[self::PHONE2]);

        $families = Family::createFromCoaches($this->season);

        // Validate family
        $this->assertEquals(3, count($families));
        $family = Family::lookupByPhone($this->season, self::$defaultCoachOrmAttributes[self::PHONE1]);
        $this->expectedDefaults['phone1'] = self::$defaultCoachOrmAttributes[self::PHONE1];
        $this->expectedDefaults['phone2'] = self::$defaultCoachOrmAttributes[self::PHONE2];
        $this->validateFamily($family, $this->season, $this->expectedDefaults);

        // Validate Coaches added to family
        $coach1 = CoachOrm::loadByTeamId($this->defaultTeamOrm->id);
        $coach2 = CoachOrm::loadByTeamId($this->team2->id);
        $this->assertEquals($family->id, $coach1->familyId);
        $this->assertEquals($family->id, $coach2->familyId);
    }

    public function test_createSamePhone2()
    {
        CoachOrm::create(
            $this->team2->id,
            null,
            'Coach2',
            'Coach2Email',
            '',
            self::$defaultCoachOrmAttributes[self::PHONE2]);

        $families = Family::createFromCoaches($this->season);

        // Validate family
        $this->assertEquals(3, count($families));
        $family = Family::lookupByPhone($this->season, self::$defaultCoachOrmAttributes[self::PHONE2]);
        $this->expectedDefaults['phone1'] = self::$defaultCoachOrmAttributes[self::PHONE1];
        $this->expectedDefaults['phone2'] = self::$defaultCoachOrmAttributes[self::PHONE2];
        $this->validateFamily($family, $this->season, $this->expectedDefaults);

        // Validate Coaches added to family
        $coach1 = CoachOrm::loadByTeamId($this->defaultTeamOrm->id);
        $coach2 = CoachOrm::loadByTeamId($this->team2->id);
        $this->assertEquals($family->id, $coach1->familyId);
        $this->assertEquals($family->id, $coach2->familyId);
    }

    public function test_createMatchPhone1Phone2()
    {
        CoachOrm::create(
            $this->team2->id,
            null,
            'Coach2',
            'Coach2Email',
            '',
            self::$defaultCoachOrmAttributes[self::PHONE1]);

        $families = Family::createFromCoaches($this->season);

        // Validate family
        $this->assertEquals(3, count($families));
        $family = Family::lookupByPhone($this->season, self::$defaultCoachOrmAttributes[self::PHONE1]);
        $this->expectedDefaults['phone1'] = self::$defaultCoachOrmAttributes[self::PHONE1];
        $this->expectedDefaults['phone2'] = self::$defaultCoachOrmAttributes[self::PHONE2];
        $this->validateFamily($family, $this->season, $this->expectedDefaults);

        // Validate Coaches added to family
        $coach1 = CoachOrm::loadByTeamId($this->defaultTeamOrm->id);
        $coach2 = CoachOrm::loadByTeamId($this->team2->id);
        $this->assertEquals($family->id, $coach1->familyId);
        $this->assertEquals($family->id, $coach2->familyId);
    }

    public function test_createMatchPhone2Phone1()
    {
        CoachOrm::create(
            $this->team2->id,
            null,
            'Coach2',
            'Coach2Email',
            self::$defaultCoachOrmAttributes[self::PHONE2],
            '');

        $families = Family::createFromCoaches($this->season);

        // Validate family
        $this->assertEquals(3, count($families));
        $family = Family::lookupByPhone($this->season, self::$defaultCoachOrmAttributes[self::PHONE2]);
        $this->expectedDefaults['phone1'] = self::$defaultCoachOrmAttributes[self::PHONE2];
        $this->expectedDefaults['phone2'] = self::$defaultCoachOrmAttributes[self::PHONE1];
        $this->validateFamily($family, $this->season, $this->expectedDefaults);

        // Validate Coaches added to family
        $coach1 = CoachOrm::loadByTeamId($this->defaultTeamOrm->id);
        $coach2 = CoachOrm::loadByTeamId($this->team2->id);
        $this->assertEquals($family->id, $coach1->familyId);
        $this->assertEquals($family->id, $coach2->familyId);
    }

    public function test_createSamePhone1AssistantCoach()
    {
        AssistantCoachOrm::create(
            $this->team2->id,
            null,
            'AssistantCoach2',
            'AssistantCoach2Email',
            self::$defaultCoachOrmAttributes[self::PHONE1],
            self::$defaultCoachOrmAttributes[self::PHONE2]);

        $families = Family::createFromCoaches($this->season);

        // Validate family
        $this->assertEquals(3, count($families));
        $family = Family::lookupByPhone($this->season, self::$defaultCoachOrmAttributes[self::PHONE1]);
        $this->expectedDefaults['phone1'] = self::$defaultCoachOrmAttributes[self::PHONE1];
        $this->expectedDefaults['phone2'] = self::$defaultCoachOrmAttributes[self::PHONE2];
        $this->validateFamily($family, $this->season, $this->expectedDefaults);

        // Validate Coaches added to family
        $coach1 = CoachOrm::loadByTeamId($this->defaultTeamOrm->id);
        $coach2 = AssistantCoachOrm::loadByTeamIdAndName($this->team2->id, 'AssistantCoach2');
        $this->assertEquals($family->id, $coach1->familyId);
        $this->assertEquals($family->id, $coach2->familyId);
    }

    public function test_createSamePhone2AssistantCoach()
    {
        AssistantCoachOrm::create(
            $this->team2->id,
            null,
            'AssistantCoach2',
            'AssistantCoach2Email',
            '',
            self::$defaultCoachOrmAttributes[self::PHONE2]);

        $families = Family::createFromCoaches($this->season);

        // Validate family
        $this->assertEquals(3, count($families));
        $family = Family::lookupByPhone($this->season, self::$defaultCoachOrmAttributes[self::PHONE1]);
        $this->expectedDefaults['phone1'] = self::$defaultCoachOrmAttributes[self::PHONE1];
        $this->expectedDefaults['phone2'] = self::$defaultCoachOrmAttributes[self::PHONE2];
        $this->validateFamily($family, $this->season, $this->expectedDefaults);

        // Validate Coaches added to family
        $coach1 = CoachOrm::loadByTeamId($this->defaultTeamOrm->id);
        $coach2 = AssistantCoachOrm::loadByTeamIdAndName($this->team2->id, 'AssistantCoach2');
        $this->assertEquals($family->id, $coach1->familyId);
        $this->assertEquals($family->id, $coach2->familyId);
    }

    public function test_createMatchPhone1Phone2AssistantCoach()
    {
        AssistantCoachOrm::create(
            $this->team2->id,
            null,
            'AssistantCoach2',
            'AssistantCoach2Email',
            '',
            self::$defaultCoachOrmAttributes[self::PHONE1]);

        $families = Family::createFromCoaches($this->season);

        // Validate family
        $this->assertEquals(3, count($families));
        $family = Family::lookupByPhone($this->season, self::$defaultCoachOrmAttributes[self::PHONE1]);
        $this->expectedDefaults['phone1'] = self::$defaultCoachOrmAttributes[self::PHONE1];
        $this->expectedDefaults['phone2'] = self::$defaultCoachOrmAttributes[self::PHONE2];
        $this->validateFamily($family, $this->season, $this->expectedDefaults);

        // Validate Coaches added to family
        $coach1 = CoachOrm::loadByTeamId($this->defaultTeamOrm->id);
        $coach2 = AssistantCoachOrm::loadByTeamIdAndName($this->team2->id, 'AssistantCoach2');
        $this->assertEquals($family->id, $coach1->familyId);
        $this->assertEquals($family->id, $coach2->familyId);
    }

    public function test_createMatchPhone2Phone1AssistantCoach()
    {
        AssistantCoachOrm::create(
            $this->team2->id,
            null,
            'AssistantCoach2',
            'AssistantCoach2Email',
            self::$defaultCoachOrmAttributes[self::PHONE2],
            '');

        $families = Family::createFromCoaches($this->season);

        // Validate family
        $this->assertEquals(3, count($families));
        $family = Family::lookupByPhone($this->season, self::$defaultCoachOrmAttributes[self::PHONE1]);
        $this->expectedDefaults['phone1'] = self::$defaultCoachOrmAttributes[self::PHONE1];
        $this->expectedDefaults['phone2'] = self::$defaultCoachOrmAttributes[self::PHONE2];
        $this->validateFamily($family, $this->season, $this->expectedDefaults);

        // Validate Coaches added to family
        $coach1 = CoachOrm::loadByTeamId($this->defaultTeamOrm->id);
        $coach2 = AssistantCoachOrm::loadByTeamIdAndName($this->team2->id, 'AssistantCoach2');
        $this->assertEquals($family->id, $coach1->familyId);
        $this->assertEquals($family->id, $coach2->familyId);
    }

    public function test_sameFamilyForManyCoaches()
    {
        $team3 = TeamOrm::create(
            $this->defaultDivisionOrm->id,
            null,
            'team3');

        CoachOrm::create(
            $this->team2->id,
            null,
            'Coach2',
            'Coach2Email',
            self::$defaultCoachOrmAttributes[self::PHONE1],
            self::$defaultCoachOrmAttributes[self::PHONE2]);

        AssistantCoachOrm::create(
            $this->team2->id,
            null,
            'AssistantCoach2',
            'AssistantCoach2Email',
            self::$defaultCoachOrmAttributes[self::PHONE1],
            self::$defaultCoachOrmAttributes[self::PHONE2]);

        CoachOrm::create(
            $team3->id,
            null,
            'Coach3',
            'Coach3Email',
            self::$defaultCoachOrmAttributes[self::PHONE1],
            self::$defaultCoachOrmAttributes[self::PHONE2]);

        AssistantCoachOrm::create(
            $team3->id,
            null,
            'AssistantCoach3',
            'AssistantCoach3Email',
            self::$defaultCoachOrmAttributes[self::PHONE1],
            self::$defaultCoachOrmAttributes[self::PHONE2]);

        $families = Family::createFromCoaches($this->season);

        // Validate family
        $this->assertEquals(3, count($families));
        $family = Family::lookupByPhone($this->season, self::$defaultCoachOrmAttributes[self::PHONE1]);
        $this->expectedDefaults['phone1'] = self::$defaultCoachOrmAttributes[self::PHONE1];
        $this->expectedDefaults['phone2'] = self::$defaultCoachOrmAttributes[self::PHONE2];
        $this->validateFamily($family, $this->season, $this->expectedDefaults);

        // Validate Coaches added to family
        $coach1 = CoachOrm::loadByTeamId($this->defaultTeamOrm->id);
        $coach2 = CoachOrm::loadByTeamId($this->team2->id);
        $assistantCoach2 = AssistantCoachOrm::loadByTeamIdAndName($this->team2->id, 'AssistantCoach2');
        $coach3 = CoachOrm::loadByTeamId($team3->id);
        $assistantCoach3 = AssistantCoachOrm::loadByTeamIdAndName($team3->id, 'AssistantCoach3');

        $this->assertEquals($family->id, $coach1->familyId);
        $this->assertEquals($family->id, $coach2->familyId);
        $this->assertEquals($family->id, $assistantCoach2->familyId);
        $this->assertEquals($family->id, $coach3->familyId);
        $this->assertEquals($family->id, $assistantCoach3->familyId);
    }

    public function test_lookupById()
    {
        $family = Family::lookupById($this->familysToCleanup[0]->id);
        $this->validateFamily($family, $this->season, $this->expectedDefaults);
    }

    public function test_lookupByPhone()
    {
        $family = Family::lookupByPhone($this->season, $this->expectedDefaults['phone1']);
        $this->validateFamily($family, $this->season, $this->expectedDefaults);
    }

    public function test_findByPhoneGoRight()
    {
        $family = null;
        $result = Family::findByPhone($this->season, $this->expectedDefaults['phone1'], $family);

        $this->assertTrue($result, "Family::lookupByPhone failed for " . $this->expectedDefaults['phone1']);
        $this->validateFamily($family, $this->season, $this->expectedDefaults);
    }

    public function test_findByPhoneGoWrong()
    {
        $family = null;
        $result = Family::findByPhone($this->season, '7075551212', $family);
        $this->assertFalse($result, "Family::lookupByPhone succeeded unexpectedly for 7075551212");
    }

    public function test_lookupBySeason()
    {
        $families = Family::lookupBySeason($this->season);
        $this->assertTrue(count($families) == 2);
    }

    public function validateFamily($family, $season, $expectedDefaults)
    {
        $this->assertTrue($family->id > 0);
        $this->assertTrue($expectedDefaults['phone1'] == $family->phone1 or $expectedDefaults['phone2'] == $family->phone1);
        // Cannot assert $family->phone2 because it all depends on which coach is the head of household :-)
        // $this->assertTrue($expectedDefaults['phone1'] == $family->phone2 or $expectedDefaults['phone2'] == $family->phone2);
        $this->assertEquals($season,                            $family->season);
    }
}