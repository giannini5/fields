<?php

use \DAG\Domain\Schedule\Division;
use \DAG\Domain\Schedule\RefereeCrew;
use \DAG\Framework\Exception\Precondition;

/**
 * Class Controller_AdminReferee_Home
 *
 * @brief Admin login and upload
 */
class Controller_AdminReferee_Home extends Controller_AdminReferee_Base {
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

            if(isset($_POST[View_Base::SUBMIT]) and ($_POST[View_Base::SUBMIT] == View_Base::UPLOAD_REFEREE_FILE)) {
                $this->m_operation = View_Base::UPLOAD_REFEREE_FILE;
            } else if(isset($_POST[View_Base::SUBMIT]) and ($_POST[View_Base::SUBMIT] == View_Base::UPLOAD_REFBYTEAM_FILE)) {
                $this->m_operation = View_Base::UPLOAD_REFBYTEAM_FILE;
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

            case View_Base::UPLOAD_REFEREE_FILE:
                $fileData = $this->_getFileData();
                if (isset($this->m_season)) {
                    $this->m_season->populateReferees($fileData);
                    $this->m_messageString = 'Operation Complete, Check out the REFEREE Tab to confirm data is correct';
                } else {
                    $this->m_errorString = 'Unable to find an enabled Season.  Click on SEASON tab first to create/enable a Season';
                }
                break;

            case View_Base::UPLOAD_REFBYTEAM_FILE:
                $fileData = $this->_getFileData();
                if (isset($this->m_season)) {
                    $this->m_season->populateRefereesByTeam($fileData);
                    $this->m_messageString = 'Operation Complete, Check out the TEAM Tab to confirm data is correct';
                } else {
                    $this->m_errorString = 'Unable to find an enabled Season.  Click on SEASON tab first to create/enable a Season';
                }
                break;

            case View_Base::GENERATE_REF_CREWS:
                $this->generateRefereeTeams();
                break;

            case View_Base::SIGN_IN:
            default:
                break;
        }

        // Display Home page with error message if login failed
        // or successful login with next steps for administration
        $view = new View_AdminReferee_Home($this);
        $view->displayPage();
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

    private function generateRefereeTeams()
    {
        $divisions = Division::lookupBySeason($this->m_season);
        RefereeCrew::generateRefereeCrews($divisions);
        $this->m_messageString = "Referee teams successfully generated - see TEAM tab for details";
    }
}