<?php

/**
 * Class Controller_Schedules_Season
 *
 * @brief Select a season to administer or create a new season
 */
class Controller_Schedules_Season extends Controller_Schedules_Base {
    public $m_seasons = NULL;
    public $m_name = NULL;
    public $m_enabled = NULL;
    public $m_seasonId = NULL;
    public $m_startDate = NULL;
    public $m_endDate = NULL;
    public $m_startTime = NULL;
    public $m_endTime = NULL;
    public $m_daysSelected = array();
    public $m_daysSelectedString = '';

    public function __construct() {
        parent::__construct();

        $this->m_seasons = \DAG\Domain\Schedule\Season::lookupByLeague($this->m_league);

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->m_name = $this->getPostAttribute(
                View_Base::NAME,
                '* Name required'
            );

            $this->m_startDate = $this->getPostAttribute(View_Base::START_DATE, null);
            $this->m_endDate = $this->getPostAttribute(View_Base::END_DATE, null);
            $this->m_startTime = $this->getPostAttribute(View_Base::START_TIME, null);
            $this->m_endTime = $this->getPostAttribute(View_Base::END_TIME, null);

            $this->m_enabled = $this->getPostAttribute(
                View_Base::ENABLED,
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
                    break;

                case View_Base::UPDATE:
                    $this->_updateSeason();
                    break;
            }
        }

        if ($this->m_isAuthenticated) {
            $view = new View_Schedules_Season($this);
        } else {
            $view = new View_Schedules_Home($this);
        }

        $view->displayPage();
    }

    /**
     * @brief Create Season.  If the season already exists then set the errorString.
     *        If enabling season then disable all other seasons
     *        Add the created Season to the list of seasons.
     */
    private function _createSeason() {
        try {
            $this->m_season = \DAG\Domain\Schedule\Season::create(
                $this->m_league,
                $this->m_name,
                $this->m_startDate,
                $this->m_endDate,
                $this->m_startTime,
                $this->m_endTime,
                $this->m_daysSelectedString,
                $this->m_enabled ? 1 : 0);

            if ($this->m_season->enabled == 1) {
                $this->_disableSeasons($this->m_season->id);
            }

            $this->m_seasons[] = $this->m_season;
        } catch (\DAG\Framework\Orm\DuplicateEntryException $e) {
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
                $season->startDate = $this->m_startDate;
                $season->endDate = $this->m_endDate;
                $season->startTime = $this->m_startTime;
                $season->endTime = $this->m_endTime;
                $season->daysOfWeek = $this->m_daysSelectedString;
                $season->enabled = $this->m_enabled;

                if ($this->m_enabled == 1) {
                    $this->_disableSeasons($season->id);
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
            }
        }
    }
}