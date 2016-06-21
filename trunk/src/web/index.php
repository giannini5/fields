<?php
ini_set('display_errors', '1');
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

require_once '../lib/autoLoader.php';


class ControllerException extends DAG_Exception
{
    public function __construct($message, $exception) {
        parent::__construct($message, 0, $exception);
    }
}


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
            case View_Base::TEST_POST_PAGE:
                $this->m_controller = new Controller_Fields_TestPost();
                break;

            case View_Base::WELCOME_PAGE:
                $this->m_controller = new Controller_Fields_Welcome();
                break;

            case View_Base::CREATE_ACCOUNT_PAGE:
                $this->m_controller = new Controller_Fields_CreateAccount();
                break;

            case View_Base::LOGIN_PAGE:
                $this->m_controller = new Controller_Fields_Login();
                break;

            case View_Base::SHOW_RESERVATION_PAGE:
                $this->m_controller = new Controller_Fields_ShowReservation();
                break;

            case View_Base::SELECT_FIELD_PAGE:
                $this->m_controller = new Controller_Fields_SelectFacility();
                break;

            case View_Base::IMAGE_PAGE:
                $this->m_controller = new Controller_Fields_Image();
                break;

            case View_Base::HELP_PAGE:
                $this->m_controller = new Controller_Fields_Help();
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

            case View_Base::ADMIN_TRANSACTION_PAGE:
                $this->m_controller = new Controller_Admin_Transaction();
                break;

            default:
                $this->m_controller = new Controller_Fields_Welcome();
                break;
        }
    }

    public function htmlFormatArray($arr) {
        $retStr = "";
        if (is_array($arr)) {
            foreach ($arr as $key=>$val) {
                if (is_array($val)){
                    $retStr .= $key . ' => ' . $this->htmlFormatArray($val) . "\n";
                }
                else {
                    $retStr .= $key . ' => ' . $val . "\n";
                }
            }
        }
        return $retStr;
    }

    public function process() {
        try {
            $this->m_controller->process();
        } catch (Exception $e) {
            $postString = $this->htmlFormatArray($_POST);
            $message = "Post data: $postString\n";
            throw new ControllerException($message, $e);
        }
    }
}

$webIndex = new Web_Index();
$webIndex->process();
