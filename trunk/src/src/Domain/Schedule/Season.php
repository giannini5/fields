<?php

namespace DAG\Domain\Schedule;

use DAG\Domain\Domain;
use DAG\Framework\Exception\Assertion;
use DAG\Orm\Schedule\SeasonOrm;
use DAG\Framework\Exception\Precondition;


/**
 * @property int    $id
 * @property League $league
 * @property string $name
 * @property string $startDate
 * @property string $endDate
 * @property string $startTime
 * @property string $endTime
 * @property string $daysOfWeek
 * @property int    $enabled
 */
class Season extends Domain
{
    public static $phoneNumbersToSkip = ['000-000-0000', '555-555-5555', '805-111-1111', ''];

    /** @var SeasonOrm */
    private $seasonOrm;

    /** @var League */
    private $league;

    /**
     * @param SeasonOrm $seasonOrm
     * @param League    $league (defaults to null)
     */
    protected function __construct(SeasonOrm $seasonOrm, $league = null)
    {
        $this->seasonOrm = $seasonOrm;
        $this->league = isset($league) ? $league : League::lookupById($seasonOrm->leagueId);
    }

    /**
     * @param League $league
     * @param string $name
     * @param string $startDate (SQL Format)
     * @param string $endDate (SQL Format)
     * @param string $startTime (SQL Format)
     * @param string $endTime (SQL Format)
     * @param string $daysOfWeek defaults to "0000011" (Saturday, Sunday only)
     * @param int    $enabled (defaults to 1)
     *
     * @return Season
     */
    public static function create(
        $league,
        $name,
        $startDate,
        $endDate,
        $startTime,
        $endTime,
        $daysOfWeek = "0000011",
        $enabled = 1)
    {
        Precondition::isTrue($startDate < $endDate, "StartDate ($startDate) must be less than EndDate ($endDate)");

        $seasonOrm = SeasonOrm::create($league->id, $name, $startDate, $endDate, $startTime, $endTime, $daysOfWeek, $enabled);
        return new static($seasonOrm, $league);
    }

    /**
     * @param int $seasonId
     *
     * @return Season
     */
    public static function lookupById($seasonId)
    {
        $seasonOrm = SeasonOrm::loadById($seasonId);
        return new static($seasonOrm);
    }

    /**
     * @param League $league
     * @param string $name
     *
     * @return Season
     */
    public static function lookupByName($league, $name)
    {
        Precondition::isNonEmpty($name, 'name should not be empty');
        $seasonOrm = SeasonOrm::loadByLeagueIdAndName($league->id, $name);
        return new static($seasonOrm, $league);
    }

    /**
     * @param League $league
     *
     * @return array Seasons
     */
    public static function lookupByLeague($league)
    {
        $seasons = [];

        $seasonOrms = SeasonOrm::loadByLeagueId($league->id);
        foreach ($seasonOrms as $seasonOrm) {
            $seasons[] = new static($seasonOrm, $league);
        }

        return $seasons;
    }

    /**
     * @param $propertyName
     * @return int|string
     */
    public function __get($propertyName)
    {
        switch ($propertyName) {
            case "id":
            case "name":
            case "startDate":
            case "endDate":
            case "startTime":
            case "endTime":
            case "daysOfWeek":
            case "enabled":
                return $this->seasonOrm->{$propertyName};

            case "league":
                return $this->{$propertyName};

            default:
                Precondition::isTrue(false, "Unrecognized property: $propertyName");
        }
    }

    /**
     * @param $propertyName
     * @param $value
     */
    public function __set($propertyName, $value)
    {
        try {
            switch ($propertyName) {
                case "startDate":
                    Precondition::isTrue($value < $this->endDate, "StartDate ($value) must be less than EndDate ($this->endDate)");
                    if ($this->seasonOrm->{$propertyName} != $value) {
                        $this->seasonOrm->{$propertyName} = $value;
                        $this->seasonOrm->save();
                    }
                    break;

                case "endDate":
                    Precondition::isTrue($this->startDate < $value, "StartDate ($this->startDate) must be less than EndDate ($value)");
                    if ($this->seasonOrm->{$propertyName} != $value) {
                        $this->seasonOrm->{$propertyName} = $value;
                        $this->seasonOrm->save();
                    }
                    break;

                case "name":
                case "startTime":
                case "endTime":
                case "daysOfWeek":
                case "enabled":
                    if ($this->seasonOrm->{$propertyName} != $value) {
                        $this->seasonOrm->{$propertyName} = $value;
                        $this->seasonOrm->save();
                    }
                    break;

                default:
                    Precondition::isTrue(false, "Unrecognized property: $propertyName");
                    break;
            }
        } catch (\Exception $e) {
            var_dump($e);
        }

    }

    /**
     * Populate Divisions, Teams, Coaches, Assistant Coaches
     *
     * @param string $data - Expected format:
     *      Approved,Team,Type,eAYSO Vol App,AYSO ID,Name,Phone,Cell,Email,Certifications
     *      Multi line data where fields are comma separated.  Example:
     *      Yes,U12G-3,Coach,,58222441,Raul Baez,805-845-8124,805-680-1687,familyrb805@yahoo.com,"Intermediate Coach,Safe Haven Coach,U-12 Coach,Needs training"
     *
     *      where:
     *          Approved is ignored
     *          Team is of the format <Division><Gender>-<TeamNumber>.  For example: U6G-5
     *          Type is "Coach" or "Asst"
     *          eAYSO Vol App is ignored
     *          AYSO ID is ignored
     *          Name is coaches name
     *          Phone is coaches phone number
     *          Cell is coaches phone number
     *          Email is coaches email address
     *          Certifications is ignored
     *
     * @param bool  $ignoreHeaderRow - defaults to true
     */
    public function populateDivisions_old($data, $ignoreHeaderRow = true )
    {
        $line = '';

        try {
            $lines = explode("\n", $data);
            $processedLines = 0;

            foreach ($lines as $line) {
                $processedLines += 1;

                // Skip first line if requested
                if ($processedLines == 1 and $ignoreHeaderRow) {
                    continue;
                }

                // Skip empty lines
                if (empty(trim($line))) {
                    continue;
                }

                $fields = explode(',', $line);
                Assertion::isTrue(count($fields) >= 10, "Invalid line: $line");

                $teamNameAttributes = explode('-', $fields[1]);
                if (count($teamNameAttributes) == 3) {
                    $teamNameAttributes = explode('--', $fields[1]);
                }
                Assertion::isTrue(count($teamNameAttributes) == 2, "Invalid team name: $fields[1]");

                $teamName               = sprintf('%s-%02d', $teamNameAttributes[0], $teamNameAttributes[1]);
                $gender                 = (strstr($teamName, 'B') == false) ? "Girls" : "Boys";
                $divisionName           = str_replace('B', '', str_replace('G', '', $teamNameAttributes[0]));
                $displayOrder           = $this->getDivisionDisplayOrder($divisionName);
                $gameDurationMinutes    = $this->getGameDurationMinutes($divisionName);
                $maxPlayersPerTeam      = $this->getMaxPlayersPerTeam($divisionName);

                $name           = $fields[5];
                $email          = $fields[8];
                $phone1         = in_array($fields[6], self::$phoneNumbersToSkip) ? '' : $fields[6];
                $phone2         = in_array($fields[7], self::$phoneNumbersToSkip) ? '' : $fields[7];

                // Do not store the same phone number a second time for a  coach
                $phone2 = ($phone1 == $phone2) ? '' : $phone2;

                // Skip teams where team number is 0
                if ($teamNameAttributes[1] == 0) {
                    continue;
                }

                // Skip teams where there is no coach
                if (empty($name)) {
                    continue;
                }

                // Create division, team and coach or assistant coach
                $division       = Division::create($this, $divisionName, $gender, $maxPlayersPerTeam, $gameDurationMinutes, $displayOrder, true);
                $team           = Team::create($division, null, $teamName, '', '', '', true);

                switch (strtolower($fields[2])) {
                    case 'coach':
                        Coach::create($team, null, $name, $email, $phone1, $phone2, true);
                        break;
                    default:
                        AssistantCoach::create($team, null, $name, $email, $phone1, $phone2, true);
                        break;
                }
            }
        } catch (\Exception $e) {
            print ("Error: Invalid line in uploaded file: '$line'<br>" . $e->getMessage());
        }
    }

    /**
     * Populate Divisions, Teams, Coaches, Assistant Coaches
     *
     * @param string $data - Expected format:
     *      TeamName,TeamId,Region,City,Division,Gender,CoachType,CoachName,CoachPhone,CoachCell,CoachEmail
     *      Multi line data where fields are comma separated.  Example:
     *      Rattlesnakes,U12G-3,122,Santa Barbara,U10,G,Coach,Raul Baez,805-845-8124,805-680-1687,familyrb805@yahoo.com
     *
     *      where:
     *          CoachType is "Coach" or "Asst"
     *
     * @param bool  $ignoreHeaderRow - defaults to true
     */
    public function populateDivisions($data, $ignoreHeaderRow = true )
    {
        $line = '';

        try {
            $lines = explode("\n", $data);
            $processedLines = 0;

            foreach ($lines as $line) {
                $processedLines += 1;

                // Skip first line if requested
                if ($processedLines == 1 and $ignoreHeaderRow) {
                    continue;
                }

                // Skip empty lines
                if (empty(trim($line))) {
                    continue;
                }

                $fields = explode(',', $line);
                Assertion::isTrue(count($fields) >= 11, "Invalid line: $line");

                $teamName       = $fields[0];
                $teamId         = $fields[1];
                $region         = $fields[2];
                $city           = $fields[3];
                $divisionName   = $fields[4];
                $gender         = $fields[5] == 'B' ? "Boys" : "Girls";
                $coachType      = $fields[6];
                $coachName      = $fields[7];
                $coachPhone     = in_array($fields[8], self::$phoneNumbersToSkip) ? '' : $fields[8];
                $coachCell      = in_array($fields[9], self::$phoneNumbersToSkip) ? '' : $fields[9];
                $coachEmail     = $fields[10];

                $displayOrder           = $this->getDivisionDisplayOrder($divisionName);
                $gameDurationMinutes    = $this->getGameDurationMinutes($divisionName);
                $maxPlayersPerTeam      = $this->getMaxPlayersPerTeam($divisionName);

                // Do not store the same phone number a second time for a  coach
                $coachPhone = ($coachPhone == $coachCell) ? '' : $coachPhone;

                // Skip teams where there is no coach (or the team number is 0)
                $teamIdAttributes   = explode("-", $teamId);
                $teamNumber         = $teamIdAttributes[count($teamIdAttributes) - 1];
                unset($teamIdAttributes[count($teamIdAttributes) - 1]);
                $teamId             = implode("-", $teamIdAttributes);
                $teamId             = sprintf("%s-%02d", $teamId, $teamNumber);

                if (empty($teamName)) {
                    $teamName = $teamId;
                }
                if (empty($coachName) or $teamNumber == 0) {
                    continue;
                }

                // Create division, team and coach or assistant coach
                $division       = Division::create($this, $divisionName, $gender, $maxPlayersPerTeam, $gameDurationMinutes, $displayOrder, true);
                $team           = Team::create($division, null, $teamName, $teamId, $region, $city, true);

                switch (strtolower($coachType)) {
                    case 'coach':
                        Coach::create($team, null, $coachName, $coachEmail, $coachPhone, $coachCell, true);
                        break;
                    default:
                        AssistantCoach::create($team, null, $coachName, $coachEmail, $coachPhone, $coachCell, true);
                        break;
                }

                // Create Assistant coach only (division and team must already exist)
                /*
                $division   = Division::lookupByNameAndGender($this, $divisionName, $gender);
                $team       = Team::lookupByNameId($division, $teamId);
                AssistantCoach::create($team, null, $coachName, $coachEmail, $coachPhone, $coachCell, true);
                */
            }
        } catch (\Exception $e) {
            print ("Error: Invalid line in uploaded file: '$line'<br>" . $e->getMessage());
        }
    }

    /**
     * @param $divisionName
     * @return int Order Division should be displayed
     */
    private function getDivisionDisplayOrder($divisionName)
    {
        switch ($divisionName) {
            case 'U5':
            case '5U':
            case '5U-2013':
                return 10;

            case 'U6':
            case '6U':
            case '6U-2012':
                return 20;

            case 'U7':
            case '7U':
            case '7U-2011':
                return 30;

            case 'U8':
            case '8U':
            case '8U-2010':
                return 40;

            case 'U9':
            case '9U':
                return 50;

            case 'U10':
            case '10U':
            case '10U-2009-8':
                return 60;

            case 'U11':
            case '11U':
                return 70;

            case 'U12':
            case '12U':
            case '12U-2007-6':
                return 80;

            case 'U13':
            case '13U':
                return 90;

            case 'U14':
            case '14U':
            case '14U-2005-4':
                return 100;

            case 'U15':
            case '15U':
                return 110;

            case 'U16':
            case '16U':
                return 120;

            case 'U17':
            case '17U':
                return 130;

            case 'U18':
            case '18U':
            case '18U-2003-0':
                return 140;

            case 'U19':
            case '19U':
                return 150;

            case 'U16/19':
                return 160;

            default:
                return 200;
        }
    }

    /**
     * Return the duration of the game, including half time and time needed after the game before the next game starts.
     *
     * @param string $divisionName
     *
     * @return int
     */
    private function getGameDurationMinutes($divisionName)
    {
        switch ($divisionName) {
            case 'U5':
            case 'U6':
            case 'U7':
            case 'U8':
            case '5U-2013':
            case '6U-2012':
            case '7U-2011':
            case '8U-2010':
            case '5U':
            case '6U':
            case '7U':
            case '8U':
                return 60;

            case 'U9':
            case 'U10':
            case '10U-2009-8':
            case '10U':
                return 75;

            case 'U11':
            case 'U12':
            case 'U13':
            case 'U14':
            case 'U15':
            case 'U16':
            case 'U17':
            case 'U18':
            case 'U19':
            case 'U16/19':
            case '12U-2007-6':
            case '14U-2005-4':
            case '18U-2003-0':
            case '12U':
            case '14U':
            case '18U':
            default:
                return 90;
        }
    }

    /**
     * Return the duration of the game, including half time and time needed after the game before the next game starts.
     *
     * @param string $divisionName
     *
     * @return int
     */
    private function getMaxPlayersPerTeam($divisionName)
    {
        switch ($divisionName) {
            case 'U5':
            case 'U6':
            case 'U7':
            case 'U8':
            case '5U-2013':
            case '6U-2012':
            case '7U-2011':
            case '8U-2010':
            case '5U':
            case '6U':
            case '7U':
            case '8U':
                return 10;

            case 'U9':
            case 'U10':
            case '10U-2009-8':
            case '10U':
                return 10;

            case 'U11':
            case 'U12':
            case '12U-2007-6':
            case '12U':
                return 14;

            case 'U13':
            case 'U14':
            case '14U':
            case '14U-2005-4':
                return 18;

            case 'U15':
            case 'U16':
            case 'U17':
            case 'U18':
            case 'U19':
            case 'U16/19':
            case '18U-2003-0':
            case '18U':
            default:
                return 22;
        }
    }

    /**
     * Populate Divisions, Teams, Players
     *
     * @param string $data - Expected format:
     *      Region, Division, Team, Status, ID, Name, Phone, RegDate, PreReg, Fee
     *      Multi line data where fields are comma separated
     *
     *      where:
     *          Region is ignored
     *          Division is of the format <Division><Gender>.  For example: U6G
     *          Team is of the format <Number>
     *          Status is ignored
     *          ID is ignored
     *          Name is the player's name
     *          Phone is player's phone number
     *          RegDate is ignored
     *          PreReg is ignored
     *          Fee is ignored
     *
     * @param bool  $ignoreHeaderRow - defaults to true
     */
    public function populatePlayers($data, $ignoreHeaderRow = true)
    {
        try {
            $lines = explode("\n", $data);
            $processedLines = 0;

            foreach ($lines as $line) {
                $processedLines += 1;
                if ($processedLines == 1 and $ignoreHeaderRow) {
                    continue;
                }

                $fields = explode(',', $line);
                Assertion::isTrue(count($fields) == 10, "Invalid line: $line");

                // Skip if team name is empty - registration that is is progress
                if (empty($fields[1])) {
                    continue;
                }

                // Skip if team number is 0 - no coach yet so team is not formed
                if (empty($fields[2]) or $fields[2] == 0) {
                    continue;
                }

                $divisionName   = str_replace('B', '', str_replace('G', '', $fields[1]));
                $gender         = (strstr($fields[1], 'B') == false) ? "Girls" : "Boys";
                $playerName     = ucfirst(str_replace(';', ',', $fields[5]));
                $division       = Division::lookupByNameAndGender($this, $divisionName, $gender);

                if (isset($division)) {
                    $teamIdPrefix = $this->getTeamIdPrefixFromDivision($division);
                    $teamId = sprintf('%s-%02d', $teamIdPrefix, $fields[2]);
                    $team = Team::lookupByNameId($division, $teamId);
                    if (isset($team)) {
                        Player::create($team, null, $playerName, '', $fields[6], true);
                    }
                }
            }
        } catch (\Exception $e) {
            print ("Error: Invalid line in uploaded file: '$line'<br>" . $e->getMessage());
        }
    }

    /**
     * @param Division  $division
     */
    private function getTeamIdPrefixFromDivision($division)
    {
        $teamIdPrefix = $division->gender == Division::$BOYS ? 'B' : 'G';

        switch ($division->name) {
            case '5U':
                $teamIdPrefix .= '2013';
                break;
            case '6U':
                $teamIdPrefix .= '2012';
                break;
            case '7U':
                $teamIdPrefix .= '2011';
                break;
            case '8U':
                $teamIdPrefix .= '2010';
                break;
            case '10U':
                $teamIdPrefix .= '2009-8';
                break;
            case '12U':
                $teamIdPrefix .= '2007-6';
                break;
            case '14U':
                $teamIdPrefix .= '2005-4';
                break;
            case '19U':
                $teamIdPrefix .= '2003-0';
                break;
            default:
                Precondition::isTrue(false, "Unrecognized division name: $division->name");
                break;
        }

        return $teamIdPrefix;
    }

    /**
     * Populate Facilities
     *
     * @param string $data - Expected format:
     *      FacilityName,Address1,Address2,City,State,ZipCode,ContactName,ContactEmail,ContactPhone,Enabled<br>
     *
     *      Multi line data where fields are comma separated
     *
     *      where:
     *          FacilityName    - name of the facility
     *          Address1        - Address of the facility
     *          Address2        - Additional address info if any
     *          City            - City of the facility
     *          State           - State of the facility
     *          ZipCode         - Zip code for the facility
     *          ContactName     - Person to contact to reserve facility
     *          ContactEmail    - Email address of contact person
     *          ContactPhone    - Phone number of contact person
     *          Enabled         - 1 if enabled; 0 if disabled
     *
     * @param bool  $ignoreHeaderRow - defaults to true
     */
    public function populateFacilities($data, $ignoreHeaderRow = true)
    {
        try {
            $lines = explode("\n", $data);
            $processedLines = 0;

            foreach ($lines as $line) {
                $processedLines += 1;
                if ($processedLines == 1 and $ignoreHeaderRow) {
                    continue;
                }

                $fields = explode(',', $line);
                Assertion::isTrue(count($fields) == 10, "Invalid line: $line");

                $facilityName = $fields[0];
                $address1       = $fields[1];
                $address2       = $fields[2];
                $city           = $fields[3];
                $state          = $fields[4];
                $zipCode        = $fields[5];
                $contactName    = $fields[6];
                $contactEmail   = $fields[7];
                $contactPhone   = $fields[8];
                $enabled        = $fields[9];

                Facility::create($this, $facilityName,$address1, $address2, $city, $state, $zipCode, 'USA', $contactName, $contactEmail, $contactPhone, '', $enabled, true);
            }
        } catch (\Exception $e) {
            print ("Error: Invalid line in uploaded file: '$line'<br>" . $e->getMessage());
        }
    }

    /**
     * Populate Fields and DivisionFields
     *
     * @param string $data - Expected format:
     *      FacilityName,FieldName,Enabled,DivisionList
     *
     *      Multi line data where fields are comma separated
     *
     *      where:
     *          FacilityName    - Name of the facility
     *          FieldName       - Name of the field
     *          Enabled         - 1 if enabled; 0 if disabled
     *          DivisionList    - List of divisions that can use the facility, ; between division names
     *
     * @param bool  $ignoreHeaderRow - defaults to true
     */
    public function populateFields($data, $ignoreHeaderRow = true)
    {
        try {
            $lines = explode("\n", $data);
            $processedLines = 0;

            foreach ($lines as $line) {
                // Skip header line
                $processedLines += 1;
                if ($processedLines == 1 and $ignoreHeaderRow) {
                    continue;
                }

                // Skip blank lines
                if ($line == '') {
                    continue;
                }

                $fields = explode(',', $line);
                Assertion::isTrue(count($fields) == 4, "Invalid line: $line");

                $facilityName   = $fields[0];
                $fieldName      = $fields[1];
                $enabled        = $fields[2];
                $divisionNames  = explode(";", $fields[3]);

                // Create Field
                $facility   = Facility::lookupByName($this, $facilityName);
                $field      = Field::create($facility, $fieldName, $enabled, true);

                // Create DivisionFields
                foreach ($divisionNames as $divisionName) {
                    $divisions = Division::lookupByName($this, $divisionName);
                    foreach ($divisions as $division) {
                        DivisionField::create($division, $field, true);
                    }
                }

                // Create GameTimes
                $this->createGameTimes($field, false);
            }
        } catch (\Exception $e) {
            print ("Error: Invalid line in uploaded file: '$line'<br>" . $e->getMessage());
        }
    }

    /**
     * Create GameTimes for Field.  Delete existing GameTimes if requested.
     *
     * @param Field $field
     * @param bool  $deleteExistingGameTimes
     */
    public function createGameTimes($field, $deleteExistingGameTimes)
    {
        // Delete game times for field.  Exception thrown if a gameTime has an assigned field
        if ($deleteExistingGameTimes) {
            $field->deleteGameTimes();
        }

        // Get the gameDates, startTime and endTime from Season
        $gameDates = GameDate::lookupBySeason($this);
        $startTime = $this->startTime;
        $endTime   = $this->endTime;

        // Create games times for the field across all of the game dates
        $ignoreDuplicates = !$deleteExistingGameTimes;
        GameTime::createByGameDates($gameDates, $field, $startTime, $endTime, $ignoreDuplicates);
    }

    /**
     *  Delete the season
     */
    public function delete()
    {
        $divisions = Division::lookupBySeason($this);
        foreach ($divisions as $division) {
            $division->delete();
        }

        $families = Family::lookupBySeason($this);
        foreach ($families as $family) {
            $family->delete();
        }

        $gameDates = GameDate::lookupBySeason($this);
        foreach ($gameDates as $gameDate) {
            $gameDate->delete();
        }

        $facilities = Facility::lookupBySeason($this);
        foreach ($facilities as $facility) {
            $facility->delete();
        }

        $this->seasonOrm->delete();
    }
}