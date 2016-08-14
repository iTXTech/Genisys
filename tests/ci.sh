#!/bin/bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === 'e115a8dc7871f15d853148a7fbac7da27d6c0030b848d9b3dc09e2a0388afed865e6a3d6b3c0fad45c48e2b5fc1196ae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
./composer.phar install
mkdir plugins
curl -fsSL https://github.com/iTXTech/Genisys-DevTools/releases/download/1.0.0/Genisys-DevTools_v1.0.0.phar -o plugins/Genisys-DevTools.phar
echo Running lint...
shopt -s globstar
for file in **/*.php; do
    OUTPUT=`php -l "$file"`
    [ $? -ne 0 ] && echo -n "$OUTPUT" && exit 1
done
echo Lint done successfully.
echo -e "version\nms\nstop\n" | php src/pocketmine/PocketMine.php --no-wizard | grep -v "\[DevTools\] Adding "
if ls plugins/Genisys-DevTools_OUTPUT/Genisys*.phar >/dev/null 2>&1; then
    echo Server packaged successfully.
else
    echo No phar created!
    exit 1
fi
