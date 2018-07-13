<?php

/**
 * Class Controller_AdminPractice_Division
 *
 * @brief Select a division to administer or create a new division
 */
class Controller_AdminPractice_Division extends Controller_AdminPractice_Base {
    public $m_name = NULL;
    public $m_enabled = NULL;
    public $m_divisionId = NULL;
    public $m_maxMinutesPerPractice = NULL;
    public $m_maxMinutesPerWeek = NULL;

    private $m_divisionUpdates = [];

    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->m_operation == View_Base::CREATE) {
                $this->m_name = $this->getPostAttribute(
                    Model_Fields_DivisionDB::DB_COLUMN_NAME,
                    '* Name required'
                );
                $this->m_maxMinutesPerPractice = $this->getPostAttribute(
                    Model_Fields_DivisionDB::DB_COLUMN_MAX_MINUTES_PER_PRACTICE,
                    '* Max Minutes Per Practice required'
                );
                $this->m_maxMinutesPerWeek = $this->getPostAttribute(
                    Model_Fields_DivisionDB::DB_COLUMN_MAX_MINUTES_PER_WEEK,
                    '* Max Minutes Per Week required'
                );
                $this->m_enabled = $this->getPostAttribute(
                    Model_Fields_DivisionDB::DB_COLUMN_ENABLED,
                    '* Enabled required',
                    TRUE,
                    TRUE
                );
            }

            if ($this->m_operation == View_Base::UPDATE) {
                $this->m_divisionUpdates = $this->getPostAttributeArray(View_Base::DIVISION_UPDATE_DATA);
            }
        }
    }

    /**
     * @brief On GET, render the page to administer divisions
     *        On POST, complete the transaction and then render the page (with error message if any)
     */
    public function process() {
        if ($this->m_missingAttributes == 0) {
            switch ($this->m_operation) {
                case View_Base::CREATE:
                    $this->_createDivision();
                    break;

                case View_Base::UPDATE:
                    $this->_updateDivision();
                    break;
            }
        }

        if ($this->m_isAuthenticated) {
            $view = new View_AdminPractice_Division($this);
        } else {
            $view = new View_AdminPractice_Home($this);
        }

        $view->displayPage();
    }

    /**
     * @brief Create Division.  If the division already exists then set the errorString.
     *        Add the created Division to the list of divisions.
     */
    private function _createDivision() {
        $division = Model_Fields_Division::LookupByName($this->m_league, $this->m_name, FALSE);
        if (!isset($division)) {
            Model_Fields_Division::Create($this->m_league, $this->m_name, $this->m_maxMinutesPerPractice, $this->m_maxMinutesPerWeek, $this->m_enabled);
            $this->m_messageString = "Division $this->m_name created";
        } else {
            $this->m_errorString = "Division '$this->m_name' already exists<br>Scroll down and update to make a change";
        }
    }

    /**
     * @brief Update Division.  Set the errorString if the Division cannot be updated.
     */
    private function _updateDivision() {
        foreach ($this->m_divisionUpdates as $divisionId => $divisionData) {
            // Error check
            $updateDivision     = Model_Fields_Division::LookupById($divisionId);
            $existingDivision   = Model_Fields_Division::LookupByName($this->m_league, $divisionData[Model_Fields_DivisionDB::DB_COLUMN_NAME], FALSE);
            if (isset($existingDivision) and $existingDivision->id != $updateDivision->id) {
                $name = $divisionData[Model_Fields_DivisionDB::DB_COLUMN_NAME];
                $this->m_errorString = "Division '$name' already exists<br>Scroll down and update to make a change";
                return;
            }

            // Update
            $updateDivision->name                   = $divisionData[Model_Fields_DivisionDB::DB_COLUMN_NAME];
            $updateDivision->maxMinutesPerPractice  = $divisionData[Model_Fields_DivisionDB::DB_COLUMN_MAX_MINUTES_PER_PRACTICE];
            $updateDivision->maxMinutesPerWeek      = $divisionData[Model_Fields_DivisionDB::DB_COLUMN_MAX_MINUTES_PER_WEEK];
            $updateDivision->enabled                = $divisionData[Model_Fields_DivisionDB::DB_COLUMN_ENABLED];;
            $updateDivision->saveModel();
        }

        $this->m_messageString = "Divisions updated";
    }
}