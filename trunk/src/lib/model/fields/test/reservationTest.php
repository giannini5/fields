<?php
require_once '../../../autoLoader.php';

class Model_ReservationTest extends PHPUnit_Framework_TestCase {

    public $m_league;

    public $m_facility;
    public $m_season;
    public $m_division;

    public $m_field;
    public $m_team;

    public $m_leagueName = 'Test AYSO Region 122';
    public $m_facilityName = 'Girsh Park';
    public $m_seasonName = 'Fall 2015';
    public $m_divisionName = 'U10G';

    public $m_fieldName = '1B';
    public $m_teamNumber = 14;

    public $m_startTime = '15:30:00';
    public $m_endTime = '17:00:00';

    /**
     * Prepare to run test
     */
    protected function setUp()
    {
        $m_league = NULL;
        $m_facility = NULL;
        $m_season = NULL;
        $m_division = NULL;
        $m_field = NULL;
        $m_team = NULL;

        $this->m_league = Model_Fields_League::LookupByName($this->m_leagueName, FALSE);
        if (isset($this->m_league)) {
            $this->m_facility = Model_Fields_Facility::LookupByName($this->m_league, $this->m_facilityName, FALSE);
            $this->m_season = Model_Fields_Season::LookupByName($this->m_league, $this->m_seasonName, FALSE);
            $this->m_division = Model_Fields_Division::LookupByName($this->m_league, $this->m_divisionName, FALSE);

            if (isset($this->m_facility)) {
                $this->m_field = Model_Fields_Field::LookupByName($this->m_facility, $this->m_fieldName, FALSE);
            }
            if (isset($this->m_division)) {
                $this->m_team = Model_Fields_Team::LookupByNumber($this->m_division, $this->m_teamNumber, FALSE);
            }

            if (isset($this->m_season) and isset($this->m_field) and isset($this->m_team)) {
                Model_Fields_Reservation::Delete($this->m_season, $this->m_field, $this->m_team);
            }
            if (isset($this->m_season)) {
                Model_Fields_Season::Delete($this->m_league, $this->m_seasonName);
            }
            if (isset($this->m_field)) {
                Model_Fields_Field::Delete($this->m_facility, $this->m_fieldName);
            }
            if (isset($this->m_facility)) {
                Model_Fields_Facility::Delete($this->m_league, $this->m_facilityName);
            }
            if (isset($this->m_team)) {
                Model_Fields_Team::Delete($this->m_division, $this->m_teamNumber);
            }
            if (isset($this->m_division)) {
                Model_Fields_Division::Delete($this->m_league, $this->m_divisionName);
            }

            Model_Fields_League::Delete($this->m_leagueName);
        }

        $this->m_league = Model_Fields_League::Create($this->m_leagueName);
        $this->m_facility = Model_Fields_Facility::Create($this->m_league, $this->m_facilityName,
            'address1', 'address2', 'city', 'state', 'p-code', 'country', 'contactName', 'contactEmail', 'contactPhone', 1);
        $this->m_season = Model_Fields_Season::Create($this->m_league, $this->m_seasonName, 1);
        $this->m_division = Model_Fields_Division::Create($this->m_league, $this->m_divisionName, 1);
        $this->m_field = Model_Fields_Field::Create($this->m_facility, $this->m_fieldName, 1);
        $this->m_team = Model_Fields_Team::Create($this->m_division, $this->m_teamNumber, 'Thunder');
    }

    /**
     * Default Construction
     */
    public function testStaticMethods()
    {
        // Test Create
        $reservation = Model_Fields_Reservation::Create($this->m_season, $this->m_field, $this->m_team, $this->m_startTime, $this->m_endTime);
        $id = $reservation->id;
        $this->assertEquals($reservation->seasonId, $this->m_season->id);
        $this->assertEquals($reservation->fieldId, $this->m_field->id);
        $this->assertEquals($reservation->teamId, $this->m_team->id);
        $this->assertEquals($reservation->startTime, $this->m_startTime);
        $this->assertEquals($reservation->endTime, $this->m_endTime);
        $this->assertEquals($reservation->m_season->name, $this->m_season->name);
        $this->assertEquals($reservation->m_field->name, $this->m_field->name);
        $this->assertEquals($reservation->m_team->teamNumber, $this->m_team->teamNumber);
        $this->assertTrue($reservation->isLoaded());
        $this->assertFalse($reservation->isModified());

        // Test LookupByNumber
        $reservation = Model_Fields_Reservation::LookupByTeam($this->m_season, $this->m_field, $this->m_team);
        $this->assertEquals($reservation->seasonId, $this->m_season->id);
        $this->assertEquals($reservation->fieldId, $this->m_field->id);
        $this->assertEquals($reservation->teamId, $this->m_team->id);
        $this->assertEquals($reservation->startTime, $this->m_startTime);
        $this->assertEquals($reservation->endTime, $this->m_endTime);
        $this->assertEquals($reservation->m_season->name, $this->m_season->name);
        $this->assertEquals($reservation->m_field->name, $this->m_field->name);
        $this->assertEquals($reservation->m_team->teamNumber, $this->m_team->teamNumber);
        $this->assertTrue($reservation->isLoaded());
        $this->assertFalse($reservation->isModified());

        // Test LookupById
        $reservation = Model_Fields_Reservation::LookupById($id);
        $this->assertEquals($reservation->seasonId, $this->m_season->id);
        $this->assertEquals($reservation->fieldId, $this->m_field->id);
        $this->assertEquals($reservation->teamId, $this->m_team->id);
        $this->assertEquals($reservation->startTime, $this->m_startTime);
        $this->assertEquals($reservation->endTime, $this->m_endTime);
        $this->assertEquals($reservation->m_season->name, $this->m_season->name);
        $this->assertEquals($reservation->m_field->name, $this->m_field->name);
        $this->assertEquals($reservation->m_team->teamNumber, $this->m_team->teamNumber);
        $this->assertTrue($reservation->isLoaded());
        $this->assertFalse($reservation->isModified());

        // Test modify, save and reload
        $reservation->startTime = '12:30:00';
        $reservation->setModified();
        $this->assertTrue($reservation->isModified());
        $reservation->saveModel();
        $reservation = Model_Fields_Reservation::LookupById($id);
        $this->assertEquals($reservation->startTime, '12:30:00');
        $this->assertTrue($reservation->isLoaded());
        $this->assertFalse($reservation->isModified());
    }
}