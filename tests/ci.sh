#!/bin/bash
echo -e "version\nms\nstop\n\n" | php src/pocketmine/PocketMine.php --no-wizard --disable-readline
exit $?
