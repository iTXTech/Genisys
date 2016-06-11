#!/bin/bash
shopt -s globstar
for file in **/*.php; do
    OUTPUT=`php -l "$file"`
    [ $? -ne 0 ] && echo -n "$OUTPUT" && exit 1
done
echo -e "version\nms\nstop\n" | php src/pocketmine/PocketMine.php --no-wizard
if ls plugins/Genisys/Genisys*.phar >/dev/null 2>&1; then
    echo "Server packaged successfully."
else
    echo "No phar created!"
    exit 1
fi
