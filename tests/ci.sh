#!/bin/bash
find . -name "*.php" -print0 | xargs -0 -n1 php -l || exit 1
echo -e "version\nms\nstop\n\n" | php src/pocketmine/PocketMine.php --no-wizard
if ls plugins/Genisys/Genisys*.phar >/dev/null 2>&1; then
    echo "Server packaged successfully."
else
    echo "No phar created!"
    exit 1
fi
