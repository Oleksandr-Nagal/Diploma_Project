@echo off
REM ===========================================================
REM  Mercure Hub launcher (dev mode, no HTTPS, port 3000)
REM  Requires mercure.exe in this folder
REM ===========================================================

cd /d "%~dp0"

if not exist mercure.exe (
    echo [ERROR] mercure.exe not found in %CD%
    echo.
    echo Download it from:
    echo   https://github.com/dunglas/mercure/releases/latest
    echo.
    echo Pick "mercure_*_Windows_x86_64.zip", extract mercure.exe
    echo into this folder, then run this script again.
    pause
    exit /b 1
)

set SERVER_NAME=:3000
set MERCURE_PUBLISHER_JWT_KEY=!ChangeThisMercureHubJWTSecretKey!
set MERCURE_PUBLISHER_JWT_ALG=HS256
set MERCURE_SUBSCRIBER_JWT_KEY=!ChangeThisMercureHubJWTSecretKey!
set MERCURE_SUBSCRIBER_JWT_ALG=HS256
set GLOBAL_OPTIONS=auto_https off
set MERCURE_TRANSPORT_URL=bolt://mercure.db

echo Starting Mercure on http://127.0.0.1:3000 ...
echo Press Ctrl+C to stop.
echo.

mercure.exe run --config Caddyfile.dev
