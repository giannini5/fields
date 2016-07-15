<?php
require_once '../../../autoLoader.php';

class Model_TestHelpers extends PHPUnit_Framework_TestCase {
    public $m_league;
    public $m_season;
    public $m_division;
    public $m_coach;
    public $m_team;
    public $m_facility;
    public $m_field;
    public $m_location;

    public $m_leagueName = 'Test AYSO Region 122';
    public $m_seasonName = 'Test 2015';
    public $m_beginReservationDate = '2015-01-02 00:00:00';
    public $m_divisionName = 'U10';
    public $m_divisionMaxMinutesPerPractice = 25;
    public $m_divisionMaxMinutesPerWeek = 250;
    public $m_gender = 'B';
    public $m_email = 'dag@callwave.com';
    public $m_locationName = 'Moon';

    public $m_startDate = '2015-01-15';
    public $m_endDate = '2015-06-30';
    public $m_startTime = '15:30:00';
    public $m_endTime = '19:00:00';
    public $m_daysOfWeek = '1100110';
    public $m_loginAllowed = 1;
    public $m_createAllowed = 1;

    /**
     * Clear out all entities for the test league
     */
    protected function clearEntities()
    {
        $this->m_league = Model_Fields_League::LookupByName($this->m_leagueName, FALSE);
        if (isset($this->m_league)) {
            $this->m_league->delete();
        }
    }

    /**
     * Prime the database for unit testing
     *
     * @param bool $createLeague    - If TRUE then League created
     * @param bool $createSeason    - If TRUE then Season created
     * @param bool $createDivision  - If TRUE then Division created
     * @param bool $createCoach     - If TRUE then Coach created
     * @param bool $createTeam      - If TRUE then Team created
     * @param bool $createFacility  - If TRUE then Facility created
     * @param bool $createField     - If TRUE then Field created
     * @param bool $createLocation  - If TRUE then Location created
     */
    protected function primeDatabase($createLeague = TRUE, $createSeason = TRUE, $createDivision = TRUE, $createCoach = TRUE, $createTeam = TRUE, $createFacility = TRUE, $createField = TRUE, $createLocation = TRUE)
    {
        $this->clearEntities();

        if ($createLeague) {
            $this->m_league = Model_Fields_League::Create($this->m_leagueName);

            if ($createSeason) {
                $this->m_season = Model_Fields_Season::Create($this->m_league, $this->m_seasonName, $this->m_beginReservationDate, $this->m_startDate, $this->m_endDate, $this->m_startTime, $this->m_endTime, 1);

                if ($createDivision) {
                    $this->m_division = Model_Fields_Division::Create($this->m_league, $this->m_divisionName, $this->m_divisionMaxMinutesPerPractice, $this->m_divisionMaxMinutesPerWeek, 1);

                    if ($createCoach) {
                        $this->m_coach = Model_Fields_Coach::Create($this->m_season, $this->m_division, 'Dave', $this->m_email, '8052523944', 'hello');

                        if ($createTeam) {
                            $this->m_team = Model_Fields_Team::Create($this->m_division, $this->m_coach, $this->m_gender, 'Test Team');
                        }
                    }
                }
            }

            if ($createFacility) {
                $this->m_facility = Model_Fields_Facility::Create($this->m_league, 'Test Facility', 'addr1', 'addr2', 'Test City', 'Test State', '11111', 'USA', 'Dave', 'dag@dave.com', '8052523944', '', 1, 1);

                if ($createField) {
                    $this->m_field = Model_Fields_Field::Create($this->m_facility, 'Test Field', 1);
                }
            }

            if ($createLocation) {
                $this->m_location = Model_Fields_Location::Create($this->m_league->id, $this->m_locationName);
            }
        }
    }

    public function testDummy()
    {
        // Dummy test to make warning that there are no tests is this file go away.
        $this->assertEquals(1, 1);
    }
}