#!/bin/bash
mkdir plugins
mkdir plugins
git clone https://github.com/pmmp/PocketMine-DevTools.git -b master
php PocketMine-DevTools/src/DevTools/ConsoleScript.php --make "./PocketMine-DevTools" --relative "./PocketMine-DevTools" --out "./plugins/DevTools.phar"
echo Running lint...
shopt -s globstar
for file in **/*.php; do
    OUTPUT=`php -l "$file"`
    [ $? -ne 0 ] && echo -n "$OUTPUT" && exit 1
done
echo Lint done successfully.
echo -e "version\nmakeserver\nstop\n" | php src/pocketmine/PocketMine.php --no-wizard --disable-readline | grep -v "\[DevTools\] Adding "
if ls plugins/DevTools/Genisys*.phar >/dev/null 2>&1; then
    echo Server packaged successfully.
else
    echo No phar created!
    exit 1
fi
