<?php

/**
 * @brief Show the all transactions (if any) ordered by reverse transaction time.
 */
class View_AdminPractice_Transactions extends View_AdminPractice_Base {
    /**
     * @brief Construct the View
     *
     * @param $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        parent::__construct(self::ADMIN_TRANSACTION_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render() {
        $facilities = $this->m_controller->getFacilities();
        $filterFacilityId = $this->m_controller->m_filterFacilityId;
        $filterDivisionId = $this->m_controller->m_filterDivisionId;
        $filterTeamId = $this->m_controller->m_filterTeamId;

        $this->_printTransactionSelectors($facilities, $filterFacilityId, $filterDivisionId, $filterTeamId);
        print "<h1>&nbsp;</h1>";

        $transactions = $this->_getTransactions($filterFacilityId, $filterDivisionId, $filterTeamId);

        $reservationCount = 0;
        $deletedCount     = 0;
        $pendingCount     = 0;
        foreach ($transactions as $transaction) {
            if ($transaction->type == Model_Fields_ReservationHistory::ADD) {
                $reservationCount += 1;
                if (!$transaction->m_field->m_facility->preApproved) {
                    $pendingCount += 1;
                }
            } else {
                $deletedCount += 1;
                $reservationCount -= 1;
                if (!$transaction->m_field->m_facility->preApproved) {
                    $pendingCount -= 1;
                }
            }
        }

        print "
            <table align='center' valign='top' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <th colspan=9 align='center' style='font-size:24px'><font color='darkblue'><b>Transaction History</b></font></th>
                </tr>
                <tr>
                    <th colspan=9 align='center'><font color='darkblue'>Reservations: $reservationCount ($pendingCount pending) Deletes: $deletedCount</font></th>
                </tr>
                <tr>
                    <th title='Date and time transaction performed'>Creation Date</th>
                    <th title='Type of transaction'>Type</th>
                    <th title='Division of Team'>Division</th>
                    <th title='Coach of Team'>Coach</th>
                    <th title='Field reserved'>Field</th>
                    <th title='Days reserved'>Days</th>
                    <th title='Times reserved'>Times</th>
                    <th title='Approved or Pending'>Status</th>
                </tr>";

        foreach ($transactions as $transaction) {
            $creationDate = $transaction->creationDate;
            $type = $transaction->type == Model_Fields_ReservationHistory::ADD ? 'Add' : 'Delete';
            $typeBgColor = $transaction->type == Model_Fields_ReservationHistory::ADD ? 'lightgreen' : 'lightsalmon';
            $division = $transaction->m_team->m_division->name . $transaction->m_team->gender;
            $coach = $transaction->m_team->m_coach->name;
            $field = $transaction->m_field->m_facility->name . ": Field " . $transaction->m_field->name;
            $days = $this->m_controller->getDaysSelectedString($transaction);
            $times = "$transaction->startTime - $transaction->endTime";
            $status = $transaction->m_field->m_facility->preApproved ? 'Approved' : 'Pending';
            $statusBgColor = $transaction->m_field->m_facility->preApproved ? 'white' : 'yellow';

                print "
                <tr>
                    <td>$creationDate</td>
                    <td bgcolor='$typeBgColor'>$type</td>
                    <td>$division</td>
                    <td>$coach</td>
                    <td>$field</td>
                    <td>$days</td>
                    <td>$times</td>
                    <td bgcolor='$statusBgColor'>$status</td>
                </tr>";
        }

        print "
            </table>";
    }

    /**
     * @brief Print the filtering selectors
     *
     * @param $facilities - List of facilities for filtering
     * @param $filterFacilityId - Show selected facility or All if none selected
     * @param $filterDivisionId - Show selected division or All if non selected
     * @param $filterTeamId - Show selected geographicArea or All if non selected
     */
    private function _printTransactionSelectors($facilities, $filterFacilityId, $filterDivisionId, $filterTeamId) {
        $sessionId = $this->m_controller->getSessionId();

        print "
            <table valign='top' align='center' width='625' border='1' cellpadding='5' cellspacing='0'>
            <tr><td>
            <table valign='top' align='center' width='625' border='0' cellpadding='5' cellspacing='0'>
            <form method='post' action='" . self::ADMIN_TRANSACTION_PAGE . $this->m_urlParams . "'>";

        $this->printFacilitySelector($facilities, $filterFacilityId);
        $this->printDivisionSelector($filterDivisionId);
        $this->printTeamSelector($filterTeamId);

        // Print Filter button and end form
        print "
            <tr>
                    <td align='left'>
                        <input style='background-color: yellow' name='" . View_Base::SUBMIT . "' type='submit' value='" . View_Base::FILTER . "'>
                        <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                    </td>
                </tr>
            </form>
            </table>
            </td></tr>
            </table>";
    }

    /**
     * @brief Return a list of transactions based on filter
     *
     * @param $filterFacilityId - Only include this facilities if filter enabled
     * @param $filterDivisionId - Only include this divisions if filter enabled
     * @param $filterTeamId - Only include this team if filter enabled
     *
     * @return array $transactions
     */
    private function _getTransactions($filterFacilityId, $filterDivisionId, $filterTeamId) {
        return $this->m_controller->getFilteredTransactions($filterFacilityId, $filterDivisionId, $filterTeamId);
    }
}