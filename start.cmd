@echo off
TITLE Genisys server software for Minecraft: Pocket Edition
cd /d %~dp0

:: Loop starting
:: Don't edit this if you don't know what this does!
set LOOP="no"

if exist "server.properties" (
	For /F "UseBackQ tokens=1-2 delims==" %%a in ("server.properties") do if "%%a"=="server-port" set sport=%%b
) else (
	set sport=19132
)

if %LOOP% == "no" (
	goto :StartPM
) else (
	goto :startloop
)

:startloop
netstat -o -n -a | findstr 0.0.0.0:%sport%>nul
if %ERRORLEVEL% equ 0 (
    echo Your server is running.
    goto :loop
) ELSE (
    echo Starting your Genisys server.
    goto :StartPM
)

:loop
echo Checking if server is online...
PING 127.0.0.1 -n 20 >NUL

netstat -o -n -a | findstr 0.0:%sport%>nul
if %ERRORLEVEL% equ 0 (
    echo Server is running.
    goto :loop
) ELSE (
    echo Starting Genisys in 10 seconds...
    PING 127.0.0.1 -n 10 >NUL
    goto :StartPM
)

:StartPM
if exist bin\php\php.exe (
	set PHPRC=""
	set PHP_BINARY=bin\php\php.exe
) else (
	set PHP_BINARY=php
)

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
	%PHP_BINARY% -c bin\php %POCKETMINE_FILE% %*
)
if %LOOP% == "no" (
	exit
) else (
	goto :loop
)
