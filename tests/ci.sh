#!/bin/bash
echo -e "version\nms\nstop\n\n" | php src/pocketmine/PocketMine.php --no-wizard
if ls plugins/Genisys/Genisys*.phar >/dev/null 2>&1; then
    echo "Server packaged successfully."
else
    echo "No phar created!"
    exit 1
fi
