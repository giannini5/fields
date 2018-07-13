<?php

/**
 * Class Controller_AdminPractice_Field
 *
 * @brief Select a field to administer or create a new field
 */
class Controller_AdminPractice_Field extends Controller_AdminPractice_Base {
    public $m_facilityName = '';

    private $m_name              = NULL;
    private $m_enabled           = NULL;
    private $m_facilityId        = NULL;
    private $m_selectedDivisions = [];
    private $m_startDate         = NULL;
    private $m_endDate           = NULL;
    private $m_startTime         = NULL;
    private $m_endTime           = NULL;
    private $m_daysSelected      = [];

    private $m_fieldId          = NULL;
    private $m_fieldUpdates     = [];

    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->m_operation == View_Base::CREATE) {
                $this->m_name = $this->getPostAttribute(
                    Model_Fields_FieldDB::DB_COLUMN_NAME,
                    '',
                    TRUE,
                    FALSE,
                    'Error: Name required'
                );
                $this->m_enabled = $this->getPostAttribute(
                    Model_Fields_FieldDB::DB_COLUMN_ENABLED,
                    '',
                    TRUE,
                    TRUE,
                    'Error: Enabled required'
                );
                $this->m_facilityId = $this->getPostAttribute(
                    View_Base::FACILITY_ID,
                    NULL,
                    FALSE
                );
                $this->m_selectedDivisions = $this->getPostAttributeArray(
                    View_Base::DIVISION_IDS
                );

                $this->m_startDate  = $this->getPostAttribute(View_Base::START_DATE, null);
                $this->m_endDate    = $this->getPostAttribute(View_Base::END_DATE, null);
                $this->m_startTime  = $this->getPostAttribute(View_Base::START_TIME, null);
                $this->m_endTime    = $this->getPostAttribute(View_Base::END_TIME, null);

                $this->m_daysSelected[View_Base::MONDAY]    = $this->_isDaySelected(View_Base::MONDAY);
                $this->m_daysSelected[View_Base::TUESDAY]   = $this->_isDaySelected(View_Base::TUESDAY);
                $this->m_daysSelected[View_Base::WEDNESDAY] = $this->_isDaySelected(View_Base::WEDNESDAY);
                $this->m_daysSelected[View_Base::THURSDAY]  = $this->_isDaySelected(View_Base::THURSDAY);
                $this->m_daysSelected[View_Base::FRIDAY]    = $this->_isDaySelected(View_Base::FRIDAY);
                $this->m_daysSelected[View_Base::SATURDAY]  = $this->_isDaySelected(View_Base::SATURDAY);
                $this->m_daysSelected[View_Base::SUNDAY]    = $this->_isDaySelected(View_Base::SUNDAY);

                // Verify that at least one days was selected
                if (!$this->m_daysSelected[View_Base::MONDAY]
                    and !$this->m_daysSelected[View_Base::TUESDAY]
                    and !$this->m_daysSelected[View_Base::WEDNESDAY]
                    and !$this->m_daysSelected[View_Base::THURSDAY]
                    and !$this->m_daysSelected[View_Base::FRIDAY]
                    and !$this->m_daysSelected[View_Base::SATURDAY]
                    and !$this->m_daysSelected[View_Base::SUNDAY]
                ) {

                    $this->setErrorString('Error: At least one day must be selected');
                }
            }

            if ($this->m_operation == View_Base::UPDATE) {
                $this->m_fieldUpdates = $this->getPostAttributeArray(View_Base::FIELD_UPDATE_DATA);
            }
        }
    }

    /**
     * @brief On GET, render the page to administer fields
     *        On POST, complete the transaction and then render the page (with error message if any)
     */
    public function process() {
        if ($this->m_missingAttributes == 0) {
            switch ($this->m_operation) {
                case View_Base::CREATE:
                    $this->_createField();
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
            $view = new View_AdminPractice_Field($this);
        } else {
            $view = new View_AdminPractice_Home($this);
        }

        $view->displayPage();
    }

    /**
     * @brief Create Field.  If the field already exists then set the errorString.
     *        Add the created Field to the list of fields.
     */
    private function _createField()
    {
        $facility               = Model_Fields_Facility::LookupById($this->m_facilityId);
        $this->m_facilityName   = $facility->name;
        $field                  = Model_Fields_Field::LookupByName($facility, $this->m_name, FALSE);
        if (!isset($field)) {
            $field = Model_Fields_Field::Create($facility, $this->m_name, $this->m_enabled);

            $this->_setDivisions($facility, $field, $this->m_selectedDivisions);
            $this->_setAvailability($field, $this->m_daysSelected, $this->m_startDate, $this->m_endDate, $this->m_startTime, $this->m_endTime);

            $name = $facility->name . ":" . $field->name;
            $this->m_messageString = "Field $name created";
        } else {
            $this->m_errorString = "Field '$this->m_name' already exists<br>Scroll down and update to make a change";
        }
    }

    /**
     * @brief Update Field.  Set the errorString if the Field cannot be updated.
     */
    private function _updateField()
    {
        foreach ($this->m_fieldUpdates as $fieldId => $fieldData) {
            // Error check
            $updateField            = Model_Fields_Field::LookupById($fieldId);
            $this->m_facilityName   = $updateField->m_facility->name;
            $existingField          = Model_Fields_Field::LookupByName($updateField->m_facility, $fieldData[Model_Fields_FieldDB::DB_COLUMN_NAME], FALSE);
            if (isset($existingField) and $existingField->id != $updateField->id) {
                $name = $updateField->m_facility->name . ":" . $fieldData[Model_Fields_FieldDB::DB_COLUMN_NAME];
                $this->m_errorString = "Field '$name' already exists<br>Scroll down and update to make a change";
                return;
            }

            // Update field
            $updateField->name      = $fieldData[Model_Fields_FieldDB::DB_COLUMN_NAME];
            $updateField->enabled   = $fieldData[Model_Fields_FieldDB::DB_COLUMN_ENABLED];
            $updateField->saveModel();

            // Update field availability
            $startDate     = $fieldData[View_Base::START_DATE];
            $endDate       = $fieldData[View_Base::END_DATE];
            $startTime     = $fieldData[View_Base::START_TIME];
            $endTime       = $fieldData[View_Base::END_TIME];

            $daysSelected[View_Base::MONDAY]    = isset($fieldData[View_Base::MONDAY]);
            $daysSelected[View_Base::TUESDAY]   = isset($fieldData[View_Base::TUESDAY]);
            $daysSelected[View_Base::WEDNESDAY] = isset($fieldData[View_Base::WEDNESDAY]);
            $daysSelected[View_Base::THURSDAY]  = isset($fieldData[View_Base::THURSDAY]);
            $daysSelected[View_Base::FRIDAY]    = isset($fieldData[View_Base::FRIDAY]);
            $daysSelected[View_Base::SATURDAY]  = isset($fieldData[View_Base::SATURDAY]);
            $daysSelected[View_Base::SUNDAY]    = isset($fieldData[View_Base::SUNDAY]);

            // Verify that at least one days was selected
            if (!$daysSelected[View_Base::MONDAY]
                and !$daysSelected[View_Base::TUESDAY]
                and !$daysSelected[View_Base::WEDNESDAY]
                and !$daysSelected[View_Base::THURSDAY]
                and !$daysSelected[View_Base::FRIDAY]
                and !$daysSelected[View_Base::SATURDAY]
                and !$daysSelected[View_Base::SUNDAY]
            ) {
                $name = $updateField->m_facility->name . ":" . $updateField->name;

                $this->setErrorString("Error: At least one day must be selected for field: $name");
                return;
            }

            $this->_setAvailability($updateField, $daysSelected, $startDate, $endDate, $startTime, $endTime);

            // Update divisions
            $this->_setDivisions($updateField->m_facility, $updateField, $fieldData[View_Base::DIVISION_IDS]);
        }

        $this->m_messageString = "Fields updated";
    }

    /**
     * @brief Update a field's divisions.  Delete ones that are no longer valid
     *        and add ones that are new
     *
     * @param Model_Fields_Facility $facility               - Facility instance that owns the field
     * @param Model_Fields_Field    $field                  - Field instance being updated
     * @param int[]                 $selectedDivisionIds    - Selected divisions for field
     */
    private function _setDivisions($facility, $field, $selectedDivisionIds) {
        // Delete current divisions for facility/field if not in updated list
        $currentDivisions = Model_Fields_DivisionField::GetFacilityFieldDivisions($facility->id, $field->id);
        foreach ($currentDivisions as $division) {
            if (!in_array($division->id, $selectedDivisionIds)) {
                Model_Fields_DivisionField::Delete($division->id, $facility->id, $field->id);
            }
        }

        // Create new divisions for field if they do not already exist
        foreach ($selectedDivisionIds as $divisionId) {
            $divisionField = Model_Fields_DivisionField::LookupByDivisionField($divisionId, $facility->id, $field->id);
            if (!isset($divisionField)) {
                Model_Fields_DivisionField::Create($divisionId, $facility->id, $field->id);
            }
        }
    }

    /**
     * @brief Update a field's availability.  Delete current availability (if any)
     *        and add new availability.
     *
     * @param Model_Fields_Field    $field          - Field instance being updated
     * @param []                    $daysSelected   - [day => <true | false>]
     * @param string                $startDate
     * @param string                $endDate
     * @param string                $startTime
     * @param string                $endTime
     */
    private function _setAvailability($field, $daysSelected, $startDate, $endDate, $startTime, $endTime) {
        // Verify that at least one day is selected
        $daysSelectedString = '';
        foreach ($daysSelected as $day=>$selected) {
            $daysSelectedString .= $selected ? '1' : '0';
        }

        // Delete current availability for field if different than new availability
        $currentAvailability = Model_Fields_FieldAvailability::LookupByFieldId($field->id, FALSE);
        if (isset($currentAvailability)) {
            if ($currentAvailability->startDate      == $startDate
                and $currentAvailability->endDate    == $endDate
                and $currentAvailability->startTime  == $startTime
                and $currentAvailability->endTime    == $endTime
                and $currentAvailability->daysOfWeek == $daysSelectedString) {
                // current availability is the same as new so we just return
                return;
            }

            // New availability found - delete this one first
            $currentAvailability->_delete();
        }

        // Create new availability
        Model_Fields_FieldAvailability::Create($field, $startDate, $endDate, $startTime, $endTime, $daysSelectedString);
    }

    /**
     * @brief Delete Field.
     */
    private function _deleteField() {
        $this->m_errorString = "Delete not supported - still need to implement cascading delete functionality";
        return;

        $field    = Model_Fields_Field::LookupById($this->m_fieldId);
        $field->_delete();

        // TODO: Delete Model_Fields_FieldAvailability and Model_Fields_DivisionField entries
    }
}