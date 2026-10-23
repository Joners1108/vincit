@echo off
chcp 65001 >nul
title Vincit - Arranque PAP
setlocal enabledelayedexpansion

REM ==== Caminhos automaticos (portavel) ====
set "PROJETO=%~dp0"
if "%PROJETO:~-1%"=="\" set "PROJETO=%PROJETO:~0,-1%"
for %%I in ("%PROJETO%\..\..") do set "LARAGON_ROOT=%%~fI"

set "NGROK=%LARAGON_ROOT%\bin\ngrok\ngrok.exe"
set "DBNAME=vincit_db"
set "DBUSER=root"
set "DBHOST=127.0.0.1"
set "SQLFILE=%PROJETO%\backups\vincit_db.sql"
set "EXTRASQL=%PROJETO%\backups\extras_pap.sql"
set "LOG=%PROJETO%\_arranque_log.txt"
set "NGROK_AUTHTOKEN=23EmAwgLDjRm5tSX4bfKyoqvQhJ_5UmD9gY4wHT7HoWUdU1L7"

REM ==== Encontrar binarios + config (qualquer versao) ====
set "MYSQL="
set "MYSQLD="
set "MYINI="
for /d %%D in ("%LARAGON_ROOT%\bin\mysql\*") do (
    if exist "%%D\bin\mysql.exe"  set "MYSQL=%%D\bin\mysql.exe"
    if exist "%%D\bin\mysqld.exe" set "MYSQLD=%%D\bin\mysqld.exe"
    if exist "%%D\my.ini"         set "MYINI=%%D\my.ini"
)
set "HTTPD="
for /d %%D in ("%LARAGON_ROOT%\bin\apache\*") do (
    if exist "%%D\bin\httpd.exe" set "HTTPD=%%D\bin\httpd.exe"
)

echo ============================================
echo   VINCIT - Arranque automatico (PAP)
echo ============================================
echo   Projeto : %PROJETO%
echo   Laragon : %LARAGON_ROOT%
echo.

if "%MYSQLD%"=="" ( echo [ERRO] mysqld.exe nao encontrado. & goto fim )
if "%HTTPD%"==""  ( echo [ERRO] httpd.exe nao encontrado. & goto fim )

echo [1/4] A arrancar MySQL e Apache...
REM Apache: arranca (se ja correr, falha sem estragar).
start "Apache Vincit" /min "%HTTPD%"

REM MySQL: so arranca se NAO estiver ja a responder (evita 2a instancia).
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
    echo     [ERRO] MySQL nao respondeu em 60s. Abre o Laragon manualmente e tenta de novo.
    goto fim
)
<nul set /p "=."
ping -n 3 127.0.0.1 >nul
goto waitmysql

:mysqlok
echo.
echo     MySQL pronto.
echo.

echo [2/4] A verificar base de dados (%DBNAME%)...
REM So importa se a BD NAO existir / estiver vazia. Assim NAO apaga edicoes do admin.
set "TEMDADOS="
for /f %%C in ('""%MYSQL%" -h %DBHOST% -u %DBUSER% -N -e "SELECT COUNT(*) FROM %DBNAME%.products" 2^>nul"') do set "TEMDADOS=%%C"

if defined TEMDADOS if !TEMDADOS! GTR 0 (
    echo     BD ja existe com !TEMDADOS! produtos - a preservar dados atuais.
    echo     (Para repor de raiz: apaga a base 'vincit_db' e corre de novo.)
    goto dbok
)

echo     BD vazia - a importar do zero...
"%MYSQL%" -h %DBHOST% -u %DBUSER% -e "CREATE DATABASE IF NOT EXISTS %DBNAME% CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>>"%LOG%"
"%MYSQL%" -h %DBHOST% -u %DBUSER% %DBNAME% < "%SQLFILE%" 2>>"%LOG%"
if errorlevel 1 (
    echo     [ERRO] Falha ao importar. Detalhe do erro:
    echo     ----------------------------------------
    type "%LOG%"
    echo     ----------------------------------------
    echo     Log guardado em: %LOG%
    goto fim
)
echo     Base importada.
if exist "%EXTRASQL%" (
    "%MYSQL%" -h %DBHOST% -u %DBUSER% %DBNAME% < "%EXTRASQL%" 2>>"%LOG%"
    echo     Extras aplicados (admin + produtos + Porto).
)
:dbok
echo.

echo [3/4] A abrir o site...
start "" "http://localhost/vincit/site"
echo.

echo [4/4] A iniciar ngrok...
if exist "%NGROK%" (
    start "ngrok" cmd /k ""%NGROK%" http 80 --host-header=localhost"
    echo     ngrok iniciado. Copia o URL https:// da janela ngrok.
) else (
    echo     [aviso] ngrok nao encontrado - ignorado.
)
echo.
echo ============================================
echo   Tudo pronto. Boa apresentacao!
echo ============================================

:fim
echo.
echo (Fecha esta janela quando terminares. Os servicos continuam a correr.)
pause
endlocal
