@echo off
title EE GNDEC — Enable Backend Modules
color 0B

echo.
echo  ╔══════════════════════════════════════════════════════╗
echo  ║    EE GNDEC — Installing Backend API Modules        ║
echo  ║    Notices / Events / Research / Labs               ║
echo  ╚══════════════════════════════════════════════════════╝
echo.

set DRUPAL_ROOT=%~dp0ee-gndec-website
set MARIADB_BIN=%~dp0mariadb\bin
set MARIADB_DATA=%~dp0mariadb\data
set PHP_BIN=%~dp0php83
set DRUSH=%DRUPAL_ROOT%\vendor\drush\drush\drush.php
set WEB=%DRUPAL_ROOT%\web

:: ── Step 1: Start MariaDB if not running ────────────────────────────────
echo [1/4] Starting MariaDB...
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe" >NUL
if "%ERRORLEVEL%"=="0" (
  echo       MariaDB already running.
) else (
  start /B "" "%MARIADB_BIN%\mysqld.exe" --datadir="%MARIADB_DATA%" --standalone
  timeout /t 4 /nobreak > NUL
  echo       MariaDB started.
)

:: ── Step 2: Verify DB ───────────────────────────────────────────────────
echo [2/4] Verifying database connection...
"%MARIADB_BIN%\mysql.exe" -u root -proot -e "SELECT 'OK';" 2>NUL | find "OK" >NUL
if errorlevel 1 (
  echo  ERROR: Cannot connect to database. Make sure MariaDB is running.
  pause
  exit /b 1
)
echo       Database OK.

:: ── Step 3: Create DB if needed ─────────────────────────────────────────
echo [3/4] Ensuring database exists...
"%MARIADB_BIN%\mysql.exe" -u root -proot -e "CREATE DATABASE IF NOT EXISTS ee_gndec CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>NUL
echo       Database 'ee_gndec' ready.

:: ── Step 4: Enable all 4 modules via Drush ──────────────────────────────
echo [4/4] Installing backend API modules...
echo.
echo   Installing: ee_notices, ee_events, ee_research, ee_labs
echo.

"%PHP_BIN%\php.exe" "%DRUSH%" --root="%WEB%" pm:install ee_notices ee_events ee_research ee_labs -y

if errorlevel 1 (
  echo.
  echo  Module install had issues. Trying cache rebuild...
  "%PHP_BIN%\php.exe" "%DRUSH%" --root="%WEB%" cache:rebuild
) else (
  echo.
  echo  ╔══════════════════════════════════════════════════════╗
  echo  ║  ✅  All 4 backend modules installed successfully!  ║
  echo  ╠══════════════════════════════════════════════════════╣
  echo  ║  API Endpoints now available:                       ║
  echo  ║    http://127.0.0.1:8080/api/notices                ║
  echo  ║    http://127.0.0.1:8080/api/events                 ║
  echo  ║    http://127.0.0.1:8080/api/research               ║
  echo  ║    http://127.0.0.1:8080/api/labs                   ║
  echo  ╠══════════════════════════════════════════════════════╣
  echo  ║  Admin UI:                                          ║
  echo  ║    /admin/ee-gndec/notices                          ║
  echo  ║    /admin/ee-gndec/events                           ║
  echo  ║    /admin/ee-gndec/research                         ║
  echo  ║    /admin/ee-gndec/labs                             ║
  echo  ╚══════════════════════════════════════════════════════╝
)

echo.
pause
