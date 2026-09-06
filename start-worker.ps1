$ErrorActionPreference='Stop'
$studioProject=Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location -LiteralPath $studioProject
$env:PHPRC=Join-Path $studioProject 'php.local.ini'
& 'C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe' -c $env:PHPRC artisan queue:work --sleep=2 --tries=2 --timeout=300
