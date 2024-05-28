#! /bin/sh

export MYSQL_OUTPUT_FILE='/tmp/mysql_output.txt'
rm -f $MYSQL_OUTPUT_FILE

function displayMysqlOutputExitOnError( )
{
    error=$1

    cat $MYSQL_OUTPUT_FILE | grep -v "Warning: Using a password on the command line interface can be insecure."

    if [ $error != 0 ]; then
        exit $error
    fi

    rm -f $MYSQL_OUTPUT_FILE
}

function installScript( )
{
    files=$5
    for f in $files
    do
        echo "Installing $f ..."
        # take action on each file. $f store current file name
        mysql --skip-column-names --host=$1 --user=$2 --password=$3 $4 < $f 1> $MYSQL_OUTPUT_FILE 2>&1
        displayMysqlOutputExitOnError $?
  done
}

function createDatabase( )
{
    statement="CREATE DATABASE "
    statement+="$4" 
    statement+=";USE "
    statement+="$4"
    statement+=";"

    mysql --host=$1 --user=$2 --password=$3 --execute="{$statement}" 1> $MYSQL_OUTPUT_FILE 2>&1
    displayMysqlOutputExitOnError $?
}

function useDatabase( )
{
    statement="USE "
    statement+="$4"
    statement+=";"

    mysql --host=$1 --user=$2 --password=$3 --execute="{$statement}" 1> $MYSQL_OUTPUT_FILE 2>&1
    displayMysqlOutputExitOnError $?
}

function runTests()
{
    host=$1
    files=$5

    # Verify host is on approved list
    if [ $host == 'localhost' ]; then
        for f in $files
        do
            echo 
            echo "==== Running $f test... ===="
            mysql --skip-column-names --host=$1 --user=$2 --password=$3 $4 < $f 1> $MYSQL_OUTPUT_FILE 2>&1
            displayMysqlOutputExitOnError $?
        done
    else
        echo "ERROR: host '$host' needs to be added to the approved list for testing"
        exit 2
    fi
}

