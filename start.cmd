@echo off
TITLE PocketMine-iTX server software for Minecraft: Pocket Edition
cd /d %~dp0

if exist bin\php\php.exe (
	set PHPRC=""
	set PHP_BINARY=bin\php\php.exe
) else (
	set PHP_BINARY=php
)

if exist PocketMine-iTX.phar (
	set POCKETMINE_FILE=PocketMine-iTX.phar
) else (
   if exist PocketMine-MP.phar (
	set POCKETMINE_FILE=PocketMine-MP.phar
	) else (
	 if exist PocketMine-Soft.phar (
	   set POCKETMINE_FILE=PocketMine-Soft.phar
     	    ) else (
    	      if exist src\pocketmine\PocketMine.php (
	       set POCKETMINE_FILE=src\pocketmine\PocketMine.php
                 ) else (
		    if exist Genisys_1.0dev_*.phar (
			set POCKETMINE_FILE=Genisys_1.0dev_*.phar
			   ) else (
		echo "Couldn't find a valid PocketMine-iTX installation"
		pause
		exit 8
	)
	)
	)
	)
)

REM if exist bin\php\php_wxwidgets.dll (
REM 	%PHP_BINARY% %POCKETMINE_FILE% --enable-gui %*
REM ) else (
	if exist bin\mintty.exe (
		start "" bin\mintty.exe -o Columns=88 -o Rows=32 -o AllowBlinking=0 -o FontQuality=3 -o Font="DejaVu Sans Mono" -o FontHeight=10 -o CursorType=0 -o CursorBlinks=1 -h error -t "PocketMine-iTX" -i bin/pocketmine.ico -w max %PHP_BINARY% %POCKETMINE_FILE% --enable-ansi %*
	) else (
		%PHP_BINARY% -c bin\php %POCKETMINE_FILE% %*
	)
REM )
