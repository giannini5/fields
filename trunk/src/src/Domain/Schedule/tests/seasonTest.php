<?php

namespace DAG\Domain\Schedule;

use DAG\Orm\Schedule\ORM_TestHelper;

require_once dirname(dirname(dirname(__DIR__))) . '/Orm/Schedule/tests/helper.php';



/**
 * @testSuite test Season
 */
class SeasonTest extends ORM_TestHelper
{
    /**
     * Expected values on create and load
     */
    protected static $expectedDefaults = array(
        'name'          => 'TEST Domain season name',
        'startDate'     => '2016-09-01',
        'endDate'       => '2016-12-01',
        'startTime'     => '08:00:00',
        'endTime'       => '17:00:00',
        'daysOfWeek'    => '1010101',
        'enabled'       => 0,
    );

    protected $seasonsToCleanup = array();
    protected $league;

    protected function setUp()
    {
        $this->primeDatabase();

        $this->league = League::lookupById($this->defaultLeagueOrm->id);

        $this->seasonsToCleanup[] = Season::create(
            $this->league,
            self::$expectedDefaults['name'],
            self::$expectedDefaults['startDate'],
            self::$expectedDefaults['endDate'],
            self::$expectedDefaults['startTime'],
            self::$expectedDefaults['endTime'],
            self::$expectedDefaults['daysOfWeek'],
            self::$expectedDefaults['enabled']);
    }

    protected function tearDown()
    {
        foreach ($this->seasonsToCleanup as $entity) {
            $entity->delete();
        }

        $this->clearDatabase();
    }

    public function test_create()
    {
        $season = $this->seasonsToCleanup[0];
        $this->validateSeason($season, $this->league, self::$expectedDefaults);
    }

    public function test_createFail()
    {
        try {
            $this->seasonsToCleanup[] = Season::create(
                $this->league,
                self::$expectedDefaults['name'],
                '2016-09-30',
                '2016-08-31',
                self::$expectedDefaults['startTime'],
                self::$expectedDefaults['endTime'],
                self::$expectedDefaults['daysOfWeek'],
                self::$expectedDefaults['enabled']);
            $this->assertTrue(false);
        } catch (\PreconditionException $e) {
            $this->assertTrue(true);
        }
    }

    public function test_lookupById()
    {
        $season = Season::lookupById($this->seasonsToCleanup[0]->id);
        $this->validateSeason($season, $this->league, self::$expectedDefaults);
    }

    public function test_lookupByName()
    {
        $season = Season::lookupByName($this->league, self::$expectedDefaults['name']);
        $this->validateSeason($season, $this->league, self::$expectedDefaults);
    }

    public function test_lookupByLeague()
    {
        $seasons = Season::lookupByLeague($this->league);
        $this->assertTrue(count($seasons) == 2);
    }

    public function test_populateDivisions()
    {
        // Setup
        $data = 'Approved,Team,Type,eAYSO Vol App,AYSO ID,Name,Phone,Cell,Email,Certifications
New,U12G-2,Coach,,,Walid Afifi,805-679-1812,805-679-1810,w-afifi@comm.ucsb.edu,"Needs training"
New,U12B-5,Coach,,,David Aguilar,805-284-2045,805-259-9680,davidoaguilar@gmail.com,"Needs training"
Yes,U6B-29,Coach,,58302620,Gerardo Aldana,805-637-0256,,soccercoachga@gmail.com,"U-6 Coach;Needs training"
New,U7G-16,Coach,,,Juan Aldana,805-448-1506,805-448-1506,jjaldana10@yahoo.com,"Needs training"
New,U6B-11,Coach,,,Gaete Alex,805-252-4711,805-358-1114,alexgranch@gmail.com,"Needs training"
New,U16/19G-2,Coach,,,Geoff Alexander,805-687-6455,818-359-4883,geoffalexander737@gmail.com,"Needs training"
New,U9B-1,Coach,,,Ken Almada,805-685-0225,805-450-9885,almada5@verizon.net,"Needs training"
New,U9B-16,Asst,,,John Anderson,805-967-0674,805-689-2964,johnanderson@andersys.com,"Needs training"';

        $season = Season::lookupById($this->defaultSeasonOrm->id);

        // Run Test
        $season->populateDivisions($data, true);

        // Validate Results
        $division = Division::lookupByNameAndGender($season, 'U12', 'Girls');
        $this->assertEquals('U12', $division->name);
        $this->assertEquals('Girls', $division->gender);
        $this->assertEquals(80, $division->displayOrder);

        $team = Team::lookupByName($division, 'U12G-02');
        $this->assertEquals('U12G-02', $team->name);

        $coach = Coach::lookupByTeam($team);
        $this->assertEquals('Walid Afifi', $coach->name);
        $this->assertEquals('805-679-1812', $coach->phone1);
        $this->assertEquals('805-679-1810', $coach->phone2);
        $this->assertEquals('w-afifi@comm.ucsb.edu', $coach->email);

        $division         = Division::lookupByNameAndGender($season, 'U9', 'Boys');
        $this->assertEquals('U9', $division->name);
        $this->assertEquals('Boys', $division->gender);
        $this->assertEquals(50, $division->displayOrder);

        $team             = Team::lookupByName($division, 'U9B-16');
        $assistantCoaches = AssistantCoach::lookupByTeam($team);
        $this->assertTrue(count($assistantCoaches) == 1);
        $this->assertEquals('John Anderson', $assistantCoaches[0]->name);
        $this->assertEquals('805-967-0674', $assistantCoaches[0]->phone1);
        $this->assertEquals('805-689-2964', $assistantCoaches[0]->phone2);
        $this->assertEquals('johnanderson@andersys.com', $assistantCoaches[0]->email);
    }

    public function test_populatePlayers()
    {
        // Setup
        $data = 'Region,Div,Team,Status,ID,Name,Phone,RegDate,PreReg,Fee
122,U8B,2,Registered,157472,Abbott; Cash,213-400-1566,2016-05-20,2016-05-20,155
122,U9B,AVAIL,Pre-Reg,158885,Abbott; Owen,618-303-2208,,2016-08-16,
122,U6B,AVAIL,Pre-Reg,158883,Abbott; Tadhg,618-303-2208,,2016-08-16,
122,U14B,7,Registered,156874,Abdullah; Muhammad,805-845-7351,2016-05-04,2016-04-25,70
122,U7B,25,Registered,154239,Abel; Ty,805-968-1215,2016-04-19,2016-04-19,155
122,U9B,15,Registered,149026,Abra-Dunbar; Robert,405-626-8567,2016-04-19,2016-04-19,155
122,U10G,17,Registered,151646,acosta; luana,805-570-8273,2016-05-23,2016-05-17,155
122,U7B,10,Registered,153284,acosta; Mateo,805-570-8273,2016-05-23,2016-05-17,155
122,U14B,2,Registered,121222,Acuna; Hector (Conner),805-898-0691,2016-06-13,2016-05-31,155
122,U7G,10,Registered,157763,Adams; Maya,805-451-2473,2016-05-30,2016-05-30,155';

        $season = Season::lookupById($this->defaultSeasonOrm->id);

        // Run Test
        $season->populatePlayers($data, true);

        // Validate Results
        $division = Division::lookupByNameAndGender($season, 'U8', 'Boys');
        $this->assertEquals('U8', $division->name);
        $this->assertEquals('Boys', $division->gender);
        $this->assertEquals(40, $division->displayOrder);

        $team = Team::lookupByName($division, 'U8B-02');
        $this->assertEquals('U8B-02', $team->name);

        $players = Player::lookupByTeam($team);
        $this->assertTrue(count($players) == 1);
        $this->assertEquals('Abbott, Cash', $players[0]->name);
        $this->assertEquals('213-400-1566', $players[0]->phone);
    }

    public function test_populateFacilities()
    {
        // Setup
        $data = 'FacilityName,Address1,Address2,City,State,ZipCode,ContactName,ContactEmail,ContactPhone,Enabled
Girsh Park,7050 Phelps Rd,,Goleta,CA,93117,Ryan Harrington,rharrington@girshpark.org,(805) 968-2773 x3,1
UCSB Rec Center,516 Ocean Road,,Santa Barbara,CA,93106,Celia Elliott,Celia.Elliott@recreation.ucsb.edu,,1';

        $season = Season::lookupById($this->defaultSeasonOrm->id);

        // Run Test
        $season->populateFacilities($data, true);

        // Validate Results
        $facilities = Facility::lookupBySeason($season);
        $this->assertEquals(3, count($facilities));

        $facility = Facility::lookupByName($season, 'Girsh Park');
        $this->assertEquals('7050 Phelps Rd', $facility->address1);
        $this->assertEquals('', $facility->address2);
        $this->assertEquals('Goleta', $facility->city);
        $this->assertEquals('CA', $facility->state);
        $this->assertEquals('93117', $facility->postalCode);
        $this->assertEquals('Ryan Harrington', $facility->contactName);
        $this->assertEquals('rharrington@girshpark.org', $facility->contactEmail);
        $this->assertEquals('(805) 968-2773 x3', $facility->contactPhone);
        $this->assertEquals(1, $facility->enabled);

        $facility = Facility::lookupByName($season, 'UCSB Rec Center');
        $this->assertEquals('516 Ocean Road', $facility->address1);
        $this->assertEquals('', $facility->address2);
        $this->assertEquals('Santa Barbara', $facility->city);
        $this->assertEquals('CA', $facility->state);
        $this->assertEquals('93106', $facility->postalCode);
        $this->assertEquals('Celia Elliott', $facility->contactName);
        $this->assertEquals('Celia.Elliott@recreation.ucsb.edu', $facility->contactEmail);
        $this->assertEquals('', $facility->contactPhone);
        $this->assertEquals(1, $facility->enabled);
    }

    public function test_populateFields()
    {
        // Setup
        $season = Season::lookupById($this->defaultSeasonOrm->id);
        Facility::create($season, 'Girsh Park', '', '', '', '', '', '', '', '', '', '', 1);
        Facility::create($season, 'UCSB Rec Center', '', '', '', '', '', '', '', '', '', '', 1);
        Division::create($season, 'U5', 'Girls', 60, 10);
        Division::create($season, 'U5', 'Boys', 60, 10);
        Division::create($season, 'U6', 'Girls', 60, 10);
        Division::create($season, 'U6', 'Boys', 60, 10);
        Division::create($season, 'U14', 'Girls', 90, 20);
        Division::create($season, 'U14', 'Boys', 90, 20);
        Division::create($season, 'U16/19', 'Girls', 90, 30);
        Division::create($season, 'U16/19', 'Boys', 90, 30);

        $data = 'FacilityName,FieldName,Enabled,DivisionsList
Girsh Park,Field A,1,U5;U6
UCSB Rec Center,Field 1,1,U14;U16/19';

        // Run Test
        $season->populateFields($data, true);

        // Validate Results
        $facility = Facility::lookupByName($season, 'Girsh Park');
        $field = Field::lookupByName($facility, 'Field A');
        $this->assertEquals('Girsh Park', $field->facility->name);
        $this->assertEquals('Field A', $field->name);
        $this->assertEquals(1, $field->enabled);

        $facility = Facility::lookupByName($season, 'UCSB Rec Center');
        $field = Field::lookupByName($facility, 'Field 1');
        $this->assertEquals('UCSB Rec Center', $field->facility->name);
        $this->assertEquals('Field 1', $field->name);
        $this->assertEquals(1, $field->enabled);

        $facility = Facility::lookupByName($season, 'Girsh Park');
        $field = Field::lookupByName($facility, 'Field A');
        $divisionFields = DivisionField::lookupByField($field);
        $this->assertEquals(4, count($divisionFields));

        $facility = Facility::lookupByName($season, 'Girsh Park');
        $field = Field::lookupByName($facility, 'Field A');
        $divisionFields = DivisionField::lookupByField($field);
        $this->assertEquals(4, count($divisionFields));

        $facility = Facility::lookupByName($season, 'UCSB Rec Center');
        $field = Field::lookupByName($facility, 'Field 1');
        $divisionFields = DivisionField::lookupByField($field);
        $this->assertEquals(4, count($divisionFields));

        $facility = Facility::lookupByName($season, 'UCSB Rec Center');
        $field = Field::lookupByName($facility, 'Field 1');
        $divisionFields = DivisionField::lookupByField($field);
        $this->assertEquals(4, count($divisionFields));
    }

    public function validateSeason($season, $league, $expectedDefaults)
    {
        $this->assertTrue($season->id > 0);
        $this->assertEquals($expectedDefaults['name'],          $season->name);
        $this->assertEquals($expectedDefaults['startDate'],     $season->startDate);
        $this->assertEquals($expectedDefaults['endDate'],       $season->endDate);
        $this->assertEquals($expectedDefaults['startTime'],     $season->startTime);
        $this->assertEquals($expectedDefaults['endTime'],       $season->endTime);
        $this->assertEquals($expectedDefaults['daysOfWeek'],    $season->daysOfWeek);
        $this->assertEquals($expectedDefaults['enabled'],       $season->enabled);
        $this->assertEquals($league,                            $season->league);
    }
}