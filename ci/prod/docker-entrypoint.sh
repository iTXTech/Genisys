#!/bin/bash

if [ "${1:0:1}" = '-' ]; then
	set -- php /Genisys.phar "$@"
fi

# allow the container to be started with `--user`
if [ "$1" = 'php' ] && [ "$2" = '/Genisys.phar' ] && [ "$(id -u)" = '0' ]; then
	chown -R genisys .
	exec su-exec genisys "$BASH_SOURCE" "$@"
fi

exec "$@"