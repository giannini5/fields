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

        switch($this->m_requestPage) {
            case View_Base::WELCOME_PAGE:
                $this->m_controller = new Controller_Welcome();
                break;

            case View_Base::LOGIN_PAGE:
                $this->m_controller = new Controller_Login();
                break;

            case View_Base::SHOW_RESERVATION_PAGE:
                $this->m_controller = new Controller_ShowReservation();
                break;

            case View_Base::SELECT_FACILITY_PAGE:
                $this->m_controller = new Controller_SelectFacility();
                break;

            case View_Base::SELECT_DAY_TIME_PAGE:
                $this->m_controller = new Controller_SelectDayTime();
                break;

            default:
                $this->m_controller = new Controller_Welcome();
                break;
        }
    }

    public function process() {
        $this->m_controller->process();
    }
}

$webIndex = new Web_Index();
$webIndex->process();
