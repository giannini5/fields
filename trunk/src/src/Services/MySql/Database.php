<?php

namespace DAG\Services\MySql;

use DAG\Framework\Exception\Precondition;

/**
 * Provides functions for building and executing queries based on input data.
 * It uses a MySql Driver to run the queries after they are built.
 */
class Database
{
    /** @var Driver */
    protected $driver;

    protected $lastRunQuery;

    /**
     * @param Driver $driver the driver with which to execute queries
     */
    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Insert data into a table
     *
     * @param string  $tableName   target table name
     * @param array[] $arrayOfRows array of rows, where each row is a key/value array; all rows must have the same keys
     */
    public function insert($tableName, $arrayOfRows)
    {
        Precondition::isNonEmptyString($tableName, 'tableName');
        Precondition::isTrue(isset($arrayOfRows[0]), 'arrayOfRows must be a non-empty array of rows (not one row)');

        $columns = '`' . implode('`,`', array_keys($arrayOfRows[0])) . '`';
        $values = '';

        foreach ($arrayOfRows as $row) {
            $rowValues = '';
            foreach ($row as $value) {
                $rowValues .= (!is_null($value)) ? "'" . $this->escapeString($value) . "'," : "NULL,";
            }
            $values .= '(' . trim($rowValues, ',') . '),';
        }
        $values = trim($values, ',');

        $sql = "INSERT INTO `{$tableName}` ({$columns}) VALUES {$values}";
        $this->query($sql);
    }

    /**
     * Update data in a table
     *
     * @param string     $tableName target table name
     * @param array      $newValues key/value array specifying new values for keys (columns)
     * @param array|null $criteria  optionally, only apply the update to rows matching $criteria (key/value array)
     */
    public function update($tableName, $newValues, $criteria = null)
    {
        Precondition::isNonEmptyString($tableName, 'tableName');
        Precondition::isNonEmptyArray($newValues, 'newValues');

        $updateValues = '';
        foreach($newValues as $key => $value){
            $updateValues .= (!is_null($value)) ? "`$key` = '" . $this->escapeString($value) . "'," : "`$key` = NULL,";
        }
        $updateValues = trim($updateValues, ',');

        $where = $this->buildWhere($criteria);

        $sql = "UPDATE `$tableName` SET $updateValues" . ($where ? " WHERE $where" : '');
        $this->query($sql);
    }

    /**
     * Delete data from a table
     *
     * @param string $tableName target table name
     * @param array  $criteria  key/value array indicating which rows to delete; must be non-empty
     */
    public function delete($tableName, $criteria)
    {
        Precondition::isNonEmptyString($tableName, 'tableName');
        Precondition::isNonEmptyArray($criteria, 'criteria');

        $where = $this->buildWhere($criteria);

        $sql = "DELETE FROM `$tableName` WHERE $where";
        $this->query($sql);
    }

    /**
     * Get data from a table
     *
     * @param string     $tableName target table name
     * @param array|null $criteria  optional key/value array indicating which rows to get
     * @param string     $queryEndFragment     optional query fragment to put at end of query (ie. for LIMIT)
     *
     * @return array[] rows; each row is a key/value array
     */
    public function get($tableName, $criteria = null, $queryEndFragment = null)
    {
        Precondition::isNonEmptyString($tableName, 'tableName');
        $where = $this->buildWhere($criteria);

        $sql    = "SELECT * FROM `$tableName`" .
            ($where ? " WHERE $where" : '') .
            ($queryEndFragment ? ' ' . $queryEndFragment : '');;
        $result = $this->query($sql);

        return (is_null($result)) ? array() : $result;
    }

    /**
     * Runs the provided query and returns the output
     *
     * @param string $sql       - query string
     * @param bool   $multi     - true if $sql contains multiple queries. Default - false
     * @param bool   $transpose - return result set as an array of rows. Default - false
     *
     * @return array|null query results
     *
     * @throws DuplicateKeyException
     * @throws QueryException
     */
    public function query($sql, $multi = false, $transpose = false)
    {
        Precondition::isNonEmptyString($sql, 'sql');

        $this->lastRunQuery = $sql;
        $resultSet = $this->driver->query($sql, $multi);
        
        if (!is_null($resultSet) && $transpose) {
            $this->transpose($resultSet, $multi, 0, $resultSet);
        }
        
        return $resultSet;
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

        return $this->driver->escapeString($value);
    }

    /**
     * Returns the number of affected rows from the previous query
     *
     * @return int
     */
    public function countAffectedRows()
    {
        return $this->driver->countAffectedRows();
    }

    /**
     * Returns the id of the row inserted by the previous query
     *
     * @return int
     */
    public function getLastInsertId()
    {
        return $this->driver->getLastInsertId();
    }

    /**
     * Provides the last-run SQL query string for logging, debugging, etc.
     *
     * @return mixed query string or null if none
     */
    public function getLastRunQuery()
    {
        return $this->lastRunQuery;
    }

    /**
     * Turns on or off auto-commit mode on queries for the database connection.
     * 
     * @param mixed $mode
     */
    public function setAutoCommit($mode)
    {
        $this->driver->setAutoCommit($mode);
    }

    /**
     * Commits a transaction to the db
     * 
     */
    public function commit()
    {
        $this->driver->commit();
    }

    /**
     * Rollback an uncommitted transaction from the db
     * 
     */
    public function rollback()
    {
        $this->driver->rollback();
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
        return $this->driver->ping();
    }

    /**
     * Gets the last error number
     * 
     * @return int - last error number
     */
    public function getLastErrorNumber()
    {
        return $this->driver->getLastErrorNumber();
    }

    /**
     * Gets the last error
     * 
     * @return string - last error
     */
    public function getLastErrorDescription()
    {
        return $this->driver->getLastErrorDescription();
    }

    /**
     * Returns the SQLSTATE error from previous MySQL operation
     * 
     * @return string - last SQLSTATE error
     */
    public function getLastErrorSqlState()
    {
        return $this->driver->getLastErrorSqlState();
    }
    
    /**
     * Build the WHERE-clause part of an SQL query
     *
     * @param array $criteria key/value (column-to-value) criteria by which to filter rows
     *
     * @return null|string search criteria (SQL fragment) to follow the WHERE statement, or null if no criteria
     */
    protected function buildWhere($criteria)
    {
        if (!$criteria) {
            return null;
        }

        $result = '';
        foreach ($criteria as $key => $value) {
            $result .= " AND `$key` = '" . $this->escapeString($value) . "'";
        }

        return substr($result, 5);
    }
    
    /**
     * transposes the rows with the columns. If the data set is from a multi query, recursion is used.
     * 
     * @param mixed $dataSet    - dataset returned from the call to query
     * @param bool  $multi      - indicates if rows are from a multi sql statement
     * @param int   $numOfSets  - the number of dataset
     * @param mixed $transposed - stored values of the transposed dataset
     * 
     * @return mixed
     */
    private function transpose($dataSet, $multi = false, $numOfSets = 0, &$transposed = null)
    {
        if (is_null($transposed)) {
            $transposed = array();
        }
        $transposedData = array();
        
        $rows = ($multi) ? array_pop($dataSet) : $dataSet;
        
        if (is_array($rows)) {
            foreach ($rows as $row) {
                foreach ($row as $columnName => $data) {
                    if (!isset($transposedData[$columnName])) {
                        $transposedData[$columnName] = array();
                    }
                    
                    $transposedData[$columnName][] = $data;
                }
            }
        }
        
        if ($multi) {
            $numOfSets = count($dataSet);
            $transposed[$numOfSets] = $transposedData;
        }
        
        if (0 === $numOfSets) {
            if (!$multi) {
                $transposed = $transposedData;
            }
            
            return;
            
        } else {
            $this->transpose($dataSet, $multi, $numOfSets, $transposed);
        }
    }
}
