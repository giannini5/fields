#! /bin/sh

echo ""
echo "*********************************"
echo "***** fields install script *****"
echo "*********************************"

# install the shared functions
. ../helpers/helpers.sh

function showUsageDw()
{
    echo "usage: install.sh hostname|ip address user password  -[c|u|t] [database]"
    echo "where arguments are mySQL inputs and database is optional(default dw)"
    echo " -c : Create database schema and tables"
    echo " -u : Upgrade database schema, tables, content and routines"
    echo " -t : Run automated tests"
    echo
}


database="fields"

if [ $# != 4 ] && [ $# != 5 ]; then
    showUsageDw
    exit 1
fi


if [ $# = 5 ]; then
    database=$5
fi

# The following option prevents $files to contain the wildcard expression when
# there are no files in the folder
shopt -s nullglob

if [ $4 = "-c" ]; then
    createDatabase $1 $2 $3 "${database}"
    installScript $1 $2 $3 $database "./tables/*.sql"
elif [ $4 = "-u" ]; then
    echo "Skipping table creation"
    useDatabase $1 $2 $3 "${database}"
elif [ $4 = "-t" ]; then
    echo "Running tests ..."
    useDatabase $1 $2 $3 "${database}"
else
    showUsageDw
    exit 1
fi

if [ $4 = "-t" ]; then
    echo "Sorry, no tests yet."
else
    echo "Sorry, no upgrades, views, routines or content yet."
    #installScript $1 $2 $3  $database "./upgrade/*.sql"
    #installScript $1 $2 $3  $database "./views/*.sql"
    #installScript $1 $2 $3  $database "./routines/*.sql"
    #installScript $1 $2 $3  $database "./content/*.sql"
fi

