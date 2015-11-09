<?php

/**
 * @brief Show the current reservation (if any) for the coach/team.
 */
class View_Fields_ShowReservation extends View_Fields_Base {
    /**
     * @brief Construct he View
     *
     * @param $controller - Controller that contains data used when rendering this view.
     */
    public function __construct($controller) {
        parent::__construct(self::SHOW_RESERVATION_PAGE, $controller);
    }

    /**
     * @brief Render data for display on the page.
     */
    public function render() {
        $this->_printConfirmationMessage();

        print "
            <table align='center' valign='top' border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td align='center' style='font-size:24px'><font color='darkblue'><b>Reservations</b></font></td>
                </tr>";

        foreach ($this->m_controller->m_reservations as $reservation) {
            $field = $reservation->m_field;
            $facility = $reservation->m_field->m_facility;
            $sessionId = $this->m_controller->getSessionId();
            $fieldAvailability = Model_Fields_FieldAvailability::LookupByFieldId($reservation->m_field->id);
            $startDate = $fieldAvailability->startDate;
            $endDate = $fieldAvailability->endDate;

            print "
                <tr>
                <td>
                <table align='center' valign='top' border='0' cellpadding='5' cellspacing='0'>
                    <tr>
                        <td align='right'><font color='lightblue'>Facility:&nbsp</font></td>
                        <td align='left'>$facility->name</td>
                    </tr>
                    <tr>
                        <td align='left'>&nbsp</td>
                        <td align='left'>$facility->address1</td>
                    </tr>";

            if (!empty($facility->address2)) {
                print "
                    <tr>
                        <td align='left'>&nbsp</td>
                        <td align='left'>$facility->address2</td>
                    </tr>";
            }

            $daysSelected = $this->m_controller->getDaysSelectedString($reservation);

            print "
                    <tr>
                        <td align='left'>&nbsp</td>
                        <td align='left'>$facility->city, $facility->state, $facility->postalCode</td>
                    </tr>
                    <tr>
                        <td align='right'><font color='lightblue'>Start Date:&nbsp</font></td>
                        <td align='left'>$startDate</td>
                    </tr>
                    <tr>
                        <td align='right'><font color='lightblue'>End Date:&nbsp</font></td>
                        <td align='left'>$endDate</td>
                    </tr>
                    <tr>
                        <td align='right'><font color='lightblue'>Field:&nbsp</font></td>
                        <td align='left'>$field->name</td>
                    </tr>
                    <tr>
                        <td align='right'><font color='lightblue'>Days:&nbsp</font></td>
                        <td align='left'>$daysSelected</td>
                        <td>&nbsp</td>
                    </tr>
                    <tr>
                        <td align='right'><font color='lightblue'>Start Time:&nbsp</font></td>
                        <td align='left'>$reservation->startTime</td>
                    </tr>
                    <tr>
                        <td align='right'><font color='lightblue'>End Time:&nbsp</font></td>
                        <td align='left'>$reservation->endTime</td>
                        <td>&nbsp</td>
                    </tr>
                    <tr>
                        <form method='post' action='" . self::SHOW_RESERVATION_PAGE . $this->m_urlParams . "'>
                        <td nowrap width='100' colspan=3 align='right'>
                            <input style='background-color: yellow' name=" . self::SUBMIT . " type='submit' value='" . self::DELETE . "'>
                            <input type='hidden' id='sessionId' name='sessionId' value='$sessionId'>
                            <input type='hidden' id='" . self::RESERVATION_ID . "' name='" . self::RESERVATION_ID . "' value='$reservation->id'>
                        </td>
                        </form>
                    </tr>";

            print "
                </table>
                </td>
                </tr>";
        }

        print "
            </table>";
    }

    /**
     * @brief Print a confirmation message (no op if no message)
     */
    private function _printConfirmationMessage() {
        if (! empty($this->m_controller->m_reservationConfirmationMessage)) {
            $message = $this->m_controller->m_reservationConfirmationMessage;

            print "
            <table valign='top' align='center' width='625' border='0' cellpadding='5' cellspacing='0'>
                <tr>
                    <td><h1 align='left'><font color='green' size='4'>$message</font></h1></td>
                </tr>
            </table>";
        }
    }
}