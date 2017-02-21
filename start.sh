#!/bin/bash

# This is the start.sh file for Genisys
# Please input ./start.sh to start server

# Variable define
DIR="$(cd -P "$( dirname "${BASH_SOURCE[0]}" )" && pwd)"

# Change Directory
cd "$DIR"

# Loop starting
# Don't edit this if you don't know what this does!

DO_LOOP="no"

##########################################
# DO NOT EDIT ANYTHING BEYOND THIS LINE! #
##########################################

while getopts "p:f:l" OPTION 2> /dev/null; do
	case ${OPTION} in
		p)
			PHP_BINARY="$OPTARG"
			;;
		f)
			POCKETMINE_FILE="$OPTARG"
			;;
		l)
			DO_LOOP="yes"
			;;
		\?)
			break
			;;
	esac
done

if [ "$PHP_BINARY" == "" ]; then
	if [ -f ./bin/php7/bin/php ]; then
		export PHPRC=""
		PHP_BINARY="./bin/php7/bin/php"
	elif type php 2>/dev/null; then
		PHP_BINARY=$(type -p php)
	else
		echo "[ERROR] Couldn't find a working PHP binary, please use the installer."
		exit 1
	fi
fi

if [ "$POCKETMINE_FILE" == "" ]; then
	if [ -f ./PocketMine-iTX.phar ]; then
		POCKETMINE_FILE="./PocketMine-iTX.phar"
	elif [ -f ./Genisys*.phar ]; then
	    	POCKETMINE_FILE="./Genisys*.phar"
	elif [ -f ./PocketMine-MP.phar ]; then
		POCKETMINE_FILE="./PocketMine-MP.phar"
	elif [ -f ./src/pocketmine/PocketMine.php ]; then
		POCKETMINE_FILE="./src/pocketmine/PocketMine.php"
	else
		echo "[ERROR] Couldn't find a valid Genisys installation."
		exit 1
	fi
fi

LOOPS=0

set +e
while [ "$LOOPS" -eq 0 ] || [ "$DO_LOOP" == "yes" ]; do
	if [ "$DO_LOOP" == "yes" ]; then
		"$PHP_BINARY" $POCKETMINE_FILE $@
	else
		exec "$PHP_BINARY" $POCKETMINE_FILE $@
	fi
	if [ "$DO_LOOP" == "yes" ]; then
		if [ ${LOOPS} -gt 0 ]; then
			echo "Restarted $LOOPS times"
		fi
		echo "To escape the loop, press CTRL+C now. Otherwise, wait 5 seconds for the server to restart."
		echo ""
		sleep 5
		((LOOPS++))
	fi
done
