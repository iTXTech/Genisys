chcp 65001
cls
@echo off
TITLE Genisys server software for Minecraft: Pocket Edition
cd /d %~dp0

rem if exist bin\php\php.exe (
rem 	set PHPRC=""
rem 	set PHP_BINARY=bin\php\php.exe
rem ) else (
	set PHP_BINARY=php
rem )

if exist Genisys*.phar (
	set POCKETMINE_FILE=Genisys*.phar
) else (
    if exist PocketMine-MP.phar (
        set POCKETMINE_FILE=PocketMine-MP.phar
	) else (
	    if exist src\pocketmine\PocketMine.php (
	        set POCKETMINE_FILE=src\pocketmine\PocketMine.php
        ) else (
            if exist Genisys.phar (
                set POCKETMINE_FILE=Genisys.phar
            ) else (
		        echo "[ERROR] Couldn't find a valid Genisys installation."
		        pause
		        exit 8
		    )
	    )
	)
)

if exist bin\mintty.exe (
	start "" bin\mintty.exe -o Columns=88 -o Rows=32 -o AllowBlinking=0 -o FontQuality=3 -o Font="Consolas" -o FontHeight=10 -o CursorType=0 -o CursorBlinks=1 -h error -t "PocketMine-iTX" -i bin/pocketmine.ico -w max %PHP_BINARY% %POCKETMINE_FILE% --enable-ansi %*
) else (
	php %POCKETMINE_FILE% %*
)