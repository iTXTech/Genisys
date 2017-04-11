#!/bin/bash
# Genisys Installation Script for Mac OS X and Linux(master)
#  _    _            _    _______ 
# | |  | |    /\    | |  |__   __|
# | |__| |   /  \   | |     | |   
# |  __  |  / /\ \  | |     | |   
# | |  | | / ____ \ | |____ | | _ 
# |_|  |_|/_/    \_\|______||_|(_)
# 
# This file is licensed under the Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License.
# Before you start doing anything, read the license for more detail into what you are allowed to do and not do.
while :
do
clear
cat << "EOF"

 *  _____   _____   __   _   _   _____  __    __  _____
 * /  ___| | ____| |  \ | | | | /  ___/ \ \  / / /  ___/
 * | |     | |__   |   \| | | | | |___   \ \/ /  | |___
 * | |  _  |  __|  | |\   | | | \___  \   \  /   \___  \
 * | |_| | | |___  | | \  | | |  ___| |   / /     ___| |
 * \_____/ |_____| |_|  \_| |_| /_____/  /_/     /_____/
  
EOF

# shopt -s extglob
echo "system> Script by majovec!"
echo "system> Welcome to Genisys!"
echo "system> The installer will guide you through installing Genisys for your server!"
echo
echo "system> Select which PHP binary you want to install:"
echo "system>   1) Linux x86(32-bit)"
echo "system>   2) Linux x64(64-bit)"
echo "system>   3) Mac x86(32-bit)"
echo "system>   4) Mac x64(64-bit)"
echo "system>   5) Raspberry Pi 2"
echo "system>   6) Exit Genisys installation"
read -e -p "system> Number (e.g. 1): " a
read -e -p "system> Number (e.g. 1): " a </dev/tty
 case "$a" in 
	1 ) z="PHP_7.0.3_x86_Linux.tar.gz";;
	2 ) z="PHP_7.0.3_x86-64_Linux.tar.gz";;
        3 ) z="PHP_7.0.3_x86_MacOS.tar.gz";;
        4 ) z="PHP_7.0.3_x86-64_MacOS.tar.gz";;
        5 ) z="RPI2";;
        6 ) exit 1;;
        * ) echo "error> An unexpected error occurred - either a 10 second timeout or an unknown selection. Restart the script, and then choose again."; exit 1;;
 esac

l="install_log/log"
le="install_log/log_errors"
lp="install_log/log_php"
lpe="install_log/log_php_errors"
w="install_log/log_wget"
wp="install_log/log_wget_php"

	mkdir install_log
	echo "system> Installing Genisys..."
	wget https://github.com/iTXTech/Genisys/archive/master.zip >>./$w 2>>./$w
	chmod 777 master.zip >>./$l 2>>./$le
	unzip -o master.zip >>./$l 2>>./$le
	chmod 777 Genisys-master >>./$l 2>>./$le
	cd Genisys-master >>./$l 2>>./$le
	chmod 777 src >>../$l 2>>../$le
	cp -rf src .. >>../$l 2>>../$le
#        cp -rf start.sh .. >>./$l 2>>./$le
	cd .. >>../$l 2>>../$le
	rm -rf Genisys-master >>./$l 2>>./$le
	rm -rf master.zip >>./$l 2>>./$le
	wget --no-check-certificate https://raw.githubusercontent.com/iTXTech/Genisys/master/start.sh >>./$l 2>>./$le
	chmod 777 start.sh >>./$l 2>>./$le
        wget --no-check-certificate https://raw.githubusercontent.com/iTXTech/Genisys/master/LICENSE.md >>./$l 2>>./$le
	echo
	echo "system> Installing PHP binary..."
        if [ "$z" == "RPI2" ]; then
        wget --no-check-certificate https://raw.githubusercontent.com/keithkfng/my-files-and-icons/master/raspberry_php.tar.gz >>./$wp 2>>./$wp
        chmod 777 rasp* >>./$lp 2>>./$lpe
	tar zxvf rasp* >>./$lp 2>>./$lpe
	rm -r rasp* >>./$lp 2>>./$lpe
	elif [ "$z" == "PHP_7.0.3_x86_MacOS.tar.gz" ]; then
	curl -O --insecure https://dl.bintray.com/pocketmine/PocketMine/$z >>./$wp 2>>./$wp
	elif [ "$z" == "PHP_7.0.3_x86-64_MacOS.tar.gz" ]; then
	curl -O --insecure https://dl.bintray.com/pocketmine/PocketMine/$z >>./$wp 2>>./$wp
	else
	wget --no-check-certificate https://dl.bintray.com/pocketmine/PocketMine/$z >>./$wp 2>>./$wp
	fi
	chmod 777 PHP* >>./$lp 2>>./$lpe
	tar zxvf PHP* >>./$lp 2>>./$lpe
	rm -r PHP* >>./$lp 2>>./$lpe
	echo
        read -e -p "system> Do you want to auto-restart your server when it stops or crashes? (Y/n):" c
        read -e -p "system> Do you want to auto-restart your server when it stops or crashes? (Y/n):" c </dev/tty
		if [ "$c" == "y" ]||[ "$c" == "Y" ]; then
                sed -i 's/DO_LOOP="no"/DO_LOOP="yes"/' start.sh
		else
                sed -i 's/DO_LOOP="yes"/DO_LOOP="no"/' start.sh
		fi
        
        echo
        echo "system> Genisys installation completed! Run ./start.sh (or ./st*) to start Genisys."
exit 0
done
