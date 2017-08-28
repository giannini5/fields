<?php

use \DAG\Domain\Schedule\Field;
use \DAG\Domain\Schedule\Facility;
use \DAG\Domain\Schedule\DivisionField;
use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\GameExistsForGameTime;
use \DAG\Domain\Schedule\GameDate;
use \DAG\Domain\Schedule\GameTime;
use \DAG\Domain\Schedule\Game;
use \DAG\Framework\Exception\Assertion;

/**
 * Class Controller_AdminSchedules_Field
 *
 * @brief Select a field to administer or create a new field
 */
class Controller_AdminSchedules_Field extends Controller_AdminSchedules_Base {

    public $m_name;
    public $m_enabled;
    public $m_facilityId;
    public $m_fieldId;
    public $m_gameDateId;
    public $m_fieldData     = [];
    public $m_divisionNames = [];
    public $m_divisionName;
    public $m_divisionNameSelection;
    public $m_moveGameId;
    public $m_moveFieldId;
    public $m_moveGameTime;

    public function __construct()
    {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->m_operation == View_Base::CREATE) {
                $this->m_name = $this->getPostAttribute(
                    View_Base::NAME,
                    null,
                    true,
                    false,
                    "* name required"
                );

                $this->m_enabled = $this->getPostAttribute(
                    View_Base::ENABLED,
                    '',
                    true,
                    true,
                    '* enabled required'
                );
            }

            if ($this->m_operation == View_Base::UPDATE
                or $this->m_operation == View_Base::DELETE) {
                $this->m_fieldData = $this->getPostAttribute(
                    View_Base::FIELD_UPDATE_DATA,
                    [],
                    true,
                    false);

                $this->m_fieldId = $this->getPostAttribute(
                    View_Base::FIELD_ID,
                    null,
                    true,
                    true,
                    '* field identifier is missing');
            }

            if ($this->m_operation == View_Base::UPDATE
                or $this->m_operation == View_Base::DELETE
                or $this->m_operation == View_Base::REMOVE
                or $this->m_operation == View_Base::ADD) {
                $this->m_fieldId = $this->getPostAttribute(
                    View_Base::FIELD_ID,
                    null,
                    true,
                    true,
                    '* field identifier is missing');
            }

            if ($this->m_operation == View_Base::REMOVE
                or $this->m_operation == View_Base::ADD) {
                $this->m_gameDateId = $this->getPostAttribute(
                    View_Base::GAME_DATE_ID,
                    null,
                    true,
                    true,
                    '* game date identifier is missing');
            }

            $this->m_divisionNameSelection = 'All';
            if ($this->m_operation == View_Base::CREATE) {
                $this->m_divisionNames = $this->getPostAttributeArray(
                    View_Base::DIVISION_NAMES
                );
            }

            if ($this->m_operation == View_Base::VIEW) {
                $m_divisionName = $this->getPostAttribute(View_Base::DIVISION_NAME, '');

                if ($m_divisionName == 'All') {
                    $divisions = Division::lookupBySeason($this->m_season);
                    foreach ($divisions as $division) {
                        $this->m_divisionNames[] = $division->name;
                    }
                    $this->m_divisionNames = array_unique($this->m_divisionNames);
                } else {
                    $this->m_divisionNameSelection = $m_divisionName;
                    $this->m_divisionNames[] = $m_divisionName;
                }
            }

            if ($this->m_operation == View_Base::MOVE) {
                $this->m_moveGameId = $this->getPostAttribute(
                    View_Base::GAME_ID,
                    null,
                    true,
                    true);

                $this->m_moveFieldId = $this->getPostAttribute(
                    View_Base::FIELD_ID,
                    null,
                    true,
                    true);

                $this->m_moveGameTime = $this->getPostAttribute(
                    View_Base::GAME_TIME,
                    null,
                    true);
            }

            $this->m_facilityId = $this->getPostAttribute(
                View_Base::FACILITY_ID,
                null,
                true,
                true,
                '* facility identifier is missing');
        }
    }

    /**
     * @brief On GET, render the page to administer seasons
     *        On POST, complete the transaction and then render the page (with error message if any)
     */
    public function process() {
        if ($this->m_missingAttributes == 0) {
            switch ($this->m_operation) {
                case View_Base::CREATE:
                    $this->_createField();
                    break;

                case View_Base::VIEW:
                    break;

                case View_Base::UPDATE:
                    $this->_updateField();
                    break;

                case View_Base::DELETE:
                    $this->_deleteField();
                    break;

                case View_Base::REMOVE:
                    $this->_removeGameDate();
                    break;

                case View_Base::ADD:
                    $this->_addGameDate();
                    break;

                case View_Base::MOVE:
                    $this->_moveGame();
                    break;
            }
        }

        if ($this->m_isAuthenticated) {
            $view = new View_AdminSchedules_Field($this);
        } else {
            $view = new View_AdminSchedules_Home($this);
        }

        $view->displayPage();
    }

    /**
     * @brief Create Field
     */
    private function _createField() {
        $facility = Facility::lookupById($this->m_facilityId);

        if (Field::findByName($facility, $this->m_name, $field)) {
            $this->m_errorString = "Field '$facility->name:$this->m_name' already exists<br>Scroll down and update to make a change";
        } else {
            $field = Field::create(
                $facility,
                $this->m_name,
                $this->m_enabled
            );

            $this->updateDivisionFields($field, $this->m_divisionNames);
            $this->updateGameTimes($field);

            $this->m_messageString = "'$facility->name:$field->name' successfully created.";
        }
    }

    /**
     * @brief Update Field
     */
    private function _updateField() {
        $facility   = Facility::lookupById($this->m_facilityId);
        $fieldName  = '';

        Assertion::isTrue(count($this->m_fieldData) == 1, "Update Field does not currently support updates of multiple fields at one time");

        foreach ($this->m_fieldData as $fieldId => $data) {
            $field          = Field::lookupById($fieldId);

            // Set page view attributes
            $fieldName      = $facility->name;
            $fieldName      .= count($this->m_fieldData) == 1 ? ": $field->name" : "";
            $this->m_divisionNames = $data[View_Base::DIVISION_NAMES];
            if (count($this->m_divisionNames) == 1) {
                $this->m_divisionNameSelection = $this->m_divisionNames[0];
            }

            // Update Field name
            $field->name = $data[View_Base::NAME];

            // Skip update of remaining fields if games already exist for field
            if ($field->gamesExists()) {
                $this->m_messageString = "'$fieldName' successfully updated (only name was updated).  Other attributes cannot be updated because games have been set.  You must delete the Schedule before you can update other Field attributes.";
                return;
            }

            // Update remaining fields
            $field->enabled = $data[View_Base::ENABLED];
            // $this->updateDivisionFields($field, $data[View_Base::DIVISION_NAMES]);
            // $this->updateGameTimes($field);
        }

        $this->m_messageString = "'$fieldName' successfully updated.";
    }

    /**
     * @brief Delete Field
     */
    private function _deleteField() {
        $facility = Facility::lookupById($this->m_facilityId);
        $field = Field::lookupById($this->m_fieldId);
        if ($field->gamesExists()) {
            $this->m_errorString = "Games have already been set.  You must delete the Schedule before you can delete fields";
            $this->setDivisionNames($field);
            return;
        }

        // Delete game times for field.  Exception thrown if a gameTime has an assigned game
        $field->deleteGameTimes();

        // Delete field
        $field->delete();

        $fieldName = $facility->name . ": " . $field->name;
        $this->m_messageString = "'$fieldName' successfully deleted.";
    }

    /**
     * @brief Remove gamesTimes for gameDate for field
     *
     * @throws GameExistsForGameTime
     */
    private function _removeGameDate()
    {
        $facility   = Facility::lookupById($this->m_facilityId);
        $field      = Field::lookupById($this->m_fieldId);
        $gameDate   = GameDate::lookupById($this->m_gameDateId);

        // Set the division names for page rendering
        $this->setDivisionNames($field);

        if ($field->gamesExists(null, array($gameDate))) {
            $this->m_errorString = "Games have already been set.  You must move the games before you can remove games dates";
            return;
        }

        // Delete game times for field.  Exception thrown if a gameTime has an assigned game
        $field->deleteGameTimes(true, $gameDate);

        $fieldName = $facility->name . ": " . $field->name;
        $this->m_messageString = "'$fieldName' successfully deleted.";
    }

    /**
     * @brief Add gamesTimes for gameDate for field
     */
    private function _addGameDate()
    {
        $facility   = Facility::lookupById($this->m_facilityId);
        $field      = Field::lookupById($this->m_fieldId);
        $gameDate   = GameDate::lookupById($this->m_gameDateId);
        $startTime  = $this->m_season->startTime;
        $endTime    = $this->m_season->endTime;

        // Set the division names for page rendering
        $this->setDivisionNames($field);

        GameTime::createByGameDates(array($gameDate), $field, $startTime, $endTime);

        $fieldName = $facility->name . ": " . $field->name;
        $this->m_messageString = "'$fieldName' successfully deleted.";
    }

    /**
     * Delete existing DivisionField entries and create new ones.
     *
     * @param Field     $field          - Field being updated
     * @param String[]  $divisionNames  - Name of divisions that can use the field
     */
    private function updateDivisionFields($field, $divisionNames)
    {
        // Delete existing DivisionFields
        $divisionFields = DivisionField::lookupByField($field);
        foreach ($divisionFields as $divisionField) {
            $divisionField->delete();
        }

        // Create new DivisionFields
        foreach ($divisionNames as $divisionName) {
            $divisions = Division::lookupByName($this->m_season, $divisionName);
            foreach ($divisions as $division) {
                DivisionField::create($division, $field);
            }
        }
    }

    /**
     * Populate divisionNames array for the specified field
     *
     * @param Field $field
     */
    private function setDivisionNames($field)
    {
        $this->m_divisionNameSelection = null;

        $divisionFields = DivisionField::lookupByField($field);
        foreach ($divisionFields as $divisionField) {
            $this->m_divisionNames[] = $divisionField->division->name;
            if (isset($this->m_divisionName)) {
                $this->m_divisionNameSelection = 'All';
            } else {
                $this->m_divisionNameSelection = $divisionField->division->name;
            }
        }
    }

    /**
     * Delete existing GameTimes and create new ones.  Duration between games is based on the max gameDurationMinutes
     * for the supported division
     *
     * @param Field $field - Field being updated
     *
     * @throws GameExistsForGameTime
     */
    private function updateGameTimes($field)
    {
        $this->m_season->createGameTimes($field, true);
    }

    /**
     * Attempt to move a game to new field and time
     */
    private function _moveGame()
    {
        $game                       = Game::lookupById($this->m_moveGameId);
        $field                      = Field::lookupById($this->m_moveFieldId);
        $gameTimes                  = GameTime::lookupByGameDateAndField($game->gameTime->gameDate, $field);
        $schedule                   = $game->pool->schedule;
        $divisionNameWithGender     = $schedule->division->name . " " . $schedule->division->gender;
        $this->m_divisionNames[]    = $divisionNameWithGender;

        // Set the division names for page rendering
        $this->setDivisionNames($field);

        // Find the game time and then move the field
        $this->m_errorString = "Error: Game Time: $this->m_moveGameTime not found for field";
        foreach ($gameTimes as $gameTime) {
            if ($gameTime->startTime == $this->m_moveGameTime) {
                // Verify the schedule is not already published
                if ($game->pool->schedule->published == 1) {
                    $this->m_errorString = "Error: Cannot move game because schedule has been published.";
                    break;
                }

                // Verify no game already scheduled at this time
                if (isset($gameTime->game)) {
                    $this->m_errorString = "Error: Cannot move game to $gameTime->startTime because a game is already scheduled at this time.";
                    break;
                }

                // Move game
                $game->move($gameTime);
                $this->m_messageString  = "Game $this->m_moveGameId successfully moved to new time";
                $this->m_errorString    = '';
                break;
            }
        }
    }
}