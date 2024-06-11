#! /bin/bash

function showUsage( )
{
    echo ""
    echo "install.sh -<c|u|t> [-h <hostname | ip address> -n <username> -p <password>]"
    echo "  where -c creates the database schema and tables"
    echo "        -u upgrades the database schema"
    echo "        -t run the integration tests"
    echo "  and the following are optional arguments that can be used to override config.sh settings"
    echo "        -h <hostname> running MySQL database"
    echo "        -n <username> for MySQL database"
    echo "        -p <password> for MySQL database"
    echo ""
}


function sourceConfgiFile( )
{
    configFile="./helpers/config.sh"
    m4ConfigFile="./helpers/config.m4"
    m4templateDefinesFile="./helpers/defines_template.m4"
    m4DefinesFile="./helpers/defines.m4"

    rm -f $configFile
    if [ ! -f "$m4DefinesFile" ]; then
        echo "ERROR: M4 Defines file is missing: $m4DefinesFile.  Please create from $m4templateDefinesFile"
        exit 1
    fi

    m4 $m4DefinesFile $m4ConfigFile > $configFile
    if [ $? != 0 ]; then
        echo "ERROR: Failure creating $configFile from $m4DefinesFile and $m4ConfigFile"
        exit 1
    fi

    source $configFile
    if [ $? != 0 ]; then
        echo "ERROR: Failed to source $configFile"
        exit 1
    fi

    grep "M4_" $configFile
    if [ $? != 1 ]; then
        echo "ERROR: M4 definitions are missing from $m4DefinesFile"
        exit 1
    fi
}


function installSubsystem()
{
    if [ $# != 5 ]; then
        echo "ERROR: installSubsystem - invalid args: $0"
        exit 1
    fi

    hostname=$1
    user=$2
    password=$3
    option=$4
    databaseName=$5

    currentWorkingDirectory=`pwd`
    cd $databaseName

    source ./install.sh $hostname $user $password $option $databaseName
    if [ $? != 0 ]; then
        exit $?
    fi

    cd $currentWorkingDirectory
}

sourceConfgiFile

option=''
hostname=$AYSO_HOSTNAME
user=$AYSO_USERNAME
password=$AYSO_PASSWORD
arg=''

while getopts ":cuth:n:p:" arg; do
    case $arg in
        c)
            option="-c"
            ;;
        u)
            option="-u"
            ;;
        t)
            option="-t"
            ;;
        h)
            hostname=$OPTARG
            ;;
        n)
            user=$OPTARG
            ;;
        p)
            password=$OPTARG
            ;;
    esac # end case
done

if [ -z $option ] || [ -z $hostname ] || [ -z $user ] || [ -z $password ]; then
    showUsage
    exit 1
fi

echo "----------------------------------------------"
echo 'Executing master install script. Bash version ' $BASH_VERSION
echo "Current dir $(pwd)"

installSubsystem $hostname $user $password $option "fields"
installSubsystem $hostname $user $password $option "schedule"

echo ""
echo "Done. All is well."
