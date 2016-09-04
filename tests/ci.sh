#!/bin/bash
mkdir plugins
curl -fsSL https://github.com/iTXTech/DevTools/releases/download/v1.1-iTX/DevTools_v1.1-iTX.phar -o plugins/DevTools.phar
if [ "$TRAVIS_PULL_REQUEST" != "false" ]; then
    curl -sSL https://api.github.com/repos/iTxTech/Genisys/pulls/"$TRAVIS_PULL_REQUEST" | \
        php tests/ciDlPlugins.php "$(realpath plugins)"
fi
echo Running lint...
shopt -s globstar
for file in **/*.php; do
    OUTPUT=`php -l "$file"`
    [ $? -ne 0 ] && echo -n "$OUTPUT" && exit 1
done
echo Lint done successfully.
echo -e "version\nms\nstop\n" | php src/pocketmine/PocketMine.php --no-wizard | grep -v "\[DevTools\] Adding "
if ls plugins/DevTools_OUTPUT/Genisys*.phar >/dev/null 2>&1; then
    echo Server packaged successfully.
else
    echo No phar created!
    exit 1
fi
