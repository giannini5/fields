<?php

namespace DAG\inLeague;

use stdClass;

/**
 * Class Region
 *
 * @brief Information about a region
 */
class Region {
    private $boyGameCardDivisionNames = ['B10U', 'B12U', 'B14U'];
    private $girlGameCardDivisionNames = ['G10U', 'G12U', 'G14U'];
    private $season;
    private $competition;
    private $api;

    public function __construct() {
        $this->api = new api(IN_LEAGUE_BASE_URL, IN_LEAGUE_TOKEN);
        $this->season = $this->getSeason(IN_LEAGUE_SEASON_NAME);
        $this->competition = $this->getCompetition(IN_LEAGUE_COMPETITION_NAME);
    }

    /**
     * getSeason
     * @brief
     *      Get the specified season data
     * @return \stdClass inLeague Season data
     */
    public function getSeason($season_name) {
        $season = null;
        $seasons = $this->api->seasons();
        foreach ($seasons as $key => $season) {
            if ($season->name == $season_name) {
                break;
            }
        }
        assertion($season, "Error: Unable to find info for season " . $season_name);

        return $season;
    }

    /**
     * getCompetition
     * @brief
     *      Get the desired "active" competition
     * @param string    competitonName
     * @param bool      verifySeason
     * @return stdClass inLeague Competition
     */
    public function getCompetition($competitonName, $verifySeason=true): stdClass {
        $competitions = null;
        $competitions = $this->api->competitions();
        assertion($competitions, "Error: Unable to find competitions for season " . $this->season->name);

        foreach ($competitions as $key => $competition) {
            if ($competition->competition == $competitonName) {
                if ($verifySeason) {
                    assertion($competition->currentCompetitionSeason->seasonUID == $this->season->seasonUID,
                        "Error: Competition found, but not for season " . $this->season->name);
                }
                break;
            }
        }
        assertion($competition, "Error: Unable to find competition " . $competitonName);

        return $competition;
    }


    /**
     * getDivisions
     * @brief
     *      Get the list of divisions
     * @return \stdClass inLeague Divisons data
     */
    public function getDivisions() {
        $divisions = null;
        $divisions = $this->api->divisions($this->season->seasonUID);
        assertion($divisions, "Error, Unable to find info for season " . $this->season->name);

        return $divisions;
    }

    /**
     * getDivisionsForGameCards
     * @brief
     *      Get the list of divisions for game card scoring
     * @param string    $division_name
     * @param string    $gender
     * @return [] arrayt inLeague Divisons data in stdClass objects
     */
    public function getDivisionsForGameCards($division_name, $gender) {
        $allGameCardDivisions = array_merge($this->boyGameCardDivisionNames, $this->girlGameCardDivisionNames);
        $genderCharacter = ($gender == 'Boys' ? 'B' : ($gender == 'Girls' ? 'G' : 'A'));

        // Filter candidate division names based on desired gender
        switch ($genderCharacter) {
            case 'B':
                $candidateDivisionNames = $this->boyGameCardDivisionNames;
                break;
            case 'G':
                $candidateDivisionNames = $this->girlGameCardDivisionNames;
                break;
            default:
                $candidateDivisionNames = $allGameCardDivisions;
                break;
        }

        // Further filter candidate division names based on desired divisionb_name
        $divsion_name_with_gender = $genderCharacter . $division_name;
        if ($division_name != 'All') {
            if ($genderCharacter == 'A') {
                $candidateDivisionNames = ['B' . $division_name, 'G' . $division_name];
            } else {
                $candidateDivisionNames = [$divsion_name_with_gender];
            }
        }

        $allDivisions = $this->getDivisions();
        $divisions = [];
        foreach ($allDivisions as $key => $division) {
            // displayName B5U, gender B, All, All
            if (in_array($division->displayName, $candidateDivisionNames)) {
                $divisions = array_merge($divisions, [$division]);
            }
        }
        assertion($divisions != [], "Error, Unable to find any divisions matching filters " . $division_name . " " . $gender);

        return $divisions;
    }

    /**
     * getGames
     * @brief
     *      Get games based on filters
     * @param []        divisions - array of inLeague stdClass division
     * @param string    startDate in MM-DD-YY
     * @param string    Optional endDate in MM-DD-YY
     */
    public function getGames($divisions, $startDate, $endDate=null) {
        $startDate = date("m-d-y", strtotime($startDate));
        $endDate = (!$endDate) ? $startDate : $endDate = date("m-d-y", strtotime($endDate));

        // B10: "9F91E3C4-D855-444D-BB86-994BF7DD0E98"
        // G10: "6DB3CA05-0174-4F61-B31C-B966C7B4D3F0"
        // B12: "4D77D368-0055-4DA9-B726-5B1DD0D21786"
        // G12: "59D26836-999F-47C5-8F76-C97C2FA38F0A"
        // B14: "5307E37E-396C-40D2-B28F-D41F9E4C5A3E"
        // G15: "CACCAD7F-7E03-46BD-AE7A-258BACB21396"

        // Get comma separated list of division GUIDS
        $divisionIds = array_map(function($x) {return $x->divID;}, $divisions);
        // $divisionIds = ['9F91E3C4-D855-444D-BB86-994BF7DD0E98'];
        $divisionGuids = implode(',', $divisionIds);

        $games = $this->api->games($this->competition->competitionID, $divisionGuids, $startDate, $endDate);

        return $games;
    }
    /**
     * getRoster
     * @brief
     *      Get roster for a team
     * @param string    teamID
     */
    public function getRoster($teamID) {
        $roster = $this->api->roster($teamID);

        return $roster;
    }
}