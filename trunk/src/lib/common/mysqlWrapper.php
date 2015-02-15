<?php
/**
* DBUtil - MySQL database utility
*/

/**
* @brief: Custom SQL exception which has knowledge of the SQL state,
*         and also grabs the SQL error number. Eventually all of this
*         code should be using some sort of exceptions, but right now
*         only execSQLStmt does, and it is an optional argument.
*/
class SQLException extends DAG_Exception
{
    protected $sqlstate;
    protected $errorCode;

    public function __construct($message, $code, $sqlState, Exception $previous = null) {
        parent::__construct($message, -5, $previous);
        $this->sqlstate = $sqlState;
        $this->errorCode = $code;
    }

    public function asString() {
        return get_class($this) . ": Error:[{$this->errorCode}], SQL State: [{$this->sqlstate}], {$this->message}";
    }

    public function getSQLState() {
        return $this->sqlstate;
    }

    public function getSQLErrorCode() {
        return $this->errorCode;
    }
}

/**
 * @brief: MySQL_Wrapper class - wraps interaction with MySQL
 */
class MySQL_Wrapper
{
    const DB_RETRY_COUNT = 1;

    const WAIT_TO_RETRY_MIN = 1000;     // microsecond
    const WAIT_TO_RETRY_MAX = 200000;   // microsecond

    /**
     * @var array(string=>MySQL_Wrapper)
     */
    private static $m_Connections = array();

    private static $mEscapeCharacters = array(
        '10'=>'\\n',
        '0'=>'\\0',
        '13'=>'\\r',
        '26'=>'\\Z',
        '34'=>'\\"',
        '39'=>"\\'",
        '92'=>'\\\\');

    private $m_sHostName = "";
    private $m_sPort = "";
    private $m_sDbUser = "";
    private $m_sDbPwd = "";
    private $m_sDbName = "";
    private $m_dbTimeout = 60;
    private $m_dbPersistant = 0;
    private $m_rsResults = null;
    private $m_bAutocommit = TRUE;
    private $m_lastStatementTime = 0;
    private $m_inTransaction = 0;

    public $m_dbResourceName = '';
    public $m_clDbConn = null;
    public $m_affectedRows = -1;
    public $m_errNo = 0;

    /**
     * @brief: Constructor: Creates the DBUtil object
     *              Connection to database is deferred to first use
     *
     * @param: $hostname   - Server running the database 
     * @param: $dbuser     - User to login to the database
     * @param: $dbpwd      - User's database password
     * @param: $dbName     - Schema name
     * @param: $timeout    - Timeout for each transaction in seconds
     * @param: $persistant - Not sure
     * @param: $port       - Port on host that is running the database
     */
    public function __construct($hostname, $dbuser, $dbpwd ,$dbName, $timeout=60, $persistant=0, $port='3306')
    {
        // $this->m_sHostName = $apc_dns_cache->_getHostByName($hostname);
        $this->m_sHostName = $hostname;
        $this->m_sPort = $port;
        $this->m_sDbUser = $dbuser;
        $this->m_sDbPwd = $dbpwd;
        $this->m_sDbName = $dbName;
        $this->m_dbTimeout = $timeout;
        $this->m_dbPersistant = $persistant;
        $this->m_lastStatementTime = time();
        $this->m_inTransaction = 0;
    }

    /**
     * @brief: Destructor: closes connection to the Db
     */
    public function __destruct()
    {
        $this->_shutdown();
    }

   /**
    * Get connection to a given database.  Save connection in a global variable for re-use.
    *
    * @param string $dbName Database name.
    * @param string $transactions If this connection uses transactions
    *
    * @return MySQL_Wrapper
    */
    static public function GetDBConnection($dbName, $transactions, $namespace)
    {
        $key = "db:$dbName;t:".($transactions?1:0).";e:".($transactions?1:0);

        if (isset(self::$m_Connections[$key]) && is_object(self::$m_Connections[$key])) {
            return self::$m_Connections[$key];
        } else {
            global $gDBConnectInfo;
            $dbConnectionInfo = $gDBConnectInfo[$dbName];
            $db_host = $dbConnectionInfo['host'];
            $db_port = $dbConnectionInfo['port'];
            $db_user = $dbConnectionInfo['user'];
            $db_passwd = $dbConnectionInfo['pwd'];
            $db_database = $dbConnectionInfo['db'];
            $db_timeout = $dbConnectionInfo['timeout'];
            $db_persistant = $dbConnectionInfo['persistant'];
            $dbConnection = new MySQL_Wrapper($db_host, $db_user, $db_passwd, $db_database, $db_timeout, $db_persistant,$db_port);
            $dbConnection->m_dbResourceName = $dbName;

            if ($transactions) {
                $dbConnection->SetAutocommit(false);
            }

            self::$m_Connections[$key] = $dbConnection;
            return $dbConnection;
        }
    }

    public function GetDbHostname(){
        return $this->m_sHostName;
    }

    /**
     * Calls mysqli->real_escape_string if there is a db connection else use home-grown routine
     *
     * @param string $inString - string value to be used in sql statement
     *
     * @uses static array self::$mEscapeCharacters
     *
     * @return string
     */
    public function EscapeString($inString)
    {
        if (empty($inString)) {
            return $inString;
        }

        if (null === $this->m_clDbConn){
            $this->_openConnection();
        }

        precondition(is_numeric($inString) || is_string($inString), '$inString not a string or number');

        $newString = "";
        $newString = $this->m_clDbConn->real_escape_string($inString);
        return $newString;
    }

    /**
     * @brief: Set auto commit behavior based on passed in value
     *
     * @param bool $bAutocommit - TRUE to enable auto commit; FALSE to disable
     */
    public function SetAutocommit($bAutocommit)
    {
        if (null === $this->m_clDbConn){
            $this->_openConnection();
        }

        if (!$this->m_clDbConn->autocommit($bAutocommit)) {
            assertion(FALSE, '$this->m_clDbConn->autocommit failed');
        }

        $this->m_bAutocommit = $bAutocommit;
        $this->m_inTransaction = 0;
    }

    /**
     * @brief: Commit current transaction (if any)
     */
    public function commit()
    {
        $this->checkConnection();

        if (!$this->m_clDbConn->commit()) {
            assertion(FALSE, '$this->m_clDbConn->commit failed');
        }

        $this->m_lastStatementTime = time();
        $this->m_inTransaction = 0;
    }

    /**
     * @brief: Rollback current transaction (if any)
     */
    public function rollback()
    {
        $this->checkConnection();

        if (!$this->m_clDbConn->rollback()) {
            assertion(FALSE, '$this->m_clDbConn->rollback failed');
        }

        $this->m_lastStatementTime = time();
        $this->m_inTransaction = 0;
    }

    /**
     * @brief: Will reset the transacton state. Use this function if your sql finishes the transaction by itself.
     */
    public function closeTransation() {
        $this->m_inTransaction = 0;
    }

    /**
     * @brief: Track the last insert identifier
     */
    public function last_insert_id()
    {
        return $this->m_clDbConn->insert_id;
    }

    /**
     * @brief: Allows execution of SQL statements
     *
     * @param: $sqlQuery - sql statement (string)
     * @param: $multi    - true to return multiple result set in an array
     * @param: $transpose - false to return result set as an array of rows (default)
     *                      true to return result set as an array of columns.
     *
     * @return: array of rows or columns containing the recordset
     *          results or null if recordset is empty.
     *
     * IMPORTANT: Does not work if your result set has 2 columns with same name.
     * Note: You should use transpose=true when the result set as a high number of rows.
     */
    public function execSQLStmt($sqlQuery, $multi=false, $transpose=false)
    {
        $this->_closeResultSet();
        $this->checkConnection();
        $this->m_inTransaction++;

        for ($retryCount=1; $retryCount<=self::DB_RETRY_COUNT; ++$retryCount)
        {
            $result = array();
            $itemsReturned = 0;

            if ($this->m_clDbConn->multi_query($sqlQuery)) {
                $this->m_affectedRows = $this->m_clDbConn->affected_rows;
                do {
                    $recordSet = array();
                    if ($this->m_rsResults = $this->m_clDbConn->store_result()) {
                        if (!$transpose) {
                            while ($row = $this->m_rsResults->fetch_assoc()) {
                                array_push($recordSet, $row);
                            }
                        }
                        else {
                            $fieldsInfo = $this->m_rsResults->fetch_fields();
                            if (is_array($fieldsInfo)) {
                                $fields = array();
                                foreach ($fieldsInfo as $info) {
                                    $fields[] = $info->name;
                                }
                                while ($row = $this->m_rsResults->fetch_row()) {
                                    for ($fieldNo=0; $fieldNo < count($row); $fieldNo++) {
                                        $recordSet[$fields[$fieldNo]][] = $row[$fieldNo];
                                    }
                                }
                            }
                        }

                        $this->m_rsResults->close();
                        $this->m_rsResults = null;

                        $itemsReturned = $itemsReturned + count($recordSet);

                        if ($multi) {
                            array_push($result, $recordSet);
                        } else {
                            if (!$transpose) {
                                $result = array_merge($result, $recordSet);
                            }
                            else {
                                if (count($result)==0) {
                                    $result = $recordSet;
                                }
                                else {
                                    for ($fieldNo=0; $fieldNo < count($recordSet); $fieldNo++) {
                                        $result[$fieldNo] = array_merge($result[$fieldNo], $recordSet[$fieldNo]);
                                    }
                                }
                            }
                        }
                    }
                }
                while ($this->m_clDbConn->next_result());

                // we are done
                break;
            }
            else {
                // The two SQL states that are 'retry-able' are 08S01
                // for a communications error, and 40001 for deadlock.
                if ($this->m_bAutocommit && ($this->m_clDbConn->sqlstate == '40001' or $this->m_clDbConn->sqlstate == '08S01')) {
                    if (self::DB_RETRY_COUNT > $retryCount){
                        usleep(mt_rand(self::WAIT_TO_RETRY_MIN, self::WAIT_TO_RETRY_MAX));
                    }
                    continue;
                } else {
                    throw new SQLException($this->m_clDbConn->error, $this->m_clDbConn->errno, $this->m_clDbConn->sqlstate);
                }
            }
        }

        $this->m_lastStatementTime = time();
        $this->m_errNo = $this->m_clDbConn->errno;

        if (0 != $this->m_clDbConn->errno) {
            throw new SQLException($this->m_clDbConn->error, $this->m_clDbConn->errno, $this->m_clDbConn->sqlstate);
        }

        return $itemsReturned > 0 ? $result : null;
    }

    /**
     * @brief: Get number of affected rows from last statement
     */
    public function getNumAffectedRows()
    {
        return $this->m_affectedRows;
    }

    /**
     * @brief: Private - Open a connection to the database
     */
    private function _openConnection()
    {
        if (isset($this->m_clDbConn)) {
            $this->m_clDbConn->close();
        }

        assertion(!empty($this->m_sHostName), 'Empty database hostname');
        assertion(!empty($this->m_sDbUser), 'Empty database user');
        assertion(!empty($this->m_sDbPwd), 'Empty database password');
        assertion(!empty($this->m_sDbName), 'Empty database schema name');
        assertion(!empty($this->m_sPort), 'Empty database port');

        $this->m_clDbConn = mysqli_init();
        $this->m_clDbConn->options(MYSQLI_OPT_CONNECT_TIMEOUT, DATABASE_CONNECTION_TIMEOUT);

        $wait = 250000; // 1/4 of a second in microseconds.
        $hostName = $this->m_dbPersistant ? 'p:' . $this->m_sHostName : $this->m_sHostName;
        $this->m_clDbConn->real_connect($hostName, $this->m_sDbUser, $this->m_sDbPwd, $this->m_sDbName, $this->m_sPort);

printf("%s %d\n", "Connection open: error", $this->m_clDbConn->connect_error);
        assertion(!$this->m_clDbConn->connect_error, '$this->m_clDbConn->real_connect failed');

        if (!$this->m_bAutocommit) {
            $this->SetAutocommit($this->m_bAutocommit);
        }
        $this->m_lastStatementTime = time();
        $this->m_inTransaction = 0;
    }

    /**
     * @brief: Private - Check that the connection to the database is operational.  Open if not yet opened.
     */
    private function checkConnection()
    {
        if (null === $this->m_clDbConn){
            $this->_openConnection();
        }

        assertion(0 == strcasecmp($this->m_clDbConn->sqlstate, "00000"), 'checkConnection failed');
    }


    /**
     * @brief: Close database resources
     */
    private function _shutdown()
    {
        $this->_closeResultSet();
        $this->_closeDbConnection();
    }

    /**
     * @brief: Close result set if open
     */
    private function _closeResultSet() {
        if (null != $this->m_rsResults) {
            $this->m_rsResults->close();
            $this->m_rsResults = null;
        }
    }

    /**
     * @brief: Close database connection if open
     */
    private function _closeDbConnection() {
        if (null != $this->m_clDbConn) {
            $this->m_clDbConn->close();
            $this->m_clDbConn = null;
        }
    }
}
?>
