<?php
/**
 * This file contains Model_Fields_FacilityDB
 */

/**
 * Model_Fields_FacilityDB class is the DB class for the Facility database table
 */
class Model_Fields_FacilityDB extends Model_Fields_BaseDB {
    // Schema
    const DB_SCHEMA_NAME = DB_FIELDS_RW;

    // Table information
    const DB_TABLE_NAME = 'facility';

    // Columns constant
    const DB_COLUMN_ID            = 'id';
    const DB_COLUMN_LEAGUE_ID     = 'leagueId';
    const DB_COLUMN_NAME          = 'name';
    const DB_COLUMN_ADDRESS1      = 'address1';
    const DB_COLUMN_ADDRESS2      = 'address2';
    const DB_COLUMN_CITY          = 'city';
    const DB_COLUMN_STATE         = 'state';
    const DB_COLUMN_POSTAL_CODE   = 'postalCode';
    const DB_COLUMN_COUNTRY       = 'country';
    const DB_COLUMN_CONTACT_NAME  = 'contactName';
    const DB_COLUMN_CONTACT_EMAIL = 'contactEmail';
    const DB_COLUMN_CONTACT_PHONE = 'contactPhone';
    const DB_COLUMN_ENABLED       = 'enabled';

    /**
     * @brief: Constructor
     *
     * @return Model_Fields_FacilityDB
     */
    public function __construct() {
        parent::__construct(self::DB_SCHEMA_NAME, self::DB_TABLE_NAME, DAG_Factory::DB_ADAPTER_DEFAULTSQL);
    }

    /**
     * @brief: Check to make sure all the data passed for DB meets a minimum requirement
     *
     * @param: DataObject $dataObject
     */
    protected function _checkPreconditions(DataObject $dataObject) {
        precondition(!empty($dataObject->{self::DB_COLUMN_LEAGUE_ID}), "fields.facility." . self::DB_COLUMN_LEAGUE_ID . " not set");
        precondition(!empty($dataObject->{self::DB_COLUMN_NAME}), "fields.facility." . self::DB_COLUMN_NAME . " not set");
    }

    /**
     * Set default value for the elements that are not set
     *
     * @param DataObject $dataObject
     */
    protected function _setDefaults(DataObject &$dataObject) {
    }

    /**
     * Check to make sure all the data passed for DB update meets a minimum requirement
     *
     * @param DataObject $dataObject
     */
    protected function _checkUpdatePreconditions(DataObject $dataObject) {
    }

    /**
     * _getUpdateKeys will return the update keys
     *
     * @return array primaryKeys K => V
     */
    public function _getUpdateKeys() {
        return array(Model_Fields_FacilityDB::DB_COLUMN_ID => $this->{Model_Fields_FacilityDB::DB_COLUMN_ID});
    }

    /**
     * create a new facility
     *
     * @param $league - Model_Fields_League instance
     * @param string $name - name of the facility
     * @param string $address1 - Address1 of the facility
     * @param string $address2 - Address2 of the facility
     * @param string $city - City where facility is located
     * @param string $state - State where facility is located
     * @param string $postalCode - Postal code of the facility
     * @param string $country - Country where the facility is located
     * @param string $contactName - Name of person in charge of facility
     * @param string $contactEmail - Email of person in charge of facility
     * @param string $contactPhone - Phone number of person in charge of facility
     * @param bool $enabled - 1 if facility is enabled; 0 otherwise
     *
     * @return DataObject[]
     */
    public function create($league, $name, $address1, $address2, $city, $state, $postalCode, $country, $contactName, $contactEmail, $contactPhone, $enabled) {
        $dataObject = new DataObject();
        $dataObject->{self::DB_COLUMN_LEAGUE_ID} = $league->id;
        $dataObject->{self::DB_COLUMN_NAME} = $name;
        $dataObject->{self::DB_COLUMN_ADDRESS1} = $address1;
        $dataObject->{self::DB_COLUMN_ADDRESS2} = $address2;
        $dataObject->{self::DB_COLUMN_CITY} = $city;
        $dataObject->{self::DB_COLUMN_STATE} = $state;
        $dataObject->{self::DB_COLUMN_POSTAL_CODE} = $postalCode;
        $dataObject->{self::DB_COLUMN_COUNTRY} = $country;
        $dataObject->{self::DB_COLUMN_CONTACT_NAME} = $contactName;
        $dataObject->{self::DB_COLUMN_CONTACT_EMAIL} = $contactEmail;
        $dataObject->{self::DB_COLUMN_CONTACT_PHONE} = $contactPhone;
        $dataObject->{self::DB_COLUMN_ENABLED} = $enabled;


        $this->insert($dataObject);

        return $this->getByName($league, $name);
    }

    /**
     * getById retrieves the facility by unique identifier
     *
     * @param int $id - ID of facility
     *
     * @return DataObject found or NULL if none found
     */
    public function getById($id) {
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_ID . "=" . $id);
        return (0 < count($dataObjectArray)) ? $dataObjectArray[0] : NULL;
    }

    /**
     * getByName retrieves the facility by unique league/email
     *
     * @param $league - The league that the practice field coordinator represents
     * @param $name - Facility's name (must be unique in the league)
     * @param $leagueId - Optional league identifier
     *
     * @return DataObject found or NULL if none found
     */
    public function getByName($league, $name, $leagueId = NULL) {
        $leagueId = isset($leagueId) ? $leagueId : $league->id;
        $dataObjectArray = $this->getWhere(self::DB_COLUMN_LEAGUE_ID . " = '" . $leagueId . "' and " . self::DB_COLUMN_NAME . " ='" . $name . "'");
        return (0 < count($dataObjectArray)) ? $dataObjectArray[0] : NULL;
    }
}