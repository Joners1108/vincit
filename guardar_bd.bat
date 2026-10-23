@echo off
chcp 65001 >nul
title Vincit - Guardar BD (snapshot)
setlocal enabledelayedexpansion

set "PROJETO=%~dp0"
if "%PROJETO:~-1%"=="\" set "PROJETO=%PROJETO:~0,-1%"
for %%I in ("%PROJETO%\..\..") do set "LARAGON_ROOT=%%~fI"

set "DBNAME=vincit_db"
set "DBUSER=root"
set "DBHOST=127.0.0.1"
set "SQLFILE=%PROJETO%\backups\vincit_db.sql"

set "DUMP="
for /d %%D in ("%LARAGON_ROOT%\bin\mysql\*") do (
    if exist "%%D\bin\mysqldump.exe" set "DUMP=%%D\bin\mysqldump.exe"
)

echo ============================================
echo   Guardar estado atual da BD -^> vincit_db.sql
echo ============================================
echo.

if "%DUMP%"=="" ( echo [ERRO] mysqldump.exe nao encontrado. & goto fim )

REM Guarda uma copia de seguranca antes de sobrescrever
if exist "%SQLFILE%" copy /y "%SQLFILE%" "%SQLFILE%.bak" >nul

echo A gravar snapshot de %DBNAME%...
"%DUMP%" -h %DBHOST% -u %DBUSER% --databases %DBNAME% --add-drop-database --default-character-set=utf8mb4 --result-file="%SQLFILE%"
if errorlevel 1 (
    echo [ERRO] Falha ao gravar. Verifica se o MySQL esta a correr.
    goto fim
)

echo.
echo Feito! Snapshot atualizado:
echo   %SQLFILE%
echo (copia de seguranca em vincit_db.sql.bak)
echo.
echo Agora as tuas edicoes viajam para qualquer PC.

:fim
echo.
pause
endlocal
