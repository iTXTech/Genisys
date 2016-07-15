#!/bin/bash
sed -i "s/const VERSION \?= \?\"\(.*\)\"/const VERSION = \"\1-${1}\"/" src/pocketmine/PocketMine.php
echo -e "version\nms\nstop\n" | /opt/php/bin/php src/pocketmine/PocketMine.php --no-wizard
mv /opt/data-2T/jenkins/jobs/Genisys-master/workspace/plugins/Genisys/Genisys_git-${1}.phar /opt/data-2T/jenkins/jobs/Genisys-master/workspace/artifact/Genisys-${1}.phar
exit $?
