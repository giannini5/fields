<?php

use \DAG\Domain\Schedule\Field;
use \DAG\Domain\Schedule\Facility;
use \DAG\Domain\Schedule\DivisionField;
use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\GameTime;
use \DAG\Domain\Schedule\GameExistsForGameTime;
use \DAG\Domain\Schedule\GameDate;

/**
 * Class Controller_Schedules_Field
 *
 * @brief Select a field to administer or create a new field
 */
class Controller_Schedules_Field extends Controller_Schedules_Base {

    public $m_name;
    public $m_enabled;
    public $m_facilityId;
    public $m_fieldId;
    public $m_fieldData     = [];
    public $m_divisionNames = [];

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
            } else if ($this->m_operation == View_Base::UPDATE
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

            if ($this->m_operation == View_Base::CREATE or $this->m_operation == View_Base::UPDATE) {
                $this->m_divisionNames = $this->getPostAttributeArray(
                    View_Base::DIVISION_NAMES
                );
            } else if ($this->m_operation == View_Base::VIEW) {
                $name = $this->getPostAttributeArray(
                    View_Base::DIVISION_NAME
                );
                if ($name == 'All') {
                    $divisions = Division::lookupBySeason($this->m_season);
                    foreach ($divisions as $division) {
                        $this->m_divisionNames[] = $division->name;
                    }
                    $this->m_divisionNames = array_unique($this->m_divisionNames);
                } else {
                    $this->m_divisionNames[] = $name;
                }
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
            }
        }

        if ($this->m_isAuthenticated) {
            $view = new View_Schedules_Field($this);
        } else {
            $view = new View_Schedules_Home($this);
        }

        $view->displayPage();
    }

    /**
     * @brief Create Field
     */
    private function _createField() {
        try {
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
        } catch (GameExistsForGameTime $e) {
            $this->m_errorString = "Games have already been set.  You must delete the Schedule before you can update fields";
        }
    }

    /**
     * @brief Update Field
     */
    private function _updateField() {
        try {
            $facility = Facility::lookupById($this->m_facilityId);

            foreach ($this->m_fieldData as $fieldId => $data) {
                $field          = Field::lookupById($fieldId);
                $field->name    = $data[View_Base::NAME];
                $field->enabled = $data[View_Base::ENABLED];

                $this->updateDivisionFields($field, $data[View_Base::DIVISION_NAMES]);
                $this->updateGameTimes($field);
            }

            $fieldName = $facility->name;
            $fieldName .= count($this->m_fieldData) == 1 ? ": $field->name" : "";
            $this->m_messageString = "'$fieldName' successfully updated.";
        } catch (GameExistsForGameTime $e) {
            $this->m_errorString = "Games have already been set.  You must delete the Schedule before you can update fields";
        }
    }

    /**
     * @brief Delete Field
     */
    private function _deleteField() {
        try {
            $facility = Facility::lookupById($this->m_facilityId);
            $field = Field::lookupById($this->m_fieldId);

            // Delete game times for field.  Exception thrown if a gameTime has an assigned field
            $field->deleteGameTimes();

            // Delete field
            $field->delete();

            $fieldName = $facility->name . ": " . $field->name;
            $this->m_messageString = "'$fieldName' successfully deleted.";
        } catch (GameExistsForGameTime $e) {
            $this->m_errorString = "Games have already been set.  You must delete the Schedule before you can delete fields";
        }
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
}