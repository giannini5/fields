<?php

/**
 * Class Controller_Admin_Transaction
 *
 * @brief On GET, render page to show all reservations transactions (Add and Delete).
 *        On POST, show filtered reservations transactions.
 */
class Controller_Admin_Transaction extends Controller_Admin_Base {
    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->m_filterFacilityId = $this->getPostAttribute(View_Base::FILTER_FACILITY_ID, 0);
            $this->m_filterDivisionId = $this->getPostAttribute(View_Base::FILTER_DIVISION_ID, 0);
            $this->m_filterTeamId = $this->getPostAttribute(View_Base::FILTER_TEAM_ID, 0);
        }
    }

    /**
     * @brief Process the GET or POST
     */
    public function process() {
        if ($this->m_isAuthenticated) {
            $view = new View_Admin_Transactions($this);
        } else {
            $view = new View_Admin_Home($this);
        }

        $view->displayPage();
    }

    /**
     * @brief Return a list of reservation transactions based on filter
     *
     * @param $filterFacilityId - Only include this facilities if filter enabled
     * @param $filterDivisionId - Only include this divisions if filter enabled
     * @param $filterTeamId - Only include this team if filter enabled
     *
     * @return array $reservations
     */
    public function getFilteredTransactions($filterFacilityId, $filterDivisionId, $filterTeamId) {
        $transactions = Model_Fields_ReservationHistory::LookupBySeason($this->m_season);
        $filteredTransactions = array();

        // if no filter set then return
        if ($filterFacilityId == 0 and $filterDivisionId == 0 and $filterTeamId == 0) {
            return $transactions;
        }

        // Conditionally apply facility, division and team filters
        if ($filterFacilityId != 0) {
            foreach ($transactions as $transaction) {
                if ($transaction->m_field->m_facility->id == $filterFacilityId) {
                    if ($filterDivisionId == 0 or $transaction->m_team->m_division->id == $filterDivisionId) {
                        if ($filterTeamId == 0 or $transaction->m_team->id == $filterTeamId) {
                            $filteredTransactions[] = $transaction;
                        }
                    }
                }
            }
            return $filteredTransactions;
        }

        // Conditionally apply division and team filters
        if ($filterDivisionId != 0) {
            foreach ($transactions as $transaction) {
                if ($transaction->m_team->m_division->id == $filterDivisionId) {
                    if ($filterTeamId == 0 or $transaction->m_team->id == $filterTeamId) {
                        $filteredTransactions[] = $transaction;
                    }
                }
            }
            return $filteredTransactions;
        }

        // Conditionally apply team filter
        if ($filterTeamId != 0) {
            foreach ($transactions as $transaction) {
                if ($transaction->m_team->id == $filterTeamId) {
                    $filteredTransactions[] = $transaction;
                }
            }
            return $filteredTransactions;
        }

        assertion(FALSE, "Error: How did we get here?");
    }
}