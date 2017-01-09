#!/bin/bash
mkdir plugins
curl -fsSL https://github.com/iTXTech/DevTools/releases/download/v1.1-iTX/DevTools_v1.1-iTX.phar -o plugins/DevTools.phar
echo Running lint...
shopt -s globstar
for file in src/pocketmine/*.php src/pocketmine/**/*.php; do
    OUTPUT=`php -l "$file"`
    [ $? -ne 0 ] && echo -n "$OUTPUT" && exit 1
done
echo Lint done successfully.
echo -e "version\nms\nstop\n" | php src/pocketmine/PocketMine.php --no-wizard --disable-readline --debug.level=2 | grep -v "\[DevTools\] Adding "
if ls plugins/DevTools_OUTPUT/Genisys*.phar >/dev/null 2>&1; then
    echo Server packaged successfully.
else
    echo No phar created!
    exit 1
fi
