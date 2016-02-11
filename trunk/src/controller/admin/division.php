<?php

/**
 * Class Controller_Admin_Division
 *
 * @brief Select a division to administer or create a new division
 */
class Controller_Admin_Division extends Controller_Admin_Base {
    public $m_divisions = NULL;
    public $m_name = NULL;
    public $m_enabled = NULL;
    public $m_divisionId = NULL;
    public $m_maxMinutesPerPractice = NULL;
    public $m_maxMinutesPerWeek = NULL;

    public function __construct() {
        parent::__construct();

        $this->m_divisions = Model_Fields_Division::GitList($this->m_league, FALSE);

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
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
            $this->m_divisionId = $this->getPostAttribute(
                View_Base::DIVISION_ID,
                NULL,
                FALSE
            );
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
            $view = new View_Admin_Division($this);
        } else {
            $view = new View_Admin_Home($this);
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
            $division = Model_Fields_Division::Create($this->m_league, $this->m_name, $this->m_maxMinutesPerPractice, $this->m_maxMinutesPerWeek, $this->m_enabled);
            $this->m_divisions[] = $division;
        } else {
            $this->m_errorString = "Division '$this->m_name' already exists<br>Scroll down and update to make a change";
        }
    }

    /**
     * @brief Update Division.  Set the errorString if the Division cannot be updated.
     */
    private function _updateDivision() {
        // Error check
        foreach ($this->m_divisions as $division) {
            if ($division->name == $this->m_name and $division->id != $this->m_divisionId) {
                $this->m_errorString = "Division '$this->m_name' already exists<br>Scroll down and update to make a change";
                return;
            }
        }

        // Update
        foreach ($this->m_divisions as $division) {
            if ($division->id == $this->m_divisionId) {
                $division->name = $this->m_name;
                $division->maxMinutesPerPractice = $this->m_maxMinutesPerPractice;
                $division->maxMinutesPerWeek = $this->m_maxMinutesPerWeek;
                $division->enabled = $this->m_enabled;
                $division->saveModel();
                return;
            }
        }
    }
}