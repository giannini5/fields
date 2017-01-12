<?php

use \DAG\Domain\Schedule\Division;

/**
 * Class Controller_AdminSchedules_Preview
 *
 * @brief Control point for previewing existing schedules
 */
class Controller_AdminSchedules_Preview extends Controller_AdminSchedules_Base {

    public $m_facilityId;
    public $m_divisionName;
    public $m_division;
    public $m_familyId;

    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->m_operation == View_Base::FIELD_VIEW) {
                $this->m_divisionName = $this->getPostAttributeArray(
                    View_Base::DIVISION_NAME
                );

                if ($this->m_divisionName == 'All') {
                    $divisions = Division::lookupBySeason($this->m_season);
                    foreach ($divisions as $division) {
                        $this->m_divisionNames[] = $division->name;
                    }
                    $this->m_divisionNames = array_unique($this->m_divisionNames);
                } else {
                    $this->m_divisionNames[] = $this->m_divisionName;
                }

                $this->m_facilityId = $this->getPostAttribute(
                    View_Base::FACILITY_ID,
                    null,
                    true,
                    true,
                    '* facility identifier is missing');
            }

            if ($this->m_operation == View_Base::DIVISION_VIEW or $this->m_operation == View_Base::TEAM_VIEW) {
                $this->m_divisionName = $this->getPostAttributeArray(
                    View_Base::DIVISION_NAME
                );

                if ($this->m_divisionName == 'All') {
                    $this->m_divisions = Division::lookupBySeason($this->m_season);
                } else {
                    $this->m_divisions = [];
                    $divisionNameAttributes = explode(' ', $this->m_divisionName);
                    if (count($divisionNameAttributes) == 2) {
                        $this->m_divisions[] = Division::lookupByNameAndGender($this->m_season, $divisionNameAttributes[0], $divisionNameAttributes[1]);
                    }
                }
            }

            if ($this->m_operation == View_Base::FAMILY_VIEW) {
                $this->m_familyId = $this->getPostAttribute(
                    View_Base::FAMILY_ID,
                    null,
                    true,
                    true);
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
                case View_Base::UPDATE:
                    // TODO
                    break;
            }
        }

        $view = new View_AdminSchedules_Preview($this);
        $view->displayPage();
    }
}