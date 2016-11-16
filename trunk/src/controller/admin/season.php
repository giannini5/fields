<?php

/**
 * Class Controller_Admin_Season
 *
 * @brief Select a season to administer or create a new season
 */
class Controller_Admin_Season extends Controller_Admin_Base {
    public $m_seasons = NULL;
    public $m_name = NULL;
    public $m_beginReservationsDate = NULL;
    public $m_enabled = NULL;
    public $m_seasonId = NULL;
    public $m_startDate = NULL;
    public $m_endDate = NULL;
    public $m_startTime = NULL;
    public $m_endTime = NULL;
    public $m_daysSelected = array();
    public $m_daysSelectedString = '';
    public $m_loginAllowed = NULL;
    public $m_createAllowed = NULL;

    public function __construct() {
        parent::__construct();

        $this->m_seasons = Model_Fields_Season::LookupByLeague($this->m_league);

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->m_name = $this->getPostAttribute(
                Model_Fields_SeasonDB::DB_COLUMN_NAME,
                '* Name required'
            );

            $this->m_beginReservationsDate = $this->getPostAttribute(View_Base::BEGIN_RESERVATION_DATE, null);
            $this->m_startDate = $this->getPostAttribute(View_Base::START_DATE, null);
            $this->m_endDate = $this->getPostAttribute(View_Base::END_DATE, null);
            $this->m_startTime = $this->getPostAttribute(View_Base::START_TIME, null);
            $this->m_endTime = $this->getPostAttribute(View_Base::END_TIME, null);

            $this->m_loginAllowed = $this->getPostAttribute(Model_Fields_SeasonDB::DB_COLUMN_CREATE_ALLOWED, 1, FALSE, TRUE);
            $this->m_createAllowed = $this->getPostAttribute(Model_Fields_SeasonDB::DB_COLUMN_CREATE_ALLOWED, 1, FALSE, TRUE);

            $this->m_enabled = $this->getPostAttribute(
                Model_Fields_SeasonDB::DB_COLUMN_ENABLED,
                '* Enabled required',
                TRUE,
                TRUE
            );
            $this->m_seasonId = $this->getPostAttribute(
                View_Base::SEASON_ID,
                NULL,
                FALSE
            );

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
                and !$this->m_daysSelected[View_Base::SUNDAY]) {

                $this->setErrorString('Error: At least one day must be selected');
            }

            foreach ($this->m_daysSelected as $day=>$selected) {
                $this->m_daysSelectedString .= $selected ? '1' : '0';
            }
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
                    $this->_createSeason();
                    $this->m_seasons = Model_Fields_Season::LookupByLeague($this->m_league);
                    break;

                case View_Base::UPDATE:
                    $this->_updateSeason();
                    // Reload seasons to get date format correct
                    $this->m_seasons = Model_Fields_Season::LookupByLeague($this->m_league);
                    break;
            }
        }

        if ($this->m_isAuthenticated) {
            $view = new View_Admin_Season($this);
        } else {
            $view = new View_Admin_Home($this);
        }

        $view->displayPage();
    }

    /**
     * @brief Create Season.  If the season already exists then set the errorString.
     *        If enabling season then disable all other seasons
     *        Add the created Season to the list of seasons.
     */
    private function _createSeason() {
        $season = Model_Fields_Season::LookupByName($this->m_league, $this->m_name, FALSE);
        if (!isset($season)) {
            $season = Model_Fields_Season::Create($this->m_league, $this->m_name, $this->m_beginReservationsDate, $this->m_startDate, $this->m_endDate, $this->m_startTime, $this->m_endTime, $this->m_enabled, $this->m_daysSelectedString, $this->m_loginAllowed, $this->m_createAllowed);
            $this->m_seasons[] = $season;
            if ($this->m_enabled == 1) {
                $this->_disableSeasons($season->id);
                $this->updateFieldAvailability($season);
            }
        } else {
            $this->m_errorString = "Season '$this->m_name' already exists<br>Scroll down and update to make a change";
        }
    }

    /**
     * @brief Update Season.  Set the errorString if the Season cannot be updated.
     *        If enabling season then disable all other seasons
     */
    private function _updateSeason() {
        // Error check
        foreach ($this->m_seasons as $season) {
            if ($season->name == $this->m_name and $season->id != $this->m_seasonId) {
                $this->m_errorString = "Season '$this->m_name' already exists<br>Scroll down and update to make a change";
                return;
            }
        }

        // Update
        foreach ($this->m_seasons as $season) {
            if ($season->id == $this->m_seasonId) {
                $season->name = $this->m_name;
                $season->beginReservationsDate = $this->m_beginReservationsDate;
                $season->startDate = $this->m_startDate;
                $season->endDate = $this->m_endDate;
                $season->startTime = $this->m_startTime;
                $season->endTime = $this->m_endTime;
                $season->daysOfWeek = $this->m_daysSelectedString;
                $season->loginAllowed = $this->m_loginAllowed;
                $season->createAllowed = $this->m_createAllowed;
                $season->enabled = $this->m_enabled;
                $season->saveModel();

                if ($this->m_enabled == 1) {
                    $this->_disableSeasons($season->id);
                    // If line below is run then start times for all fields are adjusted.
                    // Not desired when making simple updates to an already created Season
                    // $this->updateFieldAvailability($season);
                }
                return;
            }
        }
    }

    /**
     * @brief Disable all seasons
     *
     * @param $excludeSeasonId - Season that should be left enabled
     */
    private function _disableSeasons($excludeSeasonId) {
        foreach ($this->m_seasons as $season) {
            if ($season->id != $excludeSeasonId) {
                $season->enabled = 0;
                $season->saveModel();
            }
        }
    }

    /**
     * @brief Update Field Availability to use season defaults
     *
     * @param $excludeSeasonId - Season that should be left enabled
     */
    private function updateFieldAvailability($season) {
        Model_Fields_FieldAvailability::UpdateForNewSeason($season);
    }
}