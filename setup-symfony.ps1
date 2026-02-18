# Symfony project setup script
# Prerequisites: PHP and Composer installed and in PATH
# Run from: c:\backend-inspectors-api

Set-Location $PSScriptRoot

Write-Host "Creating Symfony skeleton project..." -ForegroundColor Cyan
composer create-project symfony/skeleton backend-test
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Set-Location backend-test

Write-Host "`nRequiring webapp orm maker annotations validator twig..." -ForegroundColor Cyan
composer require webapp orm maker annotations validator twig
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host "`nRequiring Nelmio API Doc Bundle..." -ForegroundColor Cyan
composer require nelmio/api-doc-bundle
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host "`nRequiring serializer pack..." -ForegroundColor Cyan
composer require symfony/serializer-pack
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host "`nRequiring framework-bundle (usually already in skeleton)..." -ForegroundColor Cyan
composer require symfony/framework-bundle
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host "`nRequiring Doctrine Migrations Bundle..." -ForegroundColor Cyan
composer require doctrine/doctrine-migrations-bundle
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host "`nSetup complete. Project is in: backend-test\" -ForegroundColor Green
Set-Location $PSScriptRoot
