@echo off
chcp 65001 >nul
title VINCIT - Painel de Arranque (PAP)
setlocal enabledelayedexpansion
color 0B

REM ============================================================
REM   Caminhos automaticos (portavel - funciona em qualquer PC)
REM ============================================================
set "PROJETO=%~dp0"
if "%PROJETO:~-1%"=="\" set "PROJETO=%PROJETO:~0,-1%"
for %%I in ("%PROJETO%\..\..") do set "LARAGON_ROOT=%%~fI"

set "DBNAME=vincit_db"
set "DBUSER=root"
set "DBHOST=127.0.0.1"

REM ==== Descobrir URL do site a partir da pasta (tem de estar sob \www) ====
set "WWWROOT=%LARAGON_ROOT%\www"
set "RELPATH=%PROJETO%"
set "RELPATH=!RELPATH:%WWWROOT%\=!"
set "FORAWWW="
if /I "!RELPATH!"=="%PROJETO%" set "FORAWWW=1"
set "RELPATH=!RELPATH:\=/!"
if defined FORAWWW for %%A in ("%PROJETO%") do set "RELPATH=%%~nxA"
set "SITEURL=http://localhost/!RELPATH!/site"

set "SQLFILE=%PROJETO%\backups\vincit_db.sql"
set "EXTRASQL=%PROJETO%\backups\extras_pap.sql"
set "LOG=%PROJETO%\_arranque_log.txt"
set "LARAGON_EXE=%LARAGON_ROOT%\laragon.exe"

REM ==== Encontrar binarios (qualquer versao instalada) ====
set "MYSQL="
set "MYSQLD="
set "MYSQLDUMP="
set "MYINI="
for /d %%D in ("%LARAGON_ROOT%\bin\mysql\*") do (
    if exist "%%D\bin\mysql.exe"     set "MYSQL=%%D\bin\mysql.exe"
    if exist "%%D\bin\mysqld.exe"    set "MYSQLD=%%D\bin\mysqld.exe"
    if exist "%%D\bin\mysqldump.exe" set "MYSQLDUMP=%%D\bin\mysqldump.exe"
    if exist "%%D\my.ini"            set "MYINI=%%D\my.ini"
)
set "HTTPD="
for /d %%D in ("%LARAGON_ROOT%\bin\apache\*") do (
    if exist "%%D\bin\httpd.exe" set "HTTPD=%%D\bin\httpd.exe"
)

:menu
cls
echo ============================================================
echo                 V I N C I T  -  PAINEL PAP
echo ============================================================
echo   Projeto : %PROJETO%
echo   Laragon : %LARAGON_ROOT%
echo   Site    : %SITEURL%
echo ------------------------------------------------------------
if defined FORAWWW (
    echo   [AVISO] Esta pasta NAO esta em %WWWROOT%
    echo           O site pode nao abrir. Move a pasta para:
    echo           %WWWROOT%\  ^(ex: %WWWROOT%\vincit^)
    echo ------------------------------------------------------------
)
echo.
echo    [1]  ARRANCAR TUDO e abrir o site   ^<== usa este na PAP
echo    [2]  Abrir o Laragon (janela)
echo    [3]  Abrir so o site no browser
echo    [4]  Guardar a base de dados (snapshot)
echo    [5]  REPOR base de dados de raiz (apaga edicoes)
echo    [6]  Verificar estado (Apache / MySQL)
echo    [0]  Sair
echo.
set "op="
set /p "op=  Escolhe uma opcao e carrega ENTER: "

if "%op%"=="1" goto arrancar
if "%op%"=="2" goto abrirlaragon
if "%op%"=="3" goto abrirsite
if "%op%"=="4" goto guardar
if "%op%"=="5" goto repor
if "%op%"=="6" goto estado
if "%op%"=="0" goto sair
goto menu

REM ============================================================
:arrancar
cls
echo ============================================================
echo   ARRANQUE AUTOMATICO
echo ============================================================
echo.
if "%MYSQLD%"=="" ( echo [ERRO] mysqld.exe nao encontrado no Laragon. & pause & goto menu )
if "%HTTPD%"==""  ( echo [ERRO] httpd.exe nao encontrado no Laragon. & pause & goto menu )

echo [1/4] A arrancar Apache e MySQL...
start "Apache Vincit" /min "%HTTPD%"

"%MYSQL%" -h %DBHOST% -u %DBUSER% -e "SELECT 1;" >nul 2>&1
if errorlevel 1 (
    if not "%MYINI%"=="" (
        start "MySQL Vincit" /min "%MYSQLD%" --defaults-file="%MYINI%"
    ) else (
        start "MySQL Vincit" /min "%MYSQLD%"
    )
)

echo     A aguardar MySQL (max 60s)...
set /a tentativas=0
:waitmysql
"%MYSQL%" -h %DBHOST% -u %DBUSER% -e "SELECT 1;" >nul 2>&1
if not errorlevel 1 goto mysqlok
set /a tentativas+=1
if !tentativas! GEQ 30 (
    echo.
    echo     [ERRO] MySQL nao respondeu em 60s.
    echo     Abre o Laragon manualmente ^(opcao 2^) e tenta de novo.
    pause
    goto menu
)
<nul set /p "=."
ping -n 3 127.0.0.1 >nul
goto waitmysql
:mysqlok
echo.
echo     MySQL pronto.
echo.

echo [2/4] A verificar base de dados (%DBNAME%)...
set "TEMDADOS="
set "CNTFILE=%TEMP%\vincit_cnt.txt"
"%MYSQL%" -h %DBHOST% -u %DBUSER% -N -e "SELECT COUNT(*) FROM %DBNAME%.products" 1>"%CNTFILE%" 2>nul
if exist "%CNTFILE%" for /f "usebackq" %%C in ("%CNTFILE%") do set "TEMDADOS=%%C"
del "%CNTFILE%" 2>nul
if defined TEMDADOS if !TEMDADOS! GTR 0 (
    echo     BD ja existe com !TEMDADOS! produtos - dados preservados.
    goto dbok
)
echo     BD vazia - a importar do zero...
if not exist "%SQLFILE%" (
    echo     [ERRO] Nao encontro o ficheiro da base de dados:
    echo            %SQLFILE%
    echo     Confirma que copiaste a pasta 'backups' toda.
    pause
    goto menu
)
"%MYSQL%" -h %DBHOST% -u %DBUSER% -e "CREATE DATABASE IF NOT EXISTS %DBNAME% CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>>"%LOG%"
"%MYSQL%" -h %DBHOST% -u %DBUSER% %DBNAME% < "%SQLFILE%" 2>>"%LOG%"
if errorlevel 1 (
    echo     [ERRO] Falha ao importar. Detalhe:
    type "%LOG%"
    pause
    goto menu
)
echo     Base importada.
REM NOTA: o snapshot vincit_db.sql ja inclui tudo (admin, Porto, 45 produtos).
REM O extras_pap.sql fica so como reserva - NAO e aplicado para nao sobrepor dados.
:dbok
echo.
echo [3/4] A abrir o site no browser...
start "" "%SITEURL%"
echo.
echo [4/4] Concluido.
echo ============================================================
echo   TUDO PRONTO. Boa apresentacao!
echo ============================================================
echo.
echo   Site  : %SITEURL%
echo   Admin : %SITEURL%/admin  (admin@vincit.pt / admin)
echo.
pause
goto menu

REM ============================================================
:abrirlaragon
if exist "%LARAGON_EXE%" (
    start "" "%LARAGON_EXE%"
    echo Laragon aberto.
) else (
    echo [ERRO] laragon.exe nao encontrado em %LARAGON_ROOT%
)
ping -n 3 127.0.0.1 >nul
goto menu

REM ============================================================
:abrirsite
start "" "%SITEURL%"
goto menu

REM ============================================================
:guardar
cls
echo ============================================================
echo   GUARDAR BASE DE DADOS (snapshot)
echo ============================================================
echo.
if "%MYSQLDUMP%"=="" ( echo [ERRO] mysqldump.exe nao encontrado. & pause & goto menu )
if exist "%SQLFILE%" copy /y "%SQLFILE%" "%SQLFILE%.bak" >nul
echo A gravar snapshot de %DBNAME%...
"%MYSQLDUMP%" -h %DBHOST% -u %DBUSER% --databases %DBNAME% --add-drop-database --default-character-set=utf8mb4 --result-file="%SQLFILE%"
if errorlevel 1 (
    echo [ERRO] Falha ao gravar. O MySQL esta a correr? (usa opcao 1 primeiro)
    pause
    goto menu
)
echo.
echo Feito! As tuas edicoes ficam guardadas e viajam para qualquer PC.
pause
goto menu

REM ============================================================
:repor
cls
echo ============================================================
echo   REPOR BASE DE DADOS DE RAIZ
echo ============================================================
echo.
echo   ATENCAO: isto APAGA a base '%DBNAME%' atual e volta a
echo   importar a versao guardada. Perdes edicoes nao guardadas.
echo.
set "conf="
set /p "conf=  Escreve SIM para continuar: "
if /I not "%conf%"=="SIM" ( echo Cancelado. & ping -n 3 127.0.0.1 >nul & goto menu )
"%MYSQL%" -h %DBHOST% -u %DBUSER% -e "SELECT 1;" >nul 2>&1
if errorlevel 1 ( echo [ERRO] MySQL nao esta a correr. Usa a opcao 1 primeiro. & pause & goto menu )
"%MYSQL%" -h %DBHOST% -u %DBUSER% -e "DROP DATABASE IF EXISTS %DBNAME%; CREATE DATABASE %DBNAME% CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>>"%LOG%"
"%MYSQL%" -h %DBHOST% -u %DBUSER% %DBNAME% < "%SQLFILE%" 2>>"%LOG%"
if exist "%EXTRASQL%" "%MYSQL%" -h %DBHOST% -u %DBUSER% %DBNAME% < "%EXTRASQL%" 2>>"%LOG%"
echo.
echo Base reposta de raiz.
pause
goto menu

REM ============================================================
:estado
cls
echo ============================================================
echo   ESTADO DOS SERVICOS
echo ============================================================
echo.
"%MYSQL%" -h %DBHOST% -u %DBUSER% -e "SELECT 1;" >nul 2>&1
if errorlevel 1 (echo   MySQL  : PARADO) else (echo   MySQL  : A CORRER)
tasklist /fi "imagename eq httpd.exe" 2>nul | find /i "httpd.exe" >nul
if errorlevel 1 (echo   Apache : PARADO) else (echo   Apache : A CORRER)
echo.
pause
goto menu

REM ============================================================
:sair
endlocal
exit /b 0
