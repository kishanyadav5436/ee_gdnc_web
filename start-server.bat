@echo off
title EE GNDEC Website — Local Dev Server
color 0A

echo.
echo  ╔══════════════════════════════════════════════════════╗
echo  ║    EE GNDEC Website — Local Development Server      ║
echo  ║    Electrical Engineering Dept., GNDEC Ludhiana     ║
echo  ╚══════════════════════════════════════════════════════╝
echo.

:: ── Set paths ──────────────────────────────────────────────────────────────
set DRUPAL_ROOT=%~dp0
set MARIADB_BIN=%DRUPAL_ROOT%mariadb\bin
set MARIADB_DATA=%DRUPAL_ROOT%mariadb\data
set PHP_BIN=%DRUPAL_ROOT%php83
set WEB_ROOT=%DRUPAL_ROOT%ee-gndec-website\web
set ROUTER=%DRUPAL_ROOT%router.php
set PORT=8080

:: ── Add to PATH ─────────────────────────────────────────────────────────────
set PATH=%MARIADB_BIN%;%PHP_BIN%;%PATH%

:: ── Start MariaDB ───────────────────────────────────────────────────────────
echo [1/3] Checking MariaDB...
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe" >NUL
if "%ERRORLEVEL%"=="0" (
  echo       MariaDB is already running.
) else (
  echo       Starting MariaDB...
  start /B "" "%MARIADB_BIN%\mysqld.exe" --datadir="%MARIADB_DATA%" --standalone
  timeout /t 3 /nobreak > NUL
  echo       MariaDB started.
)

:: ── Verify DB connection ────────────────────────────────────────────────────
echo [2/3] Verifying database connection...
"%MARIADB_BIN%\mysql.exe" -u root -proot -e "SELECT 'OK' AS status;" 2>NUL | find "OK" >NUL
if errorlevel 1 (
  echo.
  echo  ERROR: Cannot connect to MariaDB. Please check your database.
  pause
  exit /b 1
)
echo       Database: ee_gndec connected OK.

:: ── Start PHP built-in server ───────────────────────────────────────────────
echo [3/3] Starting PHP development server on port %PORT%...
echo.
echo  ┌─────────────────────────────────────────────────┐
echo  │  Site URL: http://127.0.0.1:%PORT%                  │
echo  │  Press Ctrl+C to stop the server                │
echo  └─────────────────────────────────────────────────┘
echo.

:: Open browser after 2 second delay (in background)
start /B cmd /c "timeout /t 2 /nobreak > NUL && start http://127.0.0.1:%PORT%"

:: Start PHP server (foreground — Ctrl+C stops it)
"%PHP_BIN%\php.exe" -d max_execution_time=120 -d memory_limit=256M -S 127.0.0.1:%PORT% "%ROUTER%"

echo.
echo  Server stopped. Press any key to exit.
pause > NUL
