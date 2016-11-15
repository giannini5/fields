<?php

namespace DAG\Services\MySql;

use DAG\Framework\Exception\Precondition;
use DAG\Framework\Services;
use DAG\Services\Dns\DnsCaching;

/**
 * MySQL Driver represents an open MySQL connection and provides methods to use that connection.
 *
 * The current implementation uses the MySQLi extension.
 */
class Driver
{
    /** @var int[] Amount of time to sleep between connection attempts; last element must be 0 */
    protected static $connectWaitTimes = array(25000, 125000, 200000, 250000, 0); // In microseconds; sum is .6 seconds

    protected $connectionTimeout, $lastActivityTime; // In seconds / is a UNIX timestamp

    protected $host, $port, $db, $user, $password, $timeout, $persistent;

    /** @var \mysqli */
    protected $mysqli, $numberOfRowsAffected;

    /**
     * Creates a Driver instance and opens a MySQL connection with the provided connection info
     *
     * @param int         $connectionTimeout configurable max time to wait when attempting to open the connection,
     *                                       in seconds
     * @param string|null $host
     * @param int|null    $port
     * @param string|null $db
     * @param string|null $user
     * @param string|null $password
     * @param int         $timeout           connection timeout limit in seconds
     * @param bool        $persistent        whether or not to use MySQL persistent connections
     */
    public function __construct($connectionTimeout, $host, $port, $db, $user, $password, $timeout, $persistent)
    {
        Precondition::isPositiveInt($connectionTimeout, 'connectionTimeout');
        Precondition::isPositiveInt($timeout,           'timeout');

        Precondition::isTrue($host     === null || (is_string($host) && $host),         'bad host');
        Precondition::isTrue($port     === null || is_string($port) || is_int($port),   'bad port');
        Precondition::isTrue($db       === null || (is_string($db) && $db),             'bad db');
        Precondition::isTrue($user     === null || (is_string($user) && $user),         'bad user');
        Precondition::isTrue($password === null || (is_string($password) && $password), 'bad password');

        // Check Driver static properties
        Precondition::isNonEmptyArray(self::$connectWaitTimes, 'connectWaitTimes');
        Precondition::isTrue(0 === self::$connectWaitTimes[count(self::$connectWaitTimes) - 1], 'last wait must be 0');

        $this->connectionTimeout = $connectionTimeout;

        $this->host       = DnsCaching::getByHostname($host);
        $this->port       = $port;
        $this->db         = $db;
        $this->user       = $user;
        $this->password   = $password;
        $this->timeout    = $timeout;
        $this->persistent = (bool) $persistent;

        $this->connect();
    }

    /**
     * Closes DB connection
     */
    public function __destruct()
    {
        if ($this->mysqli) {
            $this->mysqli->close();
        }
    }

    /**
     * Escapes a value so it can be put into an SQL query.
     *
     * @param mixed $value value to escape (must be scalar and not null)
     *
     * @return string
     */
    public function escapeString($value)
    {
        Precondition::isTrue($value !== null && is_scalar($value), 'value should not be null or scalar');

        $this->prepareForActivity();

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return $this->mysqli->escape_string((string) $value);
    }

    /**
     * Runs a single MySQL query
     *
     * @param string $sql   MySQL query string
     * @param bool   $multi true if $sql contains multiple queries
     *
     * @return array|null array of rows, or null if query resulted in no results
     *     If $multi, then the result is an array with one element (as above) per query.
     *
     * @throws DuplicateKeyException
     * @throws QueryException
     */
    public function query($sql, $multi = false)
    {
        Precondition::isNonEmpty($sql, 'sql required');

        $this->prepareForActivity();
        $this->lastActivityTime = time();

        $allData = array();

        if ($this->mysqli->multi_query($sql)) {
            $this->numberOfRowsAffected = $this->mysqli->affected_rows;
            // Loop through queries
            do {
                $result = $this->mysqli->store_result();

                if (!$result && $this->mysqli->errno) {
                    break;
                } elseif (false !== $result) {
                    // Loop through result rows
                    $data = array();
                    for ($index = 0; $index < $result->num_rows; $index++) {
                        $data[$index] = $result->fetch_assoc();
                    }
                    $result->close();

                    if ($multi) {
                        array_push($allData, $data);
                    } else {
                        $allData = array_merge($allData, $data);
                    }
                } elseif (false === $result) {
                    if ($multi) {
                        array_push($allData, null);
                    }
                }

                $moreResults = $this->mysqli->more_results();
            } while ($moreResults and $this->mysqli->next_result());
        }

        // Error could be from multi_query(), store_result(), or next_result()
        // Error codes: https://dev.mysql.com/doc/refman/5.5/en/error-messages-server.html
        if ($this->mysqli->errno == 1062) {
            throw new DuplicateKeyException(
                'Duplicate key ' . $this->mysqli->errno . ' ' . $this->mysqli->error . ' ' . $sql
            );
        } elseif ($this->mysqli->errno) {
            throw new QueryException('Query error ' . $this->mysqli->errno . ' ' . $this->mysqli->error . ' ' . $sql);
        }

        return (0 < count($allData)) ? $allData : null;
    }

    /**
     * Returns the number of rows affected by the last INSERT, UPDATE, REPLACE or DELETE query. An integer greater than 
     * zero indicates the number of rows affected or retrieved. Zero indicates that no records were updated for an 
     * UPDATE statement, no rows matched the WHERE clause in the query or that no query has yet been executed. 
     * -1 indicates that the query returned an error. If no previous query were ran, a null is returned.
     *
     * @return int
     */
    public function countAffectedRows()
    {
        return $this->numberOfRowsAffected;
    }

    /**
     * Returns the ID of the last inserted row; result is undefined if no INSERT has been run
     *
     * @return mixed
     */
    public function getLastInsertId()
    {
        Precondition::isNonEmpty($this->mysqli, 'no mysqli connection');

        return $this->mysqli->insert_id;
    }

    /**
     * Turns on or off auto-commit mode on queries for the database connection.
     * 
     * @param mixed $mode
     * 
     * @throws CannotSetAutoCommitException
     */
    public function setAutoCommit($mode)
    {
        $this->prepareForActivity();
        
        if (!$this->mysqli->autocommit($mode)) {
            throw new CannotSetAutoCommitException('Autocommit error');
        }
    }
    
    /**
     * Commits a transaction to the db
     * 
     * @throws CannotCommitException
     */
    public function commit()
    {
        if (!$this->mysqli->commit()) {
            throw new CannotCommitException('Commit error ');
        };
    }

    /**
     * Rollback an uncommitted transaction from the db
     * 
     * @return bool - indicates if the rollback was successful or not
     */
    public function rollback()
    {
        if (!$this->mysqli->rollback()) {
            throw new CannotRollbackException('Rollback error');
        }
    }

    /**
     * Gets the last error number
     * 
     * @return int - last error number
     */
    public function getLastErrorNumber()
    {
        return (isset($this->mysqli->errno)) ? $this->mysqli->errno : 0;
    }

    /**
     * Gets the last error
     * 
     * @return string - last error
     */
    public function getLastErrorDescription()
    {
        return (isset($this->mysqli->error)) ? $this->mysqli->error : '';
    }

    /**
     * Returns the SQLSTATE error from previous MySQL operation
     * 
     * @return string - last SQLSTATE error
     */
    public function getLastErrorSqlState()
    {
        return (isset($this->mysqli->sqlstate)) ? $this->mysqli->sqlstate : '';
    }

    /**
     * Checks whether the connection to the server is working. If it has gone down, and global option mysqli. Reconnect 
     * is enabled an automatic reconnection is attempted. This function can be used by clients that remain idle for a 
     * long while, to check whether the server has closed the connection and reconnect if necessary.
     * 
     * NOTE: We currently have this option set to false
     * 
     * @return bool - indicates if the ping was successful or not
     */
    public function ping()
    {
        // try to make sure we have a connection
        $this->prepareForActivity();
        
        return $this->mysqli->ping();
    }
    
    /**
     * If the connection is unsuccessful, re-connection will be tried based on the number of entries
     *     in $connectWaitTimes before giving up.
     *
     * @throws CannotConnectException if the connection is not successful in non-daemon mode
     */
    protected function connect()
    {
        Precondition::isNonEmpty(!$this->mysqli, 'mysqli already connected');

        $this->mysqli = mysqli_init();
        $this->mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, $this->connectionTimeout);

        $this->lastActivityTime = time();

        try {
            foreach (self::$connectWaitTimes as $wait) {
                if ($this->attemptConnection($wait == 0)) {
                    break;
                }
                usleep($wait);
            }
        } catch (CannotConnectException $e) {
            // Set to null to indicate no mysqli connection
            $this->mysqli = null;
            throw $e;
        }
    }

    /**
     * Attempts to open the database connection.
     *
     * @param bool $throwException connection error handling: true to true to throw Exception
     *
     * @throws CannotConnectException if the connection is not successful and $throwException is true
     * @return bool                   true if the connection succeeded, false otherwise
     */
    protected function attemptConnection($throwException)
    {
        if ($this->persistent) {
            @$this->mysqli->real_connect('p:' . $this->host, $this->user, $this->password, $this->db, $this->port);
        } else {
            @$this->mysqli->real_connect($this->host, $this->user, $this->password, $this->db, $this->port);
        }

        if ($this->mysqli->connect_error) {
            if ($throwException) {
                throw new CannotConnectException(
                    'Cannot connect to database.' .
                    ' host='     . $this->host .
                    ' user='     . $this->user .
                    ' password=' . $this->password .
                    ' db='       . $this->db .
                    ' port='     . $this->port .
                    ' error='    . $this->mysqli->error
                );
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Closes the open database connection.
     */
    protected function disconnect()
    {
        Precondition::isNonEmpty($this->mysqli, 'mysqli must be connected');

        $this->mysqli->close();
        $this->mysqli = null;
    }

    /**
     * Before running a query on the DB, this verifies the connection and updates $lastActivityTime
     *
     * @throws CannotConnectException
     */
    protected function prepareForActivity()
    {
        if (!$this->mysqli) {
            $this->connect();
        } elseif (time() - $this->lastActivityTime > $this->connectionTimeout) {
            $this->disconnect();
            $this->connect();
        }
    }

}
