<?php
ini_set('display_errors', '1');
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

require_once '../lib/autoLoader.php';

/**
 * Class Web_Index
 *
 * @brief Controller created to process the request based on the page being requested.
 */
class Web_Index
{
    private $m_controller;
    private $m_requestPage;

    public function __construct()
    {
        $this->m_requestPage = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : self::WELCOME;

        // Strip off the URI Params if any
        $position = strpos($this->m_requestPage, "?");
        if (!is_bool($position)) {
            $data = str_split($this->m_requestPage, $position);
            $this->m_requestPage = $data[0];
        }

        switch($this->m_requestPage) {
            case View_Base::WELCOME_PAGE:
                $this->m_controller = new Controller_Fields_Welcome();
                break;

            case View_Base::LOGIN_PAGE:
                $this->m_controller = new Controller_Fields_Login();
                break;

            case View_Base::SHOW_RESERVATION_PAGE:
                $this->m_controller = new Controller_Fields_ShowReservation();
                break;

            case View_Base::SELECT_FACILITY_PAGE:
                $this->m_controller = new Controller_Fields_SelectFacility();
                break;

            case View_Base::ADMIN_HOME_PAGE:
                $this->m_controller = new Controller_Admin_Home();
                break;

            case View_Base::ADMIN_SEASON_PAGE:
                $this->m_controller = new Controller_Admin_Season();
                break;

            case View_Base::ADMIN_DIVISION_PAGE:
                $this->m_controller = new Controller_Admin_Division();
                break;

            case View_Base::ADMIN_FACILITY_PAGE:
                $this->m_controller = new Controller_Admin_Facility();
                break;

            case View_Base::ADMIN_FIELD_PAGE:
                $this->m_controller = new Controller_Admin_Field();
                break;

            case View_Base::ADMIN_LOCATION_PAGE:
                $this->m_controller = new Controller_Admin_Location();
                break;

            default:
                $this->m_controller = new Controller_Fields_Welcome();
                break;
        }
    }

    public function process() {
        $this->m_controller->process();
    }
}

$webIndex = new Web_Index();
$webIndex->process();
