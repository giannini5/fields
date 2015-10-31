<?php

/**
 * Class Controller_Admin_Field
 *
 * @brief Select a field to administer or create a new field
 */
class Controller_Admin_Field extends Controller_Admin_Base {
    public $m_facilities = NULL;
    public $m_facilitiesById = NULL;
    public $m_fields = NULL;
    public $m_name = NULL;
    public $m_enabled = NULL;
    public $m_facilityId = NULL;
    public $m_fieldId = NULL;
    public $m_selectedDivisions = array();
    public $m_startDate = NULL;
    public $m_endDate = NULL;
    public $m_startTime = NULL;
    public $m_endTime = NULL;

    public function __construct() {
        parent::__construct();

        $this->m_facilities = Model_Fields_Facility::LookupByLeague($this->m_league);
        $this->m_facilitiesById = array();
        $this->m_fields = array();
        foreach ($this->m_facilities as $facility) {
            $this->m_facilitiesById[$facility->id] = $facility;
            $fields = Model_Fields_Field::LookupByFacility($facility);
            $this->m_fields[$facility->id] = $fields;
        }

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->m_name = $this->getPostAttribute(
                Model_Fields_FieldDB::DB_COLUMN_NAME,
                '* Name required'
            );
            $this->m_enabled = $this->getPostAttribute(
                Model_Fields_FieldDB::DB_COLUMN_ENABLED,
                '* Enabled required',
                TRUE,
                TRUE
            );
            $this->m_facilityId = $this->getPostAttribute(
                View_Base::FACILITY_ID,
                NULL,
                FALSE
            );
            $this->m_fieldId = $this->getPostAttribute(
                View_Base::FIELD_ID,
                NULL,
                FALSE
            );
            $this->m_selectedDivisions = $this->getPostAttributeArray(
                View_Base::DIVISION_IDS
            );

            $this->m_startDate = $this->getPostAttribute(View_Base::START_DATE, null);
            $this->m_endDate = $this->getPostAttribute(View_Base::END_DATE, null);
            $this->m_startTime = $this->getPostAttribute(View_Base::START_TIME, null);
            $this->m_endTime = $this->getPostAttribute(View_Base::END_TIME, null);
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
            $view = new View_Admin_Field($this);
        } else {
            $view = new View_Admin_Home($this);
        }

        $view->displayPage();
    }

    /**
     * @brief Get the divisions for the specified field
     *
     * @param $facilityId - Model_Field_Facility identifier
     * @param $fieldId - Model_Field_Field identifier
     *
     * @return Array of Model_Field_Division objects
     */
    public function getFacilityFieldDivisions($facilityId, $fieldId) {
        return Model_Fields_DivisionField::GetFacilityFieldDivisions($facilityId, $fieldId);
    }

    /**
     * @brief Create Field.  If the field already exists then set the errorString.
     *        Add the created Field to the list of fields.
     */
    private function _createField()
    {
        $facility = Model_Fields_Facility::LookupById($this->m_facilityId);
        $field = Model_Fields_Field::LookupByName($facility, $this->m_name, FALSE);
        if (!isset($field)) {
            $field = Model_Fields_Field::Create($facility, $this->m_name, $this->m_enabled);

            $fields = $this->m_fields[$facility->id];
            $fields[] = $field;
            $this->m_fields[$facility->id] = $fields;

            $this->_setDivisions($facility, $field);
            $this->_setAvailability($field);
        } else {
            $this->m_errorString = "Field '$this->m_name' already exists<br>Scroll down and update to make a change";
        }
    }

    /**
     * @brief Update Field.  Set the errorString if the Field cannot be updated.
     */
    private function _updateField()
    {
        $facility = Model_Fields_Facility::LookupById($this->m_facilityId);

        // Error check
        $fields = $this->m_fields[$facility->id];
        foreach ($this->m_fields as $field) {
            if ($field->name == $this->m_name and $field->id != $this->m_fieldId) {
                $this->m_errorString = "Field '$facility->name: $this->m_name' already exists<br>Scroll down and update to make a change";
                return;
            }
        }

        // Update
        foreach ($fields as $field) {
            if ($field->id == $this->m_fieldId) {
                $field->name = $this->m_name;
                $field->enabled = $this->m_enabled;
                $field->saveModel();

                $this->_setDivisions($facility, $field);
                $this->_setAvailability($field);
                return;
            }
        }
    }

    /**
     * @brief Update a field's divisions.  Delete ones that are no longer valid
     *        and add ones that are new
     *
     * @param $facility - Model_Fields_Facility instance that owns the field
     * @param $field - Model_Fields_Field instance being updated
     */
    private function _setDivisions($facility, $field) {
        // Delete current divisions for facility/field if not in updated list
        $currentDivisions = Model_Fields_DivisionField::GetFacilityFieldDivisions($facility->id, $field->id);
        foreach ($currentDivisions as $division) {
            if (!in_array($division->id, $this->m_selectedDivisions)) {
                Model_Fields_DivisionField::Delete($division->id, $facility->id, $field->id);
            }
        }

        // Create new divisions for field if they do not already exist
        $currentDivisions = Model_Fields_DivisionField::GetFacilityFieldDivisions($facility->id, $field->id);
        foreach ($this->m_selectedDivisions as $divisionId) {
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
     * @param $field - Model_Fields_Field instance being updated
     */
    private function _setAvailability($field) {
        // Delete current availability for field if different than new availability
        $currentAvailability = Model_Fields_FieldAvailability::LookupByFieldId($field->id, FALSE);
        if (isset($currentAvailability)) {
            if ($currentAvailability->startDate == $this->m_startDate
                and $currentAvailability->endDate == $this->m_endDate
                and $currentAvailability->startTime == $this->m_startTime
                and $currentAvailability->endTime == $this->m_endTime) {
                // current availability is the same as new so we just return
                return;
            }

            // New availability found - delete this one first
            $currentAvailability->_delete();
        }

        // Create new availability
        Model_Fields_FieldAvailability::Create($field, $this->m_startDate, $this->m_endDate, $this->m_startTime, $this->m_endTime);
    }

    /**
     * @brief Delete Field.  Set the errorString if the Field cannot be updated.
     */
    private function _deleteField() {
        $facility = Model_Fields_Facility::LookupById($this->m_facilityId);

        // Delete
        $fields = $this->m_fields[$facility->id];
        foreach ($fields as $field) {
            if ($field->id == $this->m_fieldId) {
                $field->_delete();

                $fields = Model_Fields_Field::LookupByFacility($facility);
                $this->m_fields[$facility->id] = $fields;
                return;
            }
        }
    }
}