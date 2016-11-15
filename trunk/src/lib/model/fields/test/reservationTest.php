<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/autoload.php';
require_once 'helper.php';

class Model_ReservationTest extends Model_TestHelpers {
    public $m_startTime = '15:30:00';
    public $m_endTime = '17:00:00';
    public $m_daysOfWeek = '1010101';

    /**
     * Prepare to run test
     */
    protected function setUp()
    {
        $this->primeDatabase();
    }

    /**
     * Default Construction
     */
    public function testStaticMethods()
    {
        // Test Create
        $reservation = Model_Fields_Reservation::Create($this->m_season, $this->m_field, $this->m_team, $this->m_startTime, $this->m_endTime, $this->m_daysOfWeek);
        $id = $reservation->id;
        $this->assertEquals($reservation->seasonId, $this->m_season->id);
        $this->assertEquals($reservation->fieldId, $this->m_field->id);
        $this->assertEquals($reservation->teamId, $this->m_team->id);
        $this->assertEquals($reservation->startTime, $this->m_startTime);
        $this->assertEquals($reservation->endTime, $this->m_endTime);
        $this->assertEquals($reservation->daysOfWeek, $this->m_daysOfWeek);
        $this->assertEquals($reservation->m_season->name, $this->m_season->name);
        $this->assertEquals($reservation->m_field->name, $this->m_field->name);
        $this->assertEquals($reservation->m_team->gender, $this->m_team->gender);
        $this->assertTrue($reservation->isLoaded());
        $this->assertFalse($reservation->isModified());

        // Test LookupByTeam
        $reservations = Model_Fields_Reservation::LookupByTeam($this->m_season, $this->m_team);
        $this->assertEquals($reservations[0]->seasonId, $this->m_season->id);
        $this->assertEquals($reservations[0]->fieldId, $this->m_field->id);
        $this->assertEquals($reservations[0]->teamId, $this->m_team->id);
        $this->assertEquals($reservations[0]->startTime, $this->m_startTime);
        $this->assertEquals($reservations[0]->endTime, $this->m_endTime);
        $this->assertEquals($reservations[0]->daysOfWeek, $this->m_daysOfWeek);
        $this->assertEquals($reservations[0]->m_season->name, $this->m_season->name);
        $this->assertEquals($reservations[0]->m_field->name, $this->m_field->name);
        $this->assertEquals($reservations[0]->m_team->gender, $this->m_team->gender);
        $this->assertTrue($reservations[0]->isLoaded());
        $this->assertFalse($reservations[0]->isModified());

        // Test LookupByField
        $reservations = Model_Fields_Reservation::LookupByField($this->m_season, $this->m_field);
        $this->assertEquals($reservations[0]->seasonId, $this->m_season->id);
        $this->assertEquals($reservations[0]->fieldId, $this->m_field->id);
        $this->assertEquals($reservations[0]->teamId, $this->m_team->id);
        $this->assertEquals($reservations[0]->startTime, $this->m_startTime);
        $this->assertEquals($reservations[0]->endTime, $this->m_endTime);
        $this->assertEquals($reservations[0]->daysOfWeek, $this->m_daysOfWeek);
        $this->assertEquals($reservations[0]->m_season->name, $this->m_season->name);
        $this->assertEquals($reservations[0]->m_field->name, $this->m_field->name);
        $this->assertEquals($reservations[0]->m_team->gender, $this->m_team->gender);
        $this->assertTrue($reservations[0]->isLoaded());
        $this->assertFalse($reservations[0]->isModified());

        // Test LookupById
        $reservation = Model_Fields_Reservation::LookupById($id);
        $this->assertEquals($reservation->seasonId, $this->m_season->id);
        $this->assertEquals($reservation->fieldId, $this->m_field->id);
        $this->assertEquals($reservation->teamId, $this->m_team->id);
        $this->assertEquals($reservation->startTime, $this->m_startTime);
        $this->assertEquals($reservation->endTime, $this->m_endTime);
        $this->assertEquals($reservation->daysOfWeek, $this->m_daysOfWeek);
        $this->assertEquals($reservation->m_season->name, $this->m_season->name);
        $this->assertEquals($reservation->m_field->name, $this->m_field->name);
        $this->assertEquals($reservation->m_team->gender, $this->m_team->gender);
        $this->assertTrue($reservation->isLoaded());
        $this->assertFalse($reservation->isModified());

        // Test Overlap: no overlap, new reservation is before existing
        // $m_startTime = '15:30:00';
        // $m_endTime = '17:00:00';
        // $m_daysOfWeek = '1010101';
        $startTime = '12:30:00';
        $endTime = '15:30:00';
        $daysOfWeek = '1010101';
        $overlapReservation = Model_Fields_Reservation::getOverlapping($this->m_season, $this->m_field, $startTime, $endTime, $daysOfWeek);
        $this->assertTrue($overlapReservation == NULL);

        // Test Overlap: new reservation starts before existing and ends after existing starts
        $startTime = '12:30:00';
        $endTime = '16:30:00';
        $daysOfWeek = '1010101';
        $overlapReservation = Model_Fields_Reservation::getOverlapping($this->m_season, $this->m_field, $startTime, $endTime, $daysOfWeek);
        $this->assertTrue($overlapReservation != NULL);

        // Test Overlap: new reservation starts on existing and ends after existing starts
        $startTime = '15:30:00';
        $endTime = '17:30:00';
        $daysOfWeek = '1010101';
        $overlapReservation = Model_Fields_Reservation::getOverlapping($this->m_season, $this->m_field, $startTime, $endTime, $daysOfWeek);
        $this->assertTrue($overlapReservation != NULL);

        // Test Overlap: new reservation starts after existing but before ending
        $startTime = '15:40:00';
        $endTime = '16:40:00';
        $daysOfWeek = '1010101';
        $overlapReservation = Model_Fields_Reservation::getOverlapping($this->m_season, $this->m_field, $startTime, $endTime, $daysOfWeek);
        $this->assertTrue($overlapReservation != NULL);

        // Test Overlap: new reservation that is an exact overlap
        $startTime = '15:30:00';
        $endTime = '17:00:00';
        $daysOfWeek = '1010101';
        $overlapReservation = Model_Fields_Reservation::getOverlapping($this->m_season, $this->m_field, $startTime, $endTime, $daysOfWeek);
        $this->assertTrue($overlapReservation != NULL);

        // Test Overlap: no overlap, new reservation starts when existing ends
        $startTime = '17:00:00';
        $endTime = '18:00:00';
        $daysOfWeek = '1010101';
        $overlapReservation = Model_Fields_Reservation::getOverlapping($this->m_season, $this->m_field, $startTime, $endTime, $daysOfWeek);
        $this->assertTrue($overlapReservation == NULL);

        // Test Overlap: no overlap, same time but differnt days of week
        $startTime = '15:30:00';
        $endTime = '17:00:00';
        $daysOfWeek = '0101010';
        $overlapReservation = Model_Fields_Reservation::getOverlapping($this->m_season, $this->m_field, $startTime, $endTime, $daysOfWeek);
        $this->assertTrue($overlapReservation == NULL);

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