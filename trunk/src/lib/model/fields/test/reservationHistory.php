<?php
require_once '../../../autoLoader.php';
require_once 'helper.php';

class Model_ReservationHistoryTest extends Model_TestHelpers {
    public $m_startTime = '15:30:00';
    public $m_endTime = '17:00:00';
    public $m_daysOfWeek = '1010101';
    public $m_reservation;


    /**
     * Prepare to run test
     */
    protected function setUp()
    {
        $this->primeDatabase();
        $this->m_reservation = Model_Fields_Reservation::Create($this->m_season, $this->m_field, $this->m_team, $this->m_startTime, $this->m_endTime, $this->m_daysOfWeek);
    }

    /**
     * Default Construction
     */
    public function testStaticMethods()
    {
        // Test Add reservation history
        $reservationHistory = Model_Fields_ReservationHistory::Create($this->m_reservation, Model_Fields_ReservationHistory::ADD);
        $id = $reservationHistory->id;
        $this->assertEquals($reservationHistory->seasonId, $this->m_reservation->m_season->id);
        $this->assertEquals($reservationHistory->fieldId, $this->m_reservation->m_field->id);
        $this->assertEquals($reservationHistory->teamId, $this->m_reservation->m_team->id);
        $this->assertEquals($reservationHistory->coachId, $this->m_coach->id);
        $this->assertEquals($reservationHistory->startTime, $this->m_reservation->startTime);
        $this->assertEquals($reservationHistory->endTime, $this->m_reservation->endTime);
        $this->assertEquals($reservationHistory->daysOfWeek, $this->m_reservation->daysOfWeek);
        $this->assertEquals($reservationHistory->m_season->name, $this->m_season->name);
        $this->assertEquals($reservationHistory->m_field->name, $this->m_field->name);
        $this->assertEquals($reservationHistory->m_team->gender, $this->m_team->gender);
        $this->assertEquals($reservationHistory->type, Model_Fields_ReservationHistory::ADD);
        $this->assertTrue($reservationHistory->isLoaded());
        $this->assertFalse($reservationHistory->isModified());

        // Test LookupByTeam
        $reservationHistories = Model_Fields_ReservationHistory::LookupByTeam($this->m_season, $this->m_team);
        $this->assertEquals($reservationHistories[0]->seasonId, $this->m_season->id);
        $this->assertEquals($reservationHistories[0]->fieldId, $this->m_field->id);
        $this->assertEquals($reservationHistories[0]->teamId, $this->m_team->id);
        $this->assertEquals($reservationHistories[0]->coachId, $this->m_coach->id);
        $this->assertEquals($reservationHistories[0]->startTime, $this->m_startTime);
        $this->assertEquals($reservationHistories[0]->endTime, $this->m_endTime);
        $this->assertEquals($reservationHistories[0]->daysOfWeek, $this->m_daysOfWeek);
        $this->assertEquals($reservationHistories[0]->m_season->name, $this->m_season->name);
        $this->assertEquals($reservationHistories[0]->m_field->name, $this->m_field->name);
        $this->assertEquals($reservationHistories[0]->m_team->gender, $this->m_team->gender);
        $this->assertEquals($reservationHistories[0]->type, Model_Fields_ReservationHistory::ADD);
        $this->assertTrue($reservationHistories[0]->isLoaded());
        $this->assertFalse($reservationHistories[0]->isModified());

        // Test LookupByField
        $reservationHistories = Model_Fields_ReservationHistory::LookupByField($this->m_season, $this->m_field);
        $this->assertEquals($reservationHistories[0]->seasonId, $this->m_season->id);
        $this->assertEquals($reservationHistories[0]->fieldId, $this->m_field->id);
        $this->assertEquals($reservationHistories[0]->teamId, $this->m_team->id);
        $this->assertEquals($reservationHistories[0]->coachId, $this->m_coach->id);
        $this->assertEquals($reservationHistories[0]->startTime, $this->m_startTime);
        $this->assertEquals($reservationHistories[0]->endTime, $this->m_endTime);
        $this->assertEquals($reservationHistories[0]->daysOfWeek, $this->m_daysOfWeek);
        $this->assertEquals($reservationHistories[0]->m_season->name, $this->m_season->name);
        $this->assertEquals($reservationHistories[0]->m_field->name, $this->m_field->name);
        $this->assertEquals($reservationHistories[0]->m_team->gender, $this->m_team->gender);
        $this->assertEquals($reservationHistories[0]->type, Model_Fields_ReservationHistory::ADD);
        $this->assertTrue($reservationHistories[0]->isLoaded());
        $this->assertFalse($reservationHistories[0]->isModified());

        // Test LookupByCoach
        $reservationHistories = Model_Fields_ReservationHistory::LookupByCoach($this->m_season, $this->m_coach);
        $this->assertEquals($reservationHistories[0]->seasonId, $this->m_season->id);
        $this->assertEquals($reservationHistories[0]->fieldId, $this->m_field->id);
        $this->assertEquals($reservationHistories[0]->teamId, $this->m_team->id);
        $this->assertEquals($reservationHistories[0]->coachId, $this->m_coach->id);
        $this->assertEquals($reservationHistories[0]->startTime, $this->m_startTime);
        $this->assertEquals($reservationHistories[0]->endTime, $this->m_endTime);
        $this->assertEquals($reservationHistories[0]->daysOfWeek, $this->m_daysOfWeek);
        $this->assertEquals($reservationHistories[0]->m_season->name, $this->m_season->name);
        $this->assertEquals($reservationHistories[0]->m_field->name, $this->m_field->name);
        $this->assertEquals($reservationHistories[0]->m_team->gender, $this->m_team->gender);
        $this->assertEquals($reservationHistories[0]->type, Model_Fields_ReservationHistory::ADD);
        $this->assertTrue($reservationHistories[0]->isLoaded());
        $this->assertFalse($reservationHistories[0]->isModified());

        // Test LookupById
        $reservationHistory = Model_Fields_ReservationHistory::LookupById($id);
        $this->assertEquals($reservationHistory->seasonId, $this->m_season->id);
        $this->assertEquals($reservationHistory->fieldId, $this->m_field->id);
        $this->assertEquals($reservationHistory->teamId, $this->m_team->id);
        $this->assertEquals($reservationHistory->coachId, $this->m_coach->id);
        $this->assertEquals($reservationHistory->startTime, $this->m_startTime);
        $this->assertEquals($reservationHistory->endTime, $this->m_endTime);
        $this->assertEquals($reservationHistory->daysOfWeek, $this->m_daysOfWeek);
        $this->assertEquals($reservationHistory->m_season->name, $this->m_season->name);
        $this->assertEquals($reservationHistory->m_field->name, $this->m_field->name);
        $this->assertEquals($reservationHistory->m_team->gender, $this->m_team->gender);
        $this->assertEquals($reservationHistory->type, Model_Fields_ReservationHistory::ADD);
        $this->assertTrue($reservationHistory->isLoaded());
        $this->assertFalse($reservationHistory->isModified());

        // Test Add reservation history
        $reservationHistory = Model_Fields_ReservationHistory::Create($this->m_reservation, Model_Fields_ReservationHistory::DELETE);
        $id = $reservationHistory->id;
        $this->assertEquals($reservationHistory->seasonId, $this->m_reservation->m_season->id);
        $this->assertEquals($reservationHistory->fieldId, $this->m_reservation->m_field->id);
        $this->assertEquals($reservationHistory->teamId, $this->m_reservation->m_team->id);
        $this->assertEquals($reservationHistory->coachId, $this->m_coach->id);
        $this->assertEquals($reservationHistory->startTime, $this->m_reservation->startTime);
        $this->assertEquals($reservationHistory->endTime, $this->m_reservation->endTime);
        $this->assertEquals($reservationHistory->daysOfWeek, $this->m_reservation->daysOfWeek);
        $this->assertEquals($reservationHistory->m_season->name, $this->m_season->name);
        $this->assertEquals($reservationHistory->m_field->name, $this->m_field->name);
        $this->assertEquals($reservationHistory->m_team->gender, $this->m_team->gender);
        $this->assertEquals($reservationHistory->type, Model_Fields_ReservationHistory::DELETE);
        $this->assertTrue($reservationHistory->isLoaded());
        $this->assertFalse($reservationHistory->isModified());

        // Test modify, save and reload
        $reservationHistory->startTime = '12:30:00';
        $reservationHistory->setModified();
        $this->assertTrue($reservationHistory->isModified());
        $reservationHistory->saveModel();
        $reservationHistory = Model_Fields_ReservationHistory::LookupById($id);
        $this->assertEquals($reservationHistory->startTime, '12:30:00');
        $this->assertTrue($reservationHistory->isLoaded());
        $this->assertFalse($reservationHistory->isModified());
    }
}