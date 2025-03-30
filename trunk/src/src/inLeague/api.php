<?php

namespace DAG\inLeague;

/**
 * Class inLeagueAPI
 *
 * @brief API used to GET and POST with inLeague
 */
class api {
    # Private Data
    /** @var  string */
    private $token;
    private $baseURL;

    /**
     * construct
     * @param string    baseURL - for example: https://ayso122.inleague.org
     * @param string    token - inLeague API token
     */
    public function __construct($baseURL, $token) {
        $this->baseURL = $baseURL;
        $this->token = $token;
    }

    /**
     * execute
     * @brief
     *      Execute the request, verify response status code, return json data set on success
     * @return {} json_data
     */
    private function execute($operation) {

        //setup the request, you can also use CURLOPT_URL
        $ch = curl_init(url: $this->baseURL . $operation);

        // Returns the data/output as a string instead of raw data
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //Set your auth headers
        curl_setopt($ch, CURLOPT_HTTPHEADER,
        [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token
            ]
        );

        // get stringified data/output. See CURLOPT_RETURNTRANSFER
        $data = curl_exec($ch);
        $json_data = json_decode($data);

        // get info about the request
        $info = curl_getinfo($ch);

        // close curl resource to free up system resources
        curl_close($ch);

        assertion($info['http_code'] == 200, "Error, http_code " . $info['http_code'] . " returned for " . $operation);
        return $json_data->data;
    }

    /**
     * compititions
     * @brief
     *      Get "active" competitions
     * @return {} json_data
     */
    public function competitions() {
        return $this->execute('/api/v1/competitions');
    }

    /**
     * divisions
     * @brief
     *      Get divisions for the league
     * @param int $seasonID - inLeague seasonID
     * @return {} json_data
     */
    public function divisions($seasonuid) {
        return $this->execute('/api/v1/divisions?seasonuid=' . $seasonuid);
    }

    /**
     * games
     * @brief
     *      Get games based on filters
     * @param int       competitionID - inLeague competitionID
     * @param string    divisionGuids - commas separated list of inLeague division Guid IDs
     * @param string    startDate in MM-DD-YY
     * @param string    endDate in MM-DD-YY
     * @return {} json_data
     */
    public function games($competitionID, $divisionGuids, $startDate, $endDate) {
        return $this->execute('/api/v1/games?competitionID=' . $competitionID . '?divids=' . $divisionGuids . '&startdate=' . $startDate . '&enddate=' . $endDate);
    }

    /**
     * instanceConfig
     * @brief
     *      Get inLeague instance config data
     * @return {} json_data
     */
    public function instanceConfig() {
        return $this->execute('/api/public/instanceConfig');
    }

    /**
     * seasons
     * @brief
     *      Get seasons for the league
     * @return {} json_data
     */
    public function seasons() {
        return $this->execute('/api/v1/seasons');
    }
}