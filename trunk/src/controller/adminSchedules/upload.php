<?php

use \DAG\Domain\Schedule\Family;
use \DAG\Framework\Exception\Precondition;

/**
 * Class Controller_AdminSchedules_Upload
 *
 * @brief Get user to login to a game schedule coordinator.
 */
class Controller_AdminSchedules_Upload extends Controller_AdminSchedules_Base {
    public function __construct() {
        parent::__construct();

        if (isset($_SERVER['REQUEST_METHOD']) and $_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->m_operation == View_Base::SUBMIT) {
                $this->m_email = $this->getPostAttribute(
                    Model_Fields_CoachDB::DB_COLUMN_EMAIL,
                    '* Email Address is required'
                );
                $this->m_password = $this->getPostAttribute(
                    Model_Fields_CoachDB::DB_COLUMN_PASSWORD,
                    '* Password is required'
                );
            }

            if(isset($_POST[View_Base::SUBMIT]) and ($_POST[View_Base::SUBMIT] == View_Base::UPLOAD_FILE)) {
                $this->m_operation = View_Base::UPLOAD_FILE;
            } else if(isset($_POST[View_Base::SUBMIT]) and ($_POST[View_Base::SUBMIT] == View_Base::UPLOAD_PLAYER_FILE)) {
                $this->m_operation = View_Base::UPLOAD_PLAYER_FILE;
            } else if(isset($_POST[View_Base::SUBMIT]) and ($_POST[View_Base::SUBMIT] == View_Base::UPLOAD_FACILITY_FILE)) {
                $this->m_operation = View_Base::UPLOAD_FACILITY_FILE;
            } else if(isset($_POST[View_Base::SUBMIT]) and ($_POST[View_Base::SUBMIT] == View_Base::UPLOAD_FIELD_FILE)) {
                $this->m_operation = View_Base::UPLOAD_FIELD_FILE;
            }
        }
    }

    /**
     * @brief On GET, render the page to ask user to Create account or Login.
     *        On POST, complete login or create account
     */
    public function process() {
        switch ($this->m_operation) {
            case View_Base::SUBMIT:
                $this->_login();
                break;

            case View_Base::SIGN_OUT:
                $this->signOut();
                break;

            case View_Base::UPLOAD_FILE:
                $fileData = $this->_getFileData();
                if (isset($this->m_season)) {
                    $this->m_season->populateDivisions($fileData);
                    Family::createFromCoaches($this->m_season);
                    $this->m_messageString = 'Operation Complete, Check out the DIVISION, TEAM and FAMILY Tabs to confirm data is correct';
                } else {
                    $this->m_errorString = 'Unable to find an enabled Season.  Click on SEASON tab first to create/enable a Season';
                }
                break;

            case View_Base::UPLOAD_PLAYER_FILE:
                $fileData = $this->_getFileData();
                if (isset($this->m_season)) {
                    $this->m_season->populatePlayers($fileData);
                    $this->m_messageString = 'Operation Complete, Check out the TEAM Tab to confirm data is correct';
                } else {
                    $this->m_errorString = 'Unable to find an enabled Season.  Click on SEASON tab first to create/enable a Season';
                }
                break;

            case View_Base::UPLOAD_FACILITY_FILE:
                $fileData = $this->_getFileData();
                if (isset($this->m_season)) {
                    $this->m_season->populateFacilities($fileData);
                    $this->m_messageString = 'Operation Complete, Check out the FACILITY Tab to confirm data is correct';
                } else {
                    $this->m_errorString = 'Unable to find an enabled Season.  Click on SEASON tab first to create/enable a Season';
                }
                break;

            case View_Base::UPLOAD_FIELD_FILE:
                $fileData = $this->_getFileData();
                if (isset($this->m_season)) {
                    $this->m_season->populateFields($fileData);
                    $this->m_messageString = 'Operation Complete, Check out the FIELD Tab to confirm data is correct';
                } else {
                    $this->m_errorString = 'Unable to find an enabled Season.  Click on SEASON tab first to create/enable a Season';
                }
                break;

            case View_Base::SIGN_IN:
            default:
                break;
        }

        // Display Home page with error message if login failed
        // or successful login with next steps for administration
        $view = new View_AdminSchedules_Home($this);
        $view->displayPage();
    }

    /**
     * @brief Login to existing account
     */
    private function _login() {
        try {
            $this->m_coordinator = \DAG\Orm\Schedule\ScheduleCoordinatorOrm::loadByLeagueIdAndEmail($this->m_league->id, $this->m_email);

            if ($this->m_coordinator->password != $this->m_password) {
                $this->_reset();
                $this->m_password = "* Incorrect password - try again";
            } else {
                $this->createSession($this->m_coordinator->id, Model_Fields_Session::SCHEDULE_COORDINATOR_USER_TYPE, 0);
            }
        } catch (\DAG\Framework\Orm\NoResultsException $e) {
            $this->_reset();
            $this->m_email = "* Incorrect email - try again";
        }
    }

    /**
     * @return string $fileData
     */
    private function _getFileData()
    {
        $fileName = $_FILES["fileToUpload"]["tmp_name"];
        // $fileType = pathinfo($fileName, PATHINFO_EXTENSION);

        $handle = fopen($fileName, "r");
        Precondition::isTrue($handle != false, "Unable to open file: $fileName");

        $fileData = '';
        $data = fread($handle, 1024);
        while ($data) {
            $fileData .= $data;
            $data = fread($handle, 1024);
        }
        fclose($handle);

        return $fileData;
    }
}