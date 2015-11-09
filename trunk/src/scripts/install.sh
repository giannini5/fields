#! /bin/sh

function showUsage( )
{
    echo ""
    echo "install.sh"
    echo "  -u upgrades the database and installs web"
    echo "  and the following are optional arguments that can be used to override config.sh settings"
    echo ""
}


function exitOnError( )
{
    if [ $1 != 0 ]; then
        echo "ERROR: $2"
        exit 1
    fi
}


function upgradeDatabase( )
{
    dbDir="../db"
    cwd=`pwd`

    cd $dbDir
    exitOnError $? "Failure trying to cd $dbDir"

    ./install.sh -u
    exitOnError $? "Failure runnint ./install.sh -u"

    cd $cwd
    exitOnError $? "Failure trying to cd $cwd"
}


function createConfigFile( )
{
    configFile="../lib/config.php"
    m4ConfigFile="../lib/config.m4"
    m4DefinesFile="../lib/defines-$USER.m4"
    m4DefaultDefinesFile="../lib/defines.m4"

    rm -f $configFile
    if [ ! -f "$m4DefinesFile" ]; then
        echo "WARNING: M4 Defines file is missing: $m4DefinesFile, using $m4DefaultDefinesFile instead."
        m4DefinesFile=$m4DefaultDefinesFile
    fi

    m4 $m4DefinesFile $m4ConfigFile | sed 's/M4_PHP_DEFINE/define/g' > $configFile
    exitOnError $? "Failure creating $configFile from $m4DefinesFile and $m4ConfigFile" 

    grep "M4_" $configFile
    if [ $? != 1 ]; then
        exitOnError 1 "M4 definitions are missing from $m4DefinesFile"
    fi
}


function installWeb()
{
    if [ $# != 2 ]; then
        exitOnError 1 "installSubsystem - invalid args: $0"
    fi

    srcRoot=$1
    documentRoot=$2

    # Backup current
    if [ -d "$documentRoot" ]; then
        rm -rf ${documentRoot}_backp
        exitOnError $? "Problem trying to delete backup web: ${documentRoot}_backp"

        mv $documentRoot ${documentRoot}_backup
        exitOnError $? "Problem trying mv $documentRoot to ${documentRoot}_backup"
    fi

    # Install new web and update permissions
    cp -R $srcRoot $documentRoot
    exitOnError $? "Problem trying to cp -R $srcRoot $documentRoot"

    cp $srcRoot/.htaccess $documentRoot
    exitOnError $? "Problem trying to cp $srcRoot/.htaccess $documentRoot"

    chown -R apache:apache $documentRoot
    exitOnError $? "Problem trying to chown -R apache:apache $documentRoot"

    chown -R apache:apache $documentRoot/.htaccess
    exitOnError $? "Problem trying to chown -R apache:apache $documentRoot/.htaccess"
}

arg=''

while getopts ":u" arg; do
    case $arg in
        u)
            option="-u"
            ;;
    esac # end case
done

if [ -z $option ]; then
    showUsage
    exit 1
fi

echo "----------------------------------------------"

createConfigFile
upgradeDatabase
# installWeb ../../src /var/www/sb.webyouthsoccer.com/html

echo ""
echo "Done. All is well."
