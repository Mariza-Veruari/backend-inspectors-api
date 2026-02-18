# Testing Guide

## Quick Start

### 1. Setup (if not done already)

```powershell
# Install dependencies
composer install

# Create var directory (if it doesn't exist)
New-Item -ItemType Directory -Force -Path var

# Run migrations
php bin/console doctrine:migrations:migrate --no-interaction
```

### 2. Create Test Data

You need at least one **Job** and one **Inspector** in the database. You can create them using Symfony console:

```powershell
# Start Symfony console (interactive mode)
php bin/console
```

Or create them directly via SQL (if using SQLite):

```powershell
# For SQLite, you can use sqlite3 command
sqlite3 var/data.db "INSERT INTO inspector (name, timezone) VALUES ('John Doe', 'Europe/Madrid');"
sqlite3 var/data.db "INSERT INTO inspector (name, timezone) VALUES ('Jane Smith', 'America/Mexico_City');"
sqlite3 var/data.db "INSERT INTO job (title, status) VALUES ('Inspection at Site A', 'open');"
sqlite3 var/data.db "INSERT INTO job (title, status) VALUES ('Inspection at Site B', 'open');"
```

**Or use a simple PHP script** (see `create-test-data.php` below)

### 3. Start the Server

```powershell
# Option 1: PHP built-in server
php -S localhost:8000 -t public

# Option 2: Symfony CLI (if installed)
symfony server:start
```

The API will be available at: **http://localhost:8000**

---

## Testing Methods

### Method 1: Swagger UI (Easiest)

1. Start the server (see above)
2. Open your browser: **http://localhost:8000/api/docs**
3. Use the interactive Swagger UI to test all endpoints
4. Click "Try it out" on any endpoint to test it

### Method 2: PowerShell / curl Commands

#### Test 1: List Open Jobs

```powershell
# PowerShell
Invoke-RestMethod -Uri "http://localhost:8000/api/jobs" -Method Get | ConvertTo-Json

# Or with curl (if available)
curl http://localhost:8000/api/jobs
```

**Expected Response:**
```json
{
  "items": [
    {
      "id": 1,
      "title": "Inspection at Site A",
      "status": "open"
    }
  ]
}
```

#### Test 2: Assign Job to Inspector

```powershell
# PowerShell
$body = @{
    inspectorId = 1
    scheduleAt = "2025-03-15T09:00:00+01:00"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/api/jobs/1/assign" `
    -Method Post `
    -ContentType "application/json" `
    -Body $body | ConvertTo-Json

# Or with curl
curl -X POST http://localhost:8000/api/jobs/1/assign `
  -H "Content-Type: application/json" `
  -d '{\"inspectorId\":1,\"scheduleAt\":\"2025-03-15T09:00:00+01:00\"}'
```

**Expected Response:**
```json
{
  "id": 1,
  "jobId": 1,
  "jobTitle": "Inspection at Site A",
  "inspectorId": 1,
  "scheduledAt": "2025-03-15T09:00:00+01:00",
  "status": "scheduled",
  "completedAt": null,
  "assessment": null
}
```

#### Test 3: Get Inspector Schedule

```powershell
# PowerShell
Invoke-RestMethod -Uri "http://localhost:8000/api/inspectors/1/schedule" -Method Get | ConvertTo-Json

# Or with curl
curl http://localhost:8000/api/inspectors/1/schedule
```

**Expected Response:**
```json
{
  "inspectorId": 1,
  "timezone": "Europe/Madrid",
  "assignments": [
    {
      "id": 1,
      "jobId": 1,
      "jobTitle": "Inspection at Site A",
      "scheduledAt": "2025-03-15T09:00:00+01:00",
      "status": "scheduled",
      "assessment": null,
      "completedAt": null
    }
  ]
}
```

#### Test 4: Complete Assignment

```powershell
# PowerShell
$body = @{
    assessment = "All checks passed. Minor repairs recommended."
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/api/assignments/1/complete" `
    -Method Post `
    -ContentType "application/json" `
    -Body $body | ConvertTo-Json

# Or with curl
curl -X POST http://localhost:8000/api/assignments/1/complete `
  -H "Content-Type: application/json" `
  -d '{\"assessment\":\"All checks passed. Minor repairs recommended.\"}'
```

**Expected Response:**
```json
{
  "id": 1,
  "jobId": 1,
  "jobTitle": "Inspection at Site A",
  "inspectorId": 1,
  "scheduledAt": "2025-03-15T09:00:00+01:00",
  "completedAt": "2025-03-15T11:30:00+01:00",
  "status": "completed",
  "assessment": "All checks passed. Minor repairs recommended."
}
```

---

## Error Testing

### Test Validation Errors

```powershell
# Missing required field
$body = @{ inspectorId = 1 } | ConvertTo-Json
Invoke-RestMethod -Uri "http://localhost:8000/api/jobs/1/assign" `
    -Method Post `
    -ContentType "application/json" `
    -Body $body
# Should return 400 with validation errors

# Invalid datetime format
$body = @{
    inspectorId = 1
    scheduleAt = "invalid-date"
} | ConvertTo-Json
Invoke-RestMethod -Uri "http://localhost:8000/api/jobs/1/assign" `
    -Method Post `
    -ContentType "application/json" `
    -Body $body
# Should return 400 with validation errors
```

### Test Not Found Errors

```powershell
# Non-existent job
Invoke-RestMethod -Uri "http://localhost:8000/api/jobs/999/assign" `
    -Method Post `
    -ContentType "application/json" `
    -Body '{\"inspectorId\":1,\"scheduleAt\":\"2025-03-15T09:00:00+01:00\"}'
# Should return 404

# Non-existent inspector
Invoke-RestMethod -Uri "http://localhost:8000/api/jobs/1/assign" `
    -Method Post `
    -ContentType "application/json" `
    -Body '{\"inspectorId\":999,\"scheduleAt\":\"2025-03-15T09:00:00+01:00\"}'
# Should return 404
```

### Test Conflict Errors

```powershell
# Try to assign already assigned job
Invoke-RestMethod -Uri "http://localhost:8000/api/jobs/1/assign" `
    -Method Post `
    -ContentType "application/json" `
    -Body '{\"inspectorId\":1,\"scheduleAt\":\"2025-03-15T09:00:00+01:00\"}'
# First time: success, second time: 409 conflict

# Try to complete already completed assignment
Invoke-RestMethod -Uri "http://localhost:8000/api/assignments/1/complete" `
    -Method Post `
    -ContentType "application/json" `
    -Body '{\"assessment\":\"Test\"}'
# First time: success, second time: 409 conflict
```

---

## Complete Test Script

Save this as `test-api.ps1`:

```powershell
$baseUrl = "http://localhost:8000"

Write-Host "=== Testing Inspector Scheduling API ===" -ForegroundColor Green

# Test 1: List jobs
Write-Host "`n1. Testing GET /api/jobs" -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$baseUrl/api/jobs" -Method Get
    Write-Host "✓ Success" -ForegroundColor Green
    $response | ConvertTo-Json -Depth 10
} catch {
    Write-Host "✗ Failed: $_" -ForegroundColor Red
}

# Test 2: Assign job
Write-Host "`n2. Testing POST /api/jobs/1/assign" -ForegroundColor Yellow
try {
    $body = @{
        inspectorId = 1
        scheduleAt = "2025-03-15T09:00:00+01:00"
    } | ConvertTo-Json
    
    $response = Invoke-RestMethod -Uri "$baseUrl/api/jobs/1/assign" `
        -Method Post `
        -ContentType "application/json" `
        -Body $body
    Write-Host "✓ Success" -ForegroundColor Green
    $response | ConvertTo-Json -Depth 10
} catch {
    Write-Host "✗ Failed: $_" -ForegroundColor Red
    $_.Exception.Response
}

# Test 3: Get inspector schedule
Write-Host "`n3. Testing GET /api/inspectors/1/schedule" -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$baseUrl/api/inspectors/1/schedule" -Method Get
    Write-Host "✓ Success" -ForegroundColor Green
    $response | ConvertTo-Json -Depth 10
} catch {
    Write-Host "✗ Failed: $_" -ForegroundColor Red
}

# Test 4: Complete assignment
Write-Host "`n4. Testing POST /api/assignments/1/complete" -ForegroundColor Yellow
try {
    $body = @{
        assessment = "All checks passed. Minor repairs recommended."
    } | ConvertTo-Json
    
    $response = Invoke-RestMethod -Uri "$baseUrl/api/assignments/1/complete" `
        -Method Post `
        -ContentType "application/json" `
        -Body $body
    Write-Host "✓ Success" -ForegroundColor Green
    $response | ConvertTo-Json -Depth 10
} catch {
    Write-Host "✗ Failed: $_" -ForegroundColor Red
}

Write-Host "`n=== Testing Complete ===" -ForegroundColor Green
```

Run it:
```powershell
.\test-api.ps1
```

---

## Notes

- **Timezone formats**: Use ISO 8601 format with timezone offset (e.g., `2025-03-15T09:00:00+01:00` for Europe/Madrid in winter)
- **Allowed timezones**: `Europe/Madrid`, `America/Mexico_City`, `Europe/London`
- **Database**: Default is SQLite at `var/data.db`
- **Swagger UI**: Best for interactive testing at http://localhost:8000/api/docs
