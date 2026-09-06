param([int]$Port=8095)
$ErrorActionPreference='Stop'
$studioProject=Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location -LiteralPath $studioProject
$studioPhp='C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe'
if(!(Test-Path -LiteralPath $studioPhp)){throw 'PHP 8.3.16 was not found. Set $studioPhp to a PHP 8.3+ executable.'}
$env:PHPRC=Join-Path $studioProject 'php.local.ini'
& $studioPhp -c $env:PHPRC artisan serve --host=127.0.0.1 --port=$Port --no-reload
