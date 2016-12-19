<?php

namespace DAG\Orm\Schedule;

use DAG\Framework\Orm\DuplicateEntryException;

require_once 'helper.php';


/**
 * @testSuite test Facility ORM
 */
class FamilyOrmTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected static $expectedDefaults =
        [
            self::PHONE1 => '14156890644',
            self::PHONE2 => '',
        ];

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
        $familyOrm = FamilyOrm::create(
            $this->defaultSeasonOrm->id,
            self::$expectedDefaults[self::PHONE1]);

        $this->verifyExpectedAttributes($familyOrm, self::$expectedDefaults);
    }

    public function test_createWithTwoPhones()
    {
        self::$expectedDefaults[self::PHONE2] = '18058989551';

        $familyOrm = FamilyOrm::create(
            $this->defaultSeasonOrm->id,
            self::$expectedDefaults[self::PHONE1],
            self::$expectedDefaults[self::PHONE2]);

        $this->verifyExpectedAttributes($familyOrm, self::$expectedDefaults);
    }

    public function test_createWithDuplicate()
    {
        self::$expectedDefaults[self::PHONE2] = '18058989551';

        FamilyOrm::create(
            $this->defaultSeasonOrm->id,
            self::$expectedDefaults[self::PHONE1],
            self::$expectedDefaults[self::PHONE2]);

        try {
            FamilyOrm::create(
                $this->defaultSeasonOrm->id,
                self::$expectedDefaults[self::PHONE1],
                self::$expectedDefaults[self::PHONE2]);

            $this->assertTrue(false, 'Expected DuplicateEntryException');
        } catch(DuplicateEntryException $e) {
            // Good
        }
    }

    public function test_loadById()
    {
        $familyOrm = FamilyOrm::loadById($this->defaultFamilyOrm->id);
        $this->verifyExpectedAttributes($familyOrm, self::$defaultFamilyOrmAttributes);
    }

    public function test_loadByPhone()
    {
        $familyOrm = FamilyOrm::loadBySeasonIdAndPhone($this->defaultSeasonOrm->id, self::$defaultFamilyOrmAttributes[self::PHONE1]);
        $this->verifyExpectedAttributes($familyOrm, self::$defaultFamilyOrmAttributes);
    }

    private function verifyExpectedAttributes($familyOrm, $attributes)
    {
        $this->assertTrue($familyOrm->id > 0);
        $this->assertEquals($this->defaultSeasonOrm->id,    $familyOrm->seasonId);
        $this->assertEquals($attributes[self::PHONE1],      $familyOrm->phone1);
        $this->assertEquals($attributes[self::PHONE2],      $familyOrm->phone2);
    }
}