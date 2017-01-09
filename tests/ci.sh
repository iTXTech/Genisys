#!/bin/bash
mkdir plugins
curl -o DevTools.zip "https://jenkins.pmmp.io/job/PocketMine-MP%20DevTools/lastSuccessfulBuild/artifact/*zip*/artifacts.zip"
unzip DevTools.zip
mv archive/*.phar plugins
echo Running lint...
shopt -s globstar
for file in src/pocketmine/*.php src/pocketmine/**/*.php; do
    OUTPUT=`php -l "$file"`
    [ $? -ne 0 ] && echo -n "$OUTPUT" && exit 1
done
echo Lint done successfully.
echo -e "version\nmakeserver\nstop\n" | php src/pocketmine/PocketMine.php --no-wizard --disable-readline --debug.level=2 | grep -v "\[DevTools\] Adding "
if ls plugins/DevTools/Genisys*.phar >/dev/null 2>&1; then
    echo Server packaged successfully.
else
    echo No phar created!
    exit 1
fi
