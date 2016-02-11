<?php
/**
 * This file contains Model_Fields_Field
 */

/**
 * Model_Fields_FieldAvailability class
 */
class Model_Fields_FieldAvailability extends Model_Fields_Base implements SaveModelInterface {

    public $m_field;

    /**
     * @brief: Constructor
     *
     * @param $field             - Model_Fields_Field instance
     * @param $id                - unique identifier
     * @param $fieldId           - unique field identifier
     * @param string $startDate  - Day fieldAvailability becomes available
     * @param string $endDate    - Last day fieldAvailability is available
     * @param string $startTime  - Start time during the day that the fieldAvailability is available
     * @param string $endTime    - End time during the day that the fieldAvailability is available
     * @param string $daysOfWeek - Days of week field is available.  daysOfWeek[0] = Monday
     */
    public function __construct($field = NULL, $id = NULL, $fieldId = NULL, $startDate = '', $endDate = '', $startTime = '', $endTime = '', $daysOfWeek = '0111110') {
        parent::__construct('Model_Fields_FieldAvailabilityDB', Model_Base::AUTO_DECLARE_CLASS_VARIABLE_ON);

        $this->m_field = $field;
        $this->{Model_Fields_FieldAvailabilityDB::DB_COLUMN_ID}           = $id;
        $this->{Model_Fields_FieldAvailabilityDB::DB_COLUMN_FIELD_ID}     = $fieldId;
        $this->{Model_Fields_FieldAvailabilityDB::DB_COLUMN_START_DATE}   = $startDate;
        $this->{Model_Fields_FieldAvailabilityDB::DB_COLUMN_END_DATE}     = $endDate;
        $this->{Model_Fields_FieldAvailabilityDB::DB_COLUMN_START_TIME}   = $startTime;
        $this->{Model_Fields_FieldAvailabilityDB::DB_COLUMN_END_TIME}     = $endTime;
        $this->{Model_Fields_FieldAvailabilityDB::DB_COLUMN_DAYS_OF_WEEK} = $daysOfWeek;
        $this->_setField();
    }

    /**
     * @brief: destructor
     */
    public function __destruct() {
    }

    /**
     * @brief: _load will load the object from the data storage.
     *
     * @return bool - TRUE if successfully loaded model, else FALSE
     */
    public function _load() {
        /** @var Model_Fields_FieldAvailabilityDB $dbHandle */
        $dbHandle = $this->_getDBHandle();
        if (!is_null($this->{Model_Fields_FieldAvailabilityDB::DB_COLUMN_ID})) {
            $dataObj = $dbHandle->getById($this->{Model_Fields_FieldAvailabilityDB::DB_COLUMN_ID});
        } else if (!is_null($this->{Model_Fields_FieldAvailabilityDB::DB_COLUMN_FIELD_ID})) {
            $dataObj = $dbHandle->getById($this->{Model_Fields_FieldAvailabilityDB::DB_COLUMN_FIELD_ID});
        }

        if (!empty($dataObj)) {
            $this->assignModel($dataObj);
            $this->_setField();
            return TRUE;
        }

        return FALSE;
    }

    /**
     * @brief: Set the field member variable if not already set
     */
    private function _setField() {
        if (!isset($this->m_field)) {
            $this->m_field = Model_Fields_Field::LookupById($this->{Model_Fields_FieldAvailabilityDB::DB_COLUMN_FIELD_ID});
        }
    }

    /**
     * _getUpdateKeys will return the update keys
     *
     * @return array primaryKeys K => V
     */
    public function _getUpdateKeys() {
        return array(Model_Fields_FieldAvailabilityDB::DB_COLUMN_ID => $this->{Model_Fields_FieldAvailabilityDB::DB_COLUMN_ID});
    }

    /**
     * @brief: Get and instance of this object from databases data.
     *
     * @param $dataObject - data object representing the content of the object
     * @param $field - Model_Fields_Field instance
     *
     * @return Model_Fields_Field
     */
    public static function GetInstance($dataObject, $field = NULL) {
        $fieldAvailability = new Model_Fields_FieldAvailability(
            $field,
            $dataObject->{Model_Fields_FieldAvailabilityDB::DB_COLUMN_ID},
            $dataObject->{Model_Fields_FieldAvailabilityDB::DB_COLUMN_FIELD_ID},
            $dataObject->{Model_Fields_FieldAvailabilityDB::DB_COLUMN_START_DATE},
            $dataObject->{Model_Fields_FieldAvailabilityDB::DB_COLUMN_END_DATE},
            $dataObject->{Model_Fields_FieldAvailabilityDB::DB_COLUMN_START_TIME},
            $dataObject->{Model_Fields_FieldAvailabilityDB::DB_COLUMN_END_TIME},
            $dataObject->{Model_Fields_FieldAvailabilityDB::DB_COLUMN_DAYS_OF_WEEK});

        $fieldAvailability->setLoaded();

        return $fieldAvailability;
    }

    /**
     * @brief: Create a new FieldAvailability
     *
     * @param $field             - Model_Fields_Field instance
     * @param string $startDate  - Day fieldAvailability becomes available
     * @param string $endDate    - Last day fieldAvailability is available
     * @param string $startTime  - Start time during the day that the fieldAvailability is available
     * @param string $endTime    - End time during the day that the fieldAvailability is available
     * @param string $daysOfWeek - Days of week field is available.  daysOfWeek[0] = Monday
     *
     * @return Model_Fields_Field
     * @throws AssertionException
     */
    public static function Create($field, $startDate, $endDate, $startTime, $endTime, $daysOfWeek = '1111100') {
        $dbHandle = new Model_Fields_FieldAvailabilityDB();
        $dataObject = $dbHandle->create($field, $startDate, $endDate, $startTime, $endTime, $daysOfWeek);
        assertion(!empty($dataObject), "Unable to create FieldAvailability with field name:'$field->name'");

        return Model_Fields_FieldAvailability::GetInstance($dataObject, $field);
    }

    /**
     * @brief: Get Model_Fields_FieldAvailability instance for the specified FieldAvailability identifier
     *
     * @param bigint $fieldAvailabilityId: Unique FieldAvailability identifier
     *
     * @return Model_Fields_FieldAvailability
     */
    public static function LookupById($fieldAvailabilityId) {
        $dbHandle = new Model_Fields_FieldAvailabilityDB();
        $dataObject = $dbHandle->getById($fieldAvailabilityId);
        assertion(!empty($dataObject), "FieldAvailability row for id: '$fieldAvailabilityId' not found");

        return Model_Fields_FieldAvailability::GetInstance($dataObject);
    }

    /**
     * @brief: Get Model_Fields_FieldAvailability instance for the specified Field identifier
     *
     * @param $fieldId : Model_Fields_Field unique identifier
     * @param bool $assertIfNotFound - Defaults to TRUE
     *
     * @return Model_Fields_FieldAvailability
     * @throws AssertionException
     */
    public static function LookupByFieldId($fieldId, $assertIfNotFound = TRUE) {
        $dbHandle = new Model_Fields_FieldAvailabilityDB();
        $dataObject = $dbHandle->getByFieldId($fieldId);

        if ($assertIfNotFound) {
            assertion(!empty($dataObject), "FieldAvailability row for fieldId: '$fieldId' not found");
        } else if (empty($dataObject)) {
            return NULL;
        }

        return Model_Fields_FieldAvailability::GetInstance($dataObject);
    }

    /**
     * @brief Update start date, start time, end date, end time for all Model_Field_Availability instances
     *
     * @param $season
     */
    public static function UpdateForNewSeason($season) {
        $facilities = Model_Fields_Facility::LookupByLeague($season->m_league);
        foreach($facilities as $facility) {

            $fields = Model_Fields_Field::LookupByFacility($facility);
            foreach ($fields as $field) {

                $fieldAvailability = Model_Fields_FieldAvailability::LookupByFieldId($field->id, FALSE);
                if (isset($fieldAvailability)) {
                    $fieldAvailability->startDate = $season->startDate;
                    $fieldAvailability->endDate   = $season->endDate;

                    // Only update times for pre-approved facilities.  Facilities that require pre-approval
                    // need a contract with the start/end time that are more limited than our pre-approved facilities
                    if ($facility->preApproved) {
                        $fieldAvailability->startTime  = $season->startTime;
                        $fieldAvailability->endTime    = $season->endTime;
                        $fieldAvailability->daysOfWeek = $season->daysOfWeek;
                    }

                    $fieldAvailability->setModified();
                    $fieldAvailability->saveModel();
                }
            }
        }
    }

    /**
     * @brief Check to see if field is available for reserving on the specified day of week
     *
     * @param $dayOfWeekIndex - 0 for Monday
     *
     * @return TRUE if field is reservable; FALSE otherwise
     */
    public function isFieldAvailable($dayOfWeekIndex) {
        precondition($dayOfWeekIndex >= 0 and $dayOfWeekIndex <= 6, "Error, invalid dayOfWeekIndex: $dayOfWeekIndex");

        return $this->daysOfWeek[$dayOfWeekIndex] == 1;
    }

    /**
     * @brief: Delete if exists
     *
     * @param $field - Model_Fields_Field instance
     */
    public static function Delete($field) {
        $fieldAvailability = Model_Fields_FieldAvailability::LookupByFieldId($field, FALSE);
        if (isset($fieldAvailability)) {
            $fieldAvailability->_delete();
        }
    }
}